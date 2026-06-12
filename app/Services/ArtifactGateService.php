<?php

namespace App\Services;

use App\Models\Artifact;
use App\Models\Module;
use App\Models\Project;
use Illuminate\Support\Facades\Config;

class ArtifactGateService
{
    /**
     * Gate 1: No puedes marcar domain_breakdown como done si big_picture no está done
     */
    public function canMarkDomainBreakdownDone(Project $project): bool
    {
        $bigPicture = $project->artifacts()
            ->where('type', 'big_picture')
            ->where('status', 'done')
            ->exists();

        return $bigPicture;
    }

    /**
     * Gate 2: No puedes marcar module_matrix como done si domain_breakdown no está done
     */
    public function canMarkModuleMatrixDone(Project $project): bool
    {
        $domainBreakdown = $project->artifacts()
            ->where('type', 'domain_breakdown')
            ->where('status', 'done')
            ->exists();

        return $domainBreakdown;
    }

    /**
     * Gate 3: No puedes marcar system_architecture como done
     * a menos que haya al menos N módulos validados (default 3)
     */
    public function canMarkSystemArchitectureDone(Project $project): bool
    {
        $minValidatedModules = Config::get('tcg.min_validated_modules', 3);

        $validatedModulesCount = Module::where('project_id', $project->id)
            ->where('status', 'validated')
            ->count();

        return $validatedModulesCount >= $minValidatedModules;
    }

    /**
     * Verificar todas las condiciones para marcar un artifact como done
     */
    public function canMarkAsDone(Artifact $artifact): array
    {
        $project = $artifact->project;
        $can = true;
        $reason = null;

        switch ($artifact->type) {
            case 'domain_breakdown':
                $can = $this->canMarkDomainBreakdownDone($project);
                $reason = 'Big Picture debe estar completado primero';
                break;

            case 'module_matrix':
                $can = $this->canMarkModuleMatrixDone($project);
                $reason = 'Domain Breakdown debe estar completado primero';
                break;

            case 'system_architecture':
                $can = $this->canMarkSystemArchitectureDone($project);
                $min = Config::get('tcg.min_validated_modules', 3);
                $reason = "Se necesitan al menos {$min} módulos validados";
                break;

            default:
                // Otros artifacts no tienen restricciones especiales
                $can = true;
                $reason = null;
                break;
        }

        return [
            'can' => $can,
            'reason' => $reason,
        ];
    }
}

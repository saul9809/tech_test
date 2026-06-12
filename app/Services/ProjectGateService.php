<?php

namespace App\Services;

use App\Models\Project;

class ProjectGateService
{
    /**
     * Gate 4: No puedes mover Project status de discovery → execution a menos que:
     * - strategic_alignment esté done
     * - big_picture esté done
     * - domain_breakdown esté done
     * - module_matrix esté done
     */
    public function canMoveToExecution(Project $project): array
    {
        $requiredArtifacts = [
            'strategic_alignment',
            'big_picture',
            'domain_breakdown',
            'module_matrix',
        ];

        $missingArtifacts = [];

        foreach ($requiredArtifacts as $type) {
            $artifact = $project->artifacts()
                ->where('type', $type)
                ->first();

            if (! $artifact || $artifact->status !== 'done') {
                $missingArtifacts[] = $type;
            }
        }

        $can = empty($missingArtifacts);

        return [
            'can' => $can,
            'missing_artifacts' => $missingArtifacts,
            'reason' => $can ? null : 'Faltan artifacts requeridos: '.implode(', ', $missingArtifacts),
        ];
    }

    /**
     * Verificar si se puede cambiar el status del proyecto
     */
    public function canChangeStatus(Project $project, string $newStatus): array
    {
        // Solo interesa la transición discovery → execution
        if ($project->status === 'discovery' && $newStatus === 'execution') {
            return $this->canMoveToExecution($project);
        }

        // Otras transiciones son libres (draft → discovery, execution → delivered, etc.)
        return [
            'can' => true,
            'reason' => null,
        ];
    }
}

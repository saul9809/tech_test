<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Artifact;
use App\Models\Project;
use App\Services\ArtifactGateService;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ArtifactController extends Controller
{
    protected $artifactGateService;

    protected $auditService;

    public function __construct(ArtifactGateService $artifactGateService, AuditService $auditService)
    {
        $this->artifactGateService = $artifactGateService;
        $this->auditService = $auditService;
    }

    public function index(Project $project)
    {
        Gate::authorize('view-projects');

        $artifacts = $project->artifacts()->with('owner')->get();

        // Agregar información de bloqueo
        $artifacts = $artifacts->map(function ($artifact) {
            $gateCheck = $this->artifactGateService->canMarkAsDone($artifact);
            $artifact->can_be_completed = $gateCheck['can'];
            $artifact->block_reason = $gateCheck['reason'];

            return $artifact;
        });

        return response()->json([
            'success' => true,
            'data' => $artifacts,
        ]);
    }

    public function show(Artifact $artifact)
    {
        Gate::authorize('view-projects');

        return response()->json([
            'success' => true,
            'data' => $artifact->load('project', 'owner'),
        ]);
    }

    public function update(Request $request, Artifact $artifact)
    {
        Gate::authorize('manage-artifacts');

        $validated = $request->validate([
            'owner_user_id' => 'nullable|exists:users,id',
            'content_json' => 'sometimes|array',
            'status' => 'sometimes|in:not_started,in_progress,blocked,done',
        ]);

        $oldData = $artifact->toArray();

        $artifact->update($validated);

        $this->auditService->log(
            auth()->id(),
            'artifact',
            $artifact->id,
            'updated',
            $oldData,
            $artifact->toArray()
        );

        return response()->json([
            'success' => true,
            'data' => $artifact,
            'message' => 'Artifact actualizado exitosamente',
        ]);
    }

    public function markAsDone(Artifact $artifact)
    {
        Gate::authorize('manage-artifacts');

        $gateCheck = $this->artifactGateService->canMarkAsDone($artifact);

        if (! $gateCheck['can']) {
            return response()->json([
                'success' => false,
                'message' => $gateCheck['reason'],
            ], 422);
        }

        $oldStatus = $artifact->status;

        $artifact->status = 'done';
        $artifact->completed_at = now();
        $artifact->save();

        $this->auditService->log(
            auth()->id(),
            'artifact',
            $artifact->id,
            'completed',
            ['status' => $oldStatus],
            ['status' => 'done', 'completed_at' => $artifact->completed_at]
        );

        return response()->json([
            'success' => true,
            'data' => $artifact,
            'message' => 'Artifact marcado como completado',
        ]);
    }

    public function getSchema(string $type)
    {
        Gate::authorize('view-projects');

        $schemas = [
            'strategic_alignment' => [
                'transformation' => 'text',
                'supported_decisions' => 'array',
                'measurable_success' => 'array',
                'out_of_scope' => 'array',
            ],
            'big_picture' => [
                'ecosystem_vision' => 'text',
                'impacted_domains' => 'array',
                'success_definition' => 'text',
            ],
            'domain_breakdown' => [
                'domains' => 'array',
            ],
            'module_matrix' => [
                'modules_overview' => 'array',
            ],
            'module_engineering' => [
                'notes' => 'text',
            ],
            'system_architecture' => [
                'auth_model' => 'text',
                'api_style' => 'text',
                'data_model_notes' => 'text',
                'scalability_notes' => 'text',
            ],
            'phase_scope' => [
                'included_modules' => 'array',
                'excluded_items' => 'array',
                'acceptance_criteria' => 'array',
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $schemas[$type] ?? [],
        ]);
    }
}

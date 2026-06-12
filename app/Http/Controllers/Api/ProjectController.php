<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\ProjectGateService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class ProjectController extends Controller
{
    protected $projectGateService;

    public function __construct(ProjectGateService $projectGateService)
    {
        $this->projectGateService = $projectGateService;
    }

    public function index()
    {
        Gate::authorize('view-projects');

        $projects = Project::with('createdBy')->paginate(15);

        return response()->json([
            'success' => true,
            'data' => $projects,
        ]);
    }

    public function show(Project $project)
    {
        Gate::authorize('view-projects');

        return response()->json([
            'success' => true,
            'data' => $project->load(['artifacts', 'modules']),
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('manage-projects');

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'client_name' => 'required|string|max:255',
            'status' => 'sometimes|in:draft,discovery,execution,delivered',
        ]);

        $project = DB::transaction(function () use ($validated) {
            $project = Project::create([
                'name' => $validated['name'],
                'client_name' => $validated['client_name'],
                'status' => $validated['status'] ?? 'draft',
                'created_by' => auth()->id(),
            ]);

            // ✅ IMPORTANTE: Crear los 7 artifacts automáticamente
            $this->createDefaultArtifacts($project);

            return $project;
        });

        return response()->json([
            'success' => true,
            'data' => $project,
            'message' => 'Proyecto creado exitosamente',
        ], 201);
    }

    public function update(Request $request, Project $project)
    {
        Gate::authorize('manage-projects');

        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'client_name' => 'sometimes|string|max:255',
            'status' => 'sometimes|in:draft,discovery,execution,delivered',
        ]);

        // Verificar reglas de negocio si se está cambiando el status
        if (isset($validated['status']) && $validated['status'] !== $project->status) {
            $canChange = $this->projectGateService->canChangeStatus($project, $validated['status']);

            if (! $canChange['can']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Faltan artifacts requeridos: '.implode(', ', $canChange['missing_artifacts']),
                    'missing_artifacts' => $canChange['missing_artifacts'],
                ], 422);
            }
        }

        $project->update($validated);

        return response()->json([
            'success' => true,
            'data' => $project,
            'message' => 'Proyecto actualizado exitosamente',
        ]);
    }

    public function destroy(Project $project)
    {
        Gate::authorize('delete-project');

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto archivado exitosamente',
        ]);
    }

    public function archive(Project $project)
    {
        Gate::authorize('delete-project');

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Proyecto archivado exitosamente',
        ]);
    }

    /**
     * Crear los 7 artifacts por defecto para un proyecto
     */
    private function createDefaultArtifacts(Project $project)
    {
        $artifactTypes = [
            'strategic_alignment',
            'big_picture',
            'domain_breakdown',
            'module_matrix',
            'module_engineering',
            'system_architecture',
            'phase_scope',
        ];

        foreach ($artifactTypes as $type) {
            $project->artifacts()->create([
                'type' => $type,
                'status' => 'not_started',
                'content_json' => $this->getDefaultContentForType($type),
            ]);
        }
    }

    /**
     * Obtener contenido por defecto para cada tipo de artifact
     */
    private function getDefaultContentForType(string $type): array
    {
        $defaults = [
            'strategic_alignment' => [
                'transformation' => '',
                'supported_decisions' => [],
                'measurable_success' => [],
                'out_of_scope' => [],
            ],
            'big_picture' => [
                'ecosystem_vision' => '',
                'impacted_domains' => [],
                'success_definition' => '',
            ],
            'domain_breakdown' => [
                'domains' => [],
            ],
            'module_matrix' => [
                'modules_overview' => [],
            ],
            'module_engineering' => [
                'notes' => '',
            ],
            'system_architecture' => [
                'auth_model' => '',
                'api_style' => '',
                'data_model_notes' => '',
                'scalability_notes' => '',
            ],
            'phase_scope' => [
                'included_modules' => [],
                'excluded_items' => [],
                'acceptance_criteria' => [],
            ],
        ];

        return $defaults[$type] ?? [];
    }
}

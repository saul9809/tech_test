<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Project;
use App\Services\AuditService;
use App\Services\ModuleValidationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class ModuleController extends Controller
{
    protected $moduleValidationService;

    protected $auditService;

    public function __construct(ModuleValidationService $moduleValidationService, AuditService $auditService)
    {
        $this->moduleValidationService = $moduleValidationService;
        $this->auditService = $auditService;
    }

    /**
     * Listar módulos de un proyecto
     */
    public function index(Project $project)
    {
        Gate::authorize('view-modules');

        $modules = $project->modules()->paginate(15);

        // Agregar información de validación a cada módulo
        $modules->getCollection()->transform(function ($module) {
            $validation = $this->moduleValidationService->canBeValidated($module);
            $module->can_be_validated = $validation['can'];
            $module->validation_errors = $validation['errors'];

            return $module;
        });

        return response()->json([
            'success' => true,
            'data' => $modules,
        ]);
    }

    /**
     * Mostrar un módulo específico
     */
    public function show(Module $module)
    {
        Gate::authorize('view-modules');

        return response()->json([
            'success' => true,
            'data' => $module->load('project'),
        ]);
    }

    /**
     * Crear un nuevo módulo
     */
    public function store(Request $request, Project $project)
    {
        Gate::authorize('edit-modules');

        $validated = $request->validate([
            'domain' => 'required|string|max:255',
            'name' => 'required|string|max:255',
            'objective' => 'nullable|string',
            'inputs' => 'nullable|array',
            'data_structure' => 'nullable|array',
            'logic_rules' => 'nullable|string',
            'outputs' => 'nullable|array',
            'responsibility' => 'nullable|string',
            'failure_scenarios' => 'nullable|string',
            'audit_trail_requirements' => 'nullable|string',
            'dependencies' => 'nullable|array',
            'version_note' => 'nullable|string|max:255',
        ]);

        $validated['project_id'] = $project->id;
        $validated['status'] = 'draft';

        $module = Module::create($validated);

        // Registrar auditoría
        $this->auditService->log(
            auth()->id(),
            'module',
            $module->id,
            'created',
            null,
            $module->toArray()
        );

        return response()->json([
            'success' => true,
            'data' => $module,
            'message' => 'Módulo creado exitosamente',
        ], 201);
    }

    /**
     * Actualizar un módulo
     */
    public function update(Request $request, Module $module)
    {
        Gate::authorize('edit-modules');

        $validated = $request->validate([
            'domain' => 'sometimes|string|max:255',
            'name' => 'sometimes|string|max:255',
            'objective' => 'nullable|string',
            'inputs' => 'nullable|array',
            'data_structure' => 'nullable|array',
            'logic_rules' => 'nullable|string',
            'outputs' => 'nullable|array',
            'responsibility' => 'nullable|string',
            'failure_scenarios' => 'nullable|string',
            'audit_trail_requirements' => 'nullable|string',
            'dependencies' => 'nullable|array',
            'version_note' => 'nullable|string|max:255',
        ]);

        // Guardar estado anterior
        $oldData = $module->toArray();

        $module->update($validated);

        // Registrar auditoría
        $this->auditService->log(
            auth()->id(),
            'module',
            $module->id,
            'updated',
            $oldData,
            $module->toArray()
        );

        return response()->json([
            'success' => true,
            'data' => $module,
            'message' => 'Módulo actualizado exitosamente',
        ]);
    }

    /**
     * Validar un módulo (cambiar status draft → validated)
     */
    public function validateModule(Module $module)
    {
        Gate::authorize('validate-modules');

        $result = $this->moduleValidationService->validate($module);

        if ($result['success']) {
            // Registrar auditoría
            $this->auditService->log(
                auth()->id(),
                'module',
                $module->id,
                'validated',
                ['status' => $result['old_status']],
                ['status' => $result['new_status']]
            );

            return response()->json([
                'success' => true,
                'data' => $module,
                'message' => $result['message'],
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => $result['message'],
            'errors' => $result['errors'],
        ], 422);
    }

    /**
     * Eliminar un módulo
     */
    public function destroy(Module $module)
    {
        Gate::authorize('edit-modules');

        $module->delete();

        $this->auditService->log(
            auth()->id(),
            'module',
            $module->id,
            'deleted',
            $module->toArray(),
            null
        );

        return response()->json([
            'success' => true,
            'message' => 'Módulo eliminado exitosamente',
        ]);
    }
}

<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Project;
use App\Services\AuditService;
use Illuminate\Support\Facades\Gate;

class AuditController extends Controller
{
    protected $auditService;

    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Obtener timeline de auditoría para un proyecto
     */
    public function getProjectTimeline(Project $project)
    {
        Gate::authorize('view-projects');

        $timeline = $this->auditService->getProjectTimeline($project->id);

        return response()->json([
            'success' => true,
            'data' => $timeline,
        ]);
    }
}

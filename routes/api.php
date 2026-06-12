<?php

use App\Http\Controllers\Api\ArtifactController;
use App\Http\Controllers\Api\AuditController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ModuleController;
use App\Http\Controllers\Api\ProjectController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/user', [AuthController::class, 'user']);

        // -- Projects
        Route::apiResource('projects', ProjectController::class);
        Route::patch('projects/{project}/archive', [ProjectController::class, 'archive']);
        // Artifacts
        Route::get('projects/{project}/artifacts', [ArtifactController::class, 'index']);
        Route::get('artifacts/{artifact}', [ArtifactController::class, 'show']);
        Route::put('artifacts/{artifact}', [ArtifactController::class, 'update']);
        Route::patch('artifacts/{artifact}/mark-done', [ArtifactController::class, 'markAsDone']);
        Route::get('artifacts/schema/{type}', [ArtifactController::class, 'getSchema']);
        // -- Modules
        Route::get('projects/{project}/modules', [ModuleController::class, 'index']);
        Route::post('projects/{project}/modules', [ModuleController::class, 'store']);
        Route::get('modules/{module}', [ModuleController::class, 'show']);
        Route::put('modules/{module}', [ModuleController::class, 'update']);
        Route::post('modules/{module}/validate', [ModuleController::class, 'validateModule']);
        Route::delete('modules/{module}', [ModuleController::class, 'destroy']);
    });
    // Audit
    Route::get('projects/{project}/audit', [AuditController::class, 'getProjectTimeline']);
    Route::get('/users', [UserController::class, 'index']);
});

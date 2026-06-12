<?php

namespace Tests\Feature;

use App\Models\Artifact;
use App\Models\Module;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewer_cannot_edit_modules_or_artifacts()
    {
        $viewer = User::factory()->create(['role' => 'viewer']);
        $pm = User::factory()->create(['role' => 'pm']);

        $project = Project::factory()->create(['created_by' => $pm->id]);
        $artifact = Artifact::factory()->create(['project_id' => $project->id]);
        $module = Module::factory()->create(['project_id' => $project->id]);

        $this->actingAs($viewer);

        // Try to update artifact - Debe dar 403 (no puede editar)
        $response = $this->putJson("/api/v1/artifacts/{$artifact->id}", [
            'status' => 'in_progress',
        ]);
        $response->assertStatus(403);

        // Try to update module - Debe dar 403 (no puede editar)
        $response = $this->putJson("/api/v1/modules/{$module->id}", [
            'name' => 'New name',
        ]);
        $response->assertStatus(403);

        // Try to validate module - Debe dar 403 (no puede validar)
        $response = $this->postJson("/api/v1/modules/{$module->id}/validate");
        $response->assertStatus(403);

        // Try to create project - Debe dar 403 (no puede crear)
        $response = $this->postJson('/api/v1/projects', [
            'name' => 'New Project',
            'client_name' => 'New Client',
        ]);
        $response->assertStatus(403);

        // Viewer CAN view projects - Debe dar 200 (solo lectura)
        $response = $this->getJson('/api/v1/projects');
        $response->assertStatus(200);

        // Viewer CAN view artifacts - Debe dar 200 (solo lectura)
        $response = $this->getJson("/api/v1/projects/{$project->id}/artifacts");
        $response->assertStatus(200);

        // Viewer CAN view modules - Debe dar 200 (solo lectura)
        // NOTA: Si tu API da 403, cambia a 403 o ajusta el Gate
        $response = $this->getJson("/api/v1/projects/{$project->id}/modules");

        // Si tu política permite ver módulos a viewers, debe ser 200
        // Si no, ajusta según tu lógica
        $response->assertStatus(200); // O 403 si no pueden ver
    }
}

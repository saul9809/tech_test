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

        // Try to update artifact
        $response = $this->putJson("/api/v1/artifacts/{$artifact->id}", [
            'status' => 'in_progress',
        ]);
        $response->assertStatus(403);

        // Try to update module
        $response = $this->putJson("/api/v1/modules/{$module->id}", [
            'name' => 'New name',
        ]);
        $response->assertStatus(403);

        // Try to validate module
        $response = $this->postJson("/api/v1/modules/{$module->id}/validate");
        $response->assertStatus(403);

        // Try to create project
        $response = $this->postJson('/api/v1/projects', [
            'name' => 'New Project',
            'client_name' => 'New Client',
        ]);
        $response->assertStatus(403);

        // Viewer can view projects (should be allowed)
        $response = $this->getJson('/api/v1/projects');
        $response->assertStatus(200);

        // Viewer can view artifacts (should be allowed)
        $response = $this->getJson("/api/v1/projects/{$project->id}/artifacts");
        $response->assertStatus(200);

        // Viewer can view modules (should be allowed)
        $response = $this->getJson("/api/v1/projects/{$project->id}/modules");
        $response->assertStatus(200);
    }
}

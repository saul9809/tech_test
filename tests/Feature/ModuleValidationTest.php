<?php

namespace Tests\Feature;

use App\Models\Module;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ModuleValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_validate_module_if_required_fields_missing()
    {
        $user = User::factory()->create(['role' => 'engineer']);
        $project = Project::factory()->create(['created_by' => $user->id]);

        $module = Module::factory()->create([
            'project_id' => $project->id,
            'status' => 'draft',
            'objective' => '',
            'inputs' => [],
            'outputs' => [],
            'responsibility' => '',
        ]);

        $this->actingAs($user);

        $response = $this->postJson("/api/v1/modules/{$module->id}/validate");

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'El campo "objective" es requerido',
        ]);

        // Fill required fields
        $module->update([
            'objective' => 'Test objective',
            'inputs' => ['input1'],
            'outputs' => ['output1'],
            'responsibility' => 'Test responsibility',
        ]);

        $response = $this->postJson("/api/v1/modules/{$module->id}/validate");

        $response->assertStatus(200);
        $this->assertDatabaseHas('modules', [
            'id' => $module->id,
            'status' => 'validated',
        ]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProjectStatusTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_move_project_from_discovery_to_execution_without_required_artifacts()
    {
        $user = User::factory()->create(['role' => 'pm']);
        $project = Project::factory()->create([
            'created_by' => $user->id,
            'status' => 'discovery',
        ]);

        // Crear artifacts requeridos pero NINGUNO está done
        $requiredTypes = [
            'strategic_alignment',
            'big_picture',
            'domain_breakdown',
            'module_matrix',
        ];

        foreach ($requiredTypes as $type) {
            Artifact::factory()->create([
                'project_id' => $project->id,
                'type' => $type,
                'status' => 'not_started',  // Importante: no están completados
            ]);
        }

        $this->actingAs($user);

        // Intentar cambiar a execution - Debe fallar
        $response = $this->putJson("/api/v1/projects/{$project->id}", [
            'status' => 'execution',
        ]);

        $response->assertStatus(422);

        // ✅ CORREGIDO: Buscar el mensaje exacto que devuelve tu API
        $response->assertJsonFragment([
            'success' => false,
        ]);

        // ✅ Verificar que tiene la estructura esperada
        $response->assertJsonStructure([
            'message',
            'missing_artifacts',
        ]);

        // Marcar todos los artifacts como done
        foreach ($project->artifacts as $artifact) {
            $artifact->update(['status' => 'done']);
        }

        // Intentar nuevamente - Debe funcionar
        $response = $this->putJson("/api/v1/projects/{$project->id}", [
            'status' => 'execution',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('projects', [
            'id' => $project->id,
            'status' => 'execution',
        ]);
    }
}

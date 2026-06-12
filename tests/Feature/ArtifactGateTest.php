<?php

namespace Tests\Feature;

use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArtifactGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_cannot_mark_domain_breakdown_as_done_if_big_picture_not_done()
    {
        $user = User::factory()->create(['role' => 'pm']);
        $project = Project::factory()->create(['created_by' => $user->id]);

        // Create artifacts
        $bigPicture = Artifact::factory()->create([
            'project_id' => $project->id,
            'type' => 'big_picture',
            'status' => 'in_progress',
        ]);

        $domainBreakdown = Artifact::factory()->create([
            'project_id' => $project->id,
            'type' => 'domain_breakdown',
            'status' => 'in_progress',
        ]);

        $this->actingAs($user);

        // Try to mark domain_breakdown as done
        $response = $this->patchJson("/api/v1/artifacts/{$domainBreakdown->id}/mark-done");

        $response->assertStatus(422);
        $response->assertJsonFragment([
            'reason' => 'Big Picture debe estar completado primero',
        ]);

        // Now mark big_picture as done
        $bigPicture->update(['status' => 'done']);

        // Try again
        $response = $this->patchJson("/api/v1/artifacts/{$domainBreakdown->id}/mark-done");

        $response->assertStatus(200);
        $this->assertDatabaseHas('artifacts', [
            'id' => $domainBreakdown->id,
            'status' => 'done',
        ]);
    }
}

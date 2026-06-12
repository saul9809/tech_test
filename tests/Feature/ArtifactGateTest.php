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

        $response = $this->patchJson("/api/v1/artifacts/{$domainBreakdown->id}/mark-done");

        $response->assertStatus(422);

        $bigPicture->update(['status' => 'done']);

        $response = $this->patchJson("/api/v1/artifacts/{$domainBreakdown->id}/mark-done");

        $response->assertStatus(200);
        $this->assertDatabaseHas('artifacts', [
            'id' => $domainBreakdown->id,
            'status' => 'done',
        ]);
    }
}

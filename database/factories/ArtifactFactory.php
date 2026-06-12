<?php

namespace Database\Factories;

use App\Models\Artifact;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class ArtifactFactory extends Factory
{
    protected $model = Artifact::class;

    public function definition(): array
    {
        $types = [
            'strategic_alignment',
            'big_picture',
            'domain_breakdown',
            'module_matrix',
            'module_engineering',
            'system_architecture',
            'phase_scope',
        ];

        return [
            'project_id' => Project::factory(),
            'type' => fake()->randomElement($types),
            'status' => fake()->randomElement(['not_started', 'in_progress', 'blocked', 'done']),
            'owner_user_id' => User::factory(),
            'content_json' => [],
            'completed_at' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ModuleFactory extends Factory
{
    protected $model = Module::class;

    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'domain' => fake()->word(),
            'name' => fake()->words(3, true),
            'status' => fake()->randomElement(['draft', 'validated', 'ready_for_build']),
            'objective' => fake()->sentence(),
            'inputs' => [fake()->word(), fake()->word()],
            'data_structure' => json_encode(['type' => 'object']),
            'logic_rules' => fake()->paragraph(),
            'outputs' => [fake()->word(), fake()->word()],
            'responsibility' => fake()->sentence(),
            'failure_scenarios' => fake()->paragraph(),
            'audit_trail_requirements' => fake()->sentence(),
            'dependencies' => [],
            'version_note' => 'v1.0.0',
            'created_at' => now(),
            'updated_at' => now(),
        ];
    }
}

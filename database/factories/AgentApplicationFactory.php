<?php

namespace Database\Factories;

use App\Enums\AgentApplicationStatus;
use App\Models\AgentApplication;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AgentApplication>
 */
class AgentApplicationFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'status' => AgentApplicationStatus::Pending,
            'motivation' => fake()->paragraph(),
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ];
    }
}

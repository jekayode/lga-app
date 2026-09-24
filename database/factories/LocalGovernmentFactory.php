<?php

namespace Database\Factories;

use App\Models\LocalGovernment;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<LocalGovernment>
 */
class LocalGovernmentFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->unique()->city(),
            'code' => fake()->unique()->numerify('##'),
            'state' => 'Lagos',
            'is_active' => true,
        ];
    }
}

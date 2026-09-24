<?php

namespace Database\Factories;

use App\Models\LocalGovernment;
use App\Models\Ward;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Ward>
 */
class WardFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'local_government_id' => LocalGovernment::factory(),
            'name' => fake()->unique()->streetName().' Ward',
            'code' => fake()->numerify('##'),
            'is_active' => true,
        ];
    }
}

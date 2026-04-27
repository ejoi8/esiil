<?php

namespace Database\Factories;

use App\Models\Branch;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Branch>
 */
class BranchFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'legacy_id' => null,
            'name' => fake()->unique()->company(),
            'code' => fake()->unique()->bothify('BR-###'),
            'is_active' => true,
        ];
    }
}

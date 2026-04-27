<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Participant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Participant>
 */
class ParticipantFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'full_name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'nokp' => fake()->unique()->numerify('############'),
            'phone' => fake()->optional()->phoneNumber(),
            'branch_id' => Branch::factory(),
            'membership_status' => fake()->randomElement(['member', 'non_member']),
            'membership_notes' => fake()->optional()->sentence(),
        ];
    }
}

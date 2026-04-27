<?php

namespace Database\Factories;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Services\Certificates\RegistrationCertificateIssuer;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

/**
 * @extends Factory<Registration>
 */
class RegistrationFactory extends Factory
{
    public function configure(): static
    {
        return $this->afterCreating(function (Registration $registration): void {
            if ($registration->certificate_type !== null) {
                return;
            }

            app(RegistrationCertificateIssuer::class)->issueFor($registration);
        });
    }

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $registeredAt = Carbon::instance(fake()->dateTimeBetween('-2 months', 'now'));

        return [
            'legacy_id' => null,
            'event_id' => Event::factory(),
            'participant_id' => Participant::factory(),
            'registered_at' => $registeredAt,
            'attendance_status' => fake()->randomElement(['registered', 'attended', 'no_show']),
            'checked_in_at' => fake()->optional()->dateTimeBetween($registeredAt, 'now'),
            'completed_at' => fake()->optional()->dateTimeBetween($registeredAt, 'now'),
            'source' => fake()->randomElement(['public_form', 'admin']),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}

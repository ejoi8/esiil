<?php

namespace Database\Seeders;

use App\Models\Event;
use App\Models\Participant;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'admin@example.com'],
            ['name' => 'Demo Administrator', 'password' => 'password'],
        );

        $events = Event::factory()
            ->count(5)
            ->for($admin, 'creator')
            ->create();

        $participants = Participant::factory()->count(20)->create();

        $events->each(function (Event $event) use ($participants): void {
            $selectedParticipants = $participants->random(fake()->numberBetween(4, 8));

            $selectedParticipants->each(function (Participant $participant) use ($event): void {
                Registration::factory()
                    ->for($event)
                    ->for($participant)
                    ->create();
            });
        });
    }
}

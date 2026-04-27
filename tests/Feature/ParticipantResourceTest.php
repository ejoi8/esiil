<?php

use App\Filament\Resources\Participants\Pages\ListParticipants;
use App\Models\Branch;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters participants by membership status on the participants list page', function () {
    $this->actingAs(User::factory()->create());

    $member = Participant::factory()->create([
        'membership_status' => 'member',
    ]);
    $nonMember = Participant::factory()->create([
        'membership_status' => 'non_member',
    ]);

    Livewire::test(ListParticipants::class)
        ->filterTable('membership_status', 'member')
        ->assertCanSeeTableRecords([$member])
        ->assertCanNotSeeTableRecords([$nonMember]);
});

it('keeps the branch column available and removes the placeholder email indicator on the participants list page', function () {
    $this->actingAs(User::factory()->create());

    $branch = Branch::factory()->create([
        'name' => 'Cawangan Putrajaya',
    ]);
    $participant = Participant::factory()->for($branch)->create();

    Livewire::test(ListParticipants::class)
        ->assertSuccessful()
        ->assertCanSeeTableRecords([$participant])
        ->assertTableColumnExists('branch.name')
        ->assertDontSee('Placeholder');
});

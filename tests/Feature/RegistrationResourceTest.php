<?php

use App\Filament\Resources\Participants\ParticipantResource;
use App\Filament\Resources\Participants\RelationManagers\RegistrationsRelationManager as ParticipantRegistrationsRelationManager;
use App\Filament\Resources\Registrations\Pages\ListRegistrations;
use App\Models\Registration;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters registrations by source on the registrations list page', function () {
    $this->actingAs(User::factory()->create());

    $adminRegistration = Registration::factory()->create([
        'source' => 'admin',
    ]);
    $publicRegistration = Registration::factory()->create([
        'source' => 'public_form',
    ]);

    Livewire::test(ListRegistrations::class)
        ->filterTable('source', 'admin')
        ->assertCanSeeTableRecords([$adminRegistration])
        ->assertCanNotSeeTableRecords([$publicRegistration]);
});

it('downloads a registration certificate from the admin route', function () {
    $this->actingAs(User::factory()->create());

    $registration = Registration::factory()->create();

    $this->get(route('auth.registrations.certificate', $registration))
        ->assertSuccessful()
        ->assertHeader('content-type', 'application/pdf');
});

it('registers participant registrations as a relation manager', function () {
    expect(ParticipantResource::getRelations())->toBe([
        ParticipantRegistrationsRelationManager::class,
    ])
        ->and(ParticipantRegistrationsRelationManager::isLazy())->toBeFalse();
});

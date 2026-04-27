<?php

use App\Filament\Resources\Events\Pages\ListEvents;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

it('filters events by certificate type on the events list page', function () {
    $this->actingAs(User::factory()->create());

    $participationEvent = Event::factory()->create([
        'certificate_type' => 'participation_certificate',
    ]);
    $attendanceEvent = Event::factory()->create([
        'certificate_type' => 'attendance_slip',
    ]);

    Livewire::test(ListEvents::class)
        ->filterTable('certificate_type', 'participation_certificate')
        ->assertCanSeeTableRecords([$participationEvent])
        ->assertCanNotSeeTableRecords([$attendanceEvent]);
});

it('filters events by certificate template on the events list page', function () {
    $this->actingAs(User::factory()->create());

    $templateA = CertificateTemplate::factory()->create([
        'type' => 'participation_certificate',
        'name' => 'Template A',
    ]);
    $templateB = CertificateTemplate::factory()->create([
        'type' => 'participation_certificate',
        'name' => 'Template B',
    ]);

    $matchingEvent = Event::factory()->for($templateA, 'certificateTemplate')->create([
        'certificate_type' => 'participation_certificate',
        'template_key' => $templateA->key,
    ]);
    $otherEvent = Event::factory()->for($templateB, 'certificateTemplate')->create([
        'certificate_type' => 'participation_certificate',
        'template_key' => $templateB->key,
    ]);

    Livewire::test(ListEvents::class)
        ->filterTable('certificate_template_id', $templateA->id)
        ->assertCanSeeTableRecords([$matchingEvent])
        ->assertCanNotSeeTableRecords([$otherEvent]);
});

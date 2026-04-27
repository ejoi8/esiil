<?php

use App\Enums\CertificateType;
use App\Enums\EventStatus;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\Registration;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('casts event status and certificate type to enums', function () {
    $event = Event::factory()->create([
        'status' => EventStatus::Published,
        'certificate_type' => CertificateType::ParticipationCertificate,
    ])->fresh();

    expect($event->status)->toBe(EventStatus::Published)
        ->and($event->certificate_type)->toBe(CertificateType::ParticipationCertificate);
});

it('creates events with matching certificate type and template metadata', function () {
    $event = Event::factory()->create()->fresh(['certificateTemplate']);

    expect($event->status)->toBeInstanceOf(EventStatus::class)
        ->and($event->certificate_type)->toBeInstanceOf(CertificateType::class)
        ->and($event->certificateTemplate)->not->toBeNull()
        ->and($event->certificateTemplate->type)->toBe($event->certificate_type)
        ->and($event->template_key)->toBe($event->certificate_type->templateKey());
});

it('creates registrations with matching certificate type and template metadata', function () {
    $registration = Registration::factory()->create()->fresh(['certificateTemplate']);

    expect($registration->certificate_type)->toBeInstanceOf(CertificateType::class)
        ->and($registration->certificateTemplate)->not->toBeNull()
        ->and($registration->certificateTemplate->type)->toBe($registration->certificate_type)
        ->and($registration->certificate_template_key)->toBe($registration->certificate_type->templateKey());
});

it('casts certificate template type to an enum', function () {
    $template = CertificateTemplate::factory()->create([
        'type' => CertificateType::AttendanceSlip,
    ])->fresh();

    expect($template->type)->toBe(CertificateType::AttendanceSlip);
});

it('resolves template keys and type matches through certificate template helpers', function () {
    $template = CertificateTemplate::factory()->create([
        'key' => 'attendance-template',
        'type' => CertificateType::AttendanceSlip,
    ]);

    expect(CertificateTemplate::keyFor($template->id))->toBe('attendance-template')
        ->and(CertificateTemplate::matchesDocumentType($template->id, CertificateType::AttendanceSlip))->toBeTrue()
        ->and(CertificateTemplate::matchesDocumentType($template->id, CertificateType::ParticipationCertificate))->toBeFalse()
        ->and(CertificateTemplate::keyFor(null))->toBeNull();
});

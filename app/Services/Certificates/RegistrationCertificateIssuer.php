<?php

namespace App\Services\Certificates;

use App\Enums\CertificateType;
use App\Models\Registration;
use Illuminate\Support\Str;

class RegistrationCertificateIssuer
{
    public function issueFor(Registration $registration): Registration
    {
        $registration->loadMissing('event');

        $event = $registration->event;
        $type = CertificateType::fromMixed($event->certificate_type) ?? CertificateType::ParticipationCertificate;

        $registration->forceFill([
            'certificate_type' => $type,
            'certificate_template_id' => $event->certificate_template_id,
            'certificate_template_key' => $event->template_key ?: $type->templateKey(),
            'cert_serial_number' => $registration->cert_serial_number ?: $this->serialNumber(),
            'certificate_metadata' => array_replace(
                is_array($registration->certificate_metadata) ? $registration->certificate_metadata : [],
                [
                    'source' => data_get($registration->certificate_metadata, 'source', $registration->source ?: 'public_registration'),
                ],
            ),
        ])->save();

        return $registration;
    }

    protected function serialNumber(): string
    {
        do {
            $serialNumber = strtoupper(Str::random(12));
        } while (Registration::query()->where('cert_serial_number', $serialNumber)->exists());

        return $serialNumber;
    }
}

<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Services\Certificates\RegistrationCertificateIssuer;
use Filament\Resources\Pages\CreateRecord;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function afterCreate(): void
    {
        app(RegistrationCertificateIssuer::class)->issueFor($this->record);
    }
}

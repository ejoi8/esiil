<?php

namespace App\Filament\Resources\CertificateTemplates\Pages;

use App\Filament\Resources\CertificateTemplates\CertificateTemplateResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCertificateTemplate extends CreateRecord
{
    protected static string $resource = CertificateTemplateResource::class;

    protected static bool $canCreateAnother = false;

    protected function getRedirectUrl(): string
    {
        return $this->getResourceUrl('designer', ['record' => $this->getRecord()]);
    }
}

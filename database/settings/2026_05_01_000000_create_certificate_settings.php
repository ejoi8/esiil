<?php

use App\Enums\CertificatePdfRenderer;
use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('certificates.renderer', CertificatePdfRenderer::Dompdf->value);
    }
};

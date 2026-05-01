<?php

namespace App\Settings;

use App\Enums\CertificatePdfRenderer;
use Spatie\LaravelSettings\Settings;

class CertificateSettings extends Settings
{
    public string $renderer = CertificatePdfRenderer::Dompdf->value;

    public static function group(): string
    {
        return 'certificates';
    }
}

<?php

namespace App\Services\Certificates;

use App\Models\Registration;
use Symfony\Component\HttpFoundation\StreamedResponse;

class StoredCertificatePdf
{
    public function __construct(
        protected PdfmeCertificateRenderer $renderer,
    ) {}

    public function download(Registration $registration): StreamedResponse
    {
        $pdf = $this->renderer->render($registration);

        if ($registration->certificate_issued_at === null) {
            $registration->forceFill([
                'certificate_issued_at' => now(),
            ])->save();
        }

        return response()->streamDownload(
            fn (): int => print $pdf,
            $this->renderer->fileName($registration),
            ['Content-Type' => 'application/pdf'],
        );
    }
}

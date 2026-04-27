<?php

namespace App\Http\Controllers;

use App\Models\Registration;
use App\Services\Certificates\StoredCertificatePdf;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AdminRegistrationCertificateDownloadController extends Controller
{
    public function __invoke(Registration $registration, StoredCertificatePdf $storedCertificatePdf): StreamedResponse
    {
        $registration->load('certificateTemplate', 'event.certificateTemplate', 'participant');

        abort_unless($registration->certificate_type !== null, 404);

        return $storedCertificatePdf->download($registration);
    }
}

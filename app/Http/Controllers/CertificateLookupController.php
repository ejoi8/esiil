<?php

namespace App\Http\Controllers;

use App\Http\Requests\LookupCertificateRequest;
use App\Models\Participant;
use App\Models\Registration;
use App\Services\Certificates\StoredCertificatePdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CertificateLookupController extends Controller
{
    public function index(): View
    {
        return view('certificate-lookup.index');
    }

    public function search(LookupCertificateRequest $request): RedirectResponse
    {
        $participant = Participant::query()
            ->where('nokp', $request->nokp())
            ->first();

        if ($participant === null) {
            $request->session()->forget('certificate_lookup_participant_id');

            return back()
                ->withInput()
                ->withErrors([
                    'nokp' => 'No certificate record was found for that No. KP.',
                ]);
        }

        $request->session()->put('certificate_lookup_participant_id', $participant->id);

        return redirect()->route('certificate-lookup.result');
    }

    public function result(): View|RedirectResponse
    {
        $participantId = session('certificate_lookup_participant_id');

        if ($participantId === null) {
            return redirect()->route('certificate-lookup.index');
        }

        $participant = Participant::query()
            ->with([
                'registrations' => fn ($query) => $query
                    ->with(['event'])
                    ->orderByDesc('registered_at'),
            ])
            ->find($participantId);

        if ($participant === null) {
            session()->forget('certificate_lookup_participant_id');

            return redirect()->route('certificate-lookup.index');
        }

        return view('certificate-lookup.result', [
            'participant' => $participant,
            'registrations' => $participant->registrations,
        ]);
    }

    public function download(Registration $registration, StoredCertificatePdf $storedCertificatePdf): StreamedResponse
    {
        $registration->loadMissing('certificateTemplate', 'event.certificateTemplate', 'participant');

        abort_unless(
            session('certificate_lookup_participant_id') === $registration->participant_id,
            403,
        );

        return $storedCertificatePdf->download($registration);
    }
}

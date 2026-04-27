<!DOCTYPE html>
<html lang="ms">
<head>
    <meta charset="utf-8">
    <title>Pengesahan Pendaftaran</title>
</head>
<body style="font-family: Arial, sans-serif; color: #111827; line-height: 1.6;">
    <p>Assalamualaikum dan salam sejahtera {{ $participant->full_name }},</p>

    <p>
        Pendaftaran anda untuk program berikut telah berjaya diterima.
    </p>

    <table role="presentation" cellpadding="0" cellspacing="0" style="margin: 20px 0; width: 100%; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; width: 150px; color: #4b5563;">Program</td>
            <td style="padding: 8px 0; font-weight: 600;">{{ $event->title }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #4b5563;">Tarikh</td>
            <td style="padding: 8px 0;">
                {{ $event->starts_at?->format('d/m/Y') ?? '-' }}
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #4b5563;">Masa</td>
            <td style="padding: 8px 0;">
                {{ $event->start_time_text ?: $event->starts_at?->format('g:i A') ?: '-' }}
                @if ($event->end_time_text || $event->ends_at)
                    - {{ $event->end_time_text ?: $event->ends_at?->format('g:i A') }}
                @endif
            </td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #4b5563;">Tempat</td>
            <td style="padding: 8px 0;">{{ $event->venue ?: '-' }}</td>
        </tr>
        <tr>
            <td style="padding: 8px 0; color: #4b5563;">Penganjur</td>
            <td style="padding: 8px 0;">{{ $event->organizer_name ?: '-' }}</td>
        </tr>
    </table>

    <p>
        Sila simpan emel ini sebagai rujukan pendaftaran anda.
    </p>

    <p>Terima kasih.</p>
</body>
</html>

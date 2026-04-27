<?php

namespace App\Models;

use App\Enums\CertificateType;
use Database\Factories\RegistrationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable([
    'legacy_id',
    'event_id',
    'participant_id',
    'registered_at',
    'attendance_status',
    'checked_in_at',
    'completed_at',
    'source',
    'remarks',
    'certificate_type',
    'certificate_template_id',
    'certificate_template_key',
    'certificate_template_snapshot',
    'cert_serial_number',
    'certificate_file_path',
    'certificate_issued_at',
    'certificate_metadata',
])]
class Registration extends Model
{
    /** @use HasFactory<RegistrationFactory> */
    use HasFactory, SoftDeletes;

    protected function casts(): array
    {
        return [
            'registered_at' => 'datetime',
            'checked_in_at' => 'datetime',
            'completed_at' => 'datetime',
            'certificate_type' => CertificateType::class,
            'certificate_template_snapshot' => 'array',
            'certificate_issued_at' => 'datetime',
            'certificate_metadata' => 'array',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    public function participant(): BelongsTo
    {
        return $this->belongsTo(Participant::class);
    }

    public function certificateTemplate(): BelongsTo
    {
        return $this->belongsTo(CertificateTemplate::class);
    }
}

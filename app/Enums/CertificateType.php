<?php

namespace App\Enums;

enum CertificateType: string
{
    case AttendanceSlip = 'attendance_slip';
    case ParticipationCertificate = 'participation_certificate';

    public function label(): string
    {
        return match ($this) {
            self::AttendanceSlip => 'Attendance Slip',
            self::ParticipationCertificate => 'Participation Certificate',
        };
    }

    public function templateKey(): string
    {
        return match ($this) {
            self::AttendanceSlip => 'default-attendance',
            self::ParticipationCertificate => 'default-participation',
        };
    }

    public function documentTitle(): string
    {
        return match ($this) {
            self::AttendanceSlip => 'Slip Kehadiran',
            self::ParticipationCertificate => 'Sijil Penyertaan',
        };
    }

    public function bodyIntro(): string
    {
        return match ($this) {
            self::AttendanceSlip => 'telah menyertai program berikut',
            self::ParticipationCertificate => 'telah menyertai program berikut',
        };
    }

    public function legacyTemplateKey(): string
    {
        return match ($this) {
            self::AttendanceSlip => 'legacy-slip',
            self::ParticipationCertificate => 'legacy-certificate',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function (array $options, self $type): array {
            $options[$type->value] = $type->label();

            return $options;
        }, []);
    }

    public static function fromMixed(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (! is_string($value)) {
            return null;
        }

        return self::tryFrom($value);
    }

    public static function labelFor(mixed $value): string
    {
        return self::fromMixed($value)?->label() ?? (string) $value;
    }

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_map(fn (self $type): string => $type->value, self::cases());
    }
}

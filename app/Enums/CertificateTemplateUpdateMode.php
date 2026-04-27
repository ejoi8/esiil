<?php

namespace App\Enums;

enum CertificateTemplateUpdateMode: string
{
    case UseLatestTemplate = 'use_latest_template';
    case LockIssuedSnapshot = 'lock_issued_snapshot';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $mode): array => [$mode->value => $mode->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::UseLatestTemplate => 'Use latest template',
            self::LockIssuedSnapshot => 'Lock issued snapshot',
        };
    }

    public static function labelFor(mixed $value): string
    {
        return self::fromMixed($value)?->label() ?? '-';
    }

    public static function fromMixed(mixed $value): ?self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value)) {
            return self::tryFrom($value);
        }

        return null;
    }
}

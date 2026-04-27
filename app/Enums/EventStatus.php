<?php

namespace App\Enums;

enum EventStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case Completed = 'completed';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::Completed => 'Completed',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_reduce(self::cases(), function (array $options, self $status): array {
            $options[$status->value] = $status->label();

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
}

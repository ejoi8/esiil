<?php

namespace App\Enums;

enum CertificatePdfRenderer: string
{
    case Pdfme = 'pdfme';
    case Dompdf = 'dompdf';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $renderer): array => [$renderer->value => $renderer->label()])
            ->all();
    }

    public function label(): string
    {
        return match ($this) {
            self::Pdfme => 'pdfme (exact designer output)',
            self::Dompdf => 'Dompdf (server-friendly fallback)',
        };
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

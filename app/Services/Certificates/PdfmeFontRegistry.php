<?php

namespace App\Services\Certificates;

class PdfmeFontRegistry
{
    public const BODY_FONT = 'CormorantGaramond';

    public const TITLE_FONT = 'GreatVibes';

    /**
     * @return array<string, array{path:string, public_path:string, fallback:bool, subset:bool}>
     */
    public function definitions(): array
    {
        /** @var array<string, array{path:string, public_path:string, fallback:bool, subset:bool}> $definitions */
        $definitions = config('certificates.pdfme.fonts', []);

        return $definitions;
    }

    /**
     * @return array<string, array{url:string, fallback:bool, subset:bool}>
     */
    public function forDesigner(): array
    {
        $fonts = [];

        foreach ($this->definitions() as $name => $definition) {
            $fonts[$name] = [
                'url' => asset($definition['public_path']),
                'fallback' => $definition['fallback'],
                'subset' => $definition['subset'],
            ];
        }

        return $fonts;
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    public function normalizeTemplate(array $template): array
    {
        if (! isset($template['schemas']) || ! is_array($template['schemas'])) {
            return $template;
        }

        foreach ($template['schemas'] as $pageIndex => $page) {
            if (! is_array($page)) {
                continue;
            }

            foreach ($page as $fieldIndex => $field) {
                if (! is_array($field) || (($field['type'] ?? null) !== 'text')) {
                    continue;
                }

                if (filled($field['fontName'] ?? null)) {
                    continue;
                }

                $template['schemas'][$pageIndex][$fieldIndex]['fontName'] = $this->defaultFontForField((string) ($field['name'] ?? ''));
            }
        }

        return $template;
    }

    public function defaultFontForField(string $fieldName): string
    {
        if ($fieldName === 'certificate_title') {
            return self::TITLE_FONT;
        }

        return self::BODY_FONT;
    }
}

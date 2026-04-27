<?php

namespace App\Services\Certificates;

use Illuminate\Support\Facades\Storage;

class PdfmeTemplateLegacyAssetInliner
{
    /**
     * @param  array<string, mixed>  $template
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    public function inline(array $template, array $schema): array
    {
        $template['schemas'] = $this->normalizePages($template['schemas'] ?? []);

        foreach ($this->legacyAssetMappings() as $mapping) {
            $assetPath = $schema[$mapping['schema_key']] ?? null;

            if (! is_string($assetPath) || trim($assetPath) === '') {
                continue;
            }

            $dataUri = $this->resolveAssetDataUri($assetPath);

            if ($dataUri === '') {
                continue;
            }

            $template['schemas'] = $this->upsertFieldWithContent(
                $template['schemas'],
                $mapping['field_name'],
                $mapping['preset'],
                $dataUri,
            );
        }

        return $template;
    }

    /**
     * @return array<int, array{schema_key:string, field_name:string, preset:array<string, mixed>}>
     */
    protected function legacyAssetMappings(): array
    {
        return [
            [
                'schema_key' => 'logo_path',
                'field_name' => 'logo_image',
                'preset' => $this->imageFieldPreset('logo_image', 80, 12, 50, 28),
            ],
            [
                'schema_key' => 'background_image_path',
                'field_name' => 'background_image',
                'preset' => $this->imageFieldPreset('background_image', 0, 0, 210, 297),
            ],
            [
                'schema_key' => 'signature_path',
                'field_name' => 'signature_image',
                'preset' => $this->imageFieldPreset('signature_image', 70, 230, 70, 20),
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function imageFieldPreset(string $name, float $x, float $y, float $width, float $height): array
    {
        return [
            'name' => $name,
            'type' => 'image',
            'content' => '',
            'position' => ['x' => $x, 'y' => $y],
            'width' => $width,
            'height' => $height,
        ];
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    protected function normalizePages(mixed $pages): array
    {
        if (! is_array($pages)) {
            return [[]];
        }

        $normalizedPages = array_values(array_map(function (mixed $page): array {
            if (! is_array($page)) {
                return [];
            }

            return array_values(array_filter($page, fn (mixed $field): bool => is_array($field)));
        }, $pages));

        if ($normalizedPages === []) {
            return [[]];
        }

        return $normalizedPages;
    }

    /**
     * @param  array<int, array<int, array<string, mixed>>>  $pages
     * @param  array<string, mixed>  $preset
     * @return array<int, array<int, array<string, mixed>>>
     */
    protected function upsertFieldWithContent(array $pages, string $fieldName, array $preset, string $content): array
    {
        foreach ($pages as $pageIndex => $page) {
            foreach ($page as $fieldIndex => $field) {
                if (($field['name'] ?? null) !== $fieldName) {
                    continue;
                }

                if (filled($field['content'] ?? null)) {
                    return $pages;
                }

                $pages[$pageIndex][$fieldIndex]['content'] = $content;

                return $pages;
            }
        }

        $field = $preset;
        $field['content'] = $content;

        if ($fieldName === 'background_image') {
            array_unshift($pages[0], $field);

            return $pages;
        }

        $pages[0][] = $field;

        return $pages;
    }

    protected function resolveAssetDataUri(string $path): string
    {
        $disk = Storage::disk('public');

        if (! $disk->exists($path)) {
            return '';
        }

        $absolutePath = $disk->path($path);
        $mimeType = mime_content_type($absolutePath) ?: 'application/octet-stream';
        $contents = file_get_contents($absolutePath);

        if (! is_string($contents) || $contents === '') {
            return '';
        }

        return 'data:'.$mimeType.';base64,'.base64_encode($contents);
    }
}

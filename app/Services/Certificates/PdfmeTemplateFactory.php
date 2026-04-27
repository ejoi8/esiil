<?php

namespace App\Services\Certificates;

use App\Enums\CertificateType;
use App\Models\CertificateTemplate;

class PdfmeTemplateFactory
{
    public function __construct(protected PdfmeFontRegistry $fontRegistry) {}

    /**
     * @return array<string, mixed>
     */
    public function fromCertificateTemplate(CertificateTemplate $certificateTemplate): array
    {
        $baselineTemplate = $this->defaultTemplateFor($certificateTemplate);

        if ($baselineTemplate !== null) {
            return $baselineTemplate;
        }

        return $this->fromSchema($certificateTemplate->resolvedSchema());
    }

    /**
     * @param  array<string, mixed>  $schema
     * @return array<string, mixed>
     */
    public function fromSchema(array $schema): array
    {
        $resolvedSchema = array_replace(CertificateTemplate::DEFAULT_SCHEMA, $schema);

        return $this->normalizeFullPageCanvas([
            'basePdf' => [
                'width' => 210,
                'height' => 297,
                'padding' => [0, 0, 0, 0],
            ],
            'schemas' => [[
                $this->imageField('background_image', 0, 0, 210, 297),
                $this->imageField('logo_image', 80, 12, 50, 28),
                $this->textField('certificate_title', (string) $resolvedSchema['title'], 20, 44, 170, 18, 24, $this->fontRegistry->defaultFontForField('certificate_title')),
                $this->textField('subtitle', (string) $resolvedSchema['subtitle'], 30, 66, 150, 8, 9, $this->fontRegistry->defaultFontForField('subtitle')),
                $this->textField('participant_name', '{{participant_name}}', 24, 79, 162, 10, 12, $this->fontRegistry->defaultFontForField('participant_name')),
                $this->textField('body_intro', (string) $resolvedSchema['body_intro'], 34, 93, 142, 8, 9, $this->fontRegistry->defaultFontForField('body_intro')),
                $this->textField('event_title', '{{event_title}}', 22, 105, 166, 20, 11, $this->fontRegistry->defaultFontForField('event_title')),
                $this->textField('date_line', '{{date_line}}', 55, 145, 100, 8, 10, $this->fontRegistry->defaultFontForField('date_line')),
                $this->textField('organizer_heading', (string) $resolvedSchema['organizer_heading'], 45, 170, 120, 8, 10, $this->fontRegistry->defaultFontForField('organizer_heading')),
                $this->textField('organizer_name', '{{organizer}}', 34, 184, 142, 10, 10, $this->fontRegistry->defaultFontForField('organizer_name')),
                $this->imageField('signature_image', 70, 230, 70, 20),
                $this->textField('signature_name', (string) $resolvedSchema['signature_name'], 25, 252, 160, 8, 9, $this->fontRegistry->defaultFontForField('signature_name')),
                $this->textField('signature_title', (string) $resolvedSchema['signature_title'], 25, 261, 160, 7, 7, $this->fontRegistry->defaultFontForField('signature_title')),
            ]],
        ]);
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    public function normalizeFullPageCanvas(array $template): array
    {
        $template['basePdf'] = is_array($template['basePdf'] ?? null) ? $template['basePdf'] : [];
        $template['basePdf']['width'] = (float) ($template['basePdf']['width'] ?? 210);
        $template['basePdf']['height'] = (float) ($template['basePdf']['height'] ?? 297);
        $template['basePdf']['padding'] = [0, 0, 0, 0];

        $pages = is_array($template['schemas'] ?? null) ? $template['schemas'] : [[]];
        $template['schemas'] = array_values(array_map(function (mixed $page) use ($template): array {
            if (! is_array($page)) {
                return [];
            }

            $page = array_values(array_filter($page, fn (mixed $field): bool => is_array($field)));

            foreach ($page as $fieldIndex => $field) {
                if (($field['name'] ?? null) !== 'background_image') {
                    continue;
                }

                $page[$fieldIndex]['position'] = ['x' => 0, 'y' => 0];
                $page[$fieldIndex]['width'] = $template['basePdf']['width'];
                $page[$fieldIndex]['height'] = $template['basePdf']['height'];

                if ($fieldIndex !== 0) {
                    $backgroundField = $page[$fieldIndex];
                    unset($page[$fieldIndex]);
                    array_unshift($page, $backgroundField);
                    $page = array_values($page);
                }

                break;
            }

            return $page;
        }, $pages));

        return $template;
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function defaultTemplateFor(CertificateTemplate $certificateTemplate): ?array
    {
        $certificateType = CertificateType::fromMixed($certificateTemplate->type);

        if ($certificateType === null) {
            return null;
        }

        if ($certificateTemplate->key === CertificateType::ParticipationCertificate->templateKey()) {
            return null;
        }

        $defaultTemplate = CertificateTemplate::query()
            ->where('key', CertificateType::ParticipationCertificate->templateKey())
            ->when($certificateTemplate->exists, fn ($query) => $query->whereKeyNot($certificateTemplate->getKey()))
            ->value('pdfme_template');

        if (! is_array($defaultTemplate)) {
            return null;
        }

        return $this->withCertificateTitle(
            $this->normalizeFullPageCanvas($defaultTemplate),
            $certificateType === CertificateType::AttendanceSlip
                ? (string) ($certificateTemplate->resolvedSchema()['title'] ?? $certificateType->documentTitle())
                : null,
        );
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    protected function withCertificateTitle(array $template, ?string $title): array
    {
        if ($title === null || $title === '') {
            return $template;
        }

        foreach (($template['schemas'] ?? []) as $pageIndex => $page) {
            if (! is_array($page)) {
                continue;
            }

            foreach ($page as $fieldIndex => $field) {
                if (! is_array($field) || ($field['name'] ?? null) !== 'certificate_title') {
                    continue;
                }

                $template['schemas'][$pageIndex][$fieldIndex]['content'] = $title;

                return $template;
            }
        }

        return $template;
    }

    /**
     * @return array<string, mixed>
     */
    protected function textField(
        string $name,
        string $content,
        float $x,
        float $y,
        float $width,
        float $height,
        float $fontSize,
        string $fontName,
    ): array {
        return [
            'name' => $name,
            'type' => 'text',
            'content' => $content,
            'position' => [
                'x' => $x,
                'y' => $y,
            ],
            'width' => $width,
            'height' => $height,
            'alignment' => 'center',
            'verticalAlignment' => 'middle',
            'fontSize' => $fontSize,
            'fontName' => $fontName,
            'lineHeight' => 1.3,
            'characterSpacing' => 0,
            'fontColor' => '#1f1a17',
            'backgroundColor' => '#ffffff00',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function imageField(string $name, float $x, float $y, float $width, float $height): array
    {
        return [
            'name' => $name,
            'type' => 'image',
            'content' => '',
            'position' => [
                'x' => $x,
                'y' => $y,
            ],
            'width' => $width,
            'height' => $height,
        ];
    }
}

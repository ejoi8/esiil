<?php

namespace App\Services\Certificates;

use App\Enums\CertificateTemplateUpdateMode;
use App\Enums\CertificateType;
use App\Models\CertificateTemplate;
use App\Models\Registration;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class PdfmeCertificateRenderer
{
    public function __construct(
        protected PdfmeTemplateFactory $templateFactory,
        protected PdfmeTemplateLegacyAssetInliner $legacyAssetInliner,
        protected PdfmeFontRegistry $fontRegistry,
    ) {}

    public function render(Registration $registration): string
    {
        $registration->loadMissing('certificateTemplate', 'event.certificateTemplate', 'participant');

        $template = $this->resolveTemplate($registration);
        $variables = $this->buildVariables($registration);
        $inputs = $this->buildInputs($template, $variables);

        return $this->generatePdf($template, $inputs);
    }

    public function fileName(Registration $registration): string
    {
        $registration->loadMissing('event', 'participant');

        $participant = Str::slug($registration->participant->full_name);
        $event = Str::slug(Str::limit($registration->event->title, 40, ''));
        $type = CertificateType::fromMixed($registration->certificate_type)?->value ?? (string) $registration->certificate_type;

        return "{$type}-{$participant}-{$event}.pdf";
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveTemplate(Registration $registration): array
    {
        $currentTemplate = $this->currentTemplateForRegistration($registration);

        if ($currentTemplate !== null && $registration->certificate_template_snapshot != $currentTemplate) {
            $certificateTemplate = $registration->certificateTemplate;

            $registration->forceFill([
                'certificate_template_id' => $registration->certificate_template_id ?: $certificateTemplate?->id,
                'certificate_template_key' => $registration->certificate_template_key ?: $certificateTemplate?->key,
                'certificate_template_snapshot' => $currentTemplate,
                'certificate_metadata' => $this->withTemplateSchemaSnapshot(
                    $registration,
                    $certificateTemplate?->resolvedSchema() ?? CertificateTemplate::DEFAULT_SCHEMA,
                ),
            ])->save();

            return $currentTemplate;
        }

        if ($this->isPdfmeTemplate($registration->certificate_template_snapshot)) {
            $this->persistTemplateSchemaSnapshotIfMissing(
                $registration,
                $this->resolveCurrentTemplateSchema($registration),
            );

            /** @var array<string, mixed> $template */
            $template = $registration->certificate_template_snapshot;

            return $template;
        }

        if (is_array($registration->certificate_template_snapshot)) {
            $schemaSnapshot = array_replace(CertificateTemplate::DEFAULT_SCHEMA, $registration->certificate_template_snapshot);
            $convertedTemplate = $this->templateFactory->fromSchema($registration->certificate_template_snapshot);

            $registration->forceFill([
                'certificate_template_snapshot' => $convertedTemplate,
                'certificate_metadata' => $this->withTemplateSchemaSnapshot($registration, $schemaSnapshot),
            ])->save();

            return $convertedTemplate;
        }

        $certificateTemplate = $registration->certificateTemplate ?? $registration->event?->certificateTemplate;

        if ($certificateTemplate !== null) {
            $pdfmeTemplate = $this->templateFromCertificateTemplate($certificateTemplate);
            $schemaSnapshot = $certificateTemplate->resolvedSchema();

            $registration->forceFill([
                'certificate_template_id' => $registration->certificate_template_id ?: $certificateTemplate->id,
                'certificate_template_key' => $registration->certificate_template_key ?: $certificateTemplate->key,
                'certificate_template_snapshot' => $pdfmeTemplate,
                'certificate_metadata' => $this->withTemplateSchemaSnapshot($registration, $schemaSnapshot),
            ])->save();

            return $pdfmeTemplate;
        }

        return $this->templateFactory->fromSchema(CertificateTemplate::DEFAULT_SCHEMA);
    }

    public function usesCurrentTemplate(Registration $registration): bool
    {
        $currentTemplate = $this->currentTemplateForRegistration($registration);

        if ($currentTemplate === null) {
            return true;
        }

        return $registration->certificate_template_snapshot == $currentTemplate;
    }

    /**
     * @return array<string, string>
     */
    protected function buildVariables(Registration $registration): array
    {
        $event = $registration->event;
        $participant = $registration->participant;
        $templateSchema = $this->resolveCurrentTemplateSchema($registration);

        return [
            'participant_name' => (string) $participant->full_name,
            'participant_nokp' => (string) $participant->nokp,
            'event_title' => (string) $event->title,
            'event_description' => (string) ($event->description ?? ''),
            'date_line' => (string) ($this->formatDateRange($event->starts_at?->format('d M Y'), $event->ends_at?->format('d M Y')) ?? ''),
            'time_line' => (string) ($this->formatDateRange($event->start_time_text, $event->end_time_text, ' to ') ?? ''),
            'venue' => (string) ($event->venue ?: '-'),
            'organizer' => (string) ($event->organizer_name ?? ''),
            'reference' => (string) ($registration->cert_serial_number ?: 'Pending serial number'),
            'generated_at' => now()->format('d M Y H:i'),
            'certificate_type' => CertificateType::fromMixed($registration->certificate_type)?->value ?? (string) $registration->certificate_type,
            'signature_name' => (string) ($templateSchema['signature_name'] ?? ''),
            'signature_title' => (string) ($templateSchema['signature_title'] ?? ''),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function resolveCurrentTemplateSchema(Registration $registration): array
    {
        $metadata = is_array($registration->certificate_metadata) ? $registration->certificate_metadata : [];
        $schemaSnapshot = $metadata['template_schema_snapshot'] ?? null;

        if (is_array($schemaSnapshot)) {
            return array_replace(CertificateTemplate::DEFAULT_SCHEMA, $schemaSnapshot);
        }

        if (is_array($registration->certificate_template_snapshot) && ! $this->isPdfmeTemplate($registration->certificate_template_snapshot)) {
            return array_replace(CertificateTemplate::DEFAULT_SCHEMA, $registration->certificate_template_snapshot);
        }

        return $registration->certificateTemplate?->resolvedSchema()
            ?? $registration->event?->certificateTemplate?->resolvedSchema()
            ?? CertificateTemplate::DEFAULT_SCHEMA;
    }

    /**
     * @param  array<string, mixed>  $schemaSnapshot
     * @return array<string, mixed>
     */
    protected function withTemplateSchemaSnapshot(Registration $registration, array $schemaSnapshot): array
    {
        $metadata = is_array($registration->certificate_metadata) ? $registration->certificate_metadata : [];
        $metadata['template_schema_snapshot'] = array_replace(CertificateTemplate::DEFAULT_SCHEMA, $schemaSnapshot);

        return $metadata;
    }

    /**
     * @param  array<string, mixed>  $schemaSnapshot
     */
    protected function persistTemplateSchemaSnapshotIfMissing(Registration $registration, array $schemaSnapshot): void
    {
        $metadata = is_array($registration->certificate_metadata) ? $registration->certificate_metadata : [];

        if (is_array($metadata['template_schema_snapshot'] ?? null)) {
            return;
        }

        $registration->forceFill([
            'certificate_metadata' => $this->withTemplateSchemaSnapshot($registration, $schemaSnapshot),
        ])->save();
    }

    /**
     * @param  array<string, mixed>  $template
     * @param  array<string, string>  $variables
     * @return array<string, string>
     */
    protected function buildInputs(array $template, array $variables): array
    {
        $inputs = [];

        foreach (($template['schemas'] ?? []) as $page) {
            if (! is_array($page)) {
                continue;
            }

            foreach ($page as $field) {
                if (! is_array($field)) {
                    continue;
                }

                $name = (string) ($field['name'] ?? '');

                if ($name === '') {
                    continue;
                }

                $content = (string) ($field['content'] ?? '');
                $value = $content !== ''
                    ? $this->replaceVariables($content, $variables)
                    : ($variables[$name] ?? '');

                $inputs[$name] = $value;
            }
        }

        return $inputs;
    }

    /**
     * @param  array<string, mixed>  $template
     * @param  array<string, string>  $inputs
     */
    protected function generatePdf(array $template, array $inputs): string
    {
        $template = $this->fontRegistry->normalizeTemplate($template);
        $pageSize = $this->dompdfPageSize($template);

        $html = view('certificates.pdfme-dompdf', [
            'fields' => $this->dompdfFields($template, $inputs),
            'fonts' => $this->dompdfFonts(),
            'pageSize' => $pageSize,
        ])->render();

        $dompdf = new Dompdf($this->dompdfOptions());
        $dompdf->setPaper([0, 0, $this->millimetersToPoints($pageSize['width']), $this->millimetersToPoints($pageSize['height'])]);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->render();

        return $dompdf->output();
    }

    protected function dompdfOptions(): Options
    {
        $cachePath = storage_path('framework/cache/dompdf');

        File::ensureDirectoryExists($cachePath);

        $options = new Options;
        $options->setChroot([base_path(), public_path(), storage_path()]);
        $options->setDefaultFont('DejaVu Serif');
        $options->setFontCache($cachePath);
        $options->setFontDir($cachePath);
        $options->setIsHtml5ParserEnabled(true);
        $options->setIsRemoteEnabled(false);
        $options->setTempDir($cachePath);

        return $options;
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array{width:float,height:float}
     */
    protected function dompdfPageSize(array $template): array
    {
        $basePdf = is_array($template['basePdf'] ?? null) ? $template['basePdf'] : [];
        $width = (float) ($basePdf['width'] ?? 210);
        $height = (float) ($basePdf['height'] ?? 297);

        return [
            'width' => $width > 0 ? $width : 210.0,
            'height' => $height > 0 ? $height : 297.0,
        ];
    }

    /**
     * @param  array<string, mixed>  $template
     * @param  array<string, string>  $inputs
     * @return array<int, array{type:string, content:string, style:string, contentStyle?:string}>
     */
    protected function dompdfFields(array $template, array $inputs): array
    {
        $fields = [];
        $page = $template['schemas'][0] ?? [];

        if (! is_array($page)) {
            return $fields;
        }

        foreach ($page as $field) {
            if (! is_array($field)) {
                continue;
            }

            $type = (string) ($field['type'] ?? 'text');

            if (! in_array($type, ['image', 'text'], true)) {
                continue;
            }

            $name = (string) ($field['name'] ?? '');
            $content = (string) ($inputs[$name] ?? $field['content'] ?? '');

            if ($content === '') {
                continue;
            }

            if ($type === 'image') {
                $fields[] = [
                    'type' => $type,
                    'content' => $content,
                    'style' => $this->dompdfImageStyle($field),
                ];

                continue;
            }

            $fields[] = [
                'type' => $type,
                'content' => $content,
                'style' => $this->dompdfTextContainerStyle($field),
                'contentStyle' => $this->dompdfTextContentStyle($field),
            ];
        }

        return $fields;
    }

    /**
     * @return array<string, string>
     */
    protected function dompdfFonts(): array
    {
        $fonts = [];

        foreach ($this->fontRegistry->definitions() as $name => $definition) {
            if (! File::exists($definition['path'])) {
                continue;
            }

            $fonts[$name] = str_replace('\\', '/', $definition['path']);
        }

        return $fonts;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function dompdfFieldBaseStyles(array $field): array
    {
        $position = is_array($field['position'] ?? null) ? $field['position'] : [];
        $styles = [
            'position:absolute',
            'box-sizing:border-box',
            'left:'.$this->cssMillimeters($position['x'] ?? 0),
            'top:'.$this->cssMillimeters($position['y'] ?? 0),
            'width:'.$this->cssMillimeters($field['width'] ?? 0),
            'height:'.$this->cssMillimeters($field['height'] ?? 0),
        ];

        $rotate = (float) ($field['rotate'] ?? 0);

        if ($rotate !== 0.0) {
            $styles[] = 'transform:rotate('.$rotate.'deg)';
            $styles[] = 'transform-origin:top left';
        }

        $opacity = (float) ($field['opacity'] ?? 1);

        if ($opacity >= 0 && $opacity < 1) {
            $styles[] = 'opacity:'.$opacity;
        }

        return $styles;
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function dompdfImageStyle(array $field): string
    {
        $styles = $this->dompdfFieldBaseStyles($field);
        $styles[] = 'overflow:hidden';
        $styles[] = 'object-fit:contain';

        return implode(';', $styles).';';
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function dompdfTextContainerStyle(array $field): string
    {
        $styles = $this->dompdfFieldBaseStyles($field);
        $styles[] = 'display:table';
        $styles[] = 'overflow:visible';

        if ($backgroundColor = $this->cssColor($field['backgroundColor'] ?? null)) {
            $styles[] = 'background-color:'.$backgroundColor;
        }

        return implode(';', $styles).';';
    }

    /**
     * @param  array<string, mixed>  $field
     */
    protected function dompdfTextContentStyle(array $field): string
    {
        $styles = [
            'display:table-cell',
            'vertical-align:'.$this->cssVerticalAlignment((string) ($field['verticalAlignment'] ?? 'top')),
            'text-align:'.$this->cssTextAlignment((string) ($field['alignment'] ?? 'left')),
            'white-space:pre-wrap',
            'font-family:"'.str_replace('"', '', (string) ($field['fontName'] ?? PdfmeFontRegistry::BODY_FONT)).'", "DejaVu Serif", serif',
            'font-size:'.((float) ($field['fontSize'] ?? 12)).'pt',
            'line-height:'.((float) ($field['lineHeight'] ?? 1.2)),
        ];

        $characterSpacing = (float) ($field['characterSpacing'] ?? 0);

        if ($characterSpacing !== 0.0) {
            $styles[] = 'letter-spacing:'.$characterSpacing.'pt';
        }

        if ($fontColor = $this->cssColor($field['fontColor'] ?? null)) {
            $styles[] = 'color:'.$fontColor;
        }

        return implode(';', $styles).';';
    }

    protected function cssMillimeters(mixed $value): string
    {
        return ((float) $value).'mm';
    }

    protected function millimetersToPoints(float $value): float
    {
        return $value * 72 / 25.4;
    }

    protected function cssTextAlignment(string $alignment): string
    {
        return match ($alignment) {
            'center', 'right', 'justify' => $alignment,
            default => 'left',
        };
    }

    protected function cssVerticalAlignment(string $alignment): string
    {
        return match ($alignment) {
            'middle', 'center' => 'middle',
            'bottom' => 'bottom',
            default => 'top',
        };
    }

    protected function cssColor(mixed $color): ?string
    {
        $color = trim((string) $color);

        if ($color === '' || strtolower($color) === 'transparent') {
            return null;
        }

        if (preg_match('/^#[0-9a-f]{8}$/i', $color) === 1) {
            $alpha = strtolower(substr($color, 7, 2));

            return $alpha === '00' ? null : substr($color, 0, 7);
        }

        return $color;
    }

    /**
     * @param  array<string, mixed> | null  $template
     */
    protected function isPdfmeTemplate(?array $template): bool
    {
        if (! is_array($template)) {
            return false;
        }

        return array_key_exists('basePdf', $template) && array_key_exists('schemas', $template);
    }

    protected function templateFromCertificateTemplate(CertificateTemplate $certificateTemplate): array
    {
        if ($this->isPdfmeTemplate($certificateTemplate->pdfme_template)) {
            /** @var array<string, mixed> $template */
            $template = $certificateTemplate->pdfme_template;
        } else {
            $template = $this->templateFactory->fromCertificateTemplate($certificateTemplate);
        }

        $syncedTemplate = $this->templateFactory->normalizeFullPageCanvas(
            $this->fontRegistry->normalizeTemplate(
                $this->legacyAssetInliner->inline($template, is_array($certificateTemplate->schema) ? $certificateTemplate->schema : []),
            ),
        );

        if ($certificateTemplate->pdfme_template != $syncedTemplate) {
            $certificateTemplate->forceFill([
                'pdfme_template' => $syncedTemplate,
            ])->save();
        }

        return $syncedTemplate;
    }

    /**
     * @return array<string, mixed>
     */
    public function templateForCertificateTemplate(CertificateTemplate $certificateTemplate): array
    {
        return $this->templateFromCertificateTemplate($certificateTemplate);
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function currentTemplateForRegistration(Registration $registration): ?array
    {
        $registration->loadMissing('certificateTemplate', 'event');

        if (! $this->shouldUseCurrentTemplate($registration)) {
            return null;
        }

        $certificateTemplate = $registration->certificateTemplate;

        if ($certificateTemplate === null) {
            return null;
        }

        return $this->templateFromCertificateTemplate($certificateTemplate);
    }

    protected function shouldUseCurrentTemplate(Registration $registration): bool
    {
        $mode = CertificateTemplateUpdateMode::fromMixed($registration->event?->certificate_template_update_mode);

        return $mode !== CertificateTemplateUpdateMode::LockIssuedSnapshot;
    }

    /**
     * @param  array<string, string>  $variables
     */
    protected function replaceVariables(string $content, array $variables): string
    {
        return (string) preg_replace_callback('/\{\{\s*([a-zA-Z0-9_]+)\s*\}\}/', function (array $matches) use ($variables): string {
            return $variables[$matches[1]] ?? '';
        }, $content);
    }

    protected function formatDateRange(?string $start, ?string $end, string $separator = ' - '): ?string
    {
        if ($start === null && $end === null) {
            return null;
        }

        if ($end === null || $start === $end) {
            return $start;
        }

        return "{$start}{$separator}{$end}";
    }
}

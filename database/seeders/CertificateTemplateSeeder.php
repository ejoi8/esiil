<?php

namespace Database\Seeders;

use App\Enums\CertificateType;
use App\Models\CertificateTemplate;
use App\Models\Event;
use App\Models\Registration;
use App\Services\Certificates\PdfmeTemplateFactory;
use Illuminate\Database\Seeder;

class CertificateTemplateSeeder extends Seeder
{
    public function run(): void
    {
        $pdfmeTemplateFactory = app(PdfmeTemplateFactory::class);
        $participationSchema = array_replace(CertificateTemplate::DEFAULT_SCHEMA, [
            'title' => 'Sijil Penyertaan',
            'signature_name' => 'Puan Sri Maheran Binti Jamil',
            'signature_title' => 'Yang Dipertua PUSPANITA',
        ]);

        $participationTemplate = CertificateTemplate::query()->updateOrCreate(
            ['key' => 'default-participation'],
            [
                'name' => 'Default Participation Certificate',
                'type' => CertificateType::ParticipationCertificate,
                'schema' => $participationSchema,
                'is_active' => true,
            ],
        );

        $participationTemplate->forceFill([
            'pdfme_template' => $this->seededTemplate('default-participation')
                ?? $pdfmeTemplateFactory->fromCertificateTemplate($participationTemplate),
        ])->save();

        $attendanceTemplate = CertificateTemplate::query()->updateOrCreate(
            ['key' => 'default-attendance'],
            [
                'name' => 'Default Attendance Slip',
                'type' => CertificateType::AttendanceSlip,
                'schema' => array_replace($participationSchema, [
                    'title' => 'Slip Kehadiran',
                ]),
                'is_active' => true,
            ],
        );

        $attendanceTemplate->forceFill([
            'pdfme_template' => $this->withCertificateTitle(
                $this->seededTemplate('default-participation')
                    ?? $pdfmeTemplateFactory->fromCertificateTemplate($attendanceTemplate),
                'Slip Kehadiran',
            ),
        ])->save();

        CertificateTemplate::query()
            ->whereNull('pdfme_template')
            ->orderBy('id')
            ->chunkById(100, function ($certificateTemplates) use ($pdfmeTemplateFactory): void {
                $certificateTemplates->each(function (CertificateTemplate $certificateTemplate) use ($pdfmeTemplateFactory): void {
                    $certificateTemplate->forceFill([
                        'pdfme_template' => $pdfmeTemplateFactory->fromCertificateTemplate($certificateTemplate),
                    ])->save();
                });
            });

        $this->backfillEventTemplates($attendanceTemplate, $participationTemplate);
        $this->backfillRegistrationTemplateReferences();
    }

    /**
     * @return array<string, mixed>|null
     */
    protected function seededTemplate(string $key): ?array
    {
        $path = database_path("seeders/data/{$key}.pdfme.json");

        if (! file_exists($path)) {
            return null;
        }

        $contents = file_get_contents($path);

        if (! is_string($contents) || $contents === '') {
            return null;
        }

        $template = json_decode($contents, true);

        return is_array($template) ? $template : null;
    }

    /**
     * @param  array<string, mixed>  $template
     * @return array<string, mixed>
     */
    protected function withCertificateTitle(array $template, string $title): array
    {
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

    protected function backfillEventTemplates(
        CertificateTemplate $attendanceTemplate,
        CertificateTemplate $participationTemplate,
    ): void {
        Event::query()
            ->whereNull('certificate_template_id')
            ->where('certificate_type', CertificateType::AttendanceSlip->value)
            ->update([
                'certificate_template_id' => $attendanceTemplate->id,
            ]);

        Event::query()
            ->whereNull('certificate_template_id')
            ->where('certificate_type', CertificateType::ParticipationCertificate->value)
            ->update([
                'certificate_template_id' => $participationTemplate->id,
            ]);

        Event::query()
            ->whereNull('template_key')
            ->where('certificate_template_id', $attendanceTemplate->id)
            ->update([
                'template_key' => $attendanceTemplate->key,
            ]);

        Event::query()
            ->whereNull('template_key')
            ->where('certificate_template_id', $participationTemplate->id)
            ->update([
                'template_key' => $participationTemplate->key,
            ]);
    }

    protected function backfillRegistrationTemplateReferences(): void
    {
        Event::query()
            ->select(['id', 'certificate_type', 'certificate_template_id', 'template_key'])
            ->whereNotNull('certificate_template_id')
            ->orderBy('id')
            ->chunkById(250, function ($events): void {
                $events->each(function (Event $event): void {
                    $certificateType = $event->certificate_type instanceof CertificateType
                        ? $event->certificate_type->value
                        : (string) $event->certificate_type;

                    Registration::query()
                        ->where('event_id', $event->id)
                        ->whereNull('certificate_template_id')
                        ->update([
                            'certificate_template_id' => $event->certificate_template_id,
                        ]);

                    Registration::query()
                        ->where('event_id', $event->id)
                        ->whereNull('certificate_template_key')
                        ->update([
                            'certificate_template_key' => $event->template_key,
                        ]);

                    Registration::query()
                        ->where('event_id', $event->id)
                        ->whereNull('certificate_type')
                        ->update([
                            'certificate_type' => $certificateType,
                        ]);
                });
            });
    }
}

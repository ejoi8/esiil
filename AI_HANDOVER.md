# AI Handover

This file is the minimum context a new AI should read before changing this repo.

## What This App Is

eSIJIL is a Laravel 13 + Filament 5 application for:

- managing branches, participants, events, registrations, and certificate templates
- issuing event certificates as PDFs
- allowing public certificate lookup by `nokp`
- allowing public event registration through signed links

Admin UI lives at `/auth`.

Public flows live in [routes/web.php](/mnt/c/laragon/www/esijil/routes/web.php).

## The Most Important Domain Fact

Do not assume there is a separate active certificate entity anymore.

Current state:

- there is no active `Certificate` model in `app/Models`
- there is no active `CertificateResource`
- the old `certificates` table was merged into `registrations`
- issued certificate state now lives directly on `Registration`

If you change certificate behavior, start with:

- [Registration.php](/mnt/c/laragon/www/esijil/app/Models/Registration.php)
- [2026_04_26_065610_merge_certificates_into_registrations_table.php](/mnt/c/laragon/www/esijil/database/migrations/2026_04_26_065610_merge_certificates_into_registrations_table.php)
- [RegistrationCertificateIssuer.php](/mnt/c/laragon/www/esijil/app/Services/Certificates/RegistrationCertificateIssuer.php)
- [StoredCertificatePdf.php](/mnt/c/laragon/www/esijil/app/Services/Certificates/StoredCertificatePdf.php)
- [PdfmeCertificateRenderer.php](/mnt/c/laragon/www/esijil/app/Services/Certificates/PdfmeCertificateRenderer.php)

## Current Mental Model

- `Participant` = a person
- `Event` = the event definition and certificate defaults
- `Registration` = participant joined event, plus any issued-certificate data
- `CertificateTemplate` = reusable design definition for rendering

“Issued Certificates” in the UI means registrations where certificate-related fields are present.

## Public Flows

### Certificate lookup

Handled by [CertificateLookupController.php](/mnt/c/laragon/www/esijil/app/Http/Controllers/CertificateLookupController.php).

Key facts:

- lookup is by `nokp`
- POST `/semakan` is throttled
- successful lookup stores `certificate_lookup_participant_id` in session
- downloads are allowed only when that session participant matches the registration’s participant
- the download route is `/certificates/{registration}/download`

### Event registration

Handled by [EventRegistrationController.php](/mnt/c/laragon/www/esijil/app/Http/Controllers/EventRegistrationController.php).

Key facts:

- public form requires a signed URL
- event must be `published`
- registration window must be open
- participant is created or updated by normalized `nokp`
- registration is deduplicated by `event_id + participant_id`
- certificate issuance happens immediately through `RegistrationCertificateIssuer`
- success/download access is protected by `event_registration_success_id` in session

## Certificate Rendering Rules

Certificate generation uses pdfme through Node.

Important files:

- [config/certificates.php](/mnt/c/laragon/www/esijil/config/certificates.php)
- [resources/js/pdfme-generate-certificate.mjs](/mnt/c/laragon/www/esijil/resources/js/pdfme-generate-certificate.mjs)
- [public/fonts/certificates](/mnt/c/laragon/www/esijil/public/fonts/certificates)

Important behavior:

- `certificate_template_snapshot` is persisted on the registration
- `certificate_metadata.template_schema_snapshot` preserves schema context
- renderer may sync the snapshot to the current template depending on `certificate_template_update_mode`
- downloads render from the current registration record

If you change template behavior, inspect:

- [CertificateTemplate.php](/mnt/c/laragon/www/esijil/app/Models/CertificateTemplate.php)
- [CertificateTemplateSeeder.php](/mnt/c/laragon/www/esijil/database/seeders/CertificateTemplateSeeder.php)
- [Designer.php](/mnt/c/laragon/www/esijil/app/Filament/Resources/CertificateTemplates/Pages/Designer.php)
- [PdfmeTemplateFactory.php](/mnt/c/laragon/www/esijil/app/Services/Certificates/PdfmeTemplateFactory.php)
- [PdfmeTemplateLegacyAssetInliner.php](/mnt/c/laragon/www/esijil/app/Services/Certificates/PdfmeTemplateLegacyAssetInliner.php)

## Admin Surface

The Filament panel is configured in [AuthPanelProvider.php](/mnt/c/laragon/www/esijil/app/Providers/Filament/AuthPanelProvider.php).

Current resources:

- branches
- participants
- events
- registrations
- certificate templates

There is no standalone certificate resource.

Useful resource areas:

- [app/Filament/Resources/Events](/mnt/c/laragon/www/esijil/app/Filament/Resources/Events)
- [app/Filament/Resources/Registrations](/mnt/c/laragon/www/esijil/app/Filament/Resources/Registrations)
- [app/Filament/Resources/CertificateTemplates](/mnt/c/laragon/www/esijil/app/Filament/Resources/CertificateTemplates)

## Seeded Defaults

Default seeding currently:

- creates `admin@admin.com` / `password`
- imports legacy data
- normalizes branches
- ensures default certificate templates exist

Template defaults:

- `default-participation`
- `default-attendance`

Attendance slip currently reuses the participation layout with the title switched to `Slip Kehadiran`.

## Tests To Trust

When changing behavior, read the matching test first.

Primary feature tests:

- [CertificateLookupTest.php](/mnt/c/laragon/www/esijil/tests/Feature/CertificateLookupTest.php)
- [EventRegistrationTest.php](/mnt/c/laragon/www/esijil/tests/Feature/EventRegistrationTest.php)
- [EventResourceTest.php](/mnt/c/laragon/www/esijil/tests/Feature/EventResourceTest.php)
- [CertificateTemplateManagementTest.php](/mnt/c/laragon/www/esijil/tests/Feature/CertificateTemplateManagementTest.php)
- [DomainConsistencyTest.php](/mnt/c/laragon/www/esijil/tests/Feature/DomainConsistencyTest.php)

What they protect:

- public lookup flow
- signed registration flow
- event admin form and relation managers
- template seeding and snapshot behavior
- enum casting and template/type consistency

## Local Dev Commands

Initial setup:

```bash
composer setup
```

Daily dev:

```bash
composer dev
```

Tests:

```bash
php artisan test --compact
```

Formatting for PHP edits:

```bash
vendor/bin/pint --dirty --format agent
```

## Common Mistakes To Avoid

- Do not reintroduce a separate certificate domain model unless the product explicitly changes.
- Do not document or code against a `CertificateResource`; it does not exist in the current app.
- Do not assume event registration is public-listable; there is no public `/events` index.
- Do not bypass `nokp` normalization rules in the request classes.
- Do not forget Node is required for certificate rendering.
- Do not ignore `certificate_template_update_mode` when changing rendering logic.
- Do not change public route semantics without updating the session-based authorization checks and tests.

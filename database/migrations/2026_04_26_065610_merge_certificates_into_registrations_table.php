<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('registrations', function (Blueprint $table): void {
            $table->string('certificate_type')->nullable()->after('remarks');
            $table->foreignId('certificate_template_id')
                ->nullable()
                ->after('certificate_type')
                ->constrained('certificate_templates')
                ->nullOnDelete();
            $table->string('certificate_template_key')->nullable()->after('certificate_template_id');
            $table->json('certificate_template_snapshot')->nullable()->after('certificate_template_key');
            $table->string('cert_serial_number')->nullable()->unique()->after('certificate_template_snapshot');
            $table->string('certificate_file_path')->nullable()->after('cert_serial_number');
            $table->dateTime('certificate_issued_at')->nullable()->after('certificate_file_path');
            $table->json('certificate_metadata')->nullable()->after('certificate_issued_at');
        });

        $eventsById = DB::table('events')
            ->select('id', 'certificate_type', 'certificate_template_id', 'template_key')
            ->get()
            ->keyBy('id');

        if (Schema::hasTable('certificates')) {
            DB::table('certificates')
                ->orderBy('id')
                ->chunkById(500, function ($certificates): void {
                    foreach ($certificates as $certificate) {
                        DB::table('registrations')
                            ->where('id', $certificate->registration_id)
                            ->update([
                                'certificate_type' => $certificate->type,
                                'certificate_template_id' => $certificate->certificate_template_id,
                                'certificate_template_key' => $certificate->template_key,
                                'certificate_template_snapshot' => $certificate->template_snapshot,
                                'cert_serial_number' => $certificate->verification_code,
                                'certificate_file_path' => $certificate->file_path,
                                'certificate_issued_at' => $certificate->issued_at,
                                'certificate_metadata' => $certificate->metadata,
                            ]);
                    }
                });
        }

        DB::table('registrations')
            ->orderBy('id')
            ->chunkById(500, function ($registrations) use ($eventsById): void {
                foreach ($registrations as $registration) {
                    $event = $eventsById->get($registration->event_id);

                    if ($event === null) {
                        continue;
                    }

                    DB::table('registrations')
                        ->where('id', $registration->id)
                        ->update([
                            'certificate_type' => $registration->certificate_type ?: $event->certificate_type,
                            'certificate_template_id' => $registration->certificate_template_id ?: $event->certificate_template_id,
                            'certificate_template_key' => $registration->certificate_template_key ?: $event->template_key,
                        ]);
                }
            });

        Schema::dropIfExists('certificates');
    }

    public function down(): void
    {
        Schema::create('certificates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('certificate_template_id')->nullable()->constrained('certificate_templates')->nullOnDelete();
            $table->string('template_key')->nullable();
            $table->json('template_snapshot')->nullable();
            $table->string('verification_code')->nullable()->unique();
            $table->string('file_path')->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'type']);
        });

        DB::table('registrations')
            ->whereNotNull('certificate_type')
            ->orderBy('id')
            ->chunkById(500, function ($registrations): void {
                foreach ($registrations as $registration) {
                    DB::table('certificates')->insert([
                        'registration_id' => $registration->id,
                        'type' => $registration->certificate_type,
                        'certificate_template_id' => $registration->certificate_template_id,
                        'template_key' => $registration->certificate_template_key,
                        'template_snapshot' => $registration->certificate_template_snapshot,
                        'verification_code' => $registration->cert_serial_number,
                        'file_path' => $registration->certificate_file_path,
                        'issued_at' => $registration->certificate_issued_at,
                        'metadata' => $registration->certificate_metadata,
                        'created_at' => $registration->created_at,
                        'updated_at' => $registration->updated_at,
                    ]);
                }
            });

        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('certificate_template_id');
            $table->dropUnique('registrations_cert_serial_number_unique');
            $table->dropColumn([
                'certificate_type',
                'certificate_template_key',
                'certificate_template_snapshot',
                'cert_serial_number',
                'certificate_file_path',
                'certificate_issued_at',
                'certificate_metadata',
            ]);
        });
    }
};

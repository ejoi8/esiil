<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('certificate_template_id')
                ->nullable()
                ->after('template_key')
                ->constrained('certificate_templates')
                ->nullOnDelete();
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->foreignId('certificate_template_id')
                ->nullable()
                ->after('type')
                ->constrained('certificate_templates')
                ->nullOnDelete();
            $table->json('template_snapshot')->nullable()->after('template_key');
        });
    }

    public function down(): void
    {
        Schema::table('certificates', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_template_id');
            $table->dropColumn('template_snapshot');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('certificate_template_id');
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->dropColumn('certificate_template_update_mode');
        });

        Schema::table('registrations', function (Blueprint $table): void {
            $table->dropColumn('certificate_template_snapshot');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table): void {
            $table->string('certificate_template_update_mode')
                ->default('use_latest_template')
                ->after('certificate_template_id');
        });

        Schema::table('registrations', function (Blueprint $table): void {
            $table->json('certificate_template_snapshot')
                ->nullable()
                ->after('certificate_template_key');
        });
    }
};

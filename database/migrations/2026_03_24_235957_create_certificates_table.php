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
        Schema::create('certificates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('registration_id')->constrained()->cascadeOnDelete();
            $table->string('type');
            $table->string('template_key')->nullable();
            $table->string('verification_code')->nullable()->unique();
            $table->string('file_path')->nullable();
            $table->dateTime('issued_at')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['registration_id', 'type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificates');
    }
};

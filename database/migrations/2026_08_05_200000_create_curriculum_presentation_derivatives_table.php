<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('curriculum_presentation_derivatives')) {
            return;
        }

        Schema::create('curriculum_presentation_derivatives', function (Blueprint $table) {
            $table->id();
            $table->string('source_type', 32);
            $table->unsignedBigInteger('source_id');
            $table->string('storage_disk', 32)->default('r2');
            $table->string('manifest_path')->nullable();
            $table->string('status', 32)->default('pending');
            $table->unsignedInteger('slide_count')->nullable();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('source_checksum', 128)->nullable();
            $table->text('error_message')->nullable();
            $table->string('engine', 64)->nullable();
            $table->timestamp('ready_at')->nullable();
            $table->timestamps();

            $table->unique(['source_type', 'source_id'], 'cpd_source_unique');
            $table->index(['status', 'updated_at'], 'cpd_status_updated_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_presentation_derivatives');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('curriculum_library_materials')) {
            return;
        }

        $toAdd = [];
        foreach ([
            'animation_video_path' => fn (Blueprint $table) => $table->string('animation_video_path')->nullable(),
            'animation_video_disk' => fn (Blueprint $table) => $table->string('animation_video_disk', 32)->nullable(),
            'animation_video_original_name' => fn (Blueprint $table) => $table->string('animation_video_original_name')->nullable(),
            'animation_video_mime' => fn (Blueprint $table) => $table->string('animation_video_mime', 128)->nullable(),
            'animation_video_size' => fn (Blueprint $table) => $table->unsignedBigInteger('animation_video_size')->nullable(),
            'animation_video_uploaded_at' => fn (Blueprint $table) => $table->timestamp('animation_video_uploaded_at')->nullable(),
        ] as $column => $definition) {
            if (! Schema::hasColumn('curriculum_library_materials', $column)) {
                $toAdd[$column] = $definition;
            }
        }

        if ($toAdd === []) {
            return;
        }

        Schema::table('curriculum_library_materials', function (Blueprint $table) use ($toAdd) {
            foreach ($toAdd as $definition) {
                $definition($table);
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('curriculum_library_materials')) {
            return;
        }

        $toDrop = [];
        foreach ([
            'animation_video_path',
            'animation_video_disk',
            'animation_video_original_name',
            'animation_video_mime',
            'animation_video_size',
            'animation_video_uploaded_at',
        ] as $column) {
            if (Schema::hasColumn('curriculum_library_materials', $column)) {
                $toDrop[] = $column;
            }
        }

        if ($toDrop === []) {
            return;
        }

        Schema::table('curriculum_library_materials', function (Blueprint $table) use ($toDrop) {
            $table->dropColumn($toDrop);
        });
    }
};

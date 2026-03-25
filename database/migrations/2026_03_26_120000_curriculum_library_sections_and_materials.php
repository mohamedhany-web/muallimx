<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('curriculum_library_sections')) {
            Schema::create('curriculum_library_sections', function (Blueprint $table) {
                $table->id();
                $table->foreignId('curriculum_library_item_id')
                    ->constrained('curriculum_library_items')
                    ->cascadeOnDelete();
                $table->unsignedBigInteger('parent_id')->nullable();
                $table->string('title');
                $table->text('description')->nullable();
                $table->unsignedInteger('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            Schema::table('curriculum_library_sections', function (Blueprint $table) {
                $table->foreign('parent_id', 'fk_cl_sec_parent')
                    ->references('id')
                    ->on('curriculum_library_sections')
                    ->cascadeOnDelete();
            });
        }

        if (!Schema::hasTable('curriculum_library_materials')) {
            Schema::create('curriculum_library_materials', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('curriculum_library_section_id');
                $table->string('title')->nullable();
                $table->string('path');
                $table->string('storage_disk', 32)->default('r2');
                $table->string('original_name')->nullable();
                $table->string('file_kind', 20)->default('other');
                $table->boolean('view_in_platform')->default(true);
                $table->boolean('allow_download')->default(false);
                $table->unsignedInteger('order')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();

                $table->foreign('curriculum_library_section_id', 'fk_cl_mat_sec')
                    ->references('id')
                    ->on('curriculum_library_sections')
                    ->cascadeOnDelete();
            });
        }

        $this->migrateLegacyItemFilesToMaterials();
    }

    protected function migrateLegacyItemFilesToMaterials(): void
    {
        if (!Schema::hasTable('curriculum_library_item_files') || !Schema::hasTable('curriculum_library_sections')) {
            return;
        }

        $rows = DB::table('curriculum_library_item_files')
            ->orderBy('curriculum_library_item_id')
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        if ($rows->isEmpty()) {
            return;
        }

        $sectionCache = [];

        foreach ($rows as $f) {
            $itemId = (int) $f->curriculum_library_item_id;

            if (!isset($sectionCache[$itemId])) {
                $sectionCache[$itemId] = DB::table('curriculum_library_sections')->insertGetId([
                    'curriculum_library_item_id' => $itemId,
                    'parent_id' => null,
                    'title' => 'محتوى المنهج (مستورد)',
                    'description' => null,
                    'order' => 0,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $sectionId = $sectionCache[$itemId];

            $ft = (string) ($f->file_type ?? 'other');
            $fileKind = match ($ft) {
                'html' => 'html',
                'pdf' => 'pdf',
                'presentation' => 'pptx',
                default => 'other',
            };

            $viewInPlatform = in_array($fileKind, ['html', 'pdf', 'pptx'], true);
            $allowDownload = ($fileKind === 'pdf') || ($fileKind === 'other' && $ft === 'assignment');
            if ($fileKind === 'html') {
                $allowDownload = false;
            }

            $disk = (string) ($f->storage_disk ?? 'public');
            if ($disk === '') {
                $disk = $fileKind === 'html' ? 'r2' : 'public';
            }

            DB::table('curriculum_library_materials')->insert([
                'curriculum_library_section_id' => $sectionId,
                'title' => $f->label,
                'path' => $f->path,
                'storage_disk' => $disk,
                'original_name' => $f->label,
                'file_kind' => $fileKind,
                'view_in_platform' => $viewInPlatform,
                'allow_download' => $allowDownload,
                'order' => (int) ($f->order ?? 0),
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('curriculum_library_materials');
        Schema::dropIfExists('curriculum_library_sections');
    }
};

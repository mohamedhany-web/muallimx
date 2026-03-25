<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('curriculum_library_item_files')) {
            return;
        }

        Schema::table('curriculum_library_item_files', function (Blueprint $table) {
            if (!Schema::hasColumn('curriculum_library_item_files', 'storage_disk')) {
                $table->string('storage_disk', 40)->default('public')->after('path')
                    ->comment('public=محلي، r2=Cloudflare R2');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('curriculum_library_item_files')) {
            return;
        }

        Schema::table('curriculum_library_item_files', function (Blueprint $table) {
            if (Schema::hasColumn('curriculum_library_item_files', 'storage_disk')) {
                $table->dropColumn('storage_disk');
            }
        });
    }
};

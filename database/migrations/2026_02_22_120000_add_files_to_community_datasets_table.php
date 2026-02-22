<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('community_datasets', function (Blueprint $table) {
            $table->json('files')->nullable()->after('file_size')->comment('قائمة الملفات المرفوعة: [{path, original_name, size}]');
        });
    }

    public function down(): void
    {
        Schema::table('community_datasets', function (Blueprint $table) {
            $table->dropColumn('files');
        });
    }
};

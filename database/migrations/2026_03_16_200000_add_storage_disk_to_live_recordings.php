<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('live_recordings', function (Blueprint $table) {
            $table->string('storage_disk', 30)->default('local')->after('external_url')
                ->comment('local = ملف محلي، r2 = Cloudflare R2 (بعد رفع Jibri)');
        });
    }

    public function down(): void
    {
        Schema::table('live_recordings', function (Blueprint $table) {
            $table->dropColumn('storage_disk');
        });
    }
};

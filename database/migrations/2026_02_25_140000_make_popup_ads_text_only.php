<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('popup_ads', function (Blueprint $table) {
            $table->text('body')->nullable()->after('title');
            $table->string('cta_text')->nullable()->after('link_url');
        });
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE popup_ads MODIFY image VARCHAR(255) NULL');
        }
    }

    public function down(): void
    {
        Schema::table('popup_ads', function (Blueprint $table) {
            $table->dropColumn(['body', 'cta_text']);
        });
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'mysql') {
            \Illuminate\Support\Facades\DB::statement('ALTER TABLE popup_ads MODIFY image VARCHAR(255) NOT NULL');
        }
    }
};

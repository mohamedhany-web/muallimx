<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('site_services')) {
            return;
        }

        Schema::table('site_services', function (Blueprint $table) {
            if (! Schema::hasColumn('site_services', 'image_path')) {
                $table->string('image_path', 512)->nullable()->after('slug');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('site_services')) {
            return;
        }

        Schema::table('site_services', function (Blueprint $table) {
            if (Schema::hasColumn('site_services', 'image_path')) {
                $table->dropColumn('image_path');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('assignments') && ! Schema::hasColumn('assignments', 'resource_attachments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->json('resource_attachments')->nullable()->after('instructions');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'resource_attachments')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropColumn('resource_attachments');
            });
        }
    }
};

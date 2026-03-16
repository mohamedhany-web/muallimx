<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove blog feature: drop blog_posts table.
     */
    public function up(): void
    {
        if (Schema::hasTable('blog_posts')) {
            Schema::drop('blog_posts');
        }
    }

    /**
     * Reverse: table is not recreated (blog feature removed).
     */
    public function down(): void
    {
        // Blog feature removed - no restore
    }
};

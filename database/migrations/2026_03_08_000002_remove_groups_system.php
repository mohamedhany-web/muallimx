<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Remove groups feature: drop group_messages, group_members, groups;
     * drop group_id from assignments and assignment_submissions.
     */
    public function up(): void
    {
        // Drop foreign keys and columns first (they reference groups)
        if (Schema::hasTable('assignment_submissions') && Schema::hasColumn('assignment_submissions', 'group_id')) {
            Schema::table('assignment_submissions', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            });
        }
        if (Schema::hasTable('assignments') && Schema::hasColumn('assignments', 'group_id')) {
            Schema::table('assignments', function (Blueprint $table) {
                $table->dropForeign(['group_id']);
                $table->dropColumn('group_id');
            });
        }
        // Then drop group tables
        if (Schema::hasTable('group_messages')) {
            Schema::drop('group_messages');
        }
        if (Schema::hasTable('group_members')) {
            Schema::drop('group_members');
        }
        if (Schema::hasTable('groups')) {
            Schema::drop('groups');
        }
    }

    public function down(): void
    {
        // Groups feature removed - no restore
    }
};

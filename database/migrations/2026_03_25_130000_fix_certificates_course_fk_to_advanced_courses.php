<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('certificates')) {
            return;
        }

        // Ensure the column can be set null when course deleted
        if (Schema::hasColumn('certificates', 'course_id')) {
            // Skip change() on SQLite; we're on MySQL in this project.
            if (DB::connection()->getDriverName() !== 'sqlite') {
                Schema::table('certificates', function (Blueprint $table) {
                    $table->unsignedBigInteger('course_id')->nullable()->change();
                });
            }
        }

        // Drop any existing FK on certificates.course_id (name varies across installs)
        try {
            $dbName = DB::getDatabaseName();
            $fkRows = DB::select(
                "SELECT CONSTRAINT_NAME AS name
                 FROM INFORMATION_SCHEMA.KEY_COLUMN_USAGE
                 WHERE TABLE_SCHEMA = ?
                   AND TABLE_NAME = 'certificates'
                   AND COLUMN_NAME = 'course_id'
                   AND REFERENCED_TABLE_NAME IS NOT NULL",
                [$dbName]
            );

            foreach ($fkRows as $row) {
                $name = $row->name ?? null;
                if (is_string($name) && $name !== '') {
                    try {
                        DB::statement("ALTER TABLE certificates DROP FOREIGN KEY `{$name}`");
                    } catch (\Throwable $e) {
                        // ignore if already dropped / not supported
                    }
                }
            }
        } catch (\Throwable $e) {
            // ignore introspection failures
        }

        // Add FK to advanced_courses(id)
        try {
            DB::statement(
                'ALTER TABLE certificates
                 ADD CONSTRAINT certificates_course_id_foreign
                 FOREIGN KEY (course_id)
                 REFERENCES advanced_courses(id)
                 ON DELETE SET NULL'
            );
        } catch (\Throwable $e) {
            // ignore if it already exists
        }
    }

    public function down(): void
    {
        if (!Schema::hasTable('certificates')) {
            return;
        }

        try {
            DB::statement('ALTER TABLE certificates DROP FOREIGN KEY certificates_course_id_foreign');
        } catch (\Throwable $e) {
            // ignore
        }
    }
};


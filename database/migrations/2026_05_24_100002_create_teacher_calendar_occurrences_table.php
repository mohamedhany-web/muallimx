<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('teacher_calendar_occurrences')) {
            Schema::table('teacher_calendar_occurrences', function (Blueprint $table) {
                if (! $this->indexExists('teacher_calendar_occurrences', 'tcal_occ_appt_date_uniq')) {
                    $table->unique(['appointment_id', 'occurrence_date'], 'tcal_occ_appt_date_uniq');
                }
            });

            return;
        }

        Schema::create('teacher_calendar_occurrences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appointment_id')->constrained('teacher_calendar_appointments')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->date('occurrence_date');
            $table->dateTime('reminder_sent_at')->nullable();
            $table->boolean('auto_remove_after_end')->default(false);
            $table->timestamp('removed_at')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'starts_at']);
            $table->index(['starts_at', 'reminder_sent_at']);
            $table->unique(['appointment_id', 'occurrence_date'], 'tcal_occ_appt_date_uniq');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_calendar_occurrences');
    }

    private function indexExists(string $table, string $index): bool
    {
        $rows = DB::select('SHOW INDEX FROM `'.$table.'` WHERE Key_name = ?', [$index]);

        return count($rows) > 0;
    }
};

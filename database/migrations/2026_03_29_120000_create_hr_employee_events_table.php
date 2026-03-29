<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hr_employee_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('event_type', 32);
            $table->string('title')->nullable();
            $table->text('body');
            $table->date('event_date');
            $table->timestamps();

            $table->index(['employee_id', 'event_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hr_employee_events');
    }
};

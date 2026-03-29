<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sales_leads', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable();
            $table->string('phone', 64)->nullable();
            $table->string('company')->nullable();
            $table->string('source', 32)->default('other');
            $table->string('status', 32)->default('new');
            $table->text('notes')->nullable();
            $table->foreignId('interested_advanced_course_id')->nullable()->constrained('advanced_courses')->nullOnDelete();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('linked_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('converted_order_id')->nullable()->constrained('orders')->nullOnDelete();
            $table->timestamp('converted_at')->nullable();
            $table->text('lost_reason')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['status', 'created_at']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales_leads');
    }
};

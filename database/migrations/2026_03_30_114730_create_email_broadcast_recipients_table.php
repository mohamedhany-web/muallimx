<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('email_broadcast_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_broadcast_id')->constrained('email_broadcasts')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('email');
            $table->string('name')->nullable();
            $table->string('status', 20)->default('queued'); // queued|sent|failed
            $table->timestamp('sent_at')->nullable();
            $table->text('error')->nullable();
            $table->timestamps();

            $table->unique(['email_broadcast_id', 'email'], 'email_broadcast_email_unique');
            $table->index(['email_broadcast_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_broadcast_recipients');
    }
};

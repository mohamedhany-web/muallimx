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
        Schema::create('email_broadcasts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->string('audience', 32);
            $table->string('subject');
            $table->longText('body');
            $table->string('status', 20)->default('draft'); // draft|sending|sent|failed
            $table->timestamp('sent_at')->nullable();
            $table->json('stats')->nullable();
            $table->timestamps();

            $table->index(['audience', 'created_at']);
            $table->index(['status', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('email_broadcasts');
    }
};

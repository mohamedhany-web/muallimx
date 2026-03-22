<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('consultation_settings', function (Blueprint $table) {
            $table->id();
            $table->decimal('default_price', 10, 2)->default(500);
            $table->unsignedInteger('default_duration_minutes')->default(30);
            $table->text('payment_instructions')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('consultation_settings');
    }
};

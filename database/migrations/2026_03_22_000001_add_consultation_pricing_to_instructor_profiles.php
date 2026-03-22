<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->decimal('consultation_price_egp', 10, 2)->nullable()->after('status');
            $table->unsignedInteger('consultation_duration_minutes')->nullable()->after('consultation_price_egp');
        });
    }

    public function down(): void
    {
        Schema::table('instructor_profiles', function (Blueprint $table) {
            $table->dropColumn(['consultation_price_egp', 'consultation_duration_minutes']);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * إعدادات فتح القسم: القسم التالي لا يفتح إلا بتحقيق شرط (نسبة أو إكمال كل العناصر).
     */
    public function up(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->string('unlock_rule', 32)->default('previous_all_items')->after('is_active')
                ->comment('always=دائماً مفتوح, previous_percent=نسبة من القسم السابق, previous_all_items=إكمال كل عناصر القسم السابق');
            $table->unsignedTinyInteger('unlock_percent')->nullable()->after('unlock_rule')
                ->comment('0-100 عند unlock_rule=previous_percent');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('course_sections', function (Blueprint $table) {
            $table->dropColumn(['unlock_rule', 'unlock_percent']);
        });
    }
};

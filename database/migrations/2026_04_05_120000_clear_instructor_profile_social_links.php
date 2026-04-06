<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * إزالة روابط السوشيال/الموقع المخزنة في التسويق الشخصي للمدرب (لم تعد مستخدمة).
     */
    public function up(): void
    {
        if (! \Illuminate\Support\Facades\Schema::hasTable('instructor_profiles')) {
            return;
        }
        DB::table('instructor_profiles')->update(['social_links' => null]);
    }

    public function down(): void
    {
        // لا يمكن استعادة القيم السابقة
    }
};

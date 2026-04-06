<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('referral_programs', function (Blueprint $table) {
            if (! Schema::hasColumn('referral_programs', 'is_default')) {
                $table->boolean('is_default')->default(false)->after('is_active');
            }
        });

        if (Schema::hasColumn('referral_programs', 'is_default')) {
            $hasDefault = DB::table('referral_programs')->where('is_default', true)->exists();
            if (! $hasDefault) {
                $id = DB::table('referral_programs')
                    ->where('is_active', true)
                    ->orderBy('id')
                    ->value('id');
                if ($id) {
                    DB::table('referral_programs')->where('id', $id)->update(['is_default' => true]);
                }
            }
        }
    }

    public function down(): void
    {
        Schema::table('referral_programs', function (Blueprint $table) {
            if (Schema::hasColumn('referral_programs', 'is_default')) {
                $table->dropColumn('is_default');
            }
        });
    }
};

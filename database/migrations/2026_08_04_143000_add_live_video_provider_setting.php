<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('live_settings')->where('key', 'live_video_provider')->exists();
        if ($exists) {
            return;
        }

        DB::table('live_settings')->insert([
            'key' => 'live_video_provider',
            'value' => 'jitsi',
            'type' => 'string',
            'group' => 'general',
            'label' => 'محرك الفيديو للموقع (Jitsi / LiveKit)',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        DB::table('live_settings')->where('key', 'live_video_provider')->delete();
    }
};

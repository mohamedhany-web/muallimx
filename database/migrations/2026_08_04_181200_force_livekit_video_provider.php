<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $exists = DB::table('live_settings')->where('key', 'live_video_provider')->exists();
        if ($exists) {
            DB::table('live_settings')->where('key', 'live_video_provider')->update([
                'value' => 'livekit',
                'label' => 'محرك الفيديو للموقع (LiveKit)',
                'updated_at' => now(),
            ]);
        } else {
            DB::table('live_settings')->insert([
                'key' => 'live_video_provider',
                'value' => 'livekit',
                'type' => 'string',
                'group' => 'general',
                'label' => 'محرك الفيديو للموقع (LiveKit)',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        // Keep livekit — do not revert to jitsi in production
    }
};

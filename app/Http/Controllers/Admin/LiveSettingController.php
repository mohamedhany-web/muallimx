<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveSetting;
use Illuminate\Http\Request;

class LiveSettingController extends Controller
{
    public function index()
    {
        $settings = LiveSetting::orderBy('group')->orderBy('id')->get()->groupBy('group');
        return view('admin.live-settings.index', compact('settings'));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'settings' => 'required|array',
            'settings.*.key' => 'required|string',
            'settings.*.value' => 'nullable|string',
        ]);

        foreach ($data['settings'] as $item) {
            $key = $item['key'];
            $value = $item['value'] ?? '';

            if ($key === 'live_video_provider') {
                $value = strtolower(trim((string) $value));
                if (! in_array($value, ['jitsi', 'livekit'], true)) {
                    $value = 'jitsi';
                }
                if ($value === 'livekit') {
                    $livekit = app(\App\Services\LiveKitTokenService::class);
                    if (! config('services.livekit.enabled') || ! $livekit->isConfigured()) {
                        return back()->with('error', 'لا يمكن تفعيل LiveKit بدون مفاتيح LIVEKIT_* في .env.');
                    }
                }
                LiveSetting::setLiveVideoProvider($value);
                continue;
            }

            LiveSetting::set($key, $value);
        }

        return back()->with('success', 'تم حفظ إعدادات البث المباشر بنجاح');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IntegrationSetting;
use Illuminate\Http\Request;

class N8nSettingsController extends Controller
{
    public function index()
    {
        $n8nToken = IntegrationSetting::get('n8n_token', config('services.n8n.token'));
        $n8nWebhook = IntegrationSetting::get('n8n_live_session_report_webhook', config('services.n8n.live_session_report_webhook'));

        $platformCallback = url('/api/n8n/live-session-reports/{report_id}');

        $examplePayload = [
            'status' => 'completed',
            'title' => 'تقرير الجلسة - مثال',
            'summary' => 'ملخص نصي للتقرير الناتج من أدوات الذكاء الاصطناعي.',
            'audio_path' => 'live-session-audio/2026/04/session-123-audio-20260416-120000-abc123.webm',
            'storage_disk' => 'live_recordings_r2',
            'n8n_execution_id' => 'your-n8n-execution-id',
        ];

        return view('admin.n8n.settings', compact(
            'n8nToken',
            'n8nWebhook',
            'platformCallback',
            'examplePayload'
        ));
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'n8n_token' => ['nullable', 'string', 'max:255'],
            'n8n_webhook' => ['nullable', 'string', 'max:1000'],
        ]);

        IntegrationSetting::set('n8n_token', $data['n8n_token'] ?? null, 'n8n');
        IntegrationSetting::set('n8n_live_session_report_webhook', $data['n8n_webhook'] ?? null, 'n8n');

        return redirect()->route('admin.n8n.settings')
            ->with('success', 'تم تحديث إعدادات n8n بنجاح.');
    }
}


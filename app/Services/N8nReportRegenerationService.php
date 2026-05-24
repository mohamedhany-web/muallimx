<?php

namespace App\Services;

use App\Models\ClassroomMeetingReport;
use App\Models\IntegrationSetting;
use App\Models\LiveRecording;
use App\Models\LiveSessionReport;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class N8nReportRegenerationService
{
    /**
     * @return array{ok: bool, message: string}
     */
    public function regenerate(string $source, int $reportId): array
    {
        return match ($source) {
            'live_session' => $this->regenerateLiveSessionReport($reportId),
            'classroom_meeting' => $this->regenerateClassroomMeetingReport($reportId),
            default => ['ok' => false, 'message' => 'مصدر التقرير غير معروف.'],
        };
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function regenerateLiveSessionReport(int $reportId): array
    {
        $report = LiveSessionReport::with(['session', 'instructor', 'recording'])->find($reportId);
        if (! $report) {
            return ['ok' => false, 'message' => 'التقرير غير موجود.'];
        }

        $session = $report->session;
        if (! $session) {
            return ['ok' => false, 'message' => 'جلسة البث المرتبطة بالتقرير غير موجودة.'];
        }

        $recording = $report->recording;
        if (! $recording && $report->live_recording_id) {
            $recording = LiveRecording::find($report->live_recording_id);
        }
        if (! $recording) {
            $recording = LiveRecording::where('session_id', $session->id)
                ->whereIn('status', ['ready', 'processing'])
                ->latest('id')
                ->first();
        }

        $recordingUrl = $recording?->getUrl();
        if (! $recording && ! $recordingUrl) {
            return ['ok' => false, 'message' => 'لا يوجد تسجيل صالح لهذه الجلسة — لا يمكن إعادة إرسال الطلب إلى n8n.'];
        }

        $report->update([
            'live_recording_id' => $recording?->id ?? $report->live_recording_id,
            'summary' => null,
            'status' => 'pending',
            'n8n_execution_id' => null,
        ]);

        return $this->dispatchLiveSessionWebhook($report->fresh(['session']), $recording, $recordingUrl);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function regenerateClassroomMeetingReport(int $reportId): array
    {
        $report = ClassroomMeetingReport::with(['meeting', 'user'])->find($reportId);
        if (! $report) {
            return ['ok' => false, 'message' => 'التقرير غير موجود.'];
        }

        $meeting = $report->meeting;
        if (! $meeting) {
            return ['ok' => false, 'message' => 'اجتماع Classroom المرتبط بالتقرير غير موجود.'];
        }

        if (! $meeting->ended_at) {
            return ['ok' => false, 'message' => 'الاجتماع لم يُنهَ بعد — لا يمكن إعادة توليد التقرير.'];
        }

        $audioUrl = $meeting->recording_audio_download_url;
        $audioMime = strtolower((string) ($meeting->recording_audio_mime_type ?? ''));
        if (! $audioUrl) {
            return ['ok' => false, 'message' => 'لا يوجد تقرير صوتي MP3 مرفوع لهذا الاجتماع.'];
        }
        if ($audioMime !== '' && $audioMime !== 'audio/mpeg') {
            return ['ok' => false, 'message' => 'ملف الصوت ليس MP3 — أعد رفع التسجيل من حساب المعلم أولاً.'];
        }

        $report->update([
            'summary' => null,
            'status' => 'pending',
            'n8n_execution_id' => null,
            'audio_path' => $meeting->recording_audio_path,
            'storage_disk' => $meeting->recording_disk,
        ]);

        return $this->dispatchClassroomMeetingWebhook($report->fresh(['meeting']), $meeting);
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function dispatchLiveSessionWebhook(LiveSessionReport $report, ?LiveRecording $recording, ?string $recordingUrl): array
    {
        $session = $report->session;
        if (! $session) {
            return ['ok' => false, 'message' => 'جلسة البث غير متاحة.'];
        }

        [$webhookUrl, $token, $configError] = $this->n8nConfig();
        if ($configError) {
            $report->update(['status' => 'failed']);

            return ['ok' => false, 'message' => $configError];
        }

        $callbackUrl = url('/api/n8n/live-session-reports/'.$report->id);

        try {
            $response = Http::timeout(45)
                ->connectTimeout(10)
                ->withHeaders([
                    'X-N8N-Token' => $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post($webhookUrl, [
                    'regenerate' => true,
                    'report_id' => $report->id,
                    'live_session_id' => $session->id,
                    'instructor_id' => $report->instructor_id,
                    'live_recording_id' => $recording?->id,
                    'title' => $report->title,
                    'live_session_title' => $session->title,
                    'live_session_status' => $session->status,
                    'recording' => [
                        'id' => $recording?->id,
                        'file_path' => $recording?->file_path,
                        'storage_disk' => $recording?->storage_disk,
                        'external_url' => $recording?->external_url,
                        'duration_seconds' => $recording?->duration_seconds,
                        'file_size' => $recording?->file_size,
                        'status' => $recording?->status,
                        'download_url' => $recordingUrl,
                    ],
                    'callback' => [
                        'url' => $callbackUrl,
                        'method' => 'PATCH',
                        'header' => 'X-N8N-Token',
                    ],
                ]);

            if ($response->successful()) {
                $executionId = $response->json('execution_id');
                $report->update([
                    'n8n_execution_id' => $executionId,
                    'status' => 'processing',
                ]);

                return [
                    'ok' => true,
                    'message' => 'تم إعادة إرسال طلب التقرير إلى n8n. سيظهر للمعلم في حسابه عند اكتمال المعالجة.',
                ];
            }

            $report->update(['status' => 'failed']);
            Log::warning('n8n live-session report regenerate failed', [
                'report_id' => $report->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['ok' => false, 'message' => 'تعذّر إرسال الطلب إلى n8n. تحقق من إعدادات الـ Webhook والتوكن.'];
        } catch (\Throwable $e) {
            $report->update(['status' => 'failed']);
            Log::error('n8n live-session report regenerate exception', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => 'حدث خطأ أثناء الاتصال بـ n8n: '.$e->getMessage()];
        }
    }

    /**
     * @return array{ok: bool, message: string}
     */
    private function dispatchClassroomMeetingWebhook(ClassroomMeetingReport $report, $meeting): array
    {
        [$webhookUrl, $token, $configError] = $this->n8nConfig();
        if ($configError) {
            $report->update(['status' => 'failed']);

            return ['ok' => false, 'message' => $configError];
        }

        $audioUrl = $meeting->recording_audio_download_url;
        $callbackUrl = url('/api/n8n/classroom-meeting-reports/'.$report->id);

        try {
            $response = Http::timeout(45)
                ->connectTimeout(10)
                ->withHeaders([
                    'X-N8N-Token' => $token,
                    'Accept' => 'application/json',
                    'Content-Type' => 'application/json',
                ])->post($webhookUrl, [
                    'regenerate' => true,
                    'source' => 'classroom_meeting',
                    'report_id' => $report->id,
                    'classroom_meeting_id' => $meeting->id,
                    'user_id' => $report->user_id,
                    'title' => $report->title,
                    'meeting_title' => $meeting->title,
                    'meeting_code' => $meeting->code,
                    'meeting_ended_at' => optional($meeting->ended_at)->toIso8601String(),
                    'recording' => [
                        'storage_disk' => $meeting->recording_disk,
                        'audio_path' => $meeting->recording_audio_path,
                        'video_path' => $meeting->recording_path,
                        'audio_mime_type' => $meeting->recording_audio_mime_type,
                        'video_mime_type' => $meeting->recording_mime_type,
                        'audio_download_url' => $audioUrl,
                        'video_download_url' => $meeting->recording_download_url,
                        'download_url' => $audioUrl,
                    ],
                    'callback' => [
                        'url' => $callbackUrl,
                        'method' => 'PATCH',
                        'header' => 'X-N8N-Token',
                    ],
                ]);

            if ($response->successful()) {
                $executionId = $response->json('execution_id');
                $report->update([
                    'n8n_execution_id' => $executionId,
                    'status' => 'processing',
                ]);

                return [
                    'ok' => true,
                    'message' => 'تم إعادة إرسال طلب التقرير إلى n8n. سيظهر للمعلم/الطالب في صفحة الاجتماع عند الاكتمال.',
                ];
            }

            $report->update(['status' => 'failed']);
            Log::warning('n8n classroom-meeting report regenerate failed', [
                'report_id' => $report->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['ok' => false, 'message' => 'تعذّر إرسال الطلب إلى n8n. تحقق من إعدادات الـ Webhook.'];
        } catch (\Throwable $e) {
            $report->update(['status' => 'failed']);
            Log::error('n8n classroom-meeting report regenerate exception', [
                'report_id' => $report->id,
                'error' => $e->getMessage(),
            ]);

            return ['ok' => false, 'message' => 'حدث خطأ أثناء الاتصال بـ n8n: '.$e->getMessage()];
        }
    }

    /**
     * @return array{0: ?string, 1: ?string, 2: ?string} [webhookUrl, token, error]
     */
    private function n8nConfig(): array
    {
        $webhookUrl = IntegrationSetting::get('n8n_live_session_report_webhook', config('services.n8n.live_session_report_webhook'));
        $token = IntegrationSetting::get('n8n_token', config('services.n8n.token'));

        if (! $webhookUrl || ! $token) {
            return [null, null, 'إعدادات تكامل n8n غير مكتملة (Webhook أو التوكن).'];
        }

        return [$webhookUrl, $token, null];
    }
}

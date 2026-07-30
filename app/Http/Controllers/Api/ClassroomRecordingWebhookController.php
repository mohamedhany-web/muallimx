<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ClassroomMeeting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * ويب هوك بعد انتهاء Jibri من تسجيل محاضرة Classroom ورفع الملف إلى R2.
 * يُستدعى من سكربت finalize على سيرفر live.muallimx.com.
 */
class ClassroomRecordingWebhookController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $token = config('services.live_recordings_webhook.token');
        if (empty($token) || $request->header('X-Webhook-Token') !== $token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $validated = $request->validate([
            'classroom_meeting_id' => 'nullable|integer|exists:classroom_meetings,id',
            'room_name' => 'nullable|string|max:191',
            'file_path' => 'required|string|max:500',
            'mime_type' => 'nullable|string|max:120',
            'duration_seconds' => 'nullable|integer|min:0',
            'file_size' => 'nullable|integer|min:0',
        ]);

        if (empty($validated['classroom_meeting_id']) && empty($validated['room_name'])) {
            return response()->json([
                'error' => 'classroom_meeting_id or room_name is required',
            ], 422);
        }

        $meeting = null;
        if (! empty($validated['classroom_meeting_id'])) {
            $meeting = ClassroomMeeting::query()->find((int) $validated['classroom_meeting_id']);
        }
        if (! $meeting && ! empty($validated['room_name'])) {
            $room = (string) $validated['room_name'];
            $meeting = ClassroomMeeting::query()
                ->where('room_name', $room)
                ->orderByDesc('id')
                ->first();
            if (! $meeting) {
                $meeting = ClassroomMeeting::query()
                    ->whereRaw('LOWER(room_name) = ?', [strtolower($room)])
                    ->orderByDesc('id')
                    ->first();
            }
        }

        if (! $meeting) {
            return response()->json(['error' => 'Meeting not found'], 404);
        }

        $path = ltrim((string) $validated['file_path'], '/');
        $mime = strtolower((string) ($validated['mime_type'] ?? 'video/mp4'));
        if ($mime === '' || ! str_contains($mime, '/')) {
            $mime = 'video/mp4';
        }
        $size = (int) ($validated['file_size'] ?? 0);
        $duration = (int) ($validated['duration_seconds'] ?? 0);

        $disk = Storage::disk('live_recordings_r2');
        if ($size <= 0) {
            try {
                if ($disk->exists($path)) {
                    $size = (int) $disk->size($path);
                }
            } catch (\Throwable $e) {
                // ignore
            }
        }

        $fresh = DB::transaction(function () use ($meeting, $path, $mime, $size, $duration, $disk) {
            /** @var ClassroomMeeting $locked */
            $locked = ClassroomMeeting::query()->whereKey($meeting->id)->lockForUpdate()->firstOrFail();
            $oldPath = ($locked->recording_disk === 'live_recordings_r2') ? (string) $locked->recording_path : '';

            $locked->update([
                'recording_disk' => 'live_recordings_r2',
                'recording_path' => $path,
                'recording_mime_type' => $mime,
                'recording_size' => $size,
                'recording_duration_seconds' => $duration > 0 ? $duration : (int) ($locked->recording_duration_seconds ?? 0),
                'recording_uploaded_at' => now(),
                'recording_status' => 'ready',
            ]);

            if ($oldPath !== '' && $oldPath !== $path) {
                try {
                    $disk->delete($oldPath);
                } catch (\Throwable $e) {
                    // لا نفشل الحفظ بسبب حذف قديم
                }
            }

            return $locked->fresh();
        });

        return response()->json([
            'success' => true,
            'meeting_id' => $fresh->id,
            'download_url' => $fresh->recording_download_url,
            'message' => 'تم ربط تسجيل المحاضرة بالاجتماع.',
        ], 201);
    }
}

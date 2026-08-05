<?php

namespace App\Services;

use App\Models\ClassroomMeeting;
use App\Models\ClassroomMeetingParticipant;
use App\Models\ClassroomMeetingWaitingGuest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ClassroomWaitingRoomService
{
    public function createWaitingGuest(
        ClassroomMeeting $meeting,
        string $displayName,
        ?string $ip = null,
        ?string $userAgent = null
    ): ClassroomMeetingWaitingGuest {
        $this->cleanupExpired($meeting);

        return ClassroomMeetingWaitingGuest::create([
            'classroom_meeting_id' => $meeting->id,
            'waiting_token' => Str::random(64),
            'display_name' => $displayName,
            'status' => ClassroomMeetingWaitingGuest::STATUS_PENDING,
            'ip_address' => $ip,
            'user_agent' => $userAgent ? substr($userAgent, 0, 255) : null,
        ]);
    }

    /**
     * @return array<int, array{id:int,display_name:string,waiting_since:string}>
     */
    public function listPendingForHost(ClassroomMeeting $meeting): array
    {
        $this->cleanupExpired($meeting);

        return ClassroomMeetingWaitingGuest::query()
            ->where('classroom_meeting_id', $meeting->id)
            ->where('status', ClassroomMeetingWaitingGuest::STATUS_PENDING)
            ->orderBy('created_at')
            ->get(['id', 'display_name', 'created_at'])
            ->map(fn (ClassroomMeetingWaitingGuest $g) => [
                'id' => $g->id,
                'display_name' => $g->display_name,
                'waiting_since' => $g->created_at?->toIso8601String(),
            ])
            ->values()
            ->all();
    }

    public function pendingCount(ClassroomMeeting $meeting): int
    {
        $this->cleanupExpired($meeting);

        return ClassroomMeetingWaitingGuest::query()
            ->where('classroom_meeting_id', $meeting->id)
            ->where('status', ClassroomMeetingWaitingGuest::STATUS_PENDING)
            ->count();
    }

    public function cleanupExpired(ClassroomMeeting $meeting): int
    {
        return ClassroomMeetingWaitingGuest::query()
            ->where('classroom_meeting_id', $meeting->id)
            ->where(function ($query) {
                $query->where(function ($pending) {
                    $pending->where('status', ClassroomMeetingWaitingGuest::STATUS_PENDING)
                        ->where('created_at', '<', now()->subHours(6));
                })->orWhere(function ($terminal) {
                    $terminal->whereIn('status', [
                        ClassroomMeetingWaitingGuest::STATUS_DENIED,
                        ClassroomMeetingWaitingGuest::STATUS_CANCELLED,
                        ClassroomMeetingWaitingGuest::STATUS_CONSUMED,
                    ])->where('created_at', '<', now()->subDay());
                });
            })
            ->delete();
    }

    public function admitGuest(ClassroomMeeting $meeting, ClassroomMeetingWaitingGuest $guest): ClassroomMeetingWaitingGuest
    {
        if ((int) $guest->classroom_meeting_id !== (int) $meeting->id) {
            abort(404);
        }

        if ($guest->isConsumed()) {
            return $guest;
        }

        if ($guest->isDenied() || $guest->isCancelled()) {
            abort(422, 'لا يمكن قبول طلب منتهٍ.');
        }

        if ($guest->isAdmitted()) {
            return $guest;
        }

        $guest->update([
            'status' => ClassroomMeetingWaitingGuest::STATUS_ADMITTED,
            'admitted_at' => now(),
        ]);

        return $guest->fresh();
    }

    public function denyGuest(ClassroomMeeting $meeting, ClassroomMeetingWaitingGuest $guest): ClassroomMeetingWaitingGuest
    {
        if ((int) $guest->classroom_meeting_id !== (int) $meeting->id) {
            abort(404);
        }

        if ($guest->isConsumed()) {
            abort(422, 'الضيف دخل بالفعل.');
        }

        $guest->update([
            'status' => ClassroomMeetingWaitingGuest::STATUS_DENIED,
            'denied_at' => now(),
        ]);

        return $guest->fresh();
    }

    public function cancelWaitingGuest(ClassroomMeetingWaitingGuest $guest): ClassroomMeetingWaitingGuest
    {
        if ($guest->isConsumed() || $guest->isDenied()) {
            return $guest;
        }

        $guest->update([
            'status' => ClassroomMeetingWaitingGuest::STATUS_CANCELLED,
            'cancelled_at' => now(),
        ]);

        return $guest->fresh();
    }

    public function admitAllPending(ClassroomMeeting $meeting): int
    {
        $pending = ClassroomMeetingWaitingGuest::query()
            ->where('classroom_meeting_id', $meeting->id)
            ->where('status', ClassroomMeetingWaitingGuest::STATUS_PENDING)
            ->get();

        $count = 0;
        foreach ($pending as $guest) {
            $this->admitGuest($meeting, $guest);
            $count++;
        }

        return $count;
    }

    public function setWaitingRoomEnabled(ClassroomMeeting $meeting, bool $enabled): ClassroomMeeting
    {
        $settings = is_array($meeting->settings) ? $meeting->settings : [];
        $settings['waiting_room_enabled'] = $enabled;
        $meeting->update(['settings' => $settings]);
        $meeting->refresh();

        if (! $enabled) {
            $this->admitAllPending($meeting);
        }

        return $meeting;
    }

    /**
     * After host admission (or direct join): build JSON payload including LiveKit token.
     *
     * @return array<string, mixed>
     */
    public function buildGuestEnterPayload(
        ClassroomMeeting $meeting,
        string $displayName,
        ?string $ip = null,
        ?string $userAgent = null,
        ?ClassroomMeetingWaitingGuest $waitingGuest = null
    ): array {
        return DB::transaction(function () use ($meeting, $displayName, $ip, $userAgent, $waitingGuest) {
            if ($waitingGuest) {
                $waitingGuest = ClassroomMeetingWaitingGuest::query()
                    ->whereKey($waitingGuest->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($waitingGuest->isConsumed() && $waitingGuest->participant) {
                    return $this->payloadFromParticipant($meeting, $waitingGuest->participant);
                }

                if (! $waitingGuest->isAdmitted()) {
                    return [
                        'ok' => false,
                        'waiting' => true,
                        'status' => $waitingGuest->status,
                        'message' => 'بانتظار قبول المضيف.',
                    ];
                }
            }

            $this->assertCanJoinMeeting($meeting);

            $participant = null;
            if ($waitingGuest && $waitingGuest->classroom_meeting_participant_id) {
                $participant = ClassroomMeetingParticipant::query()->find($waitingGuest->classroom_meeting_participant_id);
            }

            if (! $participant) {
                $token = Str::random(48);
                $participant = ClassroomMeetingParticipant::create([
                    'classroom_meeting_id' => $meeting->id,
                    'token' => $token,
                    'display_name' => $displayName,
                    'ip_address' => $ip,
                    'user_agent' => $userAgent ? substr((string) $userAgent, 0, 255) : null,
                    'joined_at' => now(),
                    'last_seen_at' => now(),
                ]);

                $newCount = $this->activeParticipantsCount($meeting->id);
                if ($newCount > (int) ($meeting->participants_peak ?? 0)) {
                    $meeting->update(['participants_peak' => $newCount]);
                }
            }

            if ($waitingGuest && ! $waitingGuest->isConsumed()) {
                $waitingGuest->update([
                    'status' => ClassroomMeetingWaitingGuest::STATUS_CONSUMED,
                    'classroom_meeting_participant_id' => $participant->id,
                    'consumed_at' => now(),
                ]);
            }

            return $this->payloadFromParticipant($meeting, $participant);
        });
    }

    /**
     * Poll waiting status for a guest token.
     *
     * @return array<string, mixed>
     */
    public function pollWaitingStatus(ClassroomMeeting $meeting, string $waitingToken): array
    {
        $guest = ClassroomMeetingWaitingGuest::query()
            ->where('classroom_meeting_id', $meeting->id)
            ->where('waiting_token', $waitingToken)
            ->first();

        if (! $guest) {
            return ['ok' => false, 'message' => 'طلب الانتظار غير موجود.', 'invalid' => true];
        }

        if ($meeting->ended_at) {
            return ['ok' => false, 'ended' => true, 'message' => 'هذا الاجتماع تم إنهاؤه.'];
        }

        if (! $meeting->started_at) {
            return [
                'ok' => false,
                'waiting' => true,
                'reason' => 'meeting_not_started',
                'message' => 'المعلم لم يبدأ الجلسة بعد.',
            ];
        }

        if ($guest->isDenied()) {
            return [
                'ok' => false,
                'denied' => true,
                'message' => 'رفض المضيف طلب دخولك.',
            ];
        }

        if ($guest->isCancelled()) {
            return [
                'ok' => false,
                'cancelled' => true,
                'message' => 'تم إلغاء طلب الانتظار.',
            ];
        }

        if ($guest->isConsumed() && $guest->participant) {
            return $this->payloadFromParticipant($meeting, $guest->participant);
        }

        if ($guest->isAdmitted()) {
            return $this->buildGuestEnterPayload(
                $meeting,
                $guest->display_name,
                $guest->ip_address,
                $guest->user_agent,
                $guest
            );
        }

        if (! $meeting->waitingRoomEnabled()) {
            return $this->buildGuestEnterPayload(
                $meeting,
                $guest->display_name,
                $guest->ip_address,
                $guest->user_agent,
                $this->admitGuest($meeting, $guest)
            );
        }

        return [
            'ok' => false,
            'waiting' => true,
            'reason' => 'host_admit_pending',
            'status' => ClassroomMeetingWaitingGuest::STATUS_PENDING,
            'message' => 'بانتظار قبول المضيف.',
        ];
    }

    public function assertCanJoinMeeting(ClassroomMeeting $meeting): void
    {
        if ($meeting->ended_at) {
            abort(422, 'هذا الاجتماع تم إنهاؤه من المعلم.');
        }

        if (! $meeting->started_at) {
            abort(422, 'المعلم لم يبدأ الجلسة بعد.');
        }

        if (SubscriptionLimitService::expireMeetingIfPastDuration($meeting)) {
            abort(422, 'انتهت مدة هذا الاجتماع حسب قيود الباقة.');
        }

        $owner = $meeting->user;
        $maxParticipants = (int) ($meeting->max_participants ?: 25);
        if ($owner && ! $meeting->consultation_request_id) {
            $limits = SubscriptionLimitService::limitsForUser($owner);
            $maxParticipants = min($maxParticipants, (int) $limits['classroom_max_participants']);
        }

        if ($this->activeParticipantsCount($meeting->id) >= $maxParticipants) {
            abort(422, 'تم الوصول للحد الأقصى للطلاب في هذا الاجتماع.');
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function payloadFromParticipant(ClassroomMeeting $meeting, ClassroomMeetingParticipant $participant): array
    {
        $owner = $meeting->user;
        $maxParticipants = (int) ($meeting->max_participants ?: 25);
        if ($owner && ! $meeting->consultation_request_id) {
            $limits = SubscriptionLimitService::limitsForUser($owner);
            $maxParticipants = min($maxParticipants, (int) $limits['classroom_max_participants']);
        }

        $payload = array_merge([
            'ok' => true,
            'token' => $participant->token,
            'active_participants' => $this->activeParticipantsCount($meeting->id),
            'max_participants' => $maxParticipants,
            'live_provider' => $meeting->liveProvider(),
        ], $meeting->guestPermissionsPayload());

        if ($meeting->usesLiveKit()) {
            $livekit = app(LiveKitTokenService::class);
            if (! $livekit->isConfigured()) {
                abort(503, 'جلسة LiveKit لكن المفاتيح غير مضبوطة على التطبيق.');
            }
            $sources = ['camera', 'microphone'];
            if ($meeting->allowsParticipantScreenShare()) {
                $sources[] = 'screen_share';
            }
            $payload['livekit'] = [
                'url' => $livekit->wsUrl(),
                'token' => $livekit->createToken(
                    $meeting->room_name,
                    'guest-'.substr(hash('sha256', $participant->token), 0, 16),
                    $participant->display_name ?? 'ضيف',
                    [
                        'canPublish' => true,
                        'canSubscribe' => true,
                        'canPublishData' => true,
                        'canPublishSources' => $sources,
                    ]
                ),
                'room' => $meeting->room_name,
            ];
        }

        return $payload;
    }

    public function activeParticipantsCount(int $meetingId): int
    {
        return ClassroomMeetingParticipant::query()
            ->where('classroom_meeting_id', $meetingId)
            ->whereNull('left_at')
            ->where('last_seen_at', '>=', now()->subMinutes(2))
            ->count();
    }

    public function normalizeDisplayName(Request $request): string
    {
        $displayName = trim((string) $request->input('display_name', 'ضيف'));
        if ($displayName === '') {
            $displayName = 'ضيف';
        }

        return mb_substr($displayName, 0, 120);
    }
}

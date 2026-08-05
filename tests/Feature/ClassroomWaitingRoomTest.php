<?php

namespace Tests\Feature;

use App\Models\ClassroomMeeting;
use App\Models\ClassroomMeetingParticipant;
use App\Models\ClassroomMeetingWaitingGuest;
use App\Models\User;
use App\Services\ClassroomWaitingRoomService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClassroomWaitingRoomTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('app.key', 'base64:AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA=');
        Config::set('services.livekit.enabled', true);
        Config::set('services.livekit.url', 'wss://test.example/livekit');
        Config::set('services.livekit.api_key', 'testkey');
        Config::set('services.livekit.api_secret', 'testsecretsecretsecretsecret12');

        $this->ensureSchema();
    }

    private function ensureSchema(): void
    {
        if (! Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->string('name')->nullable();
                $table->string('email')->nullable();
                $table->string('password')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('classroom_meetings')) {
            Schema::create('classroom_meetings', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->unsignedBigInteger('consultation_request_id')->nullable();
                $table->string('code', 32)->nullable();
                $table->string('room_name')->nullable();
                $table->string('title')->nullable();
                $table->unsignedInteger('max_participants')->nullable();
                $table->unsignedInteger('participants_peak')->nullable();
                $table->unsignedInteger('planned_duration_minutes')->nullable();
                $table->timestamp('started_at')->nullable();
                $table->timestamp('ended_at')->nullable();
                $table->json('settings')->nullable();
                $table->timestamps();
            });
        } elseif (! Schema::hasColumn('classroom_meetings', 'settings')) {
            Schema::table('classroom_meetings', function (Blueprint $table) {
                $table->json('settings')->nullable();
            });
        }

        if (! Schema::hasTable('classroom_meeting_participants')) {
            Schema::create('classroom_meeting_participants', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('classroom_meeting_id');
                $table->string('token', 64)->unique();
                $table->string('display_name', 120)->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('joined_at')->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamp('left_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('classroom_meeting_waiting_guests')) {
            Schema::create('classroom_meeting_waiting_guests', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('classroom_meeting_id');
                $table->string('waiting_token', 64)->unique();
                $table->string('display_name', 120);
                $table->string('status', 20)->default('pending');
                $table->unsignedBigInteger('classroom_meeting_participant_id')->nullable();
                $table->string('ip_address', 45)->nullable();
                $table->string('user_agent', 255)->nullable();
                $table->timestamp('admitted_at')->nullable();
                $table->timestamp('denied_at')->nullable();
                $table->timestamp('consumed_at')->nullable();
                $table->timestamp('cancelled_at')->nullable();
                $table->timestamps();
            });
        }

        if (! Schema::hasTable('subscriptions')) {
            Schema::create('subscriptions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('user_id');
                $table->string('status')->default('active');
                $table->date('end_date')->nullable();
                $table->timestamps();
            });
        }
    }

    private function createMeeting(array $overrides = []): ClassroomMeeting
    {
        $userId = User::query()->insertGetId([
            'name' => 'Teacher',
            'email' => 'teacher-'.uniqid().'@example.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $id = ClassroomMeeting::query()->insertGetId(array_merge([
            'user_id' => $userId,
            'consultation_request_id' => 1,
            'code' => 'WAIT'.strtoupper(substr(uniqid(), -4)),
            'room_name' => 'Muallimx-WAIT',
            'title' => 'Waiting Room Test',
            'max_participants' => 25,
            'planned_duration_minutes' => 60,
            'started_at' => now(),
            'settings' => json_encode(['waiting_room_enabled' => false]),
            'created_at' => now(),
            'updated_at' => now(),
        ], $overrides));

        return ClassroomMeeting::query()->findOrFail($id);
    }

    public function test_waiting_room_routes_are_registered(): void
    {
        foreach ([
            'classroom.join.waiting-status',
            'classroom.join.waiting-cancel',
            'student.classroom.waiting-room',
            'student.classroom.waiting-room.admit',
            'student.classroom.waiting-room.deny',
        ] as $name) {
            $this->assertTrue(Route::has($name), 'Missing route: '.$name);
        }
    }

    public function test_default_waiting_room_is_disabled(): void
    {
        $meeting = $this->createMeeting(['settings' => null]);
        $this->assertFalse($meeting->waitingRoomEnabled());
    }

    public function test_direct_join_when_waiting_room_disabled(): void
    {
        $meeting = $this->createMeeting([
            'code' => 'OPEN01',
            'room_name' => 'Muallimx-OPEN01',
            'settings' => json_encode(['waiting_room_enabled' => false]),
        ]);

        $res = $this->postJson('/classroom/join/OPEN01/enter', [
            'display_name' => 'طالب 1',
        ]);

        $res->assertOk()
            ->assertJsonPath('ok', true)
            ->assertJsonStructure(['token', 'livekit' => ['token', 'url']]);

        $this->assertSame(1, ClassroomMeetingParticipant::query()->count());
        $this->assertSame(0, ClassroomMeetingWaitingGuest::query()->count());
    }

    public function test_waiting_room_creates_pending_guest_without_livekit_token(): void
    {
        $meeting = $this->createMeeting([
            'code' => 'WAIT01',
            'room_name' => 'Muallimx-WAIT01',
            'settings' => json_encode(['waiting_room_enabled' => true]),
        ]);

        $res = $this->postJson('/classroom/join/WAIT01/enter', [
            'display_name' => 'ضيف منتظر',
        ]);

        $res->assertStatus(422)
            ->assertJsonPath('waiting', true)
            ->assertJsonPath('reason', 'host_admit_pending')
            ->assertJsonStructure(['waiting_token']);

        $this->assertSame(0, ClassroomMeetingParticipant::query()->count());
        $this->assertSame(1, ClassroomMeetingWaitingGuest::query()->where('status', 'pending')->count());
    }

    public function test_admit_guest_issues_livekit_payload_idempotently(): void
    {
        $meeting = $this->createMeeting([
            'code' => 'WAIT02',
            'room_name' => 'Muallimx-WAIT02',
            'settings' => json_encode(['waiting_room_enabled' => true]),
        ]);

        $enter = $this->postJson('/classroom/join/WAIT02/enter', ['display_name' => 'أحمد']);
        $waitingToken = (string) $enter->json('waiting_token');
        $this->assertNotSame('', $waitingToken);

        $guest = ClassroomMeetingWaitingGuest::query()->firstOrFail();
        app(ClassroomWaitingRoomService::class)->admitGuest($meeting, $guest);

        $first = $this->postJson('/classroom/join/WAIT02/waiting-status', [
            'waiting_token' => $waitingToken,
        ]);
        $first->assertOk()->assertJsonPath('ok', true)->assertJsonStructure(['livekit' => ['token']]);

        $second = $this->postJson('/classroom/join/WAIT02/waiting-status', [
            'waiting_token' => $waitingToken,
        ]);
        $second->assertOk()->assertJsonPath('ok', true);

        $this->assertSame(1, ClassroomMeetingParticipant::query()->count());
        $this->assertSame('consumed', ClassroomMeetingWaitingGuest::query()->find($guest->id)->status);
    }

    public function test_deny_guest_returns_denied_status(): void
    {
        $meeting = $this->createMeeting([
            'code' => 'WAIT03',
            'room_name' => 'Muallimx-WAIT03',
            'settings' => json_encode(['waiting_room_enabled' => true]),
        ]);

        $enter = $this->postJson('/classroom/join/WAIT03/enter', ['display_name' => 'مرفوض']);
        $waitingToken = (string) $enter->json('waiting_token');
        $guest = ClassroomMeetingWaitingGuest::query()->firstOrFail();

        app(ClassroomWaitingRoomService::class)->denyGuest($meeting, $guest);

        $res = $this->postJson('/classroom/join/WAIT03/waiting-status', [
            'waiting_token' => $waitingToken,
        ]);

        $res->assertStatus(422)->assertJsonPath('denied', true);
        $this->assertSame(0, ClassroomMeetingParticipant::query()->count());
    }

    public function test_disabling_waiting_room_admits_all_pending(): void
    {
        $meeting = $this->createMeeting([
            'code' => 'WAIT04',
            'room_name' => 'Muallimx-WAIT04',
            'settings' => json_encode(['waiting_room_enabled' => true]),
        ]);

        $service = app(ClassroomWaitingRoomService::class);
        $service->createWaitingGuest($meeting, 'ضيف 1');
        $service->createWaitingGuest($meeting, 'ضيف 2');
        $this->assertSame(2, $service->pendingCount($meeting));

        $service->setWaitingRoomEnabled($meeting, false);
        $meeting->refresh();

        $this->assertFalse($meeting->waitingRoomEnabled());
        $this->assertSame(0, $service->pendingCount($meeting));
        $this->assertSame(2, ClassroomMeetingWaitingGuest::query()->where('status', 'admitted')->count());
    }

    public function test_guest_from_another_meeting_cannot_be_admitted(): void
    {
        $first = $this->createMeeting([
            'code' => 'WAIT11',
            'room_name' => 'Muallimx-WAIT11',
            'settings' => json_encode(['waiting_room_enabled' => true]),
        ]);
        $second = $this->createMeeting([
            'code' => 'WAIT12',
            'room_name' => 'Muallimx-WAIT12',
            'settings' => json_encode(['waiting_room_enabled' => true]),
        ]);

        $guest = app(ClassroomWaitingRoomService::class)->createWaitingGuest($first, 'ضيف');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\NotFoundHttpException::class);
        app(ClassroomWaitingRoomService::class)->admitGuest($second, $guest);
    }

    public function test_expired_pending_requests_are_cleaned_up(): void
    {
        $meeting = $this->createMeeting([
            'code' => 'WAIT13',
            'room_name' => 'Muallimx-WAIT13',
            'settings' => json_encode(['waiting_room_enabled' => true]),
        ]);

        $guest = app(ClassroomWaitingRoomService::class)->createWaitingGuest($meeting, 'قديم');
        $guest->forceFill([
            'created_at' => now()->subHours(7),
            'updated_at' => now()->subHours(7),
        ])->save();

        $this->assertSame(1, ClassroomMeetingWaitingGuest::query()->count());
        $this->assertSame(0, app(ClassroomWaitingRoomService::class)->pendingCount($meeting));
        $this->assertSame(0, ClassroomMeetingWaitingGuest::query()->count());
    }

    public function test_meeting_not_started_returns_waiting_without_token(): void
    {
        $meeting = $this->createMeeting([
            'code' => 'WAIT05',
            'room_name' => 'Muallimx-WAIT05',
            'started_at' => null,
            'settings' => json_encode(['waiting_room_enabled' => true]),
        ]);

        $res = $this->postJson('/classroom/join/WAIT05/enter', ['display_name' => 'مبكر']);

        $res->assertStatus(422)
            ->assertJsonPath('waiting', true)
            ->assertJsonPath('reason', 'meeting_not_started')
            ->assertJsonMissing(['waiting_token']);

        $this->assertSame(0, ClassroomMeetingWaitingGuest::query()->count());
    }
}

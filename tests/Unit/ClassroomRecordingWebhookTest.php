<?php

namespace Tests\Unit;

use App\Models\ClassroomMeeting;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ClassroomRecordingWebhookTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Config::set('services.live_recordings_webhook.token', 'secret-token');

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
                $table->string('code', 32)->nullable();
                $table->string('room_name')->nullable();
                $table->string('title')->nullable();
                $table->unsignedInteger('max_participants')->nullable();
                $table->string('recording_disk')->nullable();
                $table->string('recording_path')->nullable();
                $table->string('recording_mime_type')->nullable();
                $table->unsignedBigInteger('recording_size')->nullable();
                $table->unsignedInteger('recording_duration_seconds')->nullable();
                $table->timestamp('recording_uploaded_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function test_webhook_rejects_missing_token(): void
    {
        $this->postJson('/api/classroom-recordings/register', [
            'room_name' => 'mx-test',
            'file_path' => 'classroom-recordings/x.mp4',
        ])->assertStatus(401);
    }

    public function test_webhook_requires_meeting_identifier(): void
    {
        $this->postJson('/api/classroom-recordings/register', [
            'file_path' => 'classroom-recordings/x.mp4',
        ], [
            'X-Webhook-Token' => 'secret-token',
        ])->assertStatus(422);
    }

    public function test_webhook_links_recording_by_meeting_id(): void
    {
        $userId = User::query()->insertGetId([
            'name' => 'Teacher',
            'email' => 'teacher-webhook@example.com',
            'password' => bcrypt('secret'),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $meetingId = ClassroomMeeting::query()->insertGetId([
            'user_id' => $userId,
            'code' => 'ABC123',
            'room_name' => 'mx-abc123',
            'title' => 'Test',
            'max_participants' => 50,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $res = $this->postJson('/api/classroom-recordings/register', [
            'classroom_meeting_id' => $meetingId,
            'room_name' => 'mx-abc123',
            'file_path' => 'classroom-recordings/2026/07/demo.mp4',
            'mime_type' => 'video/mp4',
            'duration_seconds' => 42,
            'file_size' => 9999,
        ], [
            'X-Webhook-Token' => 'secret-token',
        ]);

        $res->assertCreated()->assertJsonPath('success', true);

        $meeting = ClassroomMeeting::query()->find($meetingId);
        $this->assertNotNull($meeting);
        $this->assertSame('classroom-recordings/2026/07/demo.mp4', $meeting->recording_path);
        $this->assertSame('live_recordings_r2', $meeting->recording_disk);
        $this->assertSame(42, (int) $meeting->recording_duration_seconds);
        $this->assertSame(9999, (int) $meeting->recording_size);
    }
}

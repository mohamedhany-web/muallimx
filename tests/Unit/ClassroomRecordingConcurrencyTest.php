<?php

namespace Tests\Unit;

use App\Http\Controllers\Student\ClassroomController;
use App\Support\ClassroomRecordingGuard;
use ReflectionMethod;
use Tests\TestCase;

class ClassroomRecordingConcurrencyTest extends TestCase
{
    public function test_guard_rejects_empty_and_tiny_files(): void
    {
        $this->assertNotNull(ClassroomRecordingGuard::validateSize(0, 10));
        $this->assertNotNull(ClassroomRecordingGuard::validateSize(100, 10));
        $this->assertNull(ClassroomRecordingGuard::validateSize(ClassroomRecordingGuard::MIN_BYTES, 10));
    }

    public function test_guard_rejects_long_duration_with_tiny_payload(): void
    {
        $err = ClassroomRecordingGuard::validateSize(5000, 600, 'ملف التسجيل');
        $this->assertNotNull($err);
        $this->assertStringContainsString('مدة التسجيل', $err);
    }

    public function test_unique_recording_paths_never_collide_across_teachers(): void
    {
        $controller = app(ClassroomController::class);
        $method = new ReflectionMethod(ClassroomController::class, 'makeUniqueRecordingObject');
        $method->setAccessible(true);

        $paths = [];
        for ($i = 0; $i < 40; $i++) {
            $meetingId = 1000 + ($i % 10);
            $userId = 2000 + $i;
            [, , $full] = $method->invoke($controller, $meetingId, $userId, 'video', 'webm');
            $this->assertStringContainsString('classroom-recordings/', $full);
            $this->assertStringContainsString('meeting-'.$meetingId.'-u'.$userId.'-', $full);
            $paths[] = $full;
        }

        $this->assertCount(40, array_unique($paths));
    }

    public function test_audio_and_video_paths_use_separate_roots(): void
    {
        $controller = app(ClassroomController::class);
        $method = new ReflectionMethod(ClassroomController::class, 'makeUniqueRecordingObject');
        $method->setAccessible(true);

        [, , $video] = $method->invoke($controller, 55, 9, 'video', 'webm');
        [, , $audio] = $method->invoke($controller, 55, 9, 'audio', 'webm');

        $this->assertStringStartsWith('classroom-recordings/', $video);
        $this->assertStringStartsWith('classroom-recordings-audio/', $audio);
        $this->assertNotSame($video, $audio);
    }

    public function test_mime_normalization_defaults_to_video_webm(): void
    {
        $controller = app(ClassroomController::class);
        $method = new ReflectionMethod(ClassroomController::class, 'normalizeRecordingMime');
        $method->setAccessible(true);

        $this->assertSame('video/webm', $method->invoke($controller, ''));
        $this->assertSame('video/webm', $method->invoke($controller, 'video/webm'));
        $this->assertSame('video/mp4', $method->invoke($controller, 'video/mp4'));
        $this->assertSame('video/webm', $method->invoke($controller, 'text/plain'));
    }

    public function test_rate_limit_is_scoped_per_user_not_global(): void
    {
        $controller = app(ClassroomController::class);
        $method = new ReflectionMethod(ClassroomController::class, 'assertRecordingUploadRateLimit');
        $method->setAccessible(true);

        $userA = (object) ['id' => 91001];
        $userB = (object) ['id' => 91002];

        for ($i = 0; $i < 60; $i++) {
            $this->assertNull($method->invoke($controller, $userA));
        }
        $this->assertNotNull($method->invoke($controller, $userA));
        $this->assertNull($method->invoke($controller, $userB));
    }
}

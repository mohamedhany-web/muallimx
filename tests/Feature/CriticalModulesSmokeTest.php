<?php

namespace Tests\Feature;

use App\Http\Controllers\Public\LandingController;
use App\Http\Controllers\Student\ClassroomController;
use App\Support\ClassroomRecordingGuard;
use Tests\TestCase;

/**
 * Smoke checks that critical modules resolve without fatal class/config errors.
 */
class CriticalModulesSmokeTest extends TestCase
{
    public function test_critical_classes_are_autoloadable(): void
    {
        $classes = [
            ClassroomController::class,
            LandingController::class,
            ClassroomRecordingGuard::class,
            \App\Http\Controllers\ClassroomJoinController::class,
            \App\Services\SubscriptionLimitService::class,
            \App\Services\ClassroomWhiteboardSceneService::class,
            \App\Services\ClassroomSlugService::class,
            \App\Models\ClassroomMeeting::class,
            \App\Models\ClassroomMeetingWaitingGuest::class,
            \App\Services\ClassroomWaitingRoomService::class,
            \App\Models\CurriculumPresentationDerivative::class,
            \App\Services\CurriculumPresentationConversionService::class,
            \App\Services\CurriculumPresentationViewerService::class,
            \App\Services\ClassroomCurriculumPresentService::class,
            \App\Jobs\ConvertCurriculumPresentationJob::class,
            \App\Http\Controllers\Api\ClassroomRecordingWebhookController::class,
            \App\Http\Controllers\Admin\ClassroomRecordingController::class,
        ];

        foreach ($classes as $class) {
            $this->assertTrue(class_exists($class), $class.' missing');
        }
    }

    public function test_filesystem_disks_for_recordings_are_configured(): void
    {
        $this->assertArrayHasKey('live_recordings_r2', config('filesystems.disks'));
        $disk = config('filesystems.disks.live_recordings_r2');
        $this->assertSame('s3', $disk['driver'] ?? null);
    }

    public function test_classroom_room_view_exists(): void
    {
        $this->assertTrue(view()->exists('student.classroom.room'));
        $this->assertTrue(view()->exists('student.classroom.recording-upload-tab'));
        $this->assertTrue(view()->exists('classroom.join'));
    }
}

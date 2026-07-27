<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_application_config_boots(): void
    {
        $this->assertSame('testing', config('app.env'));
        $this->assertNotEmpty(config('app.key'));
        $this->assertSame('Muallimx', config('app.name'));
    }

    public function test_classroom_recording_routes_are_registered(): void
    {
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('student.classroom.recording.presign'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('student.classroom.recording.complete'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('student.classroom.recording.upload'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('student.classroom.recording-audio.presign'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('student.classroom.recording-audio.complete'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('instructor.classroom.recording.presign'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('student.classroom.room'));
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('classroom.join'));
    }
}

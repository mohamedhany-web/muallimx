<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lecture;
use App\Models\AdvancedCourse;
use App\Models\CourseLesson;
use App\Models\User;
use Illuminate\Http\Request;

class LectureController extends Controller
{
    /**
     * عرض قائمة الكورسات (الدخول للكورس يعرض محاضراته).
     */
    public function index()
    {
        $courses = AdvancedCourse::where('is_active', true)
            ->withCount('lectures')
            ->orderBy('title')
            ->get();

        return view('admin.lectures.index', compact('courses'));
    }

    /**
     * عرض محاضرات كورس معين مع روابط CRUD.
     */
    public function indexByCourse(AdvancedCourse $course)
    {
        $course->loadCount('lectures');
        $lectures = $course->lectures()
            ->with('instructor')
            ->orderBy('scheduled_at', 'desc')
            ->paginate(20);

        return view('admin.lectures.by-course', compact('course', 'lectures'));
    }

    public function create(Request $request)
    {
        $courses = AdvancedCourse::where('is_active', true)->get();
        $instructors = User::where('role', 'instructor')->where('is_active', true)->get();
        $preselectedCourseId = $request->query('course_id');

        return view('admin.lectures.create', compact('courses', 'instructors', 'preselectedCourseId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:advanced_courses,id',
            'instructor_id' => 'required|exists:users,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'teams_registration_link' => 'nullable|url',
            'teams_meeting_link' => 'nullable|url',
            'recording_url' => 'nullable|url',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:1',
            'notes' => 'nullable|string',
            'has_attendance_tracking' => 'boolean',
            'has_assignment' => 'boolean',
            'has_evaluation' => 'boolean',
        ]);

        $lecture = Lecture::create($validated);

        return redirect()->route('admin.lectures.by-course', $lecture->course_id)
            ->with('success', 'تم إنشاء المحاضرة بنجاح');
    }

    public function show(Lecture $lecture)
    {
        $lecture->load([
            'course', 'instructor', 'lesson',
            'materials', 'assignments', 'attendanceRecords', 'evaluations',
        ]);

        return view('admin.lectures.show', compact('lecture'));
    }

    public function edit(Lecture $lecture)
    {
        $courses = AdvancedCourse::where('is_active', true)->get();
        $instructors = User::where('role', 'instructor')->where('is_active', true)->get();
        $lessons = CourseLesson::where('advanced_course_id', $lecture->course_id)->orderBy('order')->get();

        return view('admin.lectures.edit', compact('lecture', 'courses', 'instructors', 'lessons'));
    }

    public function update(Request $request, Lecture $lecture)
    {
        $validated = $request->validate([
            'course_id' => 'required|exists:advanced_courses,id',
            'instructor_id' => 'required|exists:users,id',
            'course_lesson_id' => 'nullable|exists:course_lessons,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'recording_url' => 'nullable|url',
            'video_platform' => 'nullable|string|max:50',
            'scheduled_at' => 'required|date',
            'duration_minutes' => 'required|integer|min:1',
            'status' => 'required|in:scheduled,in_progress,completed,cancelled',
            'notes' => 'nullable|string',
            'has_attendance_tracking' => 'boolean',
            'has_assignment' => 'boolean',
            'has_evaluation' => 'boolean',
        ]);

        $validated['course_lesson_id'] = $validated['course_lesson_id'] ?? null;
        $lecture->update($validated);

        return redirect()->route('admin.lectures.by-course', $lecture->course_id)
            ->with('success', 'تم تحديث المحاضرة بنجاح');
    }

    public function destroy(Lecture $lecture)
    {
        $courseId = $lecture->course_id;
        $lecture->delete();

        return redirect()->route('admin.lectures.by-course', $courseId)
            ->with('success', 'تم حذف المحاضرة بنجاح');
    }
}

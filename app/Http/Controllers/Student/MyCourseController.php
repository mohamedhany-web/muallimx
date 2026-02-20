<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\LectureMaterial;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class MyCourseController extends Controller
{
    /**
     * عرض الكورسات المفعلة للطالب
     */
    public function index()
    {
        $user = Auth::user();
        
        // الكورسات المفعلة للطالب
        $activeCourses = $user->activeCourses()
            ->with(['academicYear', 'academicSubject', 'teacher', 'lessons'])
            ->paginate(12);

        // إحصائيات
        $stats = [
            'total_active' => $user->activeCourses()->count(),
            'total_completed' => $user->courseEnrollments()->where('status', 'completed')->count(),
            'total_hours' => $user->activeCourses()->sum('duration_hours'),
            'avg_progress' => $this->calculateAverageProgress($user),
        ];

        return view('student.my-courses.index', compact('activeCourses', 'stats'));
    }

    /**
     * عرض تفاصيل الكورس المفعل
     */
    public function show($courseId)
    {
        $user = Auth::user();
        
        // التحقق من أن الطالب مسجل في الكورس
        $course = $user->activeCourses()
            ->with([
                'academicYear', 
                'academicSubject', 
                'teacher', 
                'lessons.progress' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
                'lectures' => function($query) {
                    $query->orderBy('scheduled_at', 'desc');
                },
                'lectures.lesson',
                'lectures.instructor',
                'sections.items.item' => function($query) use ($user) {
                    // جلب تقدم الدروس والأنماط في العناصر
                    if ($query->getModel() instanceof \App\Models\CourseLesson) {
                        $query->with(['progress' => function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        }]);
                    } elseif ($query->getModel() instanceof \App\Models\LearningPattern) {
                        $query->with(['attempts' => function($q) use ($user) {
                            $q->where('user_id', $user->id)->latest();
                        }]);
                    }
                }
            ])
            ->findOrFail($courseId);

        // جلب الأقسام مع العناصر مرتبة
        $sections = $course->activeSections()
            ->with(['activeItems' => function($query) {
                $query->orderBy('order')->with('item');
            }])
            ->orderBy('order')
            ->get();
        
        // تحميل تقدم الدروس والأنماط والامتحانات والواجبات في العناصر
        foreach ($sections as $section) {
            foreach ($section->activeItems as $curriculumItem) {
                $entity = $curriculumItem->item;
                if ($entity instanceof \App\Models\CourseLesson) {
                    $entity->load(['progress' => function($q) use ($user) { $q->where('user_id', $user->id); }]);
                } elseif ($entity instanceof \App\Models\LearningPattern) {
                    $entity->load(['attempts' => function($q) use ($user) { $q->where('user_id', $user->id)->latest(); }]);
                } elseif ($entity instanceof \App\Models\AdvancedExam) {
                    $entity->load(['attempts' => function($q) use ($user) { $q->where('user_id', $user->id)->whereNotNull('submitted_at'); }]);
                } elseif ($entity instanceof \App\Models\Assignment) {
                    $entity->load(['submissions' => function($q) use ($user) { $q->where('student_id', $user->id); }]);
                }
            }
        }

        list($progress, $totalLessons, $completedLessons) = $this->calculateProgressFromSections($user, $course, $sections);

        // تجميع المحاضرات حسب الدرس (للتوافق مع الكود القديم)
        $lecturesByLesson = $course->lectures->groupBy('course_lesson_id');

        return view('student.my-courses.show', compact(
            'course', 
            'progress', 
            'totalLessons', 
            'completedLessons', 
            'lecturesByLesson',
            'sections'
        ));
    }

    /**
     * صفحة عرض المحتوى (Focus Mode)
     */
    public function learn($courseId, Request $request)
    {
        $user = Auth::user();
        
        // التحقق من أن الطالب مسجل في الكورس
        $course = $user->activeCourses()
            ->with([
                'academicYear', 
                'academicSubject', 
                'teacher', 
                'lessons.progress' => function($query) use ($user) {
                    $query->where('user_id', $user->id);
                },
                'lectures' => function($query) {
                    $query->orderBy('scheduled_at', 'desc');
                },
                'lectures.lesson',
                'lectures.instructor',
                'sections.items.item' => function($query) use ($user) {
                    // جلب تقدم الدروس والأنماط في العناصر
                    if ($query->getModel() instanceof \App\Models\CourseLesson) {
                        $query->with(['progress' => function($q) use ($user) {
                            $q->where('user_id', $user->id);
                        }]);
                    } elseif ($query->getModel() instanceof \App\Models\LearningPattern) {
                        $query->with(['attempts' => function($q) use ($user) {
                            $q->where('user_id', $user->id)->latest();
                        }]);
                    }
                }
            ])
            ->findOrFail($courseId);
        
        // جلب الدرس إذا تم تمرير lesson_id
        $lesson = null;
        if ($request->has('lesson')) {
            $lessonId = $request->input('lesson');
            $lesson = $course->lessons()->find($lessonId);
            
            // التحقق من أن الدرس نشط
            if ($lesson && !$lesson->is_active) {
                $lesson = null;
            }
            
            // التحقق من ترتيب الدروس (لا يمكن مشاهدة درس قبل إكمال السابق)
            if ($lesson) {
                $previousLessons = $course->lessons()
                    ->where('order', '<', $lesson->order)
                    ->where('is_active', true)
                    ->get();
                    
                foreach ($previousLessons as $prevLesson) {
                    $prevProgress = \App\Models\LessonProgress::where('user_id', $user->id)
                        ->where('course_lesson_id', $prevLesson->id)
                        ->first();
                        
                    if (!$prevProgress || !$prevProgress->is_completed) {
                        $lesson = null;
                        break;
                    }
                }
            }
        }

        // جلب الأقسام مع العناصر مرتبة
        $sections = $course->activeSections()
            ->with(['activeItems' => function($query) {
                $query->orderBy('order')->with('item');
            }])
            ->orderBy('order')
            ->get();
        
        // تحميل تقدم الدروس والأنماط والامتحانات في العناصر
        foreach ($sections as $section) {
            foreach ($section->activeItems as $curriculumItem) {
                if ($curriculumItem->item instanceof \App\Models\CourseLesson) {
                    $curriculumItem->item->load(['progress' => function($q) use ($user) {
                        $q->where('user_id', $user->id);
                    }]);
                } elseif ($curriculumItem->item instanceof \App\Models\LearningPattern) {
                    $curriculumItem->item->load(['attempts' => function($q) use ($user) {
                        $q->where('user_id', $user->id)->latest();
                    }]);
                } elseif ($curriculumItem->item instanceof \App\Models\AdvancedExam) {
                    $curriculumItem->item->load(['attempts' => function($q) use ($user) {
                        $q->where('user_id', $user->id)->whereNotNull('submitted_at');
                    }]);
                } elseif ($curriculumItem->item instanceof \App\Models\Assignment) {
                    $curriculumItem->item->load(['submissions' => function($q) use ($user) { $q->where('student_id', $user->id); }]);
                }
            }
        }

        list($progress, $totalLessons, $completedLessons) = $this->calculateProgressFromSections($user, $course, $sections);

        // تجميع المحاضرات حسب الدرس (للتوافق مع الكود القديم)
        $lecturesByLesson = $course->lectures->groupBy('course_lesson_id');

        // جلب الاختبارات المرتبة حسب الموضع في السايدبار
        $sidebarExams = \App\Models\AdvancedExam::where('advanced_course_id', $course->id)
            ->where('is_active', true)
            ->where(function($q) {
                $q->where('show_in_sidebar', true)
                  ->orWhereNull('show_in_sidebar'); // للتوافق مع البيانات القديمة
            })
            ->orderByRaw('CASE WHEN sidebar_position IS NULL THEN 999 ELSE sidebar_position END ASC')
            ->orderBy('created_at', 'asc')
            ->get();

        return view('student.my-courses.learn', compact(
            'course', 
            'progress', 
            'totalLessons', 
            'completedLessons', 
            'lecturesByLesson',
            'sections',
            'sidebarExams',
            'lesson'
        ));
    }

    /**
     * إرجاع بيانات محاضرة واحدة كـ JSON (لصفحة التعلم - جلب الفيديو عند الحاجة)
     */
    public function getLectureData($courseId, $lectureId)
    {
        $user = Auth::user();
        $course = $user->activeCourses()->findOrFail($courseId);
        $lecture = $course->lectures()->findOrFail($lectureId);

        $recordingUrl = $lecture->recording_url ? trim($lecture->recording_url) : null;
        $videoPlatform = $lecture->video_platform ? trim(strtolower($lecture->video_platform)) : null;

        $materials = $lecture->materials()
            ->where('is_visible_to_student', true)
            ->orderBy('sort_order')
            ->get()
            ->map(function ($m) use ($courseId, $lectureId) {
                return [
                    'id' => $m->id,
                    'title' => $m->title ?: $m->file_name,
                    'file_name' => $m->file_name,
                    'download_url' => route('my-courses.lectures.material.download', [$courseId, $lectureId, $m->id]),
                ];
            });

        return response()->json([
            'id' => $lecture->id,
            'title' => $lecture->title,
            'description' => $lecture->description,
            'scheduled_at' => $lecture->scheduled_at ? $lecture->scheduled_at->toIso8601String() : null,
            'scheduled_at_formatted' => $lecture->scheduled_at ? $lecture->scheduled_at->format('Y/m/d H:i') : null,
            'duration_minutes' => $lecture->duration_minutes ?? 60,
            'recording_url' => $recordingUrl,
            'video_platform' => $videoPlatform,
            'teams_meeting_link' => $lecture->teams_meeting_link ?? null,
            'teams_registration_link' => $lecture->teams_registration_link ?? null,
            'notes' => $lecture->notes ?? null,
            'materials' => $materials,
        ]);
    }

    /**
     * تحميل مادة محاضرة (للطالب - المواد الظاهرة فقط)
     */
    public function downloadLectureMaterial($courseId, $lectureId, $materialId)
    {
        $user = Auth::user();
        $course = $user->activeCourses()->findOrFail($courseId);
        $lecture = $course->lectures()->findOrFail($lectureId);
        $material = LectureMaterial::where('lecture_id', $lecture->id)
            ->where('id', $materialId)
            ->where('is_visible_to_student', true)
            ->firstOrFail();

        $path = Storage::disk('public')->path($material->file_path);
        if (!is_file($path)) {
            abort(404, 'الملف غير موجود');
        }

        return response()->download($path, $material->file_name);
    }

    /**
     * عرض الدرس في واجهة محمية
     */
    public function watchLesson($courseId, $lessonId)
    {
        $user = Auth::user();
        
        // التحقق من أن الطالب مسجل في الكورس
        $course = $user->activeCourses()->findOrFail($courseId);
        $lesson = $course->lessons()->findOrFail($lessonId);
        
        // التحقق من أن الدرس نشط
        if (!$lesson->is_active) {
            return redirect()->route('my-courses.show', $course)
                ->with('error', 'هذا الدرس غير متاح حالياً');
        }
        
        // التحقق من ترتيب الدروس (لا يمكن مشاهدة درس قبل إكمال السابق)
        $previousLessons = $course->lessons()
            ->where('order', '<', $lesson->order)
            ->where('is_active', true)
            ->get();
            
        foreach ($previousLessons as $prevLesson) {
            $prevProgress = \App\Models\LessonProgress::where('user_id', $user->id)
                ->where('course_lesson_id', $prevLesson->id)
                ->first();
                
            if (!$prevProgress || !$prevProgress->is_completed) {
                return redirect()->route('my-courses.show', $course)
                    ->with('error', 'يجب إكمال الدروس السابقة أولاً');
            }
        }
        
        return view('student.my-courses.lesson-viewer', compact('course', 'lesson'));
    }

    /**
     * حساب متوسط التقدم
     */
    private function calculateAverageProgress($user)
    {
        $enrollments = $user->courseEnrollments()->where('status', 'active')->get();
        if ($enrollments->isEmpty()) return 0;
        
        $totalProgress = $enrollments->sum('progress');
        return round($totalProgress / $enrollments->count(), 1);
    }

    /**
     * تحديث تقدم الدرس
     */
    public function updateLessonProgress(Request $request, $courseId, $lessonId)
    {
        $user = Auth::user();
        
        // التحقق من أن الطالب مسجل في الكورس
        $course = $user->activeCourses()->findOrFail($courseId);
        $lesson = $course->lessons()->findOrFail($lessonId);

        $watchTime = (int) $request->input('watch_time', 0);
        $clientPercent = (int) min(100, max(0, $request->input('progress_percent', 0)));

        // احتساب النسبة من الثواني المشاهدة فعلياً (منع الغش: لا نعتمد على currentTime فقط)
        $totalSeconds = $lesson->duration_minutes ? (int) ($lesson->duration_minutes * 60) : 0;
        if ($totalSeconds > 0) {
            $progressPercent = (int) min(100, round(($watchTime / $totalSeconds) * 100));
        } else {
            $progressPercent = $clientPercent;
        }
        $isCompleted = $request->boolean('completed') || $progressPercent >= 90;

        // تحديث أو إنشاء تقدم الدرس
        $progress = \App\Models\LessonProgress::updateOrCreate(
            [
                'user_id' => $user->id,
                'course_lesson_id' => $lessonId
            ],
            [
                'is_completed' => $isCompleted,
                'completed_at' => $isCompleted ? now() : null,
                'watch_time' => $watchTime,
                'progress_percent' => $progressPercent,
            ]
        );

        // تحديث التقدم الإجمالي للكورس
        $this->updateCourseProgress($user->id, $courseId);

        $course = $user->activeCourses()->findOrFail($courseId);
        $sections = $course->activeSections()->with(['activeItems' => fn ($q) => $q->with('item')])->orderBy('order')->get();
        foreach ($sections as $section) {
            foreach ($section->activeItems as $curriculumItem) {
                $entity = $curriculumItem->item;
                if ($entity instanceof \App\Models\CourseLesson) {
                    $entity->load(['progress' => fn ($q) => $q->where('user_id', $user->id)]);
                } elseif ($entity instanceof \App\Models\LearningPattern) {
                    $entity->load(['attempts' => fn ($q) => $q->where('user_id', $user->id)->latest()]);
                } elseif ($entity instanceof \App\Models\AdvancedExam) {
                    $entity->load(['attempts' => fn ($q) => $q->where('user_id', $user->id)->whereNotNull('submitted_at')]);
                } elseif ($entity instanceof \App\Models\Assignment) {
                    $entity->load(['submissions' => fn ($q) => $q->where('student_id', $user->id)]);
                }
            }
        }
        list($progressPct, $totalItems, $completedItems) = $this->calculateProgressFromSections($user, $course, $sections);

        return response()->json([
            'success' => true,
            'message' => 'تم تحديث التقدم بنجاح',
            'progress' => $progress,
            'course_progress' => $progressPct,
            'total_items' => $totalItems,
            'completed_items' => $completedItems,
        ]);
    }

    /**
     * حساب التقدم من المنهج (أقسام + عناصر) أو من الدروس فقط
     * يُرجع: [نسبة التقدم، إجمالي العناصر، العناصر المكتملة]
     */
    private function calculateProgressFromSections($user, $course, $sections)
    {
        if ($sections->isEmpty()) {
            $total = $course->lessons()->where('is_active', true)->count();
            $completed = \App\Models\LessonProgress::where('user_id', $user->id)
                ->whereIn('course_lesson_id', $course->lessons()->where('is_active', true)->pluck('id'))
                ->where('is_completed', true)
                ->count();
            $progress = $total > 0 ? round(($completed / $total) * 100, 2) : 0;
            return [$progress, $total, $completed];
        }

        $totalItems = 0;
        $completedItems = 0;

        foreach ($sections as $section) {
            foreach ($section->activeItems as $item) {
                $entity = $item->item;
                if (!$entity) continue;

                $totalItems++;
                if ($entity instanceof \App\Models\CourseLesson) {
                    $p = $entity->progress->first();
                    if ($p && $p->is_completed) $completedItems++;
                } elseif ($entity instanceof \App\Models\Lecture) {
                    // المحاضرات تُحسب في المجموع فقط (لا يوجد تتبع إكمال حالياً)
                } elseif ($entity instanceof \App\Models\Assignment) {
                    if ($entity->submissions->where('student_id', $user->id)->isNotEmpty()) $completedItems++;
                } elseif ($entity instanceof \App\Models\LearningPattern) {
                    $best = $entity->getUserBestAttempt($user->id);
                    if ($best && $best->status === 'completed') $completedItems++;
                } elseif ($entity instanceof \App\Models\AdvancedExam) {
                    $passing = (float) ($entity->passing_marks ?? 0);
                    $passed = $entity->attempts->contains(fn ($a) => $a->score !== null && (float) $a->score >= $passing);
                    if ($passed) $completedItems++;
                }
            }
        }

        $progress = $totalItems > 0 ? round(($completedItems / $totalItems) * 100, 2) : 0;
        return [$progress, $totalItems, $completedItems];
    }

    /**
     * الحصول على تقدم الكورس (نسبة مئوية)
     */
    private function getCourseProgress($userId, $courseId)
    {
        $course = \App\Models\AdvancedCourse::findOrFail($courseId);
        $user = \App\Models\User::findOrFail($userId);
        $sections = $course->activeSections()->with(['activeItems' => fn ($q) => $q->with('item')])->orderBy('order')->get();

        foreach ($sections as $section) {
            foreach ($section->activeItems as $curriculumItem) {
                $entity = $curriculumItem->item;
                if ($entity instanceof \App\Models\CourseLesson) {
                    $entity->load(['progress' => fn ($q) => $q->where('user_id', $userId)]);
                } elseif ($entity instanceof \App\Models\LearningPattern) {
                    $entity->load(['attempts' => fn ($q) => $q->where('user_id', $userId)->latest()]);
                } elseif ($entity instanceof \App\Models\AdvancedExam) {
                    $entity->load(['attempts' => fn ($q) => $q->where('user_id', $userId)->whereNotNull('submitted_at')]);
                } elseif ($entity instanceof \App\Models\Assignment) {
                    $entity->load(['submissions' => fn ($q) => $q->where('student_id', $userId)]);
                }
            }
        }

        list($progress) = $this->calculateProgressFromSections($user, $course, $sections);
        return $progress;
    }

    /**
     * تحديث التقدم الإجمالي للكورس في جدول التسجيلات (يُستدعى أيضاً بعد تسليم الامتحان)
     */
    public function updateCourseProgress($userId, $courseId)
    {
        $progress = $this->getCourseProgress($userId, $courseId);
        \App\Models\StudentCourseEnrollment::where('user_id', $userId)
            ->where('advanced_course_id', $courseId)
            ->update(['progress' => $progress]);
    }
}

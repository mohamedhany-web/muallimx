<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\TeacherCalendarAppointment;
use App\Services\TeacherCalendarTimezoneService;
use App\Services\TeacherPersonalCalendarService;
use App\Support\CalendarTimezoneCatalog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class TeacherPersonalCalendarController extends Controller
{
    public function __construct(
        protected TeacherPersonalCalendarService $calendarService,
        protected TeacherCalendarTimezoneService $timezoneService
    ) {}

    public function timezones(Request $request): JsonResponse
    {
        $q = (string) $request->query('q', '');

        return response()->json([
            'options' => $q !== ''
                ? CalendarTimezoneCatalog::search($q, 50)
                : CalendarTimezoneCatalog::popular(),
            'grouped' => $q === '' ? CalendarTimezoneCatalog::grouped() : null,
            'us_states' => CalendarTimezoneCatalog::usStates(),
            'teacher_default' => $this->timezoneService->resolveTeacherTimezone(
                Auth::user()?->calendar_timezone
            ),
        ]);
    }

    public function resolveUsState(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'state' => 'required|string|max:2',
        ]);

        $tz = CalendarTimezoneCatalog::timezoneForUsState($validated['state']);
        if (! $tz) {
            return response()->json(['message' => 'ولاية غير معروفة'], 422);
        }

        return response()->json([
            'timezone' => $tz,
            'label' => CalendarTimezoneCatalog::label($tz),
        ]);
    }

    public function preview(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'date' => 'required|date_format:Y-m-d',
            'time' => 'required|date_format:H:i',
            'family_timezone' => ['required', 'string', 'timezone:all'],
            'teacher_timezone' => ['nullable', 'string', 'timezone:all'],
        ]);

        $user = Auth::user();
        $teacherTz = $this->timezoneService->resolveTeacherTimezone(
            $validated['teacher_timezone'] ?? $user->calendar_timezone
        );

        return response()->json([
            'preview' => $this->timezoneService->formatDualTimePreview(
                $validated['date'],
                $validated['time'],
                $validated['family_timezone'],
                $teacherTz
            ),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $user = Auth::user();
        $validated = $this->validatePayload($request);

        try {
            $appointment = $this->calendarService->create($user, $validated);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'تم حفظ الموعد بنجاح.',
            'appointment' => $appointment,
        ], 201);
    }

    public function update(Request $request, TeacherCalendarAppointment $appointment): JsonResponse
    {
        $this->authorizeAppointment($appointment);
        $validated = $this->validatePayload($request, $appointment);

        try {
            $validated['user_id'] = Auth::id();
            $appointment = $this->calendarService->update($appointment, $validated);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 422);
        }

        return response()->json([
            'message' => 'تم تحديث الموعد.',
            'appointment' => $appointment,
        ]);
    }

    public function destroy(TeacherCalendarAppointment $appointment): JsonResponse
    {
        $this->authorizeAppointment($appointment);
        $this->calendarService->cancel($appointment);

        return response()->json(['message' => 'تم حذف الموعد.']);
    }

    protected function authorizeAppointment(TeacherCalendarAppointment $appointment): void
    {
        if ($appointment->user_id !== Auth::id()) {
            abort(403);
        }
    }

    protected function validatePayload(Request $request, ?TeacherCalendarAppointment $existing = null): array
    {
        $scheduleType = $request->input('schedule_type', $existing?->schedule_type ?? 'temporary');

        $rules = [
            'title' => 'required|string|max:200',
            'description' => 'nullable|string|max:2000',
            'location' => 'nullable|string|max:255',
            'schedule_type' => ['required', Rule::in(['fixed', 'temporary'])],
            'family_timezone' => ['required', 'string', 'timezone:all'],
            'family_time' => 'required|date_format:H:i',
            'duration_minutes' => 'nullable|integer|min:5|max:480',
            'teacher_timezone' => ['nullable', 'string', 'timezone:all'],
            'color' => 'nullable|string|max:7',
            'notify_platform' => 'nullable|boolean',
            'notify_email' => 'nullable|boolean',
            'reminder_minutes' => 'nullable|integer|min:1|max:1440',
            'selected_dates' => 'required|array|min:1',
            'selected_dates.*' => 'date_format:Y-m-d',
            'weekday' => 'nullable|integer|min:0|max:6',
            'month_key' => 'nullable|date_format:Y-m',
        ];

        $validated = $request->validate($rules);
        $validated['schedule_type'] = $scheduleType;

        if ($scheduleType === 'fixed') {
            $request->validate([
                'weekday' => 'required|integer|min:0|max:6',
                'month_key' => 'required|date_format:Y-m',
            ]);
            $validated['weekday'] = (int) $request->input('weekday');
            $validated['month_key'] = $request->input('month_key');
        } else {
            $validated['weekday'] = null;
            $validated['month_key'] = null;
            if (count($validated['selected_dates']) > 1) {
                $validated['selected_dates'] = [reset($validated['selected_dates'])];
            }
        }

        return $validated;
    }
}

<?php

namespace App\Http\Controllers\Instructor;

use App\Http\Controllers\Controller;
use App\Models\ConsultationRequest;
use Illuminate\Support\Facades\Auth;

class ConsultationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'role:instructor|teacher']);
    }

    public function index()
    {
        $requests = ConsultationRequest::query()
            ->where('instructor_id', Auth::id())
            ->with(['student', 'classroomMeeting'])
            ->latest()
            ->paginate(20);

        return view('instructor.consultations.index', compact('requests'));
    }

    public function show(ConsultationRequest $consultation)
    {
        if ((int) $consultation->instructor_id !== (int) Auth::id()) {
            abort(403);
        }

        $consultation->load(['student', 'classroomMeeting']);

        return view('instructor.consultations.show', compact('consultation'));
    }
}

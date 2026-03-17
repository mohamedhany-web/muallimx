<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveRecording;
use App\Models\LiveSession;
use Illuminate\Http\Request;

class LiveRecordingController extends Controller
{
    public function index(Request $request)
    {
        $query = LiveRecording::with(['session.course', 'session.instructor']);

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('session_id')) {
            $query->where('session_id', $request->session_id);
        }
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('title', 'like', "%{$request->search}%")
                  ->orWhereHas('session', function ($sq) use ($request) {
                      $sq->where('title', 'like', "%{$request->search}%");
                  });
            });
        }

        $recordings = $query->latest()->paginate(20)->withQueryString();
        $sessions = LiveSession::select('id', 'title')->orderByDesc('scheduled_at')->limit(200)->get();

        return view('admin.live-recordings.index', compact('recordings', 'sessions'));
    }

    public function create()
    {
        $sessions = LiveSession::select('id', 'title', 'scheduled_at')->orderByDesc('scheduled_at')->get();
        return view('admin.live-recordings.create', compact('sessions'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'session_id'        => 'required|exists:live_sessions,id',
            'title'             => 'nullable|string|max:255',
            'storage_disk'      => 'required|in:local,r2',
            'file_path'         => 'required|string|max:500',
            'external_url'      => 'nullable|url|max:500',
            'duration_seconds'  => 'nullable|integer|min:0',
            'file_size'         => 'nullable|integer|min:0',
            'is_published'      => 'boolean',
        ]);

        $session = LiveSession::find($validated['session_id']);
        $validated['title'] = $validated['title'] ?: ('تسجيل ' . $session->title);
        $validated['status'] = 'ready';
        $validated['is_published'] = $request->boolean('is_published');
        $validated['duration_seconds'] = $validated['duration_seconds'] ?? 0;
        $validated['file_size'] = $validated['file_size'] ?? 0;

        LiveRecording::create($validated);

        return redirect()->route('admin.live-recordings.index')->with('success', 'تم إضافة التسجيل بنجاح.');
    }

    public function show(LiveRecording $liveRecording)
    {
        $liveRecording->load(['session.course', 'session.instructor']);
        return view('admin.live-recordings.show', compact('liveRecording'));
    }

    public function update(Request $request, LiveRecording $liveRecording)
    {
        $validated = $request->validate([
            'title'        => 'nullable|string|max:255',
            'file_path'    => 'nullable|string|max:500',
            'external_url' => 'nullable|url|max:500',
            'storage_disk' => 'nullable|in:local,r2',
            'is_published' => 'boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $liveRecording->update(array_filter($validated, fn ($v) => $v !== null));

        return back()->with('success', 'تم تحديث التسجيل بنجاح');
    }

    public function togglePublish(LiveRecording $liveRecording)
    {
        $liveRecording->update(['is_published' => !$liveRecording->is_published]);
        $msg = $liveRecording->is_published ? 'تم نشر التسجيل' : 'تم إلغاء نشر التسجيل';
        return back()->with('success', $msg);
    }

    public function destroy(LiveRecording $liveRecording)
    {
        $liveRecording->update(['status' => 'deleted']);
        return back()->with('success', 'تم حذف التسجيل');
    }
}

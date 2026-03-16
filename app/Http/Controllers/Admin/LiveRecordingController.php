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

        return view('admin.live-recordings.index', compact('recordings'));
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
            'external_url' => 'nullable|url|max:500',
            'is_published' => 'boolean',
        ]);

        $validated['is_published'] = $request->boolean('is_published');
        $liveRecording->update($validated);

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

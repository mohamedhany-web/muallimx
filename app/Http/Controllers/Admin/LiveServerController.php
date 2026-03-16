<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveServer;
use Illuminate\Http\Request;

class LiveServerController extends Controller
{
    public function index()
    {
        $servers = LiveServer::withCount(['sessions', 'activeSessions'])->latest()->get();
        return view('admin.live-servers.index', compact('servers'));
    }

    public function create()
    {
        return view('admin.live-servers.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'domain'           => 'required|string|max:255',
            'provider'         => 'required|in:jitsi,custom',
            'ip_address'       => 'nullable|string|max:45',
            'max_participants' => 'required|integer|min:2|max:10000',
            'notes'            => 'nullable|string',
        ]);

        $validated['status'] = 'active';
        LiveServer::create($validated);

        return redirect()->route('admin.live-servers.index')
            ->with('success', 'تم إضافة سيرفر البث بنجاح');
    }

    public function edit(LiveServer $liveServer)
    {
        return view('admin.live-servers.edit', compact('liveServer'));
    }

    public function update(Request $request, LiveServer $liveServer)
    {
        $validated = $request->validate([
            'name'             => 'required|string|max:255',
            'domain'           => 'required|string|max:255',
            'provider'         => 'required|in:jitsi,custom',
            'status'           => 'required|in:active,inactive,maintenance',
            'ip_address'       => 'nullable|string|max:45',
            'max_participants' => 'required|integer|min:2|max:10000',
            'notes'            => 'nullable|string',
        ]);

        $liveServer->update($validated);

        return redirect()->route('admin.live-servers.index')
            ->with('success', 'تم تحديث سيرفر البث بنجاح');
    }

    public function destroy(LiveServer $liveServer)
    {
        if ($liveServer->activeSessions()->count() > 0) {
            return back()->with('error', 'لا يمكن حذف سيرفر عليه جلسات نشطة');
        }
        $liveServer->delete();
        return redirect()->route('admin.live-servers.index')
            ->with('success', 'تم حذف السيرفر بنجاح');
    }

    public function toggleStatus(LiveServer $liveServer)
    {
        $newStatus = $liveServer->status === 'active' ? 'inactive' : 'active';
        $liveServer->update(['status' => $newStatus]);
        return back()->with('success', 'تم تغيير حالة السيرفر');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TwoFactorLog;
use Illuminate\Http\Request;

class TwoFactorLogController extends Controller
{
    /**
     * عرض سجلات التحقق الثنائي (2FA)
     */
    public function index(Request $request)
    {
        $query = TwoFactorLog::with('user')
            ->orderBy('created_at', 'desc');

        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('email', 'like', '%' . $request->search . '%')
                    ->orWhereHas('user', function ($userQuery) use ($request) {
                        $userQuery->where('name', 'like', '%' . $request->search . '%')
                            ->orWhere('email', 'like', '%' . $request->search . '%');
                    });
            });
        }

        $logs = $query->paginate(20);

        $stats = [
            'total' => TwoFactorLog::count(),
            'today' => TwoFactorLog::whereDate('created_at', today())->count(),
            'challenge_sent' => TwoFactorLog::where('event', TwoFactorLog::EVENT_CHALLENGE_SENT)->count(),
            'verified' => TwoFactorLog::where('event', TwoFactorLog::EVENT_VERIFIED)->count(),
            'failed' => TwoFactorLog::where('event', TwoFactorLog::EVENT_FAILED)->count(),
        ];

        return view('admin.two-factor-logs.index', compact('logs', 'stats'));
    }
}

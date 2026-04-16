<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveSessionReport;
use Illuminate\Http\Request;

class N8nLiveReportsController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->input('status');
        $instructorId = $request->input('instructor_id');

        $query = LiveSessionReport::with(['session', 'instructor', 'recording'])->latest();

        if ($status && in_array($status, ['pending', 'processing', 'completed', 'failed'], true)) {
            $query->where('status', $status);
        }

        if ($instructorId) {
            $query->where('instructor_id', (int) $instructorId);
        }

        $reports = $query->paginate(25)->withQueryString();

        return view('admin.n8n.live-session-reports.index', compact('reports', 'status', 'instructorId'));
    }
}


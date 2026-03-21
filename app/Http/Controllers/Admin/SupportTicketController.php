<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SupportTicket;
use App\Models\SupportTicketReply;
use Illuminate\Http\Request;

class SupportTicketController extends Controller
{
    public function index(Request $request)
    {
        $status = (string) $request->get('status', 'all');
        $priority = (string) $request->get('priority', 'all');

        $query = SupportTicket::query()->with(['user', 'assignedAdmin'])->latest('last_reply_at')->latest();

        if (in_array($status, ['open', 'in_progress', 'resolved', 'closed'], true)) {
            $query->where('status', $status);
        }
        if (in_array($priority, ['low', 'normal', 'high', 'urgent'], true)) {
            $query->where('priority', $priority);
        }

        $tickets = $query->paginate(20)->withQueryString();

        $stats = [
            'open' => SupportTicket::where('status', 'open')->count(),
            'in_progress' => SupportTicket::where('status', 'in_progress')->count(),
            'resolved' => SupportTicket::where('status', 'resolved')->count(),
        ];

        return view('admin.support-tickets.index', compact('tickets', 'stats', 'status', 'priority'));
    }

    public function show(SupportTicket $ticket)
    {
        $ticket->load(['user', 'assignedAdmin', 'replies.user']);

        return view('admin.support-tickets.show', compact('ticket'));
    }

    public function updateStatus(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'status' => ['required', 'in:open,in_progress,resolved,closed'],
        ]);

        $ticket->update([
            'status' => $data['status'],
            'assigned_admin_id' => auth()->id(),
            'resolved_at' => in_array($data['status'], ['resolved', 'closed'], true) ? now() : null,
        ]);

        return back()->with('success', 'تم تحديث حالة التذكرة.');
    }

    public function reply(Request $request, SupportTicket $ticket)
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        SupportTicketReply::create([
            'support_ticket_id' => $ticket->id,
            'user_id' => auth()->id(),
            'sender_type' => 'admin',
            'message' => $data['message'],
        ]);

        $ticket->update([
            'status' => 'in_progress',
            'assigned_admin_id' => auth()->id(),
            'last_reply_at' => now(),
        ]);

        return back()->with('success', 'تم إرسال الرد للعميل.');
    }
}


@extends('layouts.admin')

@section('title', 'الدعم الفني')
@section('header', 'إدارة الدعم الفني')

@section('content')
<div class="space-y-6">
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
        <h1 class="text-2xl font-bold text-slate-900">قسم الدعم الفني</h1>
        <p class="text-sm text-slate-600 mt-1">إدارة تذاكر العملاء ومتابعة الحلول على مدار اليوم.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="rounded-xl bg-white border border-slate-200 p-4"><p class="text-xs text-slate-500">مفتوحة</p><p class="text-2xl font-bold text-slate-900">{{ number_format($stats['open']) }}</p></div>
        <div class="rounded-xl bg-white border border-slate-200 p-4"><p class="text-xs text-slate-500">قيد التنفيذ</p><p class="text-2xl font-bold text-amber-700">{{ number_format($stats['in_progress']) }}</p></div>
        <div class="rounded-xl bg-white border border-slate-200 p-4"><p class="text-xs text-slate-500">تم الحل</p><p class="text-2xl font-bold text-emerald-700">{{ number_format($stats['resolved']) }}</p></div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
            <form method="GET" class="flex flex-wrap items-center gap-2">
                <select name="status" class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>كل الحالات</option>
                    <option value="open" {{ $status === 'open' ? 'selected' : '' }}>open</option>
                    <option value="in_progress" {{ $status === 'in_progress' ? 'selected' : '' }}>in_progress</option>
                    <option value="resolved" {{ $status === 'resolved' ? 'selected' : '' }}>resolved</option>
                    <option value="closed" {{ $status === 'closed' ? 'selected' : '' }}>closed</option>
                </select>
                <select name="priority" class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
                    <option value="all" {{ $priority === 'all' ? 'selected' : '' }}>كل الأولويات</option>
                    <option value="low" {{ $priority === 'low' ? 'selected' : '' }}>low</option>
                    <option value="normal" {{ $priority === 'normal' ? 'selected' : '' }}>normal</option>
                    <option value="high" {{ $priority === 'high' ? 'selected' : '' }}>high</option>
                    <option value="urgent" {{ $priority === 'urgent' ? 'selected' : '' }}>urgent</option>
                </select>
                <button class="px-4 py-2 rounded-lg bg-sky-600 text-white text-sm font-semibold">تصفية</button>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-xs text-slate-600 uppercase">
                        <th class="px-4 py-3 text-right">العميل</th>
                        <th class="px-4 py-3 text-right">الموضوع</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">الأولوية</th>
                        <th class="px-4 py-3 text-right">آخر رد</th>
                        <th class="px-4 py-3 text-right">عرض</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($tickets as $ticket)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-800">{{ $ticket->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $ticket->subject }}</td>
                            <td class="px-4 py-3 text-xs">{{ $ticket->status }}</td>
                            <td class="px-4 py-3 text-xs">{{ $ticket->priority }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ optional($ticket->last_reply_at ?? $ticket->updated_at)->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3 text-sm"><a class="text-sky-600 hover:underline" href="{{ route('admin.support-tickets.show', $ticket) }}">فتح</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">لا توجد تذاكر دعم حالياً.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-200">{{ $tickets->links() }}</div>
    </div>
</div>
@endsection


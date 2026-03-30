@extends('layouts.admin')

@section('title', 'إشعارات البريد — '.$audienceLabel)
@section('header', 'إشعارات البريد (Gmail) — '.$audienceLabel)

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.email-broadcasts.index', 'all_users') }}" class="px-3 py-2 rounded-lg border {{ $audience === 'all_users' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-slate-200 text-slate-700' }} text-sm font-bold">كل المستخدمين</a>
            <a href="{{ route('admin.email-broadcasts.index', 'students') }}" class="px-3 py-2 rounded-lg border {{ $audience === 'students' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-slate-200 text-slate-700' }} text-sm font-bold">الطلاب</a>
            <a href="{{ route('admin.email-broadcasts.index', 'instructors') }}" class="px-3 py-2 rounded-lg border {{ $audience === 'instructors' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-slate-200 text-slate-700' }} text-sm font-bold">المدربين</a>
            <a href="{{ route('admin.email-broadcasts.index', 'employees') }}" class="px-3 py-2 rounded-lg border {{ $audience === 'employees' ? 'bg-slate-900 text-white border-slate-900' : 'bg-white border-slate-200 text-slate-700' }} text-sm font-bold">الموظفين</a>
        </div>
        <a href="{{ route('admin.email-broadcasts.create', $audience) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold">
            <i class="fas fa-paper-plane"></i> إرسال بريد جديد
        </a>
    </div>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 text-slate-600 font-semibold">
                    <tr>
                        <th class="text-right px-4 py-3">العنوان</th>
                        <th class="text-right px-4 py-3">الحالة</th>
                        <th class="text-right px-4 py-3">الإرسال</th>
                        <th class="text-right px-4 py-3">المنشئ</th>
                        <th class="text-right px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($broadcasts as $b)
                        <tr class="hover:bg-slate-50/70">
                            <td class="px-4 py-3 font-medium text-slate-900">{{ $b->subject }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $b->status }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ $b->sent_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-slate-700">{{ $b->creator?->name ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.email-broadcasts.show', [$audience, $b]) }}" class="text-blue-700 font-bold hover:underline">تفاصيل</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">لا توجد رسائل بعد.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($broadcasts->hasPages())
            <div class="px-4 py-3 border-t border-slate-100">{{ $broadcasts->links() }}</div>
        @endif
    </div>
</div>
@endsection

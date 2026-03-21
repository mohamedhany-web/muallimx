@extends('layouts.admin')

@section('title', 'طلبات التقديم على الفرصة')
@section('header', 'طلبات التقديم')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
        <h1 class="text-xl font-bold text-slate-900">{{ $opportunity->title }}</h1>
        <p class="text-sm text-slate-600 mt-1">{{ $opportunity->organization_name }}</p>
        <form method="GET" class="mt-3">
            <select name="status" class="px-3 py-2 rounded-lg border border-slate-200 text-sm">
                <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>كل الحالات</option>
                @foreach(['submitted','reviewing','accepted','rejected'] as $s)
                    <option value="{{ $s }}" {{ ($status ?? 'all') === $s ? 'selected' : '' }}>{{ $s }}</option>
                @endforeach
            </select>
            <button class="px-3 py-2 rounded-lg bg-sky-600 text-white text-sm font-semibold">تصفية</button>
        </form>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-xs uppercase text-slate-600">
                        <th class="px-4 py-3 text-right">المعلم</th>
                        <th class="px-4 py-3 text-right">Ranking</th>
                        <th class="px-4 py-3 text-right">الرسالة</th>
                        <th class="px-4 py-3 text-right">الحالة الحالية</th>
                        <th class="px-4 py-3 text-right">وقت التقديم</th>
                        <th class="px-4 py-3 text-right">تحديث الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($applications as $a)
                        <tr>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $a->user->name ?? '—' }}</td>
                            <td class="px-4 py-3 text-xs font-bold text-violet-700">{{ (int) ($a->ranking_score ?? 0) }}</td>
                            <td class="px-4 py-3 text-sm text-slate-700">{{ $a->message ?: '—' }}</td>
                            <td class="px-4 py-3 text-xs text-slate-700">{{ $a->status }}</td>
                            <td class="px-4 py-3 text-xs text-slate-500">{{ optional($a->applied_at ?? $a->created_at)->format('Y-m-d H:i') }}</td>
                            <td class="px-4 py-3">
                                <form method="POST" action="{{ route('admin.academy-opportunities.applications.status', [$opportunity, $a]) }}" class="flex items-center gap-2">
                                    @csrf
                                    <select name="status" class="px-2 py-1 rounded border border-slate-200 text-xs">
                                        @foreach(['submitted','reviewing','accepted','rejected'] as $s)
                                            <option value="{{ $s }}" {{ $a->status === $s ? 'selected' : '' }}>{{ $s }}</option>
                                        @endforeach
                                    </select>
                                    <button class="px-3 py-1 rounded bg-sky-600 text-white text-xs font-semibold">حفظ</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">لا توجد طلبات تقديم حتى الآن.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-200">{{ $applications->links() }}</div>
    </div>
</div>
@endsection


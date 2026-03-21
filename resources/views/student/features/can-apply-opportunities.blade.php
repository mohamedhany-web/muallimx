@extends('layouts.app')

@section('title', 'التقديم على فرص التدريس')
@section('header', 'التقديم على فرص التدريس')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <h1 class="text-2xl font-black text-slate-900 dark:text-white">ميزة التقديم على فرص التدريس</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">تصفح فرص الأكاديميات وقدّم مباشرة من داخل المنصة.</p>
    </div>

    @if(!$profile)
        <div class="rounded-xl bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 text-sm">
            لإظهار فرص أكثر دقة، يُفضّل إكمال ملفك التعريفي في قسم التسويق الشخصي.
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        <div class="xl:col-span-2 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                <h2 class="font-bold text-slate-900 dark:text-white">الفرص المتاحة</h2>
            </div>
            <div class="p-5 space-y-4">
                @forelse($opportunities as $op)
                    <div class="rounded-xl border border-slate-200 dark:border-slate-700 p-4">
                        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-3">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="font-bold text-slate-900 dark:text-white">{{ $op->title }}</h3>
                                    @if($op->is_featured)
                                        <span class="text-[11px] px-2 py-0.5 rounded bg-amber-100 text-amber-700">Featured</span>
                                    @endif
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">{{ $op->organization_name }} · {{ $op->city ?: 'عن بُعد' }} · {{ $op->work_mode }}</p>
                                @if($op->requirements)
                                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-2 whitespace-pre-line">{{ $op->requirements }}</p>
                                @endif
                            </div>
                            <div class="flex flex-col items-start lg:items-end gap-2">
                                <p class="text-xs text-slate-500">آخر موعد: {{ optional($op->apply_until)->format('Y-m-d') ?? 'مفتوح' }}</p>
                                @if(isset($myApplicationsMap[$op->id]))
                                    <span class="text-xs px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700">تم التقديم ({{ $myApplicationsMap[$op->id] }})</span>
                                @else
                                    <form action="{{ route('student.opportunities.apply', $op) }}" method="POST" class="flex items-center gap-2">
                                        @csrf
                                        <input type="text" name="message" placeholder="رسالة مختصرة (اختياري)" class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs">
                                        <button class="px-3 py-2 rounded-lg bg-sky-600 hover:bg-sky-700 text-white text-xs font-semibold">تقديم الآن</button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                        لا توجد فرص متاحة حالياً.
                    </div>
                @endforelse
            </div>
        </div>

        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60">
                <h2 class="font-bold text-slate-900 dark:text-white">طلباتي الأخيرة</h2>
            </div>
            <div class="p-4 space-y-3">
                @forelse($myApplications as $a)
                    <div class="rounded-lg border border-slate-200 dark:border-slate-700 p-3">
                        <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $a->opportunity->title ?? 'فرصة' }}</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">{{ $a->opportunity->organization_name ?? '' }}</p>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-[11px] px-2 py-0.5 rounded bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300">{{ $a->status }}</span>
                            <span class="text-[11px] text-slate-500">{{ optional($a->applied_at ?? $a->created_at)->format('Y-m-d') }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-slate-500 dark:text-slate-400">لم تقم بالتقديم على فرص بعد.</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection


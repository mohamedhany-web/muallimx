@extends('layouts.app')

@section('title', 'الظهور للأكاديميات')
@section('header', 'الظهور للأكاديميات')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <h1 class="text-2xl font-black text-slate-900 dark:text-white">ميزة الظهور للأكاديميات</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">يظهر ملفك أمام الجهات والأكاديميات بحسب ترتيبك التسويقي ومزايا الباقة.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">حالة البروفايل</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $profile ? \App\Models\InstructorProfile::statusLabel($profile->status) : 'غير مكتمل' }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">ترتيبك بين المدربين</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ $rankPosition ? '#' . $rankPosition : 'غير ظاهر حالياً' }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">نقاط الترتيب</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ number_format($myRankingScore) }}</p>
        </div>
        <div class="rounded-xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">أيام Featured شهرياً</p>
            <p class="text-lg font-bold text-slate-900 dark:text-white">{{ (int) ($limits['personal_marketing_monthly_featured_days'] ?? 0) }}</p>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
            <h2 class="font-bold text-slate-900 dark:text-white">فرص الأكاديميات المتاحة</h2>
            <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">يمكنك التقديم مباشرة إذا كانت ميزة <strong>التقديم على الفرص</strong> مفعلة في باقتك.</p>
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
                            @if(isset($myApplications[$op->id]))
                                <span class="text-xs px-2.5 py-1 rounded-lg bg-emerald-100 text-emerald-700">تم التقديم ({{ $myApplications[$op->id] }})</span>
                            @elseif($canApply)
                                <form action="{{ route('student.academies.opportunities.apply', $op) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    <input type="text" name="message" placeholder="رسالة مختصرة (اختياري)" class="px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs">
                                    <button class="px-3 py-2 rounded-lg {{ $hasPriority ? 'bg-violet-600 hover:bg-violet-700' : 'bg-sky-600 hover:bg-sky-700' }} text-white text-xs font-semibold">
                                        {{ $hasPriority ? 'تقديم بأولوية' : 'تقديم الآن' }}
                                    </button>
                                </form>
                            @else
                                <span class="text-xs px-2.5 py-1 rounded-lg bg-slate-100 text-slate-600">التقديم غير متاح في باقتك</span>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-8 text-center text-sm text-slate-500 dark:text-slate-400">
                    لا توجد فرص أكاديميات متاحة حالياً.
                </div>
            @endforelse
        </div>
    </div>

    <div class="rounded-xl bg-slate-50 dark:bg-slate-800/50 border border-slate-200 dark:border-slate-700 p-4 text-xs text-slate-600 dark:text-slate-300">
        يتم ترتيب ظهورك للأكاديميات بناءً على: درجة الأولوية في الباقة + أيام Featured + المزايا التسويقية المفعلة.
    </div>
</div>
@endsection


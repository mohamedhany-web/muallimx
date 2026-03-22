@extends('layouts.app')

@section('title', 'تفاصيل الاجتماع')
@section('header', 'تفاصيل الاجتماع')

@php
    $rp = ($useInstructorRoutes ?? false) ? 'instructor.' : 'student.';
@endphp
@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white">{{ $meeting->title }}</h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">الكود: <span class="font-mono font-bold">{{ $meeting->code }}</span></p>
            </div>
            <div class="flex items-center gap-2">
                @if(!$meeting->consultation_request_id && !($useInstructorRoutes ?? false))
                <a href="{{ route('student.classroom.edit', $meeting) }}" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold">تعديل</a>
                @endif
                @if(!$meeting->started_at && !$meeting->ended_at)
                    <form action="{{ route($rp.'classroom.start-meeting', $meeting) }}" method="POST">@csrf<button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">بدء الآن</button></form>
                @elseif($meeting->isLive())
                    <a href="{{ route($rp.'classroom.room', $meeting) }}" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">دخول الغرفة</a>
                    <form method="POST" action="{{ route($rp.'classroom.end', $meeting) }}" onsubmit="return confirm('إنهاء الاجتماع؟');">@csrf<button class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold">إنهاء</button></form>
                @endif
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الحالة</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">
                    {{ $meeting->isLive() ? 'مباشر' : (!$meeting->started_at ? 'مجدول' : 'منتهي') }}
                </p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الموعد المحدد</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ optional($meeting->scheduled_for)->format('Y-m-d H:i') ?? 'غير محدد' }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">مدة الاجتماع</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ (int) ($meeting->planned_duration_minutes ?? $limits['classroom_max_duration_minutes']) }} دقيقة</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الحد الأقصى للمشاركين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ (int) ($meeting->max_participants ?? 25) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">أعلى ذروة مشاركين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ (int) ($meeting->participants_peak ?? 0) }}</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">إجمالي المشاركين المسجلين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">{{ (int) ($meeting->participants_count ?? 0) }}</p>
            </div>
        </div>

        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-3 flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs text-slate-600 dark:text-slate-300">رابط الانضمام للطلاب والضيوف:</div>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="{{ $joinUrl }}" class="w-[340px] max-w-[60vw] px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs">
                <button type="button" onclick="navigator.clipboard.writeText('{{ $joinUrl }}')" class="px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold">نسخ</button>
            </div>
        </div>

        <div class="flex items-center justify-between">
            @if($meeting->consultation_request_id && ($useInstructorRoutes ?? false))
                <a href="{{ route('instructor.consultations.show', $meeting->consultation_request_id) }}" class="text-sm text-sky-600 hover:underline">العودة لتفاصيل الاستشارة</a>
            @elseif($useInstructorRoutes ?? false)
                <a href="{{ route('instructor.consultations.index') }}" class="text-sm text-sky-600 hover:underline">العودة لطلبات الاستشارة</a>
            @else
                <a href="{{ route('student.classroom.index') }}" class="text-sm text-sky-600 hover:underline">العودة لقائمة الاجتماعات</a>
            @endif
            @if(!($useInstructorRoutes ?? false) && !$meeting->consultation_request_id)
            <form action="{{ route('student.classroom.destroy', $meeting) }}" method="POST" onsubmit="return confirm('حذف الاجتماع نهائياً؟');">
                @csrf
                @method('DELETE')
                <button class="text-sm text-rose-600 hover:underline">حذف الاجتماع</button>
            </form>
            @endif
        </div>
    </div>
</div>
@endsection


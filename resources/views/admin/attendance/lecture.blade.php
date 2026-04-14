@extends('layouts.admin')

@section('title', 'تفاصيل حضور المحاضرة - ' . ($lecture->title ?? ''))
@section('header', 'تفاصيل حضور المحاضرة')

@section('content')
<div class="p-3 sm:p-4 md:p-6 space-y-4 sm:space-y-6 bg-slate-50 dark:bg-slate-900 min-h-screen">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-5 py-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-800 dark:text-red-200 px-5 py-4">
            {{ session('error') }}
        </div>
    @endif

    <section class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $lecture->title }}</h2>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                    {{ $lecture->course->title ?? 'بدون كورس' }}
                </p>
            </div>
            <a href="{{ route('admin.attendance.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600">
                <i class="fas fa-arrow-right"></i>
                الرجوع لقائمة الحضور
            </a>
        </div>

        <div class="p-5">
            <form action="{{ route('admin.attendance.upload-teams', $lecture) }}" method="POST" enctype="multipart/form-data" class="flex flex-col sm:flex-row items-start sm:items-center gap-3">
                @csrf
                <input type="file" name="file" accept=".csv,.xlsx,.xls" class="block w-full sm:w-auto text-sm text-slate-700 dark:text-slate-200 border border-slate-300 dark:border-slate-600 rounded-lg bg-white dark:bg-slate-700 file:mr-2 file:py-2 file:px-3 file:border-0 file:text-sm file:font-semibold file:bg-slate-100 dark:file:bg-slate-600 file:text-slate-700 dark:file:text-slate-100">
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-semibold text-sm">
                    <i class="fas fa-upload"></i>
                    رفع ملف Teams
                </button>
            </form>
        </div>
    </section>

    <section class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-900/40">
            <h3 class="text-lg font-black text-slate-900 dark:text-slate-100">سجلات الحضور للمحاضرة</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-900/40">
                    <tr>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">الطالب</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">الحالة</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">الدقائق</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">النسبة</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-700 dark:text-slate-300 uppercase">وقت التسجيل</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-slate-800 divide-y divide-slate-200 dark:divide-slate-700">
                    @forelse($attendanceRecords as $record)
                        <tr class="hover:bg-slate-50 dark:hover:bg-slate-700/40">
                            <td class="px-6 py-4 text-sm font-semibold text-slate-900 dark:text-slate-100">{{ $record->student->name ?? 'غير محدد' }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ $record->status }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ (int) ($record->attendance_minutes ?? 0) }}</td>
                            <td class="px-6 py-4 text-sm text-slate-700 dark:text-slate-300">{{ number_format((float) ($record->attendance_percentage ?? 0), 1) }}%</td>
                            <td class="px-6 py-4 text-sm text-slate-500 dark:text-slate-400">{{ optional($record->created_at)->format('Y-m-d H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-slate-600 dark:text-slate-300">لا توجد سجلات حضور لهذه المحاضرة حتى الآن.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
</div>
@endsection

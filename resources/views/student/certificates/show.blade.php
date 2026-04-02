@extends('layouts.app')

@section('title', 'الشهادة')
@section('header', 'الشهادة')

@section('content')
<div class="space-y-6 max-w-5xl mx-auto px-4 py-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-slate-900">{{ $certificate->title ?? $certificate->course_name ?? 'شهادة' }}</h2>
            <p class="text-sm text-slate-500 mt-1">رقم الشهادة: <span class="font-mono font-semibold">{{ $certificate->certificate_number }}</span></p>
        </div>
        <a href="{{ route('student.certificates.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-sky-600 hover:text-sky-800">
            <i class="fas fa-arrow-right"></i>
            كل الشهادات
        </a>
    </div>

    @if(!empty($certificate->pdf_path))
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('student.certificates.file', $certificate) }}" target="_blank" rel="noopener"
               class="inline-flex items-center gap-2 rounded-xl bg-slate-800 hover:bg-slate-900 text-white px-4 py-2.5 text-sm font-semibold transition-colors">
                <i class="fas fa-external-link-alt"></i>
                فتح PDF في تبويب جديد
            </a>
            <a href="{{ route('student.certificates.file', $certificate) }}" download
               class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2.5 text-sm font-semibold transition-colors">
                <i class="fas fa-download"></i>
                تحميل الملف
            </a>
        </div>

        <div class="rounded-2xl border border-slate-200 bg-slate-100 overflow-hidden shadow-sm" style="min-height: 70vh;">
            <iframe title="شهادة PDF"
                    src="{{ route('student.certificates.file', $certificate) }}"
                    class="w-full border-0 block"
                    style="min-height: 70vh; height: 75vh;"></iframe>
        </div>
    @else
        <div class="rounded-2xl border-2 border-dashed border-amber-200 bg-amber-50 p-8 text-center">
            <i class="fas fa-file-pdf text-amber-500 text-4xl mb-3"></i>
            <p class="text-amber-900 font-semibold">لم يُرفَق ملف PDF لهذه الشهادة بعد.</p>
            <p class="text-sm text-amber-800/90 mt-2">تواصل مع الإدارة لرفع ملف الشهادة.</p>
        </div>
    @endif
</div>
@endsection

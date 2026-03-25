@extends('layouts.app')

@php
    $pt = $presentationTitle ?? (isset($file) ? ($file->label ?? 'عرض تفاعلي') : 'عرض تفاعلي');
@endphp

@section('title', $pt . ' - ' . $item->title)
@section('header', $item->title)

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <a href="{{ route('curriculum-library.show', $item) }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 text-sm font-semibold">
            <i class="fas fa-arrow-right"></i> العودة لصفحة المنهج
        </a>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-black text-slate-800">{{ $pt }}</h1>
                <p class="text-xs text-slate-500 mt-1">العرض داخل المنصة فقط؛ التحميل غير متاح لهذا النوع.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-100">
                <i class="fas fa-lock ml-1.5 text-[10px]"></i> بدون تحميل
            </span>
        </div>
        <div class="p-4 bg-slate-50">
            @if(!empty($canUseOfficeViewer))
                <div class="aspect-[1410/900] w-full min-h-[480px] rounded-xl border border-slate-200 bg-white overflow-hidden shadow-inner">
                    <iframe title="عرض الشريحة"
                            src="{{ $embedUrl }}"
                            class="w-full h-full min-h-[480px]"
                            allowfullscreen></iframe>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    إذا لم يظهر العرض، تأكد أن الملف متاح عبر رابط <strong>HTTPS</strong> عام (مثل بيئة الإنتاج).
                </p>
            @else
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-900">
                    <p class="text-sm font-bold mb-1">لا يمكن فتح العرض التفاعلي في البيئة الحالية</p>
                    <p class="text-xs leading-relaxed">
                        عارض Microsoft يحتاج رابط <strong>HTTPS عام</strong> ويمكن الوصول إليه من الإنترنت. على
                        <strong>localhost / 127.0.0.1</strong> سيظهر خطأ "An error occurred".
                    </p>
                    @if(!empty($publicUrl))
                        <a href="{{ $publicUrl }}" target="_blank" rel="noopener"
                           class="inline-flex items-center gap-2 mt-3 px-3 py-2 rounded-lg bg-amber-600 text-white text-xs font-semibold hover:bg-amber-700">
                            <i class="fas fa-external-link-alt"></i> فتح رابط الملف مباشرة
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

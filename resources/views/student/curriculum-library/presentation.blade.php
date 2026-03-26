@extends('layouts.app')

@php
    $pt = $presentationTitle ?? (isset($file) ? ($file->label ?? 'عرض تفاعلي') : 'عرض تفاعلي');
@endphp

@section('title', $pt . ' - ' . $item->title)
@section('header', $item->title)

@section('content')
<div class="w-full px-2 sm:px-4 lg:px-6 py-4 sm:py-6 select-none" ondragstart="return false;">
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3 bg-slate-50">
            <div>
                <h1 class="text-lg font-black text-slate-800">{{ $pt }}</h1>
                <p class="text-xs text-slate-500 mt-1">وضع العرض المحمي داخل المنصة</p>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-100">
                    <i class="fas fa-lock ml-1.5 text-[10px]"></i> بدون تحميل
                </span>
                <a href="{{ route('curriculum-library.show', $item) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-slate-700 text-white text-xs font-semibold hover:bg-slate-800">
                    <i class="fas fa-arrow-right"></i> رجوع
                </a>
            </div>
        </div>
        <div class="p-3 sm:p-4 bg-slate-100">
            @if(!empty($canUseOfficeViewer) && !empty($embedUrl))
                <div class="aspect-[1410/900] w-full min-h-[480px] rounded-xl border border-slate-200 bg-white overflow-hidden shadow-inner"
                     oncontextmenu="return false;">
                    <iframe title="عرض الشريحة"
                            src="{{ $embedUrl }}"
                            class="w-full h-full min-h-[480px]"
                            sandbox="allow-scripts allow-same-origin allow-forms allow-popups"
                            referrerpolicy="no-referrer"
                            allowfullscreen></iframe>
                </div>
            @else
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-900">
                    <p class="text-sm font-bold mb-1">لا يمكن فتح العرض التفاعلي حالياً</p>
                    <p class="text-xs leading-relaxed">
                        عرض ملفات PowerPoint يتم عبر عارض Microsoft داخل الصفحة، ويحتاج رابط <strong>HTTPS عام</strong> يمكن الوصول له من الإنترنت.
                        في بيئة التطوير (localhost) أو إذا كان الرابط غير HTTPS قد يفشل العرض.
                    </p>
                    @if(!empty($publicUrl))
                        <p class="text-[11px] text-amber-900/80 mt-2 break-all">الرابط الحالي: <span class="font-mono">{{ $publicUrl }}</span></p>
                    @endif
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.body.classList.add('overflow-hidden');
document.addEventListener('contextmenu', function (e) {
    e.preventDefault();
});

document.addEventListener('keydown', function (e) {
    const key = (e.key || '').toLowerCase();
    const isMac = navigator.platform.toUpperCase().indexOf('MAC') >= 0;
    const modifier = isMac ? e.metaKey : e.ctrlKey;

    if (modifier && (key === 'p' || key === 's' || key === 'u' || key === 'c')) {
        e.preventDefault();
        e.stopPropagation();
    }
    if (key === 'f12' || (modifier && e.shiftKey && (key === 'i' || key === 'j'))) {
        e.preventDefault();
        e.stopPropagation();
    }
});
</script>
@endpush

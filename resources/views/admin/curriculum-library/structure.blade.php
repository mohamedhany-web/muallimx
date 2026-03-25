@extends('layouts.admin')

@section('title', 'هيكل المنهج: ' . $item->title)
@section('header', 'هيكل المنهج')

@section('content')
@php
    $countSections = $flatSections->count();
    $countMaterials = $flatSections->sum('materials_count');
@endphp
<div class="w-full max-w-none font-body space-y-6">
    {{-- شريط علوي بعرض الصفحة --}}
    <div class="relative overflow-hidden rounded-2xl border border-slate-200/80 dark:border-slate-700 bg-gradient-to-l from-indigo-50/90 via-white to-slate-50/90 dark:from-slate-800 dark:via-slate-900 dark:to-slate-800/95 shadow-sm">
        <div class="absolute top-0 inset-x-0 h-1 bg-gradient-to-l from-indigo-500 via-violet-500 to-cyan-500 opacity-90"></div>
        <div class="p-5 sm:p-6 lg:p-8">
            <nav class="flex flex-wrap items-center gap-2 text-xs sm:text-sm text-slate-500 dark:text-slate-400 mb-4">
                <a href="{{ route('admin.curriculum-library.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-semibold transition-colors">مكتبة المناهج</a>
                <i class="fas fa-chevron-left text-[10px] opacity-50"></i>
                <a href="{{ route('admin.curriculum-library.items.edit', $item) }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 font-medium truncate max-w-[10rem] sm:max-w-none transition-colors">{{ $item->title }}</a>
                <i class="fas fa-chevron-left text-[10px] opacity-50"></i>
                <span class="text-slate-800 dark:text-slate-100 font-bold">هيكل المنهج</span>
            </nav>
            <div class="flex flex-col xl:flex-row xl:items-start xl:justify-between gap-6">
                <div class="min-w-0 flex-1">
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/80 dark:bg-slate-800/80 border border-slate-200/80 dark:border-slate-600 text-[11px] font-mono text-slate-600 dark:text-slate-300">
                            <i class="fas fa-link text-indigo-500 text-[10px]"></i> {{ $item->slug }}
                        </span>
                        @if($item->category)
                            <span class="inline-flex items-center px-2.5 py-1 rounded-lg bg-slate-100 dark:bg-slate-700 text-[11px] font-semibold text-slate-700 dark:text-slate-200">{{ $item->category->name }}</span>
                        @endif
                    </div>
                    <h1 class="text-2xl sm:text-3xl font-heading font-black text-slate-900 dark:text-white tracking-tight break-words">{{ $item->title }}</h1>
                    <p class="text-sm sm:text-base text-slate-600 dark:text-slate-400 mt-2 max-w-4xl leading-relaxed">
                        نظّم الأقسام والفروع، ثم ارفع المواد إلى <strong class="text-indigo-700 dark:text-indigo-300">Cloudflare R2</strong> واضبط لكل مادة: عرض داخل المنصة أو تحميل (حسب نوع الملف).
                    </p>
                </div>
                <div class="flex flex-col sm:flex-row xl:flex-col gap-3 shrink-0">
                    <div class="flex gap-3">
                        <a href="{{ route('admin.curriculum-library.items.edit', $item) }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-slate-100 text-sm font-bold shadow-sm hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                            <i class="fas fa-pen text-indigo-500 text-xs"></i> تعديل بيانات المنهج
                        </a>
                        <a href="{{ route('admin.curriculum-library.index') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-700 text-white text-sm font-bold hover:bg-slate-900 dark:hover:bg-slate-600 transition-colors">
                            <i class="fas fa-list text-xs opacity-80"></i> كل المناهج
                        </a>
                    </div>
                    <div class="flex gap-3 sm:justify-end xl:justify-stretch">
                        <div class="flex-1 sm:flex-none min-w-[7rem] rounded-xl bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-600 px-4 py-3 text-center shadow-sm">
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">أقسام</p>
                            <p class="text-2xl font-black text-indigo-600 dark:text-indigo-400 font-heading tabular-nums">{{ $countSections }}</p>
                        </div>
                        <div class="flex-1 sm:flex-none min-w-[7rem] rounded-xl bg-white/90 dark:bg-slate-800/90 border border-slate-200/80 dark:border-slate-600 px-4 py-3 text-center shadow-sm">
                            <p class="text-[11px] font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">مواد</p>
                            <p class="text-2xl font-black text-violet-600 dark:text-violet-400 font-heading tabular-nums">{{ $countMaterials }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- إضافة قسم جذر --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/50 shadow-md overflow-hidden">
        <div class="px-5 py-3.5 border-b border-slate-100 dark:border-slate-700 bg-gradient-to-l from-indigo-50/50 to-transparent dark:from-indigo-950/30 dark:to-transparent flex items-center gap-3">
            <span class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shadow-lg shadow-indigo-500/25"><i class="fas fa-plus text-sm"></i></span>
            <div>
                <h2 class="text-base font-heading font-black text-slate-900 dark:text-white">إضافة قسم جذر</h2>
                <p class="text-xs text-slate-500 dark:text-slate-400">مثال: المستوى المبتدئ، الدراسات الإسلامية، القرآن والتجويد…</p>
            </div>
        </div>
        <div class="p-5 sm:p-6">
            <form action="{{ route('admin.curriculum-library.items.sections.store', $item) }}" method="POST" class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                @csrf
                <div class="md:col-span-7 lg:col-span-8">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">عنوان القسم</label>
                    <input type="text" name="title" required
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 transition-shadow"
                           placeholder="مثال: المستوى الأول — لغة عربية">
                </div>
                <div class="md:col-span-3 lg:col-span-2">
                    <label class="block text-xs font-bold text-slate-600 dark:text-slate-400 mb-1.5">ترتيب العرض</label>
                    <input type="number" name="order" value="0" min="0"
                           class="w-full px-4 py-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-900 dark:text-slate-100 text-sm focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="md:col-span-2 lg:col-span-2">
                    <button type="submit" class="w-full px-4 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black shadow-lg shadow-indigo-500/20 transition-colors">
                        إضافة القسم
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- شجرة الأقسام --}}
    <div class="space-y-5">
        <div class="flex items-center justify-between gap-3 flex-wrap">
            <h2 class="text-lg font-heading font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-sitemap text-indigo-500"></i> هيكل الأقسام والمواد
            </h2>
        </div>

        @forelse($tree as $root)
            @include('admin.curriculum-library._structure-section', ['section' => $root, 'item' => $item, 'depth' => 0])
        @empty
            <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-600 bg-slate-50/50 dark:bg-slate-900/40 px-8 py-16 text-center">
                <div class="w-16 h-16 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-600 flex items-center justify-center mx-auto mb-4 text-slate-400">
                    <i class="fas fa-folder-open text-2xl"></i>
                </div>
                <p class="text-slate-700 dark:text-slate-300 font-bold text-base mb-1">لا توجد أقسام بعد</p>
                <p class="text-sm text-slate-500 dark:text-slate-400 max-w-md mx-auto">استخدم النموذج أعلاه لإضافة أول قسم جذر، ثم افتح كل قسم لإضافة فروع أو رفع مواد إلى R2.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection

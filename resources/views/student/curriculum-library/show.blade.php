@extends('layouts.app')

@section('title', $item->title . ' - مكتبة المناهج')
@section('header', $item->title)

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <a href="{{ route('curriculum-library.index') }}" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 text-sm font-semibold mb-4">
                <i class="fas fa-arrow-right"></i> العودة للمكتبة
            </a>
            <div class="flex flex-wrap items-start gap-3">
                <span class="w-12 h-12 rounded-xl {{ $item->is_free_preview ? 'bg-emerald-50 text-emerald-600' : 'bg-indigo-100 text-indigo-600' }} flex items-center justify-center flex-shrink-0">
                    <i class="fas {{ $item->is_free_preview ? 'fa-star' : 'fa-book-open' }} text-lg"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800">{{ $item->title }}</h1>
                    @if($item->category || $item->subject || $item->grade_level || $item->is_free_preview)
                        <div class="flex flex-wrap gap-2 mt-2">
                            @if($item->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium">{{ $item->category->name }}</span>
                            @endif
                            @if(isset($item->language) && $item->language)
                                @php $langNames = ['ar' => 'العربية', 'en' => 'English', 'fr' => 'Français']; @endphp
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-sky-50 text-sky-700 text-xs font-medium">{{ $langNames[$item->language] ?? $item->language }}</span>
                            @endif
                            @if($item->subject)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-medium">{{ $item->subject }}</span>
                            @endif
                            @if($item->grade_level)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium">{{ $item->grade_level }}</span>
                            @endif
                            @if($item->is_free_preview)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-emerald-50 text-emerald-700 text-[11px] font-semibold">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 ml-1"></span>
                                    هذا المحتوى متاح كتجربة مجانية من مناهج X
                                </span>
                            @endif
                        </div>
                    @endif
                    @if($item->description)
                        <p class="text-slate-600 mt-3">{{ $item->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if(isset($sectionTree) && $sectionTree->isNotEmpty())
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800 dark:text-slate-100 mb-3"><i class="fas fa-sitemap text-indigo-500 ml-2"></i> محتوى المنهج (أقسام ومواد)</h2>
                <p class="text-sm text-slate-600 dark:text-slate-400 mb-4">تنظيم هرمي: أقسام وفروع ثم المواد. تظهر أزرار العرض/التحميل حسب إعدادات كل مادة.</p>
                <div class="space-y-4">
                    @include('student.curriculum-library._section-node', ['sections' => $sectionTree, 'item' => $item, 'depth' => 0])
                </div>
            </div>
        @endif

        @if($item->files && $item->files->isNotEmpty())
            <div class="p-6 border-b border-slate-100">
                <h2 class="text-lg font-bold text-slate-800 mb-3"><i class="fas fa-layer-group text-indigo-500 ml-2"></i> ملفات قديمة (قبل الهيكل الهرمي)</h2>
                <p class="text-sm text-slate-600 mb-4">
                    <strong>PPTX:</strong> عرض داخل المنصة فقط (لا تحميل).
                    <strong>PDF:</strong> عرض أو تحميل.
                    <strong>HTML:</strong> عرض تفاعلي داخل المنصة فقط.
                </p>
                <ul class="space-y-2">
                    @foreach($item->files as $file)
                        <li class="flex flex-wrap items-center gap-3 p-3 rounded-xl bg-slate-50 border border-slate-100">
                            @if($file->file_type === 'presentation')
                                <span class="w-10 h-10 rounded-lg bg-amber-100 text-amber-700 flex items-center justify-center"><i class="fas fa-file-powerpoint"></i></span>
                                <span class="flex-1 font-medium text-slate-800">{{ $file->label ?: 'عرض شرائح تفاعلي (PowerPoint)' }}</span>
                            @elseif($file->file_type === 'pdf')
                                <span class="w-10 h-10 rounded-lg bg-rose-100 text-rose-700 flex items-center justify-center"><i class="fas fa-file-pdf"></i></span>
                                <span class="flex-1 font-medium text-slate-800">{{ $file->label ?: 'ملف PDF' }}</span>
                            @elseif($file->file_type === 'assignment')
                                <span class="w-10 h-10 rounded-lg bg-indigo-100 text-indigo-700 flex items-center justify-center"><i class="fas fa-file-alt"></i></span>
                                <span class="flex-1 font-medium text-slate-800">{{ $file->label ?: 'وجبة أو مرفق' }}</span>
                            @else
                                <span class="w-10 h-10 rounded-lg bg-emerald-100 text-emerald-700 flex items-center justify-center"><i class="fas fa-code"></i></span>
                                <span class="flex-1 font-medium text-slate-800">{{ $file->label ?: 'محتوى HTML تفاعلي' }}</span>
                            @endif
                            <div class="flex items-center gap-2 flex-wrap">
                                @if($file->file_type === 'html')
                                    <a href="{{ route('curriculum-library.file.view', [$item, $file]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-emerald-600 text-white text-sm font-semibold hover:bg-emerald-700">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                @elseif($file->file_type === 'presentation')
                                    <a href="{{ route('curriculum-library.file.presentation', [$item, $file]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-amber-600 text-white text-sm font-semibold hover:bg-amber-700">
                                        <i class="fas fa-play"></i> عرض تفاعلي
                                    </a>
                                @elseif($file->file_type === 'pdf')
                                    <a href="{{ route('curriculum-library.file.pdf', [$item, $file]) }}" target="_blank" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                    <a href="{{ route('curriculum-library.file.download', [$item, $file]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                                        <i class="fas fa-download"></i> تحميل
                                    </a>
                                @else
                                    <a href="{{ route('curriculum-library.file.download', [$item, $file]) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-indigo-600 text-white text-sm font-semibold hover:bg-indigo-700">
                                        <i class="fas fa-download"></i> تحميل
                                    </a>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        @if($item->content)
            <div class="p-6 pt-4 prose prose-slate max-w-none curriculum-content">
                {!! $item->content !!}
            </div>
        @elseif(!($item->files && $item->files->isNotEmpty()) && (!isset($sectionTree) || $sectionTree->isEmpty()))
            <div class="p-6 pt-4">
                <p class="text-slate-500 italic">لا يوجد محتوى تفصيلي أو أقسام/مواد لهذا المنهج حالياً.</p>
            </div>
        @endif
    </div>
</div>
@endsection

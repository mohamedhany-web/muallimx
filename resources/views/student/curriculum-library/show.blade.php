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
                <span class="w-12 h-12 rounded-xl bg-indigo-100 text-indigo-600 flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-book-open text-lg"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800">{{ $item->title }}</h1>
                    @if($item->category || $item->subject || $item->grade_level)
                        <div class="flex flex-wrap gap-2 mt-2">
                            @if($item->category)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-slate-100 text-slate-700 text-xs font-medium">{{ $item->category->name }}</span>
                            @endif
                            @if($item->subject)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-indigo-50 text-indigo-700 text-xs font-medium">{{ $item->subject }}</span>
                            @endif
                            @if($item->grade_level)
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-lg bg-amber-50 text-amber-700 text-xs font-medium">{{ $item->grade_level }}</span>
                            @endif
                        </div>
                    @endif
                    @if($item->description)
                        <p class="text-slate-600 mt-3">{{ $item->description }}</p>
                    @endif
                </div>
            </div>
        </div>

        @if($item->content)
            <div class="p-6 pt-4 prose prose-slate max-w-none curriculum-content">
                {!! $item->content !!}
            </div>
        @else
            <div class="p-6 pt-4">
                <p class="text-slate-500 italic">لا يوجد محتوى تفصيلي لهذا العنصر حالياً.</p>
            </div>
        @endif
    </div>
</div>
@endsection

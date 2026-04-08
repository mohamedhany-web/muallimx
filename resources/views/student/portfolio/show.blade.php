@extends('layouts.app')

@section('title', __('student.portfolio_marketing.show_title'))
@section('header', __('student.portfolio_marketing.show_header'))

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/25 border border-emerald-200 dark:border-emerald-800/60 px-4 py-3 flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
            <span class="font-semibold text-emerald-800 dark:text-emerald-200">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-red-50 dark:bg-red-900/25 border border-red-200 dark:border-red-800/60 px-4 py-3 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
            <span class="font-semibold text-red-800 dark:text-red-200">{{ session('error') }}</span>
        </div>
    @endif

    @php
        $statusMap = [
            \App\Models\PortfolioProject::STATUS_PENDING_REVIEW => [__('student.portfolio_marketing.status_pending_review'), 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200', 'fa-hourglass-half'],
            \App\Models\PortfolioProject::STATUS_APPROVED => [__('student.portfolio_marketing.status_approved'), 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200', 'fa-check-circle'],
            \App\Models\PortfolioProject::STATUS_REJECTED => [__('student.portfolio_marketing.status_rejected'), 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200', 'fa-times-circle'],
            \App\Models\PortfolioProject::STATUS_PUBLISHED => [__('student.portfolio_marketing.status_published'), 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-200', 'fa-globe'],
        ];
        $meta = $statusMap[$project->status] ?? [__('student.portfolio_marketing.status_unknown'), 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200', 'fa-question-circle'];
    @endphp

    <div class="bg-white dark:bg-slate-800/95 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 truncate">{{ $project->title }}</h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-2 text-[11px] font-bold px-3 py-1 rounded-full {{ $meta[1] }}">
                        <i class="fas {{ $meta[2] }}"></i>
                        {{ $meta[0] }}
                    </span>
                    @if($project->academicYear)
                        <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
                            المسار: {{ $project->academicYear->name }}
                        </span>
                    @endif
                    @if($project->advancedCourse)
                        <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
                            الكورس: {{ $project->advancedCourse->title }}
                        </span>
                    @endif
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('student.portfolio.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                    رجوع
                </a>
                @if(!in_array($project->status, [\App\Models\PortfolioProject::STATUS_APPROVED, \App\Models\PortfolioProject::STATUS_PUBLISHED], true))
                    <a href="{{ route('student.portfolio.edit', $project) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition-colors">
                        <i class="fas fa-edit"></i>
                        تعديل
                    </a>
                @endif
            </div>
        </div>

        <div class="p-6 space-y-6">
            @if($project->status === \App\Models\PortfolioProject::STATUS_REJECTED && $project->rejected_reason)
                <div class="rounded-2xl border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-900/20 p-4">
                    <p class="font-bold text-red-800 dark:text-red-200 mb-1">{{ __('student.portfolio_marketing.show_rejected_reason_title') }}</p>
                    <p class="text-sm text-red-700 dark:text-red-200/90">{{ $project->rejected_reason }}</p>
                </div>
            @endif

            @if($project->description)
                <div>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">الوصف</p>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $project->description }}</div>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-2">{{ __('student.portfolio_marketing.show_links_heading') }}</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fas fa-link ml-2"></i>{{ __('student.portfolio_marketing.field_external_link') }}</span>
                            @if($project->project_url)
                                <a href="{{ $project->project_url }}" target="_blank" rel="noopener noreferrer" class="font-bold text-sky-700 dark:text-sky-300 hover:underline">{{ __('student.portfolio_marketing.show_open') }}</a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </div>
                        @if($project->github_url)
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fas fa-globe ml-2"></i>{{ __('student.portfolio_marketing.field_extra_link') }}</span>
                            <a href="{{ $project->github_url }}" target="_blank" rel="noopener noreferrer" class="font-bold text-sky-700 dark:text-sky-300 hover:underline">{{ __('student.portfolio_marketing.show_open') }}</a>
                        </div>
                        @endif
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fas fa-video ml-2"></i>{{ __('student.portfolio_marketing.field_video') }}</span>
                            @if($project->video_url)
                                <a href="{{ $project->video_url }}" target="_blank" rel="noopener noreferrer" class="font-bold text-sky-700 dark:text-sky-300 hover:underline">{{ __('student.portfolio_marketing.show_open') }}</a>
                            @else
                                <span class="text-slate-400">—</span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-2">{{ __('student.portfolio_marketing.show_review_card_title') }}</p>
                    <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                        <p><span class="font-bold">{{ __('student.portfolio_marketing.show_reviewer_label') }}:</span> {{ $project->reviewer?->name ?? '—' }}</p>
                        <p><span class="font-bold">{{ __('student.portfolio_marketing.show_reviewed_at_label') }}:</span> {{ $project->reviewed_at ? $project->reviewed_at->format('Y-m-d H:i') : '—' }}</p>
                        <p><span class="font-bold">{{ __('student.portfolio_marketing.show_published_at_label') }}:</span> {{ $project->published_at ? $project->published_at->format('Y-m-d H:i') : '—' }}</p>
                    </div>
                </div>
            </div>

            @if($project->content_type === \App\Models\PortfolioProject::CONTENT_TEXT && $project->content_text)
                <div>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.show_content_heading') }}</p>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $project->content_text }}</div>
                </div>
            @endif

            @if($project->content_type === \App\Models\PortfolioProject::CONTENT_VIDEO && $project->video_url)
                @php $embed = $project->videoEmbedUrl(); @endphp
                <div>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.show_video_heading') }}</p>
                    @if($embed)
                        <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-black aspect-video">
                            <iframe src="{{ $embed }}" class="w-full h-full" allowfullscreen></iframe>
                        </div>
                    @else
                        <a href="{{ $project->video_url }}" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold transition-colors">
                            <i class="fas fa-play"></i>
                            {{ __('student.portfolio_marketing.show_open_video') }}
                        </a>
                    @endif
                </div>
            @endif

            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">{{ __('student.portfolio_marketing.show_section_photos') }}</p>
                    <span class="text-xs text-slate-500 dark:text-slate-400">{{ $project->images->count() }}/5</span>
                </div>
                @if($project->images->count() === 0)
                    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-600 p-8 text-center text-slate-500 dark:text-slate-400">
                        {{ __('student.portfolio_marketing.show_no_photos') }}
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        @foreach($project->images as $img)
                            @php $imgUrl = \App\Services\PortfolioImageStorage::publicUrl($img->image_path); @endphp
                            @if($imgUrl)
                            <a href="{{ $imgUrl }}" target="_blank" rel="noopener noreferrer" class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/40">
                                <img src="{{ $imgUrl }}" alt="" class="w-full h-28 object-cover">
                            </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection


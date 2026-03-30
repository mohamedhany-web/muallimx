@extends('layouts.app')

@section('title', __('student.my_certificates_title'))
@section('header', __('student.my_certificates_title'))

@section('content')
<div class="w-full">
    {{-- Header بنفس ستايل my-portfolio/create --}}
    <section class="relative overflow-hidden rounded-3xl mx-4 sm:mx-6 lg:mx-8 mt-6 bg-white dark:bg-slate-800/95 border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-white via-brand-50/40 to-slate-50/60 dark:from-slate-900/20 dark:via-slate-900/10 dark:to-slate-900/20"></div>
        <div class="relative z-10 px-6 sm:px-8 lg:px-10 pt-10 pb-10">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-brand-50 border border-brand-100 text-brand-700 text-sm font-black">
                        <i class="fas fa-certificate text-brand-600"></i>
                        {{ __('student.my_certificates_title') }}
                    </div>
                    <h1 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-slate-100 leading-tight mt-4">
                        {{ __('student.my_certificates_title') }}
                    </h1>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3 max-w-2xl">
                        {{ __('student.certificates_subtitle') }}
                    </p>
                </div>
                <a href="{{ route('my-courses.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white dark:bg-slate-900/20 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-black hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                    <i class="fas fa-book-open"></i>
                    {{ __('student.view_my_courses') }}
                </a>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 pb-10 space-y-6">
        @if(isset($stats))
            <div class="grid grid-cols-2 gap-3 sm:gap-4">
                <div class="bg-white dark:bg-slate-800/95 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('student.total_certificates') }}</p>
                            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 leading-none mt-1">{{ $stats['total'] ?? 0 }}</p>
                        </div>
                        <div class="w-11 h-11 rounded-2xl bg-brand-50 dark:bg-slate-900/30 flex items-center justify-center text-brand-700 flex-shrink-0 border border-brand-100 dark:border-slate-700">
                            <i class="fas fa-certificate"></i>
                        </div>
                    </div>
                </div>
                <div class="bg-white dark:bg-slate-800/95 rounded-3xl p-5 border border-slate-200 dark:border-slate-700 shadow-sm">
                    <div class="flex items-center justify-between gap-3">
                        <div class="min-w-0">
                            <p class="text-xs font-black text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('student.issued_label') }}</p>
                            <p class="text-2xl sm:text-3xl font-black text-slate-900 dark:text-slate-100 leading-none mt-1">{{ $stats['issued'] ?? 0 }}</p>
                        </div>
                        <div class="w-11 h-11 rounded-2xl bg-slate-50 dark:bg-slate-900/30 flex items-center justify-center text-slate-700 dark:text-slate-200 flex-shrink-0 border border-slate-200 dark:border-slate-700">
                            <i class="fas fa-check-circle"></i>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if(isset($certificates) && $certificates->count() > 0)
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($certificates as $certificate)
                    <a href="{{ route('student.certificates.show', $certificate) }}" class="bg-white dark:bg-slate-800/95 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden hover:shadow-md hover:border-brand-200 transition-all block">
                        <div class="p-5 sm:p-6">
                            <div class="w-12 h-12 rounded-2xl bg-brand-50 dark:bg-slate-900/30 flex items-center justify-center text-brand-700 mb-4 border border-brand-100 dark:border-slate-700">
                                <i class="fas fa-certificate text-lg"></i>
                            </div>
                            <h3 class="text-base font-black text-slate-900 dark:text-slate-100 mb-2 line-clamp-2 leading-snug">
                                {{ $certificate->title ?? $certificate->course_name ?? __('student.completion_certificate') }}
                            </h3>
                            @if($certificate->course)
                                <p class="text-sm text-slate-600 dark:text-slate-300 mb-3 line-clamp-2">{{ $certificate->course->title }}</p>
                            @endif
                            <div class="flex flex-wrap items-center gap-2 text-xs text-slate-500 dark:text-slate-400 mb-3">
                                <span><i class="fas fa-calendar text-brand-600 ml-1"></i>{{ ($certificate->issued_at ? $certificate->issued_at->format('Y-m-d') : ($certificate->issue_date ? $certificate->issue_date->format('Y-m-d') : '-')) }}</span>
                                @if($certificate->certificate_number)
                                    <span class="font-mono bg-slate-100 dark:bg-slate-900/30 px-2 py-0.5 rounded-xl border border-slate-200 dark:border-slate-700">#{{ substr($certificate->certificate_number, -6) }}</span>
                                @endif
                            </div>
                            <span class="inline-flex items-center gap-2 text-brand-700 dark:text-brand-300 font-black text-sm">
                                {{ __('student.view_certificate') }} <i class="fas fa-arrow-left text-xs"></i>
                            </span>
                        </div>
                    </a>
                @endforeach
            </div>
            @if($certificates->hasPages())
                <div class="flex justify-center">{{ $certificates->links() }}</div>
            @endif
        @else
            <div class="rounded-3xl p-10 sm:p-12 text-center bg-slate-50/70 dark:bg-slate-800/40 border border-dashed border-slate-200 dark:border-slate-700">
                <div class="w-16 h-16 bg-brand-50 dark:bg-slate-900/30 rounded-2xl flex items-center justify-center mx-auto mb-4 text-brand-700 border border-brand-100 dark:border-slate-700">
                    <i class="fas fa-certificate text-2xl"></i>
                </div>
                <h3 class="text-lg font-black text-slate-900 dark:text-slate-100 mb-2">{{ __('student.no_certificates') }}</h3>
                <p class="text-sm text-slate-500 dark:text-slate-300 mb-6 max-w-sm mx-auto">{{ __('student.no_certificates_desc') }}</p>
                <a href="{{ route('my-courses.index') }}" class="inline-flex items-center gap-2 bg-brand-600 hover:bg-brand-700 text-white px-5 py-2.5 rounded-2xl text-sm font-black transition-colors shadow-sm">
                    <i class="fas fa-book-open"></i> {{ __('student.view_my_courses') }}
                </a>
            </div>
        @endif
    </section>
</div>
@endsection

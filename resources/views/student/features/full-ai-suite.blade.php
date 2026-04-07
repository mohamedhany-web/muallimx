@extends('layouts.app')

@section('title', $label . ' — ' . __('student.full_ai_suite.layer1_title'))
@section('header', $label)

@php
    $cfg = $featureConfig ?? [];
    $icon = $cfg['icon'] ?? 'fa-wand-magic-sparkles';
    $iconBg = $cfg['icon_bg'] ?? 'bg-purple-100 dark:bg-purple-900/40';
    $iconText = $cfg['icon_text'] ?? 'text-purple-600 dark:text-purple-400';
    $qTypes = \App\Services\FullAiSuiteContextService::QUESTION_TYPES;
    $preview = session('full_ai_preview');
    $jsonPreview = $preview && is_array($preview) ? json_encode($preview['context'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) : '';
    $promptText = $preview && is_array($preview) ? (string) ($preview['prompt'] ?? '') : '';
@endphp

@section('content')
{{-- عرض كامل لمساحة المحتوى: إلغاء حد العرض + تمديد لحدود الـ main --}}
<div class="w-full min-w-0 min-h-[calc(100dvh-7rem)] sm:min-h-[calc(100dvh-8rem)] -mx-4 px-4 md:-mx-6 md:px-6 lg:-mx-8 lg:px-8 pb-8 space-y-6 sm:space-y-8">
    {{-- شريط الخطوات --}}
    <div class="flex flex-wrap items-center justify-center gap-2 sm:gap-4 text-[11px] sm:text-xs font-semibold text-slate-500 dark:text-slate-400">
        <span class="hidden sm:inline text-slate-400 dark:text-slate-500">{{ __('student.full_ai_suite.step_intro') }}</span>
        <div class="flex items-center gap-2 sm:gap-3">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-violet-100 dark:bg-violet-900/50 text-violet-800 dark:text-violet-200 border border-violet-200/80 dark:border-violet-800/60">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-violet-600 text-[10px] text-white font-black">1</span>
                UI
            </span>
            <span class="text-slate-300 dark:text-slate-600 select-none" aria-hidden="true">·</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-sky-100 dark:bg-sky-900/40 text-sky-800 dark:text-sky-200 border border-sky-200/80 dark:border-sky-800/60">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-sky-600 text-[10px] text-white font-black">2</span>
                {{ __('student.full_ai_suite.step_context_label') }}
            </span>
            <span class="text-slate-300 dark:text-slate-600 select-none" aria-hidden="true">·</span>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full bg-purple-100 dark:bg-purple-900/40 text-purple-800 dark:text-purple-200 border border-purple-200/80 dark:border-purple-800/60">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-purple-600 text-[10px] text-white font-black">3</span>
                Prompt
            </span>
        </div>
    </div>

    {{-- الطبقة 1 + 2 — بطاقة بعرض كامل --}}
    <div class="w-full rounded-3xl bg-white dark:bg-slate-800/95 border border-slate-200/90 dark:border-slate-700 shadow-xl shadow-slate-200/40 dark:shadow-none overflow-hidden ring-1 ring-slate-100/80 dark:ring-slate-700/50">
        <div class="relative overflow-hidden bg-gradient-to-br from-violet-50 via-white to-fuchsia-50/40 dark:from-violet-950/50 dark:via-slate-800 dark:to-slate-900 p-6 sm:p-8 lg:p-10 border-b border-slate-100 dark:border-slate-700/80">
            <div class="pointer-events-none absolute -top-24 -end-24 h-48 w-48 rounded-full bg-violet-400/10 dark:bg-violet-500/5 blur-3xl"></div>
            <div class="pointer-events-none absolute -bottom-16 -start-16 h-40 w-40 rounded-full bg-fuchsia-400/10 dark:bg-fuchsia-500/5 blur-3xl"></div>

            <div class="relative grid grid-cols-1 xl:grid-cols-[auto_minmax(0,1fr)] gap-6 xl:gap-10 xl:items-start">
                <div class="flex shrink-0 items-center justify-center w-16 h-16 sm:w-[4.5rem] sm:h-[4.5rem] xl:w-20 xl:h-20 rounded-2xl {{ $iconBg }} {{ $iconText }} shadow-inner ring-4 ring-white/80 dark:ring-slate-800/80 mx-auto xl:mx-0">
                    <i class="fas {{ $icon }} text-2xl sm:text-[1.65rem] xl:text-3xl"></i>
                </div>
                <div class="flex-1 min-w-0 space-y-3 text-center xl:text-start">
                    <div class="flex flex-col gap-2 sm:flex-row sm:flex-wrap sm:items-center sm:gap-2 xl:justify-start justify-center">
                        <h1 class="text-2xl sm:text-3xl xl:text-4xl font-black tracking-tight text-slate-900 dark:text-white">{{ $label }}</h1>
                        <span class="inline-flex w-fit mx-auto sm:mx-0 items-center gap-2 px-3 py-1.5 rounded-xl bg-emerald-500/10 dark:bg-emerald-400/10 text-emerald-800 dark:text-emerald-200 text-xs font-bold border border-emerald-300/40 dark:border-emerald-600/40 shadow-sm">
                            <i class="fas fa-graduation-cap text-emerald-600 dark:text-emerald-400"></i>
                            {{ __('student.full_ai_suite.layer1_title') }}
                        </span>
                    </div>
                    <p class="text-sm sm:text-[15px] text-slate-600 dark:text-slate-400 leading-relaxed max-w-none">{{ $description }}</p>
                    <div class="rounded-2xl border border-s-4 border-violet-500/80 bg-violet-50/80 dark:bg-violet-950/35 dark:border-violet-500/60 px-4 py-3 text-sm font-medium text-violet-950 dark:text-violet-100 leading-relaxed shadow-sm text-start">
                        {{ __('student.full_ai_suite.layer1_subtitle') }}
                    </div>
                </div>
            </div>
        </div>

        {{-- الطبقة 2 — النموذج بعرض كامل --}}
        <div class="p-5 sm:p-8 lg:p-10">
            <div class="flex items-start gap-3 mb-6 lg:mb-8">
                <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-sky-100 dark:bg-sky-900/45 text-sky-700 dark:text-sky-300 text-sm font-black shadow-sm">2</span>
                <div class="min-w-0">
                    <h2 class="text-lg lg:text-xl font-black text-slate-900 dark:text-white">{{ __('student.full_ai_suite.layer2_title') }}</h2>
                    <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed max-w-none">{{ __('student.full_ai_suite.layer2_hint') }}</p>
                </div>
            </div>

            @if ($errors->any())
                <div class="mb-6 rounded-2xl bg-red-50 dark:bg-red-950/30 border border-red-200/90 dark:border-red-800/60 px-4 py-3.5 text-sm text-red-800 dark:text-red-200 space-y-1.5 shadow-sm">
                    @foreach ($errors->all() as $err)
                        <p class="flex items-start gap-2"><i class="fas fa-circle-exclamation mt-0.5 shrink-0 opacity-80"></i><span>{{ $err }}</span></p>
                    @endforeach
                </div>
            @endif

            @if($courses->isEmpty())
                <div class="rounded-2xl border-2 border-dashed border-slate-200 dark:border-slate-600 bg-gradient-to-b from-slate-50/90 to-white dark:from-slate-900/50 dark:to-slate-800/30 px-6 py-12 sm:py-16 text-center">
                    <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-slate-200/80 dark:bg-slate-700 text-slate-500 dark:text-slate-400">
                        <i class="fas fa-book-open text-xl"></i>
                    </div>
                    <p class="text-sm text-slate-600 dark:text-slate-400 max-w-2xl mx-auto leading-relaxed">{{ __('student.full_ai_suite.course_empty') }}</p>
                    <a href="{{ route('public.courses') }}" class="mt-5 inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold shadow-md shadow-sky-600/20 transition-colors">
                        <i class="fas fa-magnifying-glass text-xs opacity-90"></i>
                        {{ __('student.full_ai_suite.course_empty_link') }}
                    </a>
                </div>
            @else
                <form action="{{ route('student.features.full-ai-suite.preview') }}" method="POST" class="space-y-6 w-full">
                    @csrf
                    <div class="rounded-2xl border border-slate-200/90 dark:border-slate-600/80 bg-slate-50/70 dark:bg-slate-900/40 p-5 sm:p-6 lg:p-8 space-y-5 lg:space-y-6 shadow-inner w-full">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-2 gap-5 lg:gap-6 w-full">
                            <div class="space-y-2 min-w-0">
                                <label for="advanced_course_id" class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white dark:bg-slate-800 text-sky-600 dark:text-sky-400 shadow-sm border border-slate-200/80 dark:border-slate-600"><i class="fas fa-book text-xs"></i></span>
                                    {{ __('student.full_ai_suite.course_label') }}
                                </label>
                                <select name="advanced_course_id" id="advanced_course_id" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-4 py-3 text-sm shadow-sm focus:ring-2 focus:ring-violet-500/80 focus:border-violet-500 transition">
                                    <option value="">{{ __('student.full_ai_suite.course_placeholder') }}</option>
                                    @foreach($courses as $c)
                                        <option value="{{ $c->id }}" @selected(old('advanced_course_id') == $c->id)>
                                            {{ $c->title }}@if($c->category) — {{ $c->category }} @endif
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="space-y-2 min-w-0">
                                <label for="question_type" class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200">
                                    <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white dark:bg-slate-800 text-violet-600 dark:text-violet-400 shadow-sm border border-slate-200/80 dark:border-slate-600"><i class="fas fa-sliders text-xs"></i></span>
                                    {{ __('student.full_ai_suite.question_type_label') }}
                                </label>
                                <select name="question_type" id="question_type" required
                                    class="w-full rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-4 py-3 text-sm shadow-sm focus:ring-2 focus:ring-violet-500/80 focus:border-violet-500 transition">
                                    @foreach($qTypes as $key => $transKey)
                                        <option value="{{ $key }}" @selected(old('question_type') === $key)>{{ __($transKey) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="space-y-2 w-full" x-data="{ len: {{ strlen(old('question', '')) }} }">
                            <label for="question" class="flex items-center gap-2 text-sm font-bold text-slate-800 dark:text-slate-200">
                                <span class="flex h-7 w-7 items-center justify-center rounded-lg bg-white dark:bg-slate-800 text-fuchsia-600 dark:text-fuchsia-400 shadow-sm border border-slate-200/80 dark:border-slate-600"><i class="fas fa-message text-xs"></i></span>
                                {{ __('student.full_ai_suite.question_label') }}
                            </label>
                            <textarea name="question" id="question" rows="7" required minlength="10" maxlength="4000"
                                placeholder="{{ __('student.full_ai_suite.question_placeholder') }}"
                                x-on:input="len = $el.value.length"
                                class="w-full min-h-[160px] lg:min-h-[180px] rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-900 dark:text-slate-100 px-4 py-3 text-sm leading-relaxed shadow-sm focus:ring-2 focus:ring-violet-500/80 focus:border-violet-500 transition resize-y">{{ old('question') }}</textarea>
                            <div class="flex justify-between text-[11px] text-slate-400 dark:text-slate-500 px-0.5">
                                <span>10 — 4000</span>
                                <span><span x-text="len">0</span> / 4000</span>
                            </div>
                        </div>
                    </div>
                    <div class="flex flex-col lg:flex-row lg:items-center gap-3 lg:gap-6 w-full">
                        <button type="submit" class="inline-flex items-center justify-center gap-2.5 px-8 py-3.5 rounded-xl bg-gradient-to-l from-violet-600 to-fuchsia-600 hover:from-violet-700 hover:to-fuchsia-700 text-white text-sm font-black shadow-lg shadow-violet-500/25 dark:shadow-violet-900/40 transition-all active:scale-[0.98] w-full lg:w-auto lg:min-w-[200px]">
                            <i class="fas fa-wand-magic-sparkles"></i>
                            {{ __('student.full_ai_suite.preview_button') }}
                        </button>
                        <p class="text-[11px] text-slate-500 dark:text-slate-500 text-center lg:text-start flex-1 leading-relaxed">{{ __('student.full_ai_suite.preview_hint_short') }}</p>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- الطبقة 3 — معاينة بعرض كامل: عمودان على الشاشات العريضة --}}
    @if($preview && is_array($preview))
        <div class="w-full rounded-3xl bg-white dark:bg-slate-800/95 border border-slate-200/90 dark:border-slate-700 shadow-xl overflow-hidden ring-1 ring-slate-100/80 dark:ring-slate-700/50" x-data="{ tab: 'json' }">
            <div class="px-5 sm:px-8 lg:px-10 py-5 sm:py-6 border-b border-slate-100 dark:border-slate-700/80 bg-gradient-to-l from-slate-50 to-white dark:from-slate-800/80 dark:to-slate-900/90">
                <div class="flex items-start gap-3">
                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-purple-100 dark:bg-purple-900/45 text-purple-700 dark:text-purple-300 text-sm font-black shadow-sm">3</span>
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg lg:text-xl font-black text-slate-900 dark:text-white">{{ __('student.full_ai_suite.layer3_title') }}</h2>
                        <p class="text-xs sm:text-sm text-slate-500 dark:text-slate-400 mt-1 leading-relaxed">{{ __('student.full_ai_suite.layer3_note') }}</p>
                    </div>
                </div>
                {{-- تبويب للشاشات الصغيرة فقط (نفس نطاق Alpine للمحتوى) --}}
                <div class="xl:hidden mt-4 flex rounded-xl bg-slate-100/90 dark:bg-slate-900/60 p-1 gap-1">
                    <button type="button" @click="tab = 'json'" :class="tab === 'json' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500'" class="flex-1 rounded-lg px-3 py-2 text-xs font-bold transition">{{ __('student.full_ai_suite.context_json_title') }}</button>
                    <button type="button" @click="tab = 'prompt'" :class="tab === 'prompt' ? 'bg-white dark:bg-slate-800 text-slate-900 dark:text-white shadow-sm' : 'text-slate-500'" class="flex-1 rounded-lg px-3 py-2 text-xs font-bold transition">{{ __('student.full_ai_suite.prompt_preview_title') }}</button>
                </div>
            </div>

            <div class="p-5 sm:p-8 lg:p-10">
                {{-- موبايل/تابلت: تبويب --}}
                <div class="xl:hidden space-y-6">
                    <div x-show="tab === 'json'" x-cloak class="space-y-3" x-data="{ copied: false }">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('student.full_ai_suite.context_json_title') }}</span>
                            <button type="button" @click="navigator.clipboard.writeText($refs.jsonBlockSm.textContent.trim()); copied = true; setTimeout(() => copied = false, 2000)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                                <i class="fas fa-copy text-[10px]"></i>
                                <span x-show="!copied">{{ __('student.full_ai_suite.copy') }}</span>
                                <span x-show="copied" x-cloak class="text-emerald-600 dark:text-emerald-400">{{ __('student.full_ai_suite.copied') }}</span>
                            </button>
                        </div>
                        <pre x-ref="jsonBlockSm" class="text-[11px] sm:text-xs leading-relaxed p-4 sm:p-5 rounded-2xl bg-[#0d1117] text-[#7ee787] overflow-x-auto border border-slate-700/80 font-mono shadow-inner max-h-[min(50vh,480px)] min-h-[200px] overflow-y-auto" dir="ltr">{{ $jsonPreview }}</pre>
                    </div>
                    <div x-show="tab === 'prompt'" x-cloak class="space-y-3" x-data="{ copied: false }">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('student.full_ai_suite.prompt_preview_title') }}</span>
                            <button type="button" @click="navigator.clipboard.writeText($refs.promptBlockSm.textContent.trim()); copied = true; setTimeout(() => copied = false, 2000)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition">
                                <i class="fas fa-copy text-[10px]"></i>
                                <span x-show="!copied">{{ __('student.full_ai_suite.copy') }}</span>
                                <span x-show="copied" x-cloak class="text-emerald-600 dark:text-emerald-400">{{ __('student.full_ai_suite.copied') }}</span>
                            </button>
                        </div>
                        <pre x-ref="promptBlockSm" class="text-[11px] sm:text-xs leading-relaxed p-4 sm:p-5 rounded-2xl bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-200 overflow-x-auto border border-slate-200 dark:border-slate-700 whitespace-pre-wrap font-mono shadow-inner max-h-[min(50vh,480px)] min-h-[200px] overflow-y-auto" dir="ltr">{{ $promptText }}</pre>
                    </div>
                </div>

                {{-- سطح المكتب: عمودان بعرض كامل --}}
                <div class="hidden xl:grid xl:grid-cols-2 xl:gap-8 w-full min-w-0">
                    <div class="space-y-3 min-w-0 flex flex-col" x-data="{ copied: false }">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('student.full_ai_suite.context_json_title') }}</span>
                            <button type="button" @click="navigator.clipboard.writeText($refs.jsonBlockLg.textContent.trim()); copied = true; setTimeout(() => copied = false, 2000)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition shrink-0">
                                <i class="fas fa-copy text-[10px]"></i>
                                <span x-show="!copied">{{ __('student.full_ai_suite.copy') }}</span>
                                <span x-show="copied" x-cloak class="text-emerald-600 dark:text-emerald-400">{{ __('student.full_ai_suite.copied') }}</span>
                            </button>
                        </div>
                        <pre x-ref="jsonBlockLg" class="flex-1 text-[11px] sm:text-xs leading-relaxed p-4 sm:p-5 rounded-2xl bg-[#0d1117] text-[#7ee787] overflow-x-auto border border-slate-700/80 font-mono shadow-inner min-h-[280px] max-h-[min(60vh,560px)] overflow-y-auto w-full" dir="ltr">{{ $jsonPreview }}</pre>
                    </div>
                    <div class="space-y-3 min-w-0 flex flex-col" x-data="{ copied: false }">
                        <div class="flex items-center justify-between gap-2">
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400 uppercase tracking-wide">{{ __('student.full_ai_suite.prompt_preview_title') }}</span>
                            <button type="button" @click="navigator.clipboard.writeText($refs.promptBlockLg.textContent.trim()); copied = true; setTimeout(() => copied = false, 2000)" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-600 transition shrink-0">
                                <i class="fas fa-copy text-[10px]"></i>
                                <span x-show="!copied">{{ __('student.full_ai_suite.copy') }}</span>
                                <span x-show="copied" x-cloak class="text-emerald-600 dark:text-emerald-400">{{ __('student.full_ai_suite.copied') }}</span>
                            </button>
                        </div>
                        <pre x-ref="promptBlockLg" class="flex-1 text-[11px] sm:text-xs leading-relaxed p-4 sm:p-5 rounded-2xl bg-slate-100 dark:bg-slate-950 text-slate-800 dark:text-slate-200 overflow-x-auto border border-slate-200 dark:border-slate-700 whitespace-pre-wrap font-mono shadow-inner min-h-[280px] max-h-[min(60vh,560px)] overflow-y-auto w-full" dir="ltr">{{ $promptText }}</pre>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="flex flex-col sm:flex-row items-stretch sm:items-center justify-center gap-3 pt-2 w-full">
        <a href="{{ route('student.my-subscription') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800/80 text-slate-700 dark:text-slate-200 text-sm font-bold hover:bg-slate-50 dark:hover:bg-slate-700 transition shadow-sm w-full sm:w-auto">
            <i class="fas fa-receipt text-sky-600 dark:text-sky-400"></i>
            {{ __('student.full_ai_suite.back_subscription') }}
        </a>
    </div>
</div>

@push('styles')
<style>[x-cloak]{display:none!important}</style>
@endpush
@endsection

@extends('layouts.app')

@section('title', __('student.dashboard_title'))

@push('styles')
<style>
    .td-feature {
        display: flex; flex-direction: column;
        background: white;
        border: 1px solid rgba(226, 232, 240, 0.95);
        border-radius: 18px;
        padding: 1.15rem 1.2rem;
        transition: transform .2s ease, box-shadow .2s ease, border-color .2s ease;
        position: relative;
        overflow: hidden;
        min-height: 168px;
    }
    .td-feature:hover {
        transform: translateY(-3px);
        box-shadow: 0 18px 36px -18px rgba(15, 23, 42, .12);
        border-color: rgba(40, 53, 147, .22);
    }
    .td-feature.is-locked {
        background: linear-gradient(180deg, #fff 0%, #f8fafc 100%);
    }
    .td-feature.is-locked:hover { border-color: rgba(251, 86, 7, .35); }
    .dark .td-feature {
        background: rgba(30, 41, 59, .92);
        border-color: rgba(71, 85, 105, .8);
        color: #e2e8f0;
    }
    .dark .td-feature.is-locked {
        background: linear-gradient(180deg, rgba(30,41,59,.95) 0%, rgba(15,23,42,.9) 100%);
    }
</style>
@endpush

@section('content')
@php
    $unlockedPct = $stats['features_total'] > 0
        ? (int) round(($stats['features_unlocked'] / $stats['features_total']) * 100)
        : 0;
@endphp

<div class="space-y-6">
    {{-- ترحيب واضح للمعلم --}}
    <div class="rounded-2xl bg-white dark:bg-slate-800/95 border border-slate-200/80 dark:border-slate-700 overflow-hidden">
        <div class="bg-gradient-to-l from-[#FFE5F7]/70 via-white to-white dark:from-slate-800/80 dark:via-slate-800/90 dark:to-slate-900/90 p-5 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-5">
                <div class="flex-1 min-w-0">
                    <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-lg bg-[#FFE5F7] dark:bg-brand-900/30 text-[#283593] dark:text-brand-300 text-xs font-bold mb-3 border border-[#f5c7e8] dark:border-brand-800/50">
                        <i class="fas fa-chalkboard-teacher text-[10px]"></i>
                        {{ __('student.teacher_hub_badge') }}
                    </span>
                    <h1 class="font-heading text-2xl sm:text-3xl font-black text-slate-800 dark:text-slate-100 mb-1 leading-tight">
                        {{ __('student.welcome_name', ['name' => auth()->user()->name]) }}
                    </h1>
                    <p class="text-slate-500 dark:text-slate-400 text-sm max-w-2xl leading-relaxed">
                        @if(empty($activeSubscription))
                            {{ __('student.dashboard_subtitle_no_subscription') }}
                        @elseif(!empty($isFreeTrial))
                            {{ __('student.dashboard_subtitle_free_trial') }}
                        @else
                            {{ __('student.dashboard_subtitle_teacher') }}
                        @endif
                    </p>

                    <div class="mt-4 flex flex-wrap items-center gap-2">
                        <a href="{{ $pricingUrl }}"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#FB5607] hover:bg-[#e04d00] text-white text-sm font-bold shadow-md shadow-[#FB5607]/25 transition-colors">
                            <i class="fas fa-{{ empty($activeSubscription) ? 'bolt' : 'sync-alt' }} text-xs"></i>
                            {{ empty($activeSubscription) ? __('student.subscribe_now') : __('student.renew_or_upgrade') }}
                        </a>
                        <a href="{{ $pricingUrl }}#plans"
                           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-[#283593]/20 bg-white dark:bg-slate-800 text-[#283593] dark:text-brand-300 text-sm font-bold hover:bg-[#FFE5F7]/50 dark:hover:bg-slate-700 transition-colors">
                            <i class="fas fa-tags text-xs"></i>
                            {{ __('student.view_packages') }}
                        </a>
                        @if($activeSubscription && Route::has('student.my-subscription'))
                            <a href="{{ route('student.my-subscription') }}"
                               class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-sm font-semibold hover:bg-slate-50 dark:hover:bg-slate-700 transition-colors">
                                <i class="fas fa-gem text-xs"></i>
                                {{ __('student.my_subscription_short') }}
                            </a>
                        @endif
                    </div>
                </div>

                <div class="shrink-0 rounded-2xl border border-slate-200 dark:border-slate-600 bg-white/80 dark:bg-slate-900/60 px-5 py-4 min-w-[200px]">
                    <p class="text-xs font-bold text-slate-500 dark:text-slate-400 mb-1">{{ __('student.features_unlocked_label') }}</p>
                    <p class="text-3xl font-black text-[#283593] dark:text-indigo-300 tabular-nums">
                        {{ $stats['features_unlocked'] }}<span class="text-lg text-slate-400">/{{ $stats['features_total'] }}</span>
                    </p>
                    <div class="mt-3 h-2 rounded-full bg-slate-100 dark:bg-slate-700 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-l from-[#283593] to-[#FB5607]" style="width: {{ $unlockedPct }}%"></div>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 mt-2">{{ __('student.features_unlocked_hint') }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- حالة الاشتراك / التجربة / التجديد --}}
    @if(empty($activeSubscription))
        <div class="rounded-2xl border-2 border-[#FB5607]/30 bg-gradient-to-l from-[#FFF7ED] via-white to-[#FFE5F7]/40 dark:from-slate-800/90 dark:via-slate-800/95 dark:to-slate-900/90 dark:border-amber-500/30 p-5 sm:p-6">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                <div class="flex items-start gap-4 flex-1">
                    <span class="w-12 h-12 shrink-0 rounded-2xl bg-gradient-to-br from-[#FB5607] to-[#283593] text-white flex items-center justify-center">
                        <i class="fas fa-crown"></i>
                    </span>
                    <div>
                        <h2 class="font-heading text-lg font-black text-slate-800 dark:text-slate-100">{{ __('student.subscribe_cta_title') }}</h2>
                        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1 leading-relaxed">{{ __('student.subscribe_cta_description') }}</p>
                        <p class="text-xs text-amber-700 dark:text-amber-300 font-semibold mt-2">{{ __('student.locked_features_hint') }}</p>
                    </div>
                </div>
                <a href="{{ $pricingUrl }}" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#FB5607] hover:bg-[#e04d00] text-white text-sm font-bold shrink-0">
                    <i class="fas fa-bolt"></i> {{ __('student.choose_package') }}
                </a>
            </div>
        </div>
    @else
        <div class="rounded-2xl bg-white dark:bg-slate-800/95 border border-slate-200/80 dark:border-slate-700 overflow-hidden {{ !empty($renewHighlight) ? 'ring-2 ring-[#FB5607]/40' : '' }}">
            <div class="p-4 sm:p-5 flex flex-col lg:flex-row lg:items-center justify-between gap-4 bg-gradient-to-l from-[#FFE5F7]/80 to-white dark:from-slate-800/90 dark:to-slate-900/90">
                <div class="flex items-start gap-3 min-w-0">
                    <span class="w-11 h-11 rounded-xl {{ !empty($isFreeTrial) ? 'bg-amber-100 text-amber-700' : 'bg-[#FFE5F7] text-[#283593]' }} dark:bg-indigo-900/40 dark:text-indigo-300 flex items-center justify-center shrink-0">
                        <i class="fas {{ !empty($isFreeTrial) ? 'fa-gift' : 'fa-layer-group' }}"></i>
                    </span>
                    <div class="min-w-0">
                        <h2 class="font-bold text-slate-800 dark:text-slate-100 truncate">{{ $activeSubscription->plan_name }}</h2>
                        <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                            {{ \App\Models\Subscription::getDurationLabel($activeSubscription->billing_cycle) }}
                            @if($activeSubscription->end_date)
                                · {{ __('student.ends_on') }} {{ $activeSubscription->end_date->format('Y-m-d') }}
                            @endif
                            @if($daysRemaining !== null)
                                · <span class="font-semibold {{ $daysRemaining <= 3 ? 'text-rose-600' : 'text-emerald-600' }}">{{ __('student.days_left', ['days' => $daysRemaining]) }}</span>
                            @endif
                        </p>
                        @if(!empty($isFreeTrial))
                            <p class="text-xs text-amber-700 dark:text-amber-300 font-semibold mt-1.5">
                                {{ __('student.free_trial_banner', ['used' => $daysUsed ?? 0]) }}
                            </p>
                        @endif
                    </div>
                </div>
                <div class="flex flex-wrap gap-2 shrink-0">
                    @if(!empty($showRenewCta))
                        <a href="{{ $pricingUrl }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#FB5607] hover:bg-[#e04d00] text-white text-sm font-bold shadow-md shadow-[#FB5607]/20">
                            <i class="fas fa-sync-alt"></i>
                            {{ !empty($isFreeTrial) ? __('student.upgrade_after_trial') : __('student.renew_package') }}
                        </a>
                    @endif
                    @if(Route::has('student.my-subscription'))
                        <a href="{{ route('student.my-subscription') }}"
                           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#283593] text-white text-sm font-semibold hover:bg-[#1f2a7a]">
                            <i class="fas fa-info-circle"></i>
                            {{ __('student.my_subscription_short') }}
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- خطوات بسيطة لأول مرة --}}
    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/95 p-5">
        <h2 class="font-heading text-base font-black text-slate-800 dark:text-slate-100 mb-4">{{ __('student.getting_started_title') }}</h2>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
            <div class="rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 p-4">
                <span class="inline-flex w-7 h-7 rounded-lg bg-[#283593] text-white text-xs font-black items-center justify-center mb-2">1</span>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('student.step_choose_package') }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ __('student.step_choose_package_desc') }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 p-4">
                <span class="inline-flex w-7 h-7 rounded-lg bg-[#FB5607] text-white text-xs font-black items-center justify-center mb-2">2</span>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('student.step_open_features') }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ __('student.step_open_features_desc') }}</p>
            </div>
            <div class="rounded-xl border border-slate-100 dark:border-slate-700 bg-slate-50/80 dark:bg-slate-900/40 p-4">
                <span class="inline-flex w-7 h-7 rounded-lg bg-emerald-600 text-white text-xs font-black items-center justify-center mb-2">3</span>
                <p class="text-sm font-bold text-slate-800 dark:text-slate-100">{{ __('student.step_start_teaching') }}</p>
                <p class="text-xs text-slate-500 mt-1 leading-relaxed">{{ __('student.step_start_teaching_desc') }}</p>
            </div>
        </div>
    </div>

    {{-- شبكة مزايا الباقة — ظاهرة دائماً --}}
    <div>
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <div>
                <h2 class="font-heading text-lg sm:text-xl font-black text-slate-800 dark:text-slate-100">{{ __('student.package_features_title') }}</h2>
                <p class="text-sm text-slate-500 dark:text-slate-400 mt-0.5">{{ __('student.package_features_subtitle') }}</p>
            </div>
            <div class="flex items-center gap-3 text-xs font-semibold">
                <span class="inline-flex items-center gap-1.5 text-emerald-700 dark:text-emerald-400"><span class="w-2 h-2 rounded-full bg-emerald-500"></span>{{ __('student.feature_active') }}</span>
                <span class="inline-flex items-center gap-1.5 text-slate-500"><span class="w-2 h-2 rounded-full bg-slate-300"></span>{{ __('student.feature_locked') }}</span>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($packageFeatures as $feature)
                <a href="{{ $feature['url'] }}" class="td-feature {{ $feature['unlocked'] ? '' : 'is-locked' }} group">
                    <div class="flex items-start justify-between gap-3 mb-3">
                        <span class="w-11 h-11 rounded-xl flex items-center justify-center {{ $feature['icon_bg'] }} {{ $feature['icon_text'] }}">
                            <i class="fas {{ $feature['icon'] }}"></i>
                        </span>
                        @if($feature['unlocked'])
                            <span class="inline-flex items-center gap-1 text-[10px] font-black px-2 py-1 rounded-lg bg-emerald-50 text-emerald-700 border border-emerald-200 dark:bg-emerald-900/30 dark:text-emerald-300 dark:border-emerald-800">
                                <i class="fas fa-check"></i> {{ __('student.feature_active') }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-black px-2 py-1 rounded-lg bg-slate-100 text-slate-600 border border-slate-200 dark:bg-slate-700 dark:text-slate-300 dark:border-slate-600">
                                <i class="fas fa-lock"></i> {{ __('student.feature_locked') }}
                            </span>
                        @endif
                    </div>
                    <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm leading-snug group-hover:text-[#283593] dark:group-hover:text-indigo-300 transition-colors">
                        {{ $feature['label'] }}
                    </h3>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-1.5 leading-relaxed line-clamp-3 flex-1">
                        {{ $feature['description'] }}
                    </p>
                    <div class="mt-3 pt-3 border-t border-slate-100 dark:border-slate-700 flex items-center justify-between text-xs font-bold">
                        @if($feature['unlocked'])
                            <span class="text-[#283593] dark:text-indigo-300">{{ __('student.open_feature') }}</span>
                        @else
                            <span class="text-[#FB5607]">{{ __('student.unlock_via_package') }}</span>
                        @endif
                        <i class="fas fa-arrow-left text-[10px] opacity-60"></i>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
</div>
@endsection

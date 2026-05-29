@props([
    'variant' => 'banner',
])

@php
    $pricingUrl = route('public.pricing');
@endphp

@if($variant === 'compact')
    <div class="flex flex-wrap items-center gap-2">
        <a href="{{ $pricingUrl }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#FB5607] hover:bg-[#e04d00] text-white text-sm font-bold shadow-md shadow-[#FB5607]/25 transition-colors">
            <i class="fas fa-bolt text-xs"></i>
            {{ __('student.subscribe_now') }}
        </a>
        <a href="{{ $pricingUrl }}#plans"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-[#283593]/20 bg-white dark:bg-slate-800 text-[#283593] dark:text-brand-300 text-sm font-bold hover:border-[#283593]/40 hover:bg-[#FFE5F7]/50 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-tags text-xs"></i>
            {{ __('student.view_packages') }}
        </a>
    </div>
@else
    <div class="rounded-2xl border-2 border-[#FB5607]/30 bg-gradient-to-l from-[#FFF7ED] via-white to-[#FFE5F7]/40 dark:from-slate-800/90 dark:via-slate-800/95 dark:to-slate-900/90 dark:border-amber-500/30 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center gap-5">
            <div class="flex items-start gap-4 flex-1 min-w-0">
                <span class="w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-[#FB5607] to-[#283593] text-white flex items-center justify-center shadow-lg shadow-[#FB5607]/20">
                    <i class="fas fa-crown text-xl"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="font-heading text-lg sm:text-xl font-black text-slate-800 dark:text-slate-100 mb-1">
                        {{ __('student.subscribe_cta_title') }}
                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed max-w-2xl">
                        {{ __('student.subscribe_cta_description') }}
                    </p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                <a href="{{ $pricingUrl }}"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#FB5607] hover:bg-[#e04d00] text-white text-sm font-bold shadow-md shadow-[#FB5607]/25 transition-colors">
                    <i class="fas fa-bolt"></i>
                    {{ __('student.subscribe_now') }}
                </a>
                <a href="{{ $pricingUrl }}#plans"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#283593] hover:bg-[#1f2a7a] text-white text-sm font-bold transition-colors">
                    <i class="fas fa-layer-group"></i>
                    {{ __('student.view_packages') }}
                </a>
            </div>
        </div>
    </div>
@endif

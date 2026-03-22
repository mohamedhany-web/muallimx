@extends('layouts.admin')

@section('title', __('admin.hiring_academies'))
@section('header', __('admin.hiring_academies'))

@section('content')
<div class="space-y-8">
    @if(session('success'))
        <div class="rounded-2xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-5 py-4 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl bg-rose-50 dark:bg-rose-900/20 border border-rose-200 dark:border-rose-800 text-rose-800 dark:text-rose-200 px-5 py-4 text-sm font-medium">{{ session('error') }}</div>
    @endif

    {{-- Hero --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-indigo-900 via-slate-900 to-slate-950 text-white p-8 md:p-10 shadow-xl">
        <div class="absolute top-0 left-0 w-72 h-72 bg-indigo-500/20 rounded-full blur-3xl -translate-y-1/2 -translate-x-1/2"></div>
        <div class="absolute bottom-0 right-0 w-64 h-64 bg-cyan-500/15 rounded-full blur-3xl translate-y-1/2 translate-x-1/2"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
            <div>
                <p class="text-indigo-200 text-xs font-bold uppercase tracking-widest mb-2">{{ __('admin.hiring_academies_tagline') }}</p>
                <h1 class="text-2xl md:text-3xl font-black font-heading mb-2">{{ __('admin.hiring_academies_hero_title') }}</h1>
                <p class="text-slate-300 text-sm max-w-xl leading-relaxed">{{ __('admin.hiring_academies_hero_desc') }}</p>
            </div>
            <a href="{{ route('admin.hiring-academies.create') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3.5 rounded-2xl bg-white text-indigo-900 font-bold text-sm shadow-lg hover:shadow-xl hover:scale-[1.02] transition-all shrink-0">
                <i class="fas fa-plus"></i>
                {{ __('admin.hiring_academy_add') }}
            </a>
        </div>
        <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-4 mt-8">
            <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/10 p-4">
                <p class="text-indigo-200 text-xs font-semibold">{{ __('admin.hiring_stats_academies') }}</p>
                <p class="text-2xl font-black mt-1">{{ number_format($stats['total']) }}</p>
            </div>
            <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/10 p-4">
                <p class="text-indigo-200 text-xs font-semibold">{{ __('admin.hiring_stats_active') }}</p>
                <p class="text-2xl font-black mt-1 text-emerald-300">{{ number_format($stats['active']) }}</p>
            </div>
            <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/10 p-4">
                <p class="text-indigo-200 text-xs font-semibold">{{ __('admin.hiring_stats_leads') }}</p>
                <p class="text-2xl font-black mt-1 text-amber-300">{{ number_format($stats['lead']) }}</p>
            </div>
            <div class="rounded-2xl bg-white/10 backdrop-blur border border-white/10 p-4">
                <p class="text-indigo-200 text-xs font-semibold">{{ __('admin.hiring_stats_opportunities') }}</p>
                <p class="text-2xl font-black mt-1">{{ number_format($stats['opportunities']) }}</p>
            </div>
        </div>
    </div>

    <form method="get" class="flex flex-wrap gap-3 items-end">
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">{{ __('admin.search') }}</label>
            <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('admin.hiring_search_placeholder') }}" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
        </div>
        <div class="w-40">
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-400 mb-1">{{ __('admin.status') }}</label>
            <select name="status" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-800 dark:text-white">
                <option value="">{{ __('admin.all') }}</option>
                @foreach(\App\Models\HiringAcademy::statusLabels() as $k => $label)
                    <option value="{{ $k }}" @selected(request('status') === $k)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-600 text-white text-sm font-bold">{{ __('admin.filter') }}</button>
    </form>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs uppercase text-slate-500 dark:text-slate-400">
                    <tr>
                        <th class="px-4 py-3 text-right">{{ __('admin.hiring_academy_name') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.contact') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.city') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.status') }}</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.opportunities') }}</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($academies as $a)
                        <tr class="hover:bg-slate-50/80 dark:hover:bg-slate-900/30">
                            <td class="px-4 py-3">
                                <p class="font-bold text-slate-900 dark:text-white">{{ $a->name }}</p>
                                @if($a->legal_name)<p class="text-xs text-slate-500">{{ $a->legal_name }}</p>@endif
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300">
                                @if($a->contact_name)<span class="block">{{ $a->contact_name }}</span>@endif
                                @if($a->contact_email)<span class="text-xs">{{ $a->contact_email }}</span>@endif
                            </td>
                            <td class="px-4 py-3 text-slate-600">{{ $a->city ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-bold
                                    @if($a->status === 'active') bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-200
                                    @elseif($a->status === 'lead') bg-amber-100 text-amber-900 dark:bg-amber-900/30 dark:text-amber-100
                                    @else bg-slate-200 text-slate-700 dark:bg-slate-600 dark:text-slate-100 @endif">
                                    {{ $a->statusLabel() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-indigo-600 dark:text-indigo-400">{{ number_format($a->opportunities_count) }}</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap gap-2">
                                    <a href="{{ route('admin.hiring-academies.show', $a) }}" class="text-sky-600 dark:text-sky-400 font-semibold hover:underline">{{ __('admin.view') }}</a>
                                    <a href="{{ route('admin.hiring-academies.edit', $a) }}" class="text-slate-600 dark:text-slate-300 font-semibold hover:underline">{{ __('admin.edit') }}</a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-16 text-center text-slate-500">{{ __('admin.hiring_no_academies') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700">{{ $academies->links() }}</div>
    </div>
</div>
@endsection

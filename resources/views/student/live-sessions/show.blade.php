@extends('layouts.app')
@section('title', $liveSession->title)

@section('content')
<div class="max-w-3xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('student.live-sessions.index') }}" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors"><i class="fas fa-arrow-right"></i></a>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">{{ $liveSession->title }}</h1>
    </div>

    @if($liveSession->isLive())
    <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl p-6 text-center">
        <span class="w-4 h-4 bg-red-500 rounded-full animate-pulse inline-block mb-3"></span>
        <p class="text-lg font-bold text-red-700 dark:text-red-400 mb-3">البث مباشر الآن!</p>
        <form method="POST" action="{{ route('student.live-sessions.join', $liveSession) }}">
            @csrf
            <button class="px-8 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold shadow-lg shadow-red-500/25 transition-all text-lg">
                <i class="fas fa-video ml-2"></i> انضم الآن
            </button>
        </form>
    </div>
    @endif

    <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-4">
        <div class="grid sm:grid-cols-2 gap-4 text-sm">
            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                <i class="fas fa-chalkboard-teacher text-blue-500"></i>
                <div><p class="text-[11px] text-slate-400">المدرب</p><p class="font-semibold text-slate-800 dark:text-white">{{ $liveSession->instructor?->name }}</p></div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                <i class="fas fa-graduation-cap text-emerald-500"></i>
                <div><p class="text-[11px] text-slate-400">الكورس</p><p class="font-semibold text-slate-800 dark:text-white">{{ $liveSession->course?->title ?? 'جلسة عامة' }}</p></div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                <i class="fas fa-calendar text-amber-500"></i>
                <div><p class="text-[11px] text-slate-400">الموعد</p><p class="font-semibold text-slate-800 dark:text-white">{{ $liveSession->scheduled_at?->format('Y/m/d — H:i') }}</p></div>
            </div>
            <div class="flex items-center gap-3 p-3 bg-slate-50 dark:bg-slate-700/50 rounded-lg">
                <i class="fas fa-clock text-violet-500"></i>
                <div><p class="text-[11px] text-slate-400">الحالة</p>
                    <p class="font-semibold">
                        @if($liveSession->isLive()) <span class="text-red-600">مباشر</span>
                        @elseif($liveSession->isScheduled()) <span class="text-blue-600">{{ $liveSession->scheduled_at?->diffForHumans() }}</span>
                        @else <span class="text-slate-500">منتهية</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>

        @if($liveSession->description)
        <div class="pt-4 border-t border-slate-200 dark:border-slate-700">
            <h3 class="font-semibold text-slate-700 dark:text-slate-200 mb-2 text-sm">وصف الجلسة</h3>
            <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed">{{ $liveSession->description }}</p>
        </div>
        @endif
    </div>

    @if($liveSession->isScheduled())
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-5 text-center">
        <i class="fas fa-clock text-3xl text-blue-400 mb-2"></i>
        <p class="font-semibold text-blue-700 dark:text-blue-400">الجلسة ستبدأ {{ $liveSession->scheduled_at?->diffForHumans() }}</p>
        <p class="text-sm text-blue-600/70 mt-1">عند بدء البث ستظهر لك زر الانضمام</p>
    </div>
    @endif

    @if($liveSession->status === 'ended' && $liveSession->recordings && $liveSession->recordings->count() > 0)
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-5">
        <h3 class="font-bold text-emerald-800 dark:text-emerald-200 mb-3"><i class="fas fa-play-circle ml-1"></i> تسجيلات الجلسة</h3>
        <ul class="space-y-2">
            @foreach($liveSession->recordings as $rec)
            <li>
                <a href="{{ route('student.live-recordings.show', $rec) }}" class="flex items-center justify-between p-3 rounded-lg bg-white dark:bg-slate-800 border border-emerald-100 dark:border-emerald-800 hover:border-emerald-300 transition-colors">
                    <span class="font-medium text-slate-800 dark:text-white">{{ $rec->title ?? 'تسجيل #' . $rec->id }}</span>
                    <span class="text-xs text-slate-500">{{ $rec->duration_for_humans }}</span>
                </a>
            </li>
            @endforeach
        </ul>
        <a href="{{ route('student.live-recordings.index') }}" class="inline-block mt-3 text-sm font-semibold text-emerald-600 dark:text-emerald-400 hover:underline">كل التسجيلات</a>
    </div>
    @endif
</div>
@endsection

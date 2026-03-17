@extends('layouts.app')

@section('title', 'MuallimX Classroom — كلاس روم اكس')
@section('header', 'MuallimX Classroom')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
        <div class="bg-gradient-to-l from-blue-50 dark:from-blue-900/20 via-white dark:via-slate-800 to-white dark:to-slate-800 p-6 border-b border-slate-200 dark:border-slate-700">
            <div class="flex flex-wrap items-center gap-3 mb-2">
                <span class="w-12 h-12 rounded-xl bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 flex items-center justify-center">
                    <i class="fas fa-video text-xl"></i>
                </span>
                <div class="flex-1 min-w-0">
                    <h1 class="text-xl sm:text-2xl font-black text-slate-800 dark:text-white">MuallimX Classroom</h1>
                    <p class="text-sm text-slate-600 dark:text-slate-400 mt-0.5">افتح اجتماعاً وشارك الرابط أو الكود مع أي شخص — يدخل مباشرة بدون اشتراك في المنصة.</p>
                </div>
            </div>
        </div>
        <div class="p-6 space-y-6">
            {{-- بدء اجتماع جديد --}}
            <div class="rounded-xl border-2 border-dashed border-blue-200 dark:border-blue-800 bg-blue-50/50 dark:bg-blue-900/20 p-6 text-center">
                <form action="{{ route('student.classroom.start') }}" method="POST" class="inline-block">
                    @csrf
                    <input type="text" name="title" placeholder="عنوان الاجتماع (اختياري)" class="mb-4 w-full max-w-sm mx-auto block px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 text-slate-800 dark:text-white text-sm">
                    <button type="submit" class="inline-flex items-center gap-2 px-8 py-4 rounded-2xl bg-red-500 hover:bg-red-600 text-white font-bold text-lg shadow-lg shadow-red-500/30 transition-all">
                        <i class="fas fa-video"></i>
                        بدء اجتماع جديد
                    </button>
                </form>
            </div>

            {{-- تنزيل تطبيق سطح المكتب --}}
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 p-4 flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <span class="w-10 h-10 rounded-lg bg-slate-200 dark:bg-slate-700 flex items-center justify-center text-slate-600 dark:text-slate-300">
                        <i class="fas fa-desktop"></i>
                    </span>
                    <div>
                        <p class="font-semibold text-slate-800 dark:text-white">تطبيق سطح المكتب</p>
                        <p class="text-xs text-slate-500 dark:text-slate-400">ثبّت Jitsi Meet على جهازك لتجربة أفضل (مكالمات، تسجيل، مشاركة شاشة).</p>
                    </div>
                </div>
                <a href="https://github.com/jitsi/jitsi-meet-electron/releases" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-sm font-semibold transition-colors">
                    <i class="fas fa-download"></i>
                    تنزيل التطبيق
                </a>
            </div>

            {{-- اجتماعاتي الأخيرة --}}
            <div>
                <h2 class="text-base font-bold text-slate-800 dark:text-white mb-3">اجتماعاتي الأخيرة</h2>
                @if($meetings->isEmpty())
                    <p class="text-sm text-slate-500 dark:text-slate-400 py-4">لم تبدأ أي اجتماع بعد. اضغط "بدء اجتماع جديد" أعلاه.</p>
                @else
                    <ul class="space-y-2">
                        @foreach($meetings as $m)
                            @php $joinUrl = $joinBaseUrl . '/' . $m->code; @endphp
                            <li class="flex flex-wrap items-center justify-between gap-3 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-slate-800 dark:text-white truncate">{{ $m->title ?: 'اجتماع بدون عنوان' }}</p>
                                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                                        الكود: <span class="font-mono font-bold text-blue-600 dark:text-blue-400">{{ $m->code }}</span>
                                        · {{ $m->created_at->format('Y-m-d H:i') }}
                                        @if($m->isLive())
                                            <span class="text-red-600 dark:text-red-400 font-semibold">· مباشر الآن</span>
                                        @endif
                                    </p>
                                </div>
                                <div class="flex items-center gap-2">
                                    <button type="button" onclick="navigator.clipboard.writeText('{{ $joinUrl }}'); this.textContent='تم النسخ!'; setTimeout(()=>this.textContent='نسخ الرابط', 1500)" class="px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold hover:bg-slate-200 dark:hover:bg-slate-600 transition-colors">
                                        نسخ الرابط
                                    </button>
                                    @if($m->isLive())
                                        <a href="{{ route('student.classroom.room', $m) }}" class="px-3 py-1.5 rounded-lg bg-red-500 text-white text-xs font-semibold hover:bg-red-600 transition-colors">
                                            دخول الغرفة
                                        </a>
                                    @endif
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

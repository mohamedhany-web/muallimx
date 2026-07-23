@extends('layouts.app')

@section('title', 'Muallimx Classroom — إدارة الاجتماعات')
@section('header', 'إدارة اجتماعات Classroom')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
    @endif

    @if(session('info'))
        <div class="rounded-xl bg-sky-50 border border-sky-200 text-sky-800 px-4 py-3 text-sm font-medium">{{ session('info') }}</div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white">Muallimx Classroom</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">شارك رابطاً ثابتاً واحداً مع طلابك — يدخلون فقط عندما تبدأ اللايف، وكل جلسة تُحسب من باقتك.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if(!empty($activeLiveMeeting))
                    <a href="{{ route('student.classroom.room', $activeLiveMeeting) }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold shadow-lg">
                        <i class="fas fa-broadcast-tower"></i>
                        العودة للجلسة المباشرة
                    </a>
                @elseif(!empty($quotaExhausted))
                    <a href="{{ route('public.pricing') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold shadow-lg">
                        <i class="fas fa-tags"></i>
                        الرصيد خلص — ترقية الباقة
                    </a>
                @else
                    <form action="{{ route('student.classroom.start') }}" method="POST" class="inline">
                        @csrf
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold shadow-lg shadow-rose-500/30">
                            <i class="fas fa-play"></i>
                            بدء لايف الآن
                        </button>
                    </form>
                @endif
                <a href="{{ route('student.classroom.create') }}" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 text-white text-sm font-bold">
                    <i class="fas fa-plus"></i>
                    إنشاء / جدولة
                </a>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-gradient-to-l from-[#283593] to-[#1F2A7A] text-white shadow-lg p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-start gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-white/70 uppercase tracking-wider mb-1">رابطك الثابت للطلاب</p>
                <p class="text-sm text-white/85 mb-3">انسخه مرة واحدة وشاركه دائماً. الطلاب ينتظرون هنا حتى تبدأ اللايف — ثم يدخلون تلقائياً.</p>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" readonly value="{{ $fixedJoinUrl }}" id="fixed-join-url"
                           class="flex-1 min-w-0 rounded-xl bg-white/10 border border-white/20 px-3 py-2.5 text-sm font-mono text-white" dir="ltr">
                    <button type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('fixed-join-url').value); this.textContent='تم النسخ'; setTimeout(()=>this.textContent='نسخ الرابط',1500)"
                            class="px-4 py-2.5 rounded-xl bg-[#FB5607] hover:bg-[#e84d00] text-white text-sm font-bold shrink-0">
                        نسخ الرابط
                    </button>
                    <a href="{{ $fixedJoinUrl }}" target="_blank" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-sm font-bold shrink-0 text-center">فتح</a>
                </div>
                <form method="POST" action="{{ route('student.classroom.fixed-link') }}" class="mt-4 flex flex-col sm:flex-row gap-2 items-stretch sm:items-end">
                    @csrf
                    @method('PUT')
                    <div class="flex-1">
                        <label class="block text-[11px] font-semibold text-white/70 mb-1">تخصيص الجزء الأخير من الرابط</label>
                        <div class="flex items-center gap-1 rounded-xl bg-white/10 border border-white/20 px-3 py-2" dir="ltr">
                            <span class="text-xs text-white/50 whitespace-nowrap">/classroom/join/t/</span>
                            <input type="text" name="classroom_slug" value="{{ auth()->user()->classroom_slug }}"
                                   pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required maxlength="80"
                                   class="flex-1 bg-transparent border-0 text-sm text-white focus:ring-0 p-0">
                        </div>
                        @error('classroom_slug')<p class="text-rose-200 text-xs mt-1">{{ $message }}</p>@enderror
                    </div>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-white text-[#283593] text-sm font-bold shrink-0">حفظ</button>
                </form>
            </div>
            <div class="lg:w-56 rounded-xl bg-white/10 border border-white/15 p-4 text-center">
                <p class="text-[11px] text-white/70 mb-1">استهلاك هذا الشهر</p>
                <p class="text-2xl font-black">{{ number_format($usedMeetingsThisMonth) }} <span class="text-base font-bold text-white/70">/ {{ number_format($limits['classroom_meetings_per_month']) }}</span></p>
                <p class="text-xs mt-2 {{ $remainingMeetingsThisMonth > 0 ? 'text-emerald-300' : 'text-rose-300' }}">متبقي: {{ number_format($remainingMeetingsThisMonth) }} جلسة</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-3">أدوات الاجتماع</h2>
        <div class="flex flex-wrap items-center gap-3">
            <a href="{{ route('student.classroom.whiteboard') }}" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-800 dark:text-amber-200 text-sm font-semibold border border-amber-400/40 dark:border-amber-500/35 transition-colors">
                وايت بورد
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">إجمالي الاجتماعات</p>
            <p class="text-xl font-bold text-slate-800 dark:text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">اجتماعات مباشرة</p>
            <p class="text-xl font-bold text-rose-600 dark:text-rose-400">{{ number_format($stats['live']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">الحد الشهري / المستخدم</p>
            <p class="text-xl font-bold text-slate-800 dark:text-white">{{ number_format($usedMeetingsThisMonth) }} / {{ number_format($limits['classroom_meetings_per_month']) }}</p>
        </div>
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">المتبقي هذا الشهر</p>
            <p class="text-xl font-bold {{ $remainingMeetingsThisMonth > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400' }}">{{ number_format($remainingMeetingsThisMonth) }}</p>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
            <form method="GET" action="{{ route('student.classroom.index') }}" class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-500 dark:text-slate-400">فلتر الحالة:</span>
                @foreach(['all' => 'الكل', 'live' => 'مباشر', 'scheduled' => 'مجدول', 'ended' => 'منتهي'] as $k => $label)
                    <button type="submit" name="status" value="{{ $k }}" class="px-3 py-1.5 rounded-lg text-xs font-semibold {{ $status === $k ? 'bg-sky-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800/70">
                    <tr class="text-xs text-slate-600 dark:text-slate-300 uppercase">
                        <th class="px-4 py-3 text-right">الاجتماع</th>
                        <th class="px-4 py-3 text-right">الكود</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">الحد/الذروة</th>
                        <th class="px-4 py-3 text-right">الرابط</th>
                        <th class="px-4 py-3 text-right">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    @forelse($meetings as $m)
                        @php $joinUrl = $joinBaseUrl . '/' . $m->code; @endphp
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/20">
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white">{{ $m->title ?: 'اجتماع بدون عنوان' }}</p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    الإنشاء: {{ $m->created_at->format('Y-m-d H:i') }}
                                    @if($m->scheduled_for)
                                        · الموعد: {{ $m->scheduled_for->format('Y-m-d H:i') }}
                                    @endif
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-slate-700 dark:text-slate-300">{{ $m->code }}</td>
                            <td class="px-4 py-3">
                                @if($m->isLive())
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold bg-rose-100 text-rose-700">مباشر</span>
                                @elseif(!$m->started_at)
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700">مجدول</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">منتهي</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                {{ (int) ($m->max_participants ?? 25) }} / {{ (int) ($m->participants_peak ?? 0) }}
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <button type="button" onclick="navigator.clipboard.writeText('{{ $joinUrl }}'); this.textContent='تم النسخ'; setTimeout(()=>this.textContent='نسخ', 1000)" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold">نسخ</button>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('student.classroom.show', $m) }}" class="text-sky-600 hover:underline">عرض</a>
                                    @if(!$m->started_at && !$m->ended_at)
                                        <a href="{{ route('student.classroom.edit', $m) }}" class="text-amber-600 hover:underline">تعديل</a>
                                        <form action="{{ route('student.classroom.start-meeting', $m) }}" method="POST" class="inline">@csrf<button class="text-emerald-600 hover:underline">بدء</button></form>
                                    @elseif($m->isLive())
                                        <a href="{{ route('student.classroom.room', $m) }}" class="text-rose-600 hover:underline">دخول</a>
                                    @elseif($m->ended_at && $m->recording_download_url)
                                        <a href="{{ $m->recording_download_url }}" target="_blank" class="text-indigo-600 hover:underline">تحميل التسجيل</a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">لا توجد اجتماعات حتى الآن.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700">{{ $meetings->links() }}</div>
    </div>
</div>
@endsection

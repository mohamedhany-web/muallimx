@extends('layouts.employee')

@section('title', 'تفاصيل الجلسة')
@section('header', 'تفاصيل جلسة Classroom')

@section('content')
@php
    $fmt = fn ($dt) => $dt ? $dt->timezone(config('app.timezone'))->format('Y-m-d H:i') : '—';
    $durLabel = $durationMinutes === null ? '—' : (
        $durationMinutes >= 60
            ? floor($durationMinutes / 60).' س '.($durationMinutes % 60).' د'
            : $durationMinutes.' دقيقة'
    );
@endphp
<div class="space-y-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <p class="text-xs text-gray-500 font-semibold">المعلّم المضيف: {{ $student->name }}</p>
            <h2 class="text-lg font-bold text-gray-900">{{ $meeting->title ?: ('غرفة '.$meeting->code) }}</h2>
            <p class="text-sm text-gray-600 mt-1">كود الجلسة: <span class="font-mono font-bold">{{ $meeting->code }}</span></p>
        </div>
        <div class="flex flex-wrap gap-3">
            @if($meeting->isLive())
                <a href="{{ route('employee.academic-supervision.meeting.observe', $meeting) }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold hover:bg-emerald-500">مراقبة الآن</a>
            @endif
            <a href="{{ route('employee.academic-supervision.show', $student) }}" class="text-sm font-semibold text-teal-700 hover:underline self-center">← رجوع لتفاصيل المعلّم</a>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500 font-semibold mb-1">بدأت</p>
            <p class="text-sm font-bold text-gray-900 tabular-nums">{{ $fmt($meeting->started_at) }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500 font-semibold mb-1">انتهت</p>
            <p class="text-sm font-bold text-gray-900 tabular-nums">
                @if($meeting->isLive())
                    <span class="text-emerald-700">جارية الآن</span>
                @else
                    {{ $fmt($meeting->ended_at) }}
                @endif
            </p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500 font-semibold mb-1">المدة</p>
            <p class="text-sm font-bold text-gray-900">{{ $durLabel }}</p>
        </div>
        <div class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
            <p class="text-xs text-gray-500 font-semibold mb-1">الحضور</p>
            <p class="text-sm font-bold text-gray-900">{{ $participants->count() }} شخص
                @if($meeting->participants_peak)
                    <span class="text-gray-400 font-normal text-xs">(ذروة {{ $meeting->participants_peak }})</span>
                @endif
            </p>
            @if($meeting->isLive())
                <p class="text-xs text-emerald-700 mt-1">متصل الآن: {{ $presentNow->count() }}</p>
            @endif
        </div>
    </div>

    @if($meeting->isLive() && $presentNow->isNotEmpty())
    <div class="rounded-2xl border border-emerald-200 bg-emerald-50/50 shadow-sm overflow-hidden">
        <div class="px-5 py-3 border-b border-emerald-100">
            <h3 class="text-sm font-bold text-emerald-900">المتصلون الآن ({{ $presentNow->count() }})</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-emerald-50/80 text-emerald-900 font-semibold">
                    <tr>
                        <th class="text-right px-4 py-2">الاسم</th>
                        <th class="text-right px-4 py-2">دخل</th>
                        <th class="text-right px-4 py-2">آخر ظهور</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-emerald-100 bg-white">
                    @foreach($presentNow as $p)
                        <tr>
                            <td class="px-4 py-2 font-medium text-gray-900">{{ $p->display_name ?: 'بدون اسم' }}</td>
                            <td class="px-4 py-2 tabular-nums text-gray-700">{{ $fmt($p->joined_at) }}</td>
                            <td class="px-4 py-2 tabular-nums text-gray-500">{{ $fmt($p->last_seen_at) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <div class="rounded-2xl border border-gray-200 bg-white shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-100">
            <h3 class="text-base font-bold text-gray-900">سجل الحضور الكامل ({{ $participants->count() }})</h3>
            <p class="text-xs text-gray-500 mt-1">أسماء من دخلوا عبر رابط الانضمام (الضيوف). المعلّم المضيف يدير الجلسة من حسابه وقد لا يظهر في هذا السجل.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-gray-50 text-gray-600 font-semibold">
                    <tr>
                        <th class="text-right px-4 py-2">#</th>
                        <th class="text-right px-4 py-2">الاسم</th>
                        <th class="text-right px-4 py-2">دخل</th>
                        <th class="text-right px-4 py-2">خرج</th>
                        <th class="text-right px-4 py-2">المدة داخل الغرفة</th>
                        <th class="text-right px-4 py-2">الحالة</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($participants as $i => $p)
                        @php
                            $pEnd = $p->left_at ?: ($meeting->ended_at ?: ($meeting->isLive() ? now() : null));
                            $pMins = ($p->joined_at && $pEnd) ? max(0, $p->joined_at->diffInMinutes($pEnd)) : null;
                        @endphp
                        <tr>
                            <td class="px-4 py-2 text-gray-400">{{ $i + 1 }}</td>
                            <td class="px-4 py-2 font-medium text-gray-900">{{ $p->display_name ?: 'بدون اسم' }}</td>
                            <td class="px-4 py-2 tabular-nums">{{ $fmt($p->joined_at) }}</td>
                            <td class="px-4 py-2 tabular-nums">{{ $p->left_at ? $fmt($p->left_at) : ($meeting->isLive() ? '—' : $fmt($meeting->ended_at)) }}</td>
                            <td class="px-4 py-2">{{ $pMins === null ? '—' : $pMins.' د' }}</td>
                            <td class="px-4 py-2">
                                @if($p->left_at === null && $meeting->isLive())
                                    <span class="text-emerald-700 font-semibold text-xs">متصل</span>
                                @elseif($p->left_at)
                                    <span class="text-gray-500 text-xs">غادر</span>
                                @else
                                    <span class="text-gray-500 text-xs">انتهت الجلسة</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-8 text-center text-gray-500">لا يوجد سجل حضور لهذه الجلسة (لم يدخل أحد عبر رابط الانضمام).</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

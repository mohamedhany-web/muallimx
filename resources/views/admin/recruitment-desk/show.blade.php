@extends('layouts.admin')

@section('title', __('admin.recruitment_desk').' — '.$opportunity->title)
@section('header', __('admin.recruitment_desk'))

@section('content')
@php
    $statusLabels = \App\Models\RecruitmentTeacherPresentation::statusLabels();
@endphp
<div class="space-y-8">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-800 dark:text-emerald-200 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 text-sm font-medium">{{ session('error') }}</div>
    @endif

    {{-- Opportunity header --}}
    <div class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-900 via-slate-900 to-slate-950 text-white p-8 shadow-xl">
        <div class="absolute inset-0 opacity-30 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.06\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
        <div class="relative z-10 flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
            <div>
                <p class="text-violet-200 text-xs font-bold uppercase tracking-widest mb-2">{{ __('admin.recruitment_opportunity') }}</p>
                <h1 class="text-2xl md:text-3xl font-black font-heading mb-2">{{ $opportunity->title }}</h1>
                <p class="text-slate-300 text-sm">{{ $opportunity->organization_name }}</p>
                @if($opportunity->hiringAcademy)
                    <a href="{{ route('admin.hiring-academies.show', $opportunity->hiringAcademy) }}" class="inline-flex items-center gap-2 mt-3 text-sm text-cyan-300 hover:text-white font-semibold">
                        <i class="fas fa-link"></i> {{ __('admin.recruitment_linked_academy') }}: {{ $opportunity->hiringAcademy->name }}
                    </a>
                @endif
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.academy-opportunities.applications', $opportunity) }}" class="px-4 py-2 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-sm font-bold">طلبات المعلمين (تقديم ذاتي)</a>
                <a href="{{ route('admin.academy-opportunities.edit', $opportunity) }}" class="px-4 py-2 rounded-xl bg-white text-violet-900 text-sm font-bold">تعديل الفرصة</a>
            </div>
        </div>
        <p class="relative z-10 mt-6 text-sm text-slate-300 max-w-3xl leading-relaxed border-t border-white/10 pt-6">{{ __('admin.recruitment_desk_intro') }}</p>
        <div class="relative z-10 grid grid-cols-2 md:grid-cols-4 gap-3 mt-6">
            @foreach($statusLabels as $key => $label)
                <div class="rounded-xl bg-white/5 border border-white/10 px-3 py-2">
                    <p class="text-[10px] text-violet-200 font-bold uppercase">{{ $label }}</p>
                    <p class="text-xl font-black">{{ number_format($statusCounts[$key] ?? 0) }}</p>
                </div>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-5 gap-8">
        {{-- Add presentation --}}
        <div class="xl:col-span-2">
            <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg p-6 sticky top-24">
                <h2 class="text-lg font-black text-slate-900 dark:text-white mb-1 flex items-center gap-2">
                    <i class="fas fa-user-plus text-violet-500"></i> {{ __('admin.recruitment_add_teacher') }}
                </h2>
                <p class="text-xs text-slate-500 mb-5">معلم بملف معتمد فقط — المنصة تتحكم في النص المعروض للأكاديمية.</p>

                <form action="{{ route('admin.academy-opportunities.recruitment.presentations.store', $opportunity) }}" method="POST" class="space-y-4" id="new-presentation-form">
                    @csrf
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">{{ __('admin.recruitment_search_instructor') }}</label>
                        <div class="flex gap-2">
                            <input type="text" id="instructor-search-q" class="flex-1 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm" placeholder="اكتب حرفين على الأقل..." autocomplete="off">
                            <button type="button" id="instructor-search-btn" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-bold">بحث</button>
                        </div>
                        <div id="instructor-search-results" class="mt-2 space-y-1 max-h-48 overflow-y-auto hidden border border-slate-100 dark:border-slate-700 rounded-xl p-2 bg-slate-50 dark:bg-slate-900/50"></div>
                        <input type="hidden" name="user_id" id="selected-instructor-id" value="{{ old('user_id') }}" required>
                        <p id="selected-instructor-label" class="text-xs text-emerald-600 dark:text-emerald-400 mt-2 font-semibold min-h-[1rem]"></p>
                        @error('user_id')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">{{ __('admin.recruitment_curated_profile') }} <span class="text-rose-500">*</span></label>
                        <p class="text-[11px] text-slate-500 mb-2">{{ __('admin.recruitment_curated_hint') }}</p>
                        <textarea name="curated_public_profile" rows="8" required minlength="20" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm">{{ old('curated_public_profile') }}</textarea>
                        @error('curated_public_profile')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
                    </div>
                    <label class="flex items-center gap-2 text-sm text-slate-700 dark:text-slate-200 cursor-pointer">
                        <input type="hidden" name="hide_identity" value="0">
                        <input type="checkbox" name="hide_identity" value="1" class="rounded border-slate-300" {{ old('hide_identity') ? 'checked' : '' }}>
                        {{ __('admin.recruitment_hide_identity') }}
                    </label>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">{{ __('admin.recruitment_internal_notes') }}</label>
                        <textarea name="internal_notes" rows="3" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm">{{ old('internal_notes') }}</textarea>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-600 dark:text-slate-300 mb-1">{{ __('admin.recruitment_initial_status') }}</label>
                        <select name="status" class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-900 dark:text-white text-sm">
                            @foreach($statusLabels as $k => $lab)
                                @if(in_array($k, [\App\Models\RecruitmentTeacherPresentation::STATUS_DRAFT, \App\Models\RecruitmentTeacherPresentation::STATUS_SHARED], true))
                                    <option value="{{ $k }}" @selected(old('status', \App\Models\RecruitmentTeacherPresentation::STATUS_DRAFT) === $k)>{{ $lab }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-l from-violet-600 to-indigo-600 text-white font-black text-sm shadow-lg hover:shadow-xl transition-shadow">حفظ العرض</button>
                </form>
            </div>
        </div>

        {{-- List --}}
        <div class="xl:col-span-3 space-y-4">
            <h2 class="text-xl font-black text-slate-900 dark:text-white flex items-center gap-2">
                <i class="fas fa-clipboard-list text-indigo-500"></i> {{ __('admin.recruitment_presentations') }}
            </h2>

            @forelse($presentations as $p)
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-slate-100 dark:border-slate-700 flex flex-wrap items-center justify-between gap-3 bg-slate-50/80 dark:bg-slate-900/40">
                        <div>
                            <p class="font-bold text-slate-900 dark:text-white">{{ $p->user->name ?? '—' }} <span class="text-xs font-mono text-violet-600 dark:text-violet-400">({{ $p->display_code }})</span></p>
                            <p class="text-xs text-slate-500">{{ $p->user->email ?? '' }}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 dark:bg-indigo-900/40 dark:text-indigo-200">{{ $p->statusLabel() }}</span>
                    </div>
                    <div class="p-5 space-y-4">
                        <div class="flex flex-wrap gap-2">
                            <a href="{{ route('admin.academy-opportunities.recruitment.presentations.print', [$opportunity, $p]) }}" target="_blank" class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-slate-100 dark:bg-slate-700 text-slate-800 dark:text-slate-100 text-xs font-bold hover:bg-slate-200 dark:hover:bg-slate-600">
                                <i class="fas fa-print"></i> {{ __('admin.recruitment_print_packet') }}
                            </a>
                            @if($p->status === \App\Models\RecruitmentTeacherPresentation::STATUS_DRAFT)
                                <form action="{{ route('admin.academy-opportunities.recruitment.presentations.destroy', [$opportunity, $p]) }}" method="POST" onsubmit="return confirm('حذف هذا العرض؟');" class="inline">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs text-rose-600 font-bold hover:underline">حذف مسودة</button>
                                </form>
                            @endif
                        </div>

                        <form action="{{ route('admin.academy-opportunities.recruitment.presentations.update', [$opportunity, $p]) }}" method="POST" class="space-y-3">
                            @csrf @method('PUT')
                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('admin.recruitment_curated_profile') }}</label>
                                <textarea name="curated_public_profile" rows="6" required class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-900 text-sm">{{ old('curated_public_profile_'.$p->id, $p->curated_public_profile) }}</textarea>
                            </div>
                            <label class="flex items-center gap-2 text-xs text-slate-600 dark:text-slate-300">
                                <input type="hidden" name="hide_identity" value="0">
                                <input type="checkbox" name="hide_identity" value="1" {{ $p->hide_identity ? 'checked' : '' }}>
                                {{ __('admin.recruitment_hide_identity') }}
                            </label>
                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('admin.recruitment_internal_notes') }}</label>
                                <textarea name="internal_notes" rows="2" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-900 text-sm">{{ $p->internal_notes }}</textarea>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('admin.recruitment_academy_feedback') }}</label>
                                <textarea name="academy_feedback" rows="2" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-900 text-sm">{{ $p->academy_feedback }}</textarea>
                            </div>
                            <div>
                                <label class="text-xs font-bold text-slate-600 dark:text-slate-300">{{ __('admin.status') }}</label>
                                <select name="status" class="mt-1 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 dark:bg-slate-900 text-sm">
                                    @foreach($statusLabels as $k => $lab)
                                        <option value="{{ $k }}" @selected($p->status === $k)>{{ $lab }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-bold">تحديث العرض</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-600 p-12 text-center text-slate-500">
                    لا توجد عروض معلمين بعد. أضف أول عرض من اللوحة اليسرى.
                </div>
            @endforelse
        </div>
    </div>
</div>

@push('scripts')
<script>
(function(){
    const searchUrl = @json(route('admin.academy-opportunities.recruitment.instructors.search', $opportunity));
    const btn = document.getElementById('instructor-search-btn');
    const input = document.getElementById('instructor-search-q');
    const box = document.getElementById('instructor-search-results');
    const hid = document.getElementById('selected-instructor-id');
    const lbl = document.getElementById('selected-instructor-label');

    function runSearch() {
        const q = (input.value || '').trim();
        if (q.length < 2) { box.classList.add('hidden'); return; }
        fetch(searchUrl + '?q=' + encodeURIComponent(q), { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(r => r.json())
            .then(data => {
                box.innerHTML = '';
                if (!data.data || !data.data.length) {
                    box.innerHTML = '<p class="text-xs text-slate-500 p-2">لا نتائج</p>';
                    box.classList.remove('hidden');
                    return;
                }
                data.data.forEach(u => {
                    const row = document.createElement('button');
                    row.type = 'button';
                    row.className = 'w-full text-right px-3 py-2 rounded-lg hover:bg-white dark:hover:bg-slate-800 border border-transparent hover:border-slate-200 dark:hover:border-slate-600 text-sm transition';
                    row.innerHTML = '<span class="font-bold block">' + (u.name || '') + '</span><span class="text-xs text-slate-500">' + (u.email || '') + '</span>' + (u.headline ? '<span class="text-[11px] text-slate-400 block">' + u.headline + '</span>' : '');
                    row.addEventListener('click', function() {
                        hid.value = u.id;
                        lbl.textContent = 'المختار: ' + (u.name || '') + ' #' + u.id;
                        box.classList.add('hidden');
                    });
                    box.appendChild(row);
                });
                box.classList.remove('hidden');
            })
            .catch(() => { box.innerHTML = '<p class="text-xs text-rose-600 p-2">تعذر البحث</p>'; box.classList.remove('hidden'); });
    }
    if (btn) btn.addEventListener('click', runSearch);
    if (input) input.addEventListener('keydown', function(e) { if (e.key === 'Enter') { e.preventDefault(); runSearch(); } });
})();
</script>
@endpush
@endsection

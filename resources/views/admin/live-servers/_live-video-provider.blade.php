@php
    $liveVideoProvider = $liveVideoProvider ?? \App\Models\LiveSetting::getLiveVideoProvider();
    $livekitConfigured = $livekitConfigured ?? false;
@endphp
<div class="rounded-2xl border border-violet-200 dark:border-violet-800 bg-gradient-to-l from-violet-50 to-white dark:from-violet-950/40 dark:to-slate-800 p-5 space-y-4">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">
                <i class="fas fa-exchange-alt text-violet-500 ml-1"></i>
                محرك الفيديو للموقع بالكامل
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                اختيار واحد يطبّق على كل جلسات <strong>Classroom</strong> (معلم + طلاب). جلسات Live Sessions ما زالت على Jitsi حاليًا.
            </p>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold
            {{ ($liveVideoProvider ?? 'jitsi') === 'livekit'
                ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300'
                : 'bg-cyan-100 text-cyan-700 dark:bg-cyan-900/40 dark:text-cyan-300' }}">
            الحالي: {{ ($liveVideoProvider ?? 'jitsi') === 'livekit' ? 'LiveKit' : 'Jitsi' }}
        </span>
    </div>

    <form method="POST" action="{{ route('admin.live-servers.live-video-provider') }}" class="flex flex-wrap items-end gap-3">
        @csrf
        <div class="flex-1 min-w-[220px]">
            <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">الخدمة النشطة</label>
            <select name="live_video_provider" class="w-full rounded-xl border border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm px-3 py-2.5">
                <option value="jitsi" {{ ($liveVideoProvider ?? 'jitsi') === 'jitsi' ? 'selected' : '' }}>Jitsi Meet.Line</option>
                <option value="livekit" {{ ($liveVideoProvider ?? '') === 'livekit' ? 'selected' : '' }} {{ empty($livekitConfigured) ? 'disabled' : '' }}>
                    LiveKit{{ empty($livekitConfigured) ? ' (أضف LIVEKIT_* في .env أولاً)' : '' }}
                </option>
            </select>
        </div>
        <button type="submit" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-violet-600 hover:bg-violet-700 text-white text-sm font-semibold shadow-lg shadow-violet-500/20">
            <i class="fas fa-save"></i> حفظ وتطبيق
        </button>
    </form>

    @if(empty($livekitConfigured))
        <p class="text-xs text-amber-700 dark:text-amber-300">
            <i class="fas fa-exclamation-triangle ml-1"></i>
            مفاتيح LiveKit غير مضبوطة على سيرفر التطبيق — لن يظهر التفعيل حتى تُضاف في ملف .env.
        </p>
    @endif
</div>

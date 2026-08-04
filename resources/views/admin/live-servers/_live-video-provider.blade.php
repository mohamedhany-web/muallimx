@php
    $livekitConfigured = $livekitConfigured ?? app(\App\Services\LiveKitTokenService::class)->isConfigured();
@endphp
<div class="rounded-2xl border border-emerald-200 dark:border-emerald-800 bg-gradient-to-l from-emerald-50 to-white dark:from-emerald-950/40 dark:to-slate-800 p-5 space-y-3">
    <div class="flex flex-wrap items-start justify-between gap-3">
        <div>
            <h2 class="text-lg font-bold text-slate-800 dark:text-white">
                <i class="fas fa-bolt text-emerald-500 ml-1"></i>
                محرك الفيديو — LiveKit
            </h2>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">
                الموقع بالكامل على <strong>LiveKit</strong> (Classroom + Live Sessions + الإشراف). تم إيقاف مسار Jitsi من التطبيق.
            </p>
        </div>
        <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
            نشط: LiveKit
        </span>
    </div>

    @if(empty($livekitConfigured))
        <p class="text-xs text-amber-700 dark:text-amber-300">
            <i class="fas fa-exclamation-triangle ml-1"></i>
            مفاتيح LiveKit غير مضبوطة — أضف <code class="px-1">LIVEKIT_URL</code> و<code class="px-1">LIVEKIT_API_KEY</code> و<code class="px-1">LIVEKIT_API_SECRET</code> في .env ثم <code class="px-1">php artisan config:clear</code>.
        </p>
    @else
        <p class="text-xs text-emerald-800 dark:text-emerald-200">
            <i class="fas fa-check-circle ml-1"></i>
            المفاتيح مضبوطة. الإشارة: <code class="px-1">{{ config('services.livekit.url') }}</code>
        </p>
    @endif
</div>

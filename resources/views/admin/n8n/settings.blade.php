@extends('layouts.admin')

@section('title', 'إعداد تكامل n8n')

@section('content')
    <div class="space-y-6">
        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-sky-500 to-indigo-600 flex items-center justify-center text-white shadow-md">
                    <i class="fas fa-plug text-lg"></i>
                </div>
                <div>
                    <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
                        إعداد تكامل n8n مع Muallimx
                    </h1>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">
                        هنا تجد روابط الـ Endpoints والتوكن المطلوب لربط n8n بالمنصة وبخدمة تسجيلات البث (Cloudflare R2 عبر Laravel).
                    </p>
                </div>
            </div>
        </div>

        {{-- 1) إعداد القيم من الواجهة (Token + Webhook) --}}
        <section class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-key text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        1) إعداد قيم n8n من لوحة التحكم
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        يمكن للعميل هنا تحديد توكن الأمان ورابط الـ Webhook دون الحاجة لتعديل أي كود أو ملف إعدادات يدوياً.
                    </p>
                </div>
            </div>
            <div class="px-6 py-5">
                <form method="POST" action="{{ route('admin.n8n.settings.update') }}" class="space-y-4 max-w-xl">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            X-N8N-Token (توكن الأمان)
                        </label>
                        <input type="text"
                               name="n8n_token"
                               value="{{ old('n8n_token', $n8nToken) }}"
                               class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm"
                               placeholder="أدخل توكن سري يستخدم في Header X-N8N-Token">
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            سيتم استخدام هذه القيمة في جميع الاتصالات بين n8n والمنصة للتحقق من الصلاحيات.
                        </p>
                        @error('n8n_token')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">
                            رابط Webhook الخاص بـ n8n
                        </label>
                        <input type="text"
                               name="n8n_webhook"
                               value="{{ old('n8n_webhook', $n8nWebhook) }}"
                               class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm"
                               placeholder="https://your-n8n-host/webhook/live-session-report">
                        <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                            هذا هو الرابط الذي سيتم استدعاؤه من المنصة عند ضغط المعلم على زر \"تقرير ذكي للجلسة\".
                        </p>
                        @error('n8n_webhook')
                        <p class="text-xs text-rose-600 mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="pt-2">
                        <button type="submit"
                                class="inline-flex items-center gap-2 px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-sm font-semibold shadow-sm">
                            <i class="fas fa-save text-xs"></i>
                            <span>حفظ إعدادات n8n</span>
                        </button>
                    </div>
                </form>
            </div>
        </section>

        {{-- 2) Laravel → n8n (Webhook الذي تستدعيه المنصة) --}}
        <section class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                    <i class="fas fa-arrow-up-right-from-square text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        1) Webhook من Muallimx إلى n8n (إنشاء التقرير)
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        هذا هو الـ URL الذي يستدعيه Laravel داخل `generateAiReport` لتشغيل الـ Workflow في n8n.
                    </p>
                </div>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm text-slate-700 dark:text-slate-200">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">الرابط المستخدَم حالياً (من قاعدة البيانات):</p>
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 font-mono text-xs break-all">
                        {{ $n8nWebhook ?: 'لم يتم ضبطه بعد من النموذج أعلاه' }}
                    </div>
                </div>

                <div class="rounded-xl bg-slate-50 dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-700 px-4 py-3">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">
                        مثال Request يرسله Laravel إلى n8n:
                    </p>
                    <pre class="text-xs font-mono text-slate-700 dark:text-slate-200 whitespace-pre-wrap break-all">
POST {{ $n8nWebhook ?: 'https://your-n8n-host/webhook/live-session-report' }}
Headers:
  Accept: application/json
  X-N8N-Token: {{ $n8nToken ?: 'YOUR_SECURE_TOKEN' }}

Body (JSON):
{
  "report_id": 123,
  "live_session_id": 45,
  "instructor_id": 67,
  "live_recording_id": 89,
  "title": "تقرير الجلسة - عنوان المثال"
}</pre>
                    <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                        في n8n: استخدم عقدة <strong>Webhook</strong> لاستقبال هذا الطلب، ثم تابع معالجة الصوت والذكاء الاصطناعي.
                    </p>
                </div>
            </div>
        </section>

        {{-- 2) n8n → Laravel (Callback مع التقرير النهائي) --}}
        <section class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-sky-100 text-sky-600 flex items-center justify-center">
                    <i class="fas fa-arrow-down-left-from-square text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        2) Webhook من n8n إلى Muallimx (تحديث التقرير)
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        هذا هو الـ Endpoint الذي تستدعيه n8n بعد انتهاء التحليل والذكاء الاصطناعي لتحديث التقرير في المنصة.
                    </p>
                </div>
            </div>
            <div class="px-6 py-5 space-y-4 text-sm text-slate-700 dark:text-slate-200">
                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">رابط الـ Endpoint:</p>
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 font-mono text-xs break-all">
                        PATCH {{ $platformCallback }}
                    </div>
                    <p class="mt-1 text-[11px] text-slate-500 dark:text-slate-400">
                        استبدل <code>{report_id}</code> بـ <code>report_id</code> القادم من المنصة (القيمة التي أرسلناها لـ n8n في الخطوة الأولى).
                    </p>
                </div>

                <div>
                    <p class="text-xs font-semibold text-slate-500 mb-1">الهيدرات المطلوبة من n8n:</p>
                    <div class="rounded-lg bg-slate-50 dark:bg-slate-900 border border-slate-200 dark:border-slate-700 px-3 py-2 font-mono text-xs">
                        X-N8N-Token: {{ $n8nToken ?: 'ضع نفس القيمة في N8N_WEBHOOK_TOKEN في .env' }}
                    </div>
                    @unless($n8nToken)
                        <p class="mt-1 text-[11px] text-amber-700 dark:text-amber-300">
                            لم يتم ضبط <code>N8N_WEBHOOK_TOKEN</code> بعد في <code>.env</code>. اختر توكن سري وضع نفس القيمة في n8n (Header) وداخل ملف البيئة.
                        </p>
                    @endunless
                </div>

                <div class="rounded-xl bg-slate-50 dark:bg-slate-900 border border-dashed border-slate-200 dark:border-slate-700 px-4 py-3">
                    <p class="text-xs font-semibold text-slate-600 dark:text-slate-300 mb-2">
                        مثال Body (JSON) يرسله n8n إلى المنصة:
                    </p>
                    <pre class="text-xs font-mono text-slate-700 dark:text-slate-200 whitespace-pre-wrap break-all">@json($examplePayload, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)</pre>
                    <p class="mt-2 text-[11px] text-slate-500 dark:text-slate-400">
                        الحقول الأساسية:
                        <br>- <code>status</code>: <code>pending</code> أو <code>processing</code> أو <code>completed</code> أو <code>failed</code>
                        <br>- <code>summary</code>: نص التقرير النهائي من الذكاء الاصطناعي
                        <br>- <code>audio_path</code> + <code>storage_disk</code>: لو قمت بتعديل/نقل ملف الصوت وأردت تحديث مرجع التخزين
                        <br>- <code>n8n_execution_id</code>: اختياري لربط التقرير بتنفيذ معين في n8n.
                    </p>
                </div>
            </div>
        </section>

        {{-- 3) ملاحظات حول صلاحيات Cloudflare / R2 --}}
        <section class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg overflow-hidden">
            <div class="px-6 py-4 bg-slate-50 dark:bg-slate-900/40 border-b border-slate-200 dark:border-slate-700 flex items-center gap-3">
                <div class="w-9 h-9 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                    <i class="fas fa-cloud text-sm"></i>
                </div>
                <div>
                    <h2 class="text-sm font-semibold text-slate-800 dark:text-slate-100">
                        3) صلاحيات الوصول إلى Cloudflare R2
                    </h2>
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-0.5">
                        n8n لا يحتاج مفاتيح Cloudflare مباشرة؛ التكامل مع R2 يتم من خلال Laravel عبر الـ disk <code>live_recordings_r2</code>.
                    </p>
                </div>
            </div>
            <div class="px-6 py-5 space-y-3 text-sm text-slate-700 dark:text-slate-200">
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    عند تسجيل الجلسة، يقوم المتصفح أو نظام التسجيل برفع الصوت إلى R2 باستخدام الـ Endpoints التالية:
                </p>
                <ul class="list-disc list-inside text-xs text-slate-600 dark:text-slate-300 space-y-1">
                    <li><code>{{ route('instructor.live-sessions.audio.presign', ['liveSession' => 123]) }}</code> – للحصول على رابط رفع مباشر (Presigned URL)</li>
                    <li><code>{{ route('instructor.live-sessions.audio.complete', ['liveSession' => 123]) }}</code> – لتأكيد الرفع وتسجيله في جدول <code>live_recordings</code></li>
                </ul>
                <p class="text-xs text-slate-600 dark:text-slate-300">
                    n8n يتعامل فقط مع <strong>المسار</strong> و<strong>معرف التسجيل</strong> عبر API المنصة، ولا يحتاج إلى مفاتيح Cloudflare. مفاتيح R2 تظل مخزنة في
                    <code>.env</code> وفي إعدادات <code>config/filesystems.php</code> داخل Laravel.
                </p>
            </div>
        </section>
    </div>
@endsection


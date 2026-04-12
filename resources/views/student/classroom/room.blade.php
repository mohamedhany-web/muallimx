<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muallimx Classroom — {{ $meeting->title ?: $meeting->code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; }
        body { margin: 0; padding: 0; background: #0c1222; overflow: hidden; height: 100vh; }
        #jitsi-container {
            width: 100%;
            flex: 1;
            min-height: 0;
            background: #0f172a;
        }
        .room-body { display: flex; flex-direction: column; height: calc(100vh - 72px); }
        #jitsi-container iframe { width: 100% !important; height: 100% !important; border: none; }
        #meeting-stage { flex: 1; min-height: 0; position: relative; display: flex; flex-direction: column; width: 100%; }
        /* شعار Jitsi: الخوادم الحديثة لا تطبّق SHOW_JITSI_WATERMARK من الـ iframe API — تغطية زاوية بلا اعتراض النقرات */
        .jitsi-brand-mask {
            position: absolute;
            left: 0;
            top: 0;
            width: min(240px, 46vw);
            height: 96px;
            z-index: 11;
            pointer-events: none;
            background: #0f172a;
            border-bottom-right-radius: 12px;
            box-shadow: 0 0 0 1px rgba(15, 23, 42, 0.5);
        }
        #wb-popup { z-index: 140; }
        /* عدم خلط display مع Tailwind: عند الإغلاق لا يبقى flex يتعارض مع hidden */
        #wb-popup.is-open {
            display: flex;
            align-items: center;
            justify-content: center;
        }
        #pkg-features-dd-panel { z-index: 130; }
        .pkg-features-dd-panel-inner { box-shadow: 0 18px 40px rgba(0, 0, 0, 0.45), 0 0 0 1px rgba(34, 211, 238, 0.06); }
        #pkg-features-dd-btn:focus-visible {
            outline: none;
            box-shadow: 0 0 0 2px rgba(15, 23, 42, 0.9), 0 0 0 4px rgba(34, 211, 238, 0.35);
        }
        #wb-popup-stage { min-height: 50vh; }
        .classroom-excalidraw-host {
            position: absolute;
            inset: 0;
            width: 100%;
            height: 100%;
        }
        .classroom-excalidraw-host .excalidraw {
            --color-surface-lowest: #0f172a;
        }
        /* Muallimx Whiteboard: مكتبة + روابط وخدمات خارجية داخل واجهة اللوحة */
        .mx-muallimx-whiteboard .excalidraw .layer-ui__library,
        .mx-muallimx-whiteboard .excalidraw .layer-ui__library-message,
        .mx-muallimx-whiteboard .excalidraw .library-menu,
        .mx-muallimx-whiteboard .excalidraw .library-menu-dropdown-container,
        .mx-muallimx-whiteboard .excalidraw .library-menu-dropdown-container--in-heading,
        .mx-muallimx-whiteboard .excalidraw .library-menu-items-container,
        .mx-muallimx-whiteboard .excalidraw .library-menu-control-buttons,
        .mx-muallimx-whiteboard .excalidraw .library-menu-control-buttons--at-bottom,
        .mx-muallimx-whiteboard .excalidraw .library-menu-browse-button,
        .mx-muallimx-whiteboard .excalidraw .library-menu-items-private-library-container,
        .mx-muallimx-whiteboard .excalidraw .library-actions-counter,
        .mx-muallimx-whiteboard .excalidraw .single-library-item,
        .mx-muallimx-whiteboard .excalidraw .single-library-item-wrapper,
        .mx-muallimx-whiteboard .excalidraw .library-unit,
        .mx-muallimx-whiteboard .excalidraw .selected-library-items,
        .mx-muallimx-whiteboard .excalidraw [class*="publish-library"] {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        /* قائمة البرغر: روابط خارجية (GitHub / Discord / Twitter …) + عنوان المجموعة */
        .mx-muallimx-whiteboard .excalidraw .dropdown-menu a.dropdown-menu-item[href^="http://"],
        .mx-muallimx-whiteboard .excalidraw .dropdown-menu a.dropdown-menu-item[href^="https://"] {
            display: none !important;
            visibility: hidden !important;
            pointer-events: none !important;
        }
        .mx-muallimx-whiteboard .excalidraw .dropdown-menu .dropdown-menu-group:has(a.dropdown-menu-item[href^="http"]) {
            display: none !important;
        }
        .mx-muallimx-whiteboard .excalidraw .dropdown-menu .dropdown-menu-group:has(a.dropdown-menu-item[href^="https"]) {
            display: none !important;
        }
        /* مساعدة: شريط المدونة والتوثيق وGitHub */
        .mx-muallimx-whiteboard .excalidraw .HelpDialog__header {
            display: none !important;
        }
        /* تعاون مباشر (خوادم خارجية) */
        .mx-muallimx-whiteboard .excalidraw [data-testid="collab-button"] {
            display: none !important;
            pointer-events: none !important;
        }
        /* شاشة الترحيب: شعار Excalidraw وروابط ترحيب خارجية */
        .mx-muallimx-whiteboard .excalidraw .ExcalidrawLogo,
        .mx-muallimx-whiteboard .excalidraw .welcome-screen-center__logo {
            display: none !important;
            pointer-events: none !important;
        }
        .mx-muallimx-whiteboard .excalidraw a.welcome-screen-menu-item[href^="http://"],
        .mx-muallimx-whiteboard .excalidraw a.welcome-screen-menu-item[href^="https://"] {
            display: none !important;
            pointer-events: none !important;
        }
        /* حوارات محددة: روابط خارجية (بدون لمس نوافذ رابط الشكل على العناصر) */
        .mx-muallimx-whiteboard .excalidraw .ExportDialog a[href^="http://"],
        .mx-muallimx-whiteboard .excalidraw .ExportDialog a[href^="https://"],
        .mx-muallimx-whiteboard .excalidraw .ImageExportModal a[href^="http://"],
        .mx-muallimx-whiteboard .excalidraw .ImageExportModal a[href^="https://"],
        .mx-muallimx-whiteboard .excalidraw .OverwriteConfirm a[href^="http://"],
        .mx-muallimx-whiteboard .excalidraw .OverwriteConfirm a[href^="https://"],
        .mx-muallimx-whiteboard .excalidraw [class*="publish-library"] a[href^="http://"],
        .mx-muallimx-whiteboard .excalidraw [class*="publish-library"] a[href^="https://"],
        .mx-muallimx-whiteboard .excalidraw .HelpDialog a[href^="http://"],
        .mx-muallimx-whiteboard .excalidraw .HelpDialog a[href^="https://"] {
            display: none !important;
            pointer-events: none !important;
            visibility: hidden !important;
        }
        .classroom-excalidraw-loading {
            position: absolute;
            inset: 0;
            z-index: 5;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(15,23,42,0.75);
            color: #94a3b8;
            font-size: 14px;
        }
    </style>
</head>
<body class="bg-slate-950">
@php
    $academicObserverMode = !empty($academicObserverMode);
    $rp = ($useInstructorRoutes ?? false) ? 'instructor.' : 'student.';
    if ($academicObserverMode) {
        $roomExitUrl = $academicObserverExitUrl ?? route('employee.dashboard');
    } elseif (($useInstructorRoutes ?? false)) {
        $roomExitUrl = $meeting->consultation_request_id ? route('instructor.consultations.show', $meeting->consultation_request_id) : route('instructor.consultations.index');
    } else {
        $roomExitUrl = route('student.classroom.index');
    }
@endphp
    {{-- شريط Muallimx العلوي — تصميم المنصة فقط --}}
    <header class="h-[72px] bg-gradient-to-l from-slate-900 to-slate-800 border-b border-slate-700/50 flex items-center justify-between px-4 sm:px-6 shadow-lg">
        <div class="flex items-center gap-4">
            <a href="{{ $roomExitUrl }}" class="flex items-center gap-2 text-slate-300 hover:text-white transition-colors">
                <span class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    <i class="fas fa-video text-lg"></i>
                </span>
                <span class="font-bold text-white hidden sm:inline">Muallimx</span>
            </a>
            <span class="w-px h-6 bg-slate-600 hidden sm:block"></span>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse shadow-lg shadow-emerald-400/50"></span>
                <span class="text-white font-semibold text-sm">{{ $meeting->title ?: 'غرفة ' . $meeting->code }}</span>
                <span class="text-slate-400 text-xs px-2 py-0.5 rounded-md bg-slate-700/80 font-mono">{{ $meeting->code }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-slate-300 text-xs px-2 py-1 rounded-md bg-slate-700/80">
                الحد الأقصى للطلاب: {{ (int) ($meeting->max_participants ?? 25) }}
            </span>
            <span class="text-amber-200 text-xs px-2 py-1 rounded-md bg-amber-500/20 border border-amber-500/30" id="meeting-timer-chip">
                مدة الاجتماع: {{ (int) $effectiveDurationMinutes }} دقيقة (حد الباقة {{ (int) $maxDurationMinutes }})
            </span>
            <span class="hidden text-sky-200 text-xs px-2 py-1 rounded-md bg-sky-500/20 border border-sky-500/30" id="record-status-chip"></span>
            @unless($academicObserverMode)
            @if(!empty($subscriptionFeatureMenuItems))
            <div class="relative shrink-0" id="pkg-features-dd-wrap">
                <button type="button" id="pkg-features-dd-btn" class="inline-flex items-center gap-2 px-2.5 sm:px-3 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600/90 text-slate-100 text-sm font-medium transition-colors border border-slate-600 hover:border-cyan-500/35 max-w-[11rem] sm:max-w-none" aria-expanded="false" aria-haspopup="true" title="مزايا اشتراكك — تفتح في تاب جديد">
                    <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg bg-cyan-500/15 text-cyan-400 border border-cyan-500/20">
                        <i class="fas fa-layer-group text-sm"></i>
                    </span>
                    <span class="flex min-w-0 flex-1 flex-col items-stretch text-right leading-tight">
                        <span class="truncate font-semibold text-slate-100">مزايا الباقة</span>
                        @if(!empty($subscriptionPackageLabel))
                        <span class="truncate text-[10px] font-normal text-slate-400">{{ $subscriptionPackageLabel }}</span>
                        @else
                        <span class="text-[10px] font-normal text-slate-500">اشتراكك النشط</span>
                        @endif
                    </span>
                    <i class="fas fa-chevron-down text-[10px] text-slate-400 shrink-0 transition-transform duration-200" id="pkg-features-dd-chevron" aria-hidden="true"></i>
                </button>
                <div id="pkg-features-dd-panel" class="pkg-features-dd-panel-inner hidden absolute top-[calc(100%+0.5rem)] end-0 w-[min(100vw-2rem,19.5rem)] rounded-xl border border-slate-600 bg-slate-900/98 backdrop-blur-md overflow-hidden" role="menu">
                    <div class="px-3 py-2.5 border-b border-slate-700/90 bg-slate-800/70 flex items-start gap-2">
                        <span class="mt-0.5 flex h-6 w-6 shrink-0 items-center justify-center rounded-md bg-cyan-500/10 text-cyan-400">
                            <i class="fas fa-arrow-up-left-from-square text-[10px]"></i>
                        </span>
                        <div class="min-w-0">
                            <p class="text-xs font-semibold text-slate-200 m-0 leading-snug">روابط سريعة</p>
                            <p class="text-[11px] text-slate-500 m-0 mt-0.5 leading-relaxed">كل رابط يُفتح في نافذة جديدة دون إغلاق الاجتماع.</p>
                        </div>
                    </div>
                    <div class="max-h-[min(58vh,20rem)] overflow-y-auto py-1.5 px-1">
                        @foreach($subscriptionFeatureMenuItems as $item)
                        <a href="{{ $item['url'] }}" target="_blank" rel="noopener noreferrer" role="menuitem" class="group flex items-center gap-3 px-2.5 py-2 mx-0.5 rounded-lg text-slate-200 hover:bg-slate-700/70 transition-colors border border-transparent hover:border-slate-600/80">
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-lg {{ $item['icon_bg'] }} {{ $item['icon_text'] }} ring-1 ring-white/5 group-hover:ring-cyan-500/15 transition-[box-shadow]">
                                <i class="fas {{ $item['icon'] }} text-sm"></i>
                            </span>
                            <span class="min-w-0 flex-1 text-sm font-medium leading-snug text-right group-hover:text-white">{{ $item['label'] }}</span>
                            <i class="fas fa-arrow-up-left-from-square text-slate-500 group-hover:text-cyan-400/90 text-[11px] shrink-0 transition-colors"></i>
                        </a>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
            <button type="button" id="btn-wb-popup-open" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-amber-600/25 hover:bg-amber-600/35 text-amber-100 text-sm font-medium transition-colors border border-amber-500/40" title="فتح لوحة بيضاء كبيرة في نافذة منبثقة">
                <i class="fas fa-expand text-amber-300"></i>
                <span class="hidden sm:inline">لوحة كبيرة</span>
            </button>
            <button type="button" id="btn-record" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600" title="تسجيل المحاضرة">
                <i class="fas fa-circle-dot text-rose-400" id="record-icon"></i>
                <span id="record-label">تسجيل المحاضرة</span>
            </button>
            <button type="button" onclick="navigator.clipboard.writeText('{{ url('classroom/join/' . $meeting->code) }}'); this.innerHTML='<i class=\'fas fa-check ml-1\'></i> تم النسخ'; setTimeout(()=>{ this.innerHTML='<i class=\'fas fa-link ml-1\'></i> مشاركة الرابط'; }, 2000)" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600">
                <i class="fas fa-link ml-1"></i> مشاركة الرابط
            </button>
            <form method="POST" action="{{ route($rp.'classroom.end', $meeting) }}" class="inline" onsubmit="return confirm('إنهاء الاجتماع للجميع؟');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-colors shadow-lg shadow-rose-500/20">
                    <i class="fas fa-stop"></i> إنهاء الاجتماع
                </button>
            </form>
            @else
            <span class="text-amber-200 text-xs px-3 py-2 rounded-lg bg-amber-500/15 border border-amber-500/30 font-semibold">
                <i class="fas fa-eye ml-1"></i> وضع مراقبة (قراءة فقط)
            </span>
            @endunless
        </div>
    </header>

    <div class="room-body">
    {{-- بوابة إذن الميكروفون/الكاميرا قبل تحميل Jitsi (تحل مشكلة بعض الأجهزة التي لا تُظهر الطلب تلقائياً) --}}
    <div id="permission-gate" class="absolute inset-0 z-20 bg-slate-950/95 backdrop-blur-sm flex items-center justify-center p-4">
        <div class="w-full max-w-xl rounded-2xl border border-slate-700 bg-slate-900/95 shadow-2xl p-6 sm:p-7 text-center">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-cyan-500/15 text-cyan-400 flex items-center justify-center mb-4">
                <i class="fas fa-microphone-lines text-xl"></i>
            </div>
            <h2 class="text-xl sm:text-2xl font-bold text-white mb-2">السماح بالميكروفون والكاميرا</h2>
            <p class="text-slate-300 text-sm leading-7 mb-5">
                قبل دخول الاجتماع، اضغط على الزر التالي للسماح بالوصول إلى
                <strong class="text-white">الميكروفون والكاميرا</strong>.
                هذا يساعد في حل مشكلة الأجهزة التي لا يظهر فيها طلب الإذن تلقائياً.
            </p>
            <div class="flex flex-col sm:flex-row gap-3 justify-center">
                <button type="button" id="btn-request-media"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-white font-semibold transition-colors">
                    <i class="fas fa-shield-check"></i>
                    طلب الأذونات والدخول
                </button>
                <button type="button" id="btn-join-without-media"
                        class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-100 font-semibold transition-colors">
                    <i class="fas fa-arrow-left"></i>
                    دخول بدون تفعيل الأجهزة
                </button>
            </div>
            <p id="permission-help" class="mt-4 text-xs text-slate-400"></p>
        </div>
    </div>

    {{-- تنبيه: meet.jit.si للاختبار فقط — يُقطع بعد 5 دقائق --}}
    @if(!empty($isDemoJitsi))
    <div class="bg-amber-500/15 border-b border-amber-500/40 px-4 py-2 flex items-center justify-between gap-3 text-amber-800 dark:text-amber-200 text-sm flex-shrink-0">
        <span class="flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>للاختبار فقط:</strong> استخدام meet.jit.si يُقطع المكالمة بعد 5 دقائق. للإنتاج: من لوحة الإدارة → <strong>جلسات البث المباشر والمعلمين → إعدادات نظام اللايف</strong> غيّر نطاق Jitsi إلى سيرفر خاص أو Jitsi as a Service.
        </span>
        <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-800 p-1" aria-label="إغلاق"><i class="fas fa-times"></i></button>
    </div>
    @endif

    {{-- منطقة الاجتماع --}}
    <div id="meeting-stage" class="flex-1 min-h-0 relative w-full">
        <main id="jitsi-container" class="flex-1 min-h-0 relative w-full" role="application" aria-label="غرفة الاجتماع">
            <div id="jitsi-loading" class="flex flex-col items-center justify-center h-full text-slate-400 text-sm gap-3">
                <i class="fas fa-spinner fa-spin text-2xl text-cyan-400"></i>
                <span>جاري تحميل غرفة الاجتماع…</span>
            </div>
            <div id="jitsi-error" class="hidden flex-col items-center justify-center h-full p-6 text-center max-w-lg mx-auto" style="display: none;">
                <i class="fas fa-exclamation-triangle text-amber-500 text-4xl mb-3"></i>
                <p class="font-bold text-slate-200 mb-2">لا يمكن تحميل غرفة الاجتماع</p>
                <p class="text-slate-400 text-sm mb-3">المتصفح لم يستطع الاتصال بـ <strong class="text-slate-300">{{ $jitsiDomain }}</strong>.</p>
                <ul class="text-right text-slate-400 text-sm mb-4 list-none space-y-1">
                    <li>• النطاق يجب أن يكون <strong class="text-slate-300">النطاق الذي يعمل عليه Jitsi Meet</strong> (مثلاً <code class="bg-slate-700 px-1 rounded">meet.muallimx.com</code> وليس بالضرورة الموقع الرئيسي).</li>
                    <li>• جرّب فتح <a href="https://{{ $jitsiDomain }}/external_api.js" target="_blank" rel="noopener" class="text-cyan-400 hover:underline">هذا الرابط</a> في تاب جديد — إن لم يُحمّل، فـ Jitsi غير مُثبت على هذا النطاق أو النطاق غير متاح من جهازك.</li>
                    <li>• إن كان Jitsi على نطاق فرعي (مثل meet.muallimx.com)، غيّر النطاق من: <strong>لوحة الإدارة → سيرفرات البث</strong> ثم «استخدام كنطاق افتراضي» للسيرفر الصحيح.</li>
                </ul>
                <a href="https://{{ $jitsiDomain }}/{{ $meeting->room_name }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-white font-semibold transition-colors">
                    <i class="fas fa-external-link-alt"></i> فتح الغرفة في نافذة جديدة
                </a>
            </div>
        </main>
        <div class="jitsi-brand-mask" aria-hidden="true"></div>
    </div>
    </div>

    {{-- لوحة بيضاء منبثقة بشاشة كبيرة --}}
    <div id="wb-popup" class="hidden fixed inset-0 p-2 sm:p-4" inert aria-hidden="true" role="dialog" aria-labelledby="wb-popup-title" aria-modal="true">
        <div id="wb-popup-backdrop" class="absolute inset-0 bg-slate-950/80 backdrop-blur-sm cursor-pointer" aria-hidden="true"></div>
        <div id="wb-popup-panel" class="relative z-[141] flex flex-col w-full max-w-[min(1680px,99vw)] h-[min(92vh,calc(100dvh-1rem))] rounded-2xl border border-slate-600 bg-slate-900 shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-700 bg-slate-800/95 shrink-0">
                <h2 id="wb-popup-title" class="text-base font-bold text-white m-0 flex items-center gap-2">
                    <i class="fas fa-chalkboard text-amber-400"></i>
                    لوحة بيضاء — شاشة كبيرة
                </h2>
                <div class="flex items-center gap-2">
                    <button type="button" id="btn-wb-popup-fullscreen" class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-medium border border-slate-600" title="ملء الشاشة (اخرج بـ Esc)">
                        <i class="fas fa-expand"></i>
                        <span class="hidden sm:inline">ملء الشاشة</span>
                    </button>
                    <button type="button" id="wb-popup-close" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-700 hover:bg-rose-600/80 text-white text-lg leading-none border border-slate-600" aria-label="إغلاق اللوحة">&times;</button>
                </div>
            </div>
            <div id="wb-popup-stage" class="relative flex-1 min-h-0 bg-[#121212]">
                <div id="classroom-excalidraw-root" class="classroom-excalidraw-host mx-muallimx-whiteboard" data-view-only="0" data-lang="ar"></div>
                <div id="classroom-excalidraw-loading" class="classroom-excalidraw-loading">جاري تحميل Muallimx Whiteboard…</div>
            </div>
            <div id="wb-popup-toolbar" class="flex flex-wrap items-center justify-center gap-2 px-4 py-2.5 border-t border-slate-700 bg-slate-800/95 shrink-0">
                <span class="text-slate-400 text-[11px] leading-relaxed text-center max-w-3xl">
                    <strong class="text-slate-200">Muallimx Whiteboard</strong> — أدوات رسم كاملة (أشكال، نص، تصدير PNG/SVG من القائمة). الرسم محلي على جهازك فقط.
                </span>
            </div>
        </div>
    </div>

    @include('partials.jitsi-iframe-media-allow')
    @php
        $mxBp = rtrim((string) request()->getBasePath(), '/');
        $mxP = $mxBp !== '' ? $mxBp : '';
        $mxExBases = array_values(array_unique(array_filter([
            $mxP . '/mx-vendor/excalidraw/',
            '/mx-vendor/excalidraw/',
            $mxP . '/vendor/excalidraw/',
            '/vendor/excalidraw/',
        ])));
    @endphp
    {{-- Muallimx Whiteboard: تحميل ديناميكي + أكثر من مسار (Laravel ثم ملفات public المباشرة) --}}
    <script>
        (function() {
            var jitsiDomain = '{{ $jitsiDomain }}';
            var roomName = '{{ $meeting->room_name }}';
            var userName = {!! json_encode($jitsiDisplayName ?? $user->name) !!};
            var userEmail = {!! json_encode($user->email ?? '') !!};
            var container = document.getElementById('jitsi-container');
            var loadingEl = document.getElementById('jitsi-loading');
            var errorEl = document.getElementById('jitsi-error');
            var meetingEndsAt = {!! json_encode(optional($meetingEndsAt)->toIso8601String()) !!};
            var timerChip = document.getElementById('meeting-timer-chip');
            var recordBtn = document.getElementById('btn-record');
            var recordIcon = document.getElementById('record-icon');
            var recordLabel = document.getElementById('record-label');
            var recordStatusChip = document.getElementById('record-status-chip');
            var uploadRecordingUrl = '{{ route($rp . 'classroom.recording.upload', $meeting) }}';
            var presignRecordingUrl = '{{ route($rp . 'classroom.recording.presign', $meeting) }}';
            var completeRecordingUrl = '{{ route($rp . 'classroom.recording.complete', $meeting) }}';
            var presignAudioUrl = '{{ route($rp . 'classroom.recording-audio.presign', $meeting) }}';
            var uploadAudioUrl = '{{ route($rp . 'classroom.recording-audio.upload', $meeting) }}';
            var completeAudioUrl = '{{ route($rp . 'classroom.recording-audio.complete', $meeting) }}';
            var csrfToken = '{{ csrf_token() }}';
            var roomExitUrl = {!! json_encode($roomExitUrl) !!};
            var permissionGate = document.getElementById('permission-gate');
            var permissionHelp = document.getElementById('permission-help');
            var requestMediaBtn = document.getElementById('btn-request-media');
            var joinWithoutMediaBtn = document.getElementById('btn-join-without-media');
            var api = null;
            var hasJoinedConference = false;
            var isRecording = false;
            var mediaRecorder = null;
            var recordedChunks = [];
            var audioRecorder = null;
            var recordedAudioChunks = [];
            var recordingStartedAt = null;
            var activeRecordingStream = null;
            var micStream = null;
            var audioOnlyStream = null;

            var wbCanvas = null;
            var wbCtx = null;

            var wbPopup = document.getElementById('wb-popup');
            var wbPopupStage = document.getElementById('wb-popup-stage');
            var wbPopupPanel = document.getElementById('wb-popup-panel');
            var excRoot = document.getElementById('classroom-excalidraw-root');
            var excLoading = document.getElementById('classroom-excalidraw-loading');
            var excReactRoot = null;
            var excMounted = false;
            var excMountPromise = null;
            var wbPopupClosing = false;
            var mxExcalidrawBases = {!! json_encode($mxExBases) !!};
            var excVendorPromise = null;

            function excShowLoading(on) {
                if (excLoading) excLoading.style.display = on ? 'flex' : 'none';
            }

            function nudgeClassroomExLayout() {
                window.dispatchEvent(new Event('resize'));
                if (window.requestAnimationFrame) {
                    requestAnimationFrame(function() { window.dispatchEvent(new Event('resize')); });
                }
            }

            function mxAbsAssetUrl(basePath) {
                var b = String(basePath || '').replace(/\/?$/, '/');
                if (b.indexOf('http') === 0) return b;
                if (b.charAt(0) !== '/') b = '/' + b;
                return window.location.origin + b;
            }

            function loadScriptSequential(url) {
                return new Promise(function(resolve, reject) {
                    var s = document.createElement('script');
                    s.src = url;
                    s.async = false;
                    s.onerror = function() {
                        s.onerror = s.onload = null;
                        reject(new Error('فشل تحميل: ' + url));
                    };
                    s.onload = function() {
                        s.onerror = s.onload = null;
                        resolve();
                    };
                    (document.head || document.documentElement).appendChild(s);
                });
            }

            function getExcalidrawLib() {
                if (typeof ExcalidrawLib !== 'undefined') return ExcalidrawLib;
                if (typeof window.ExcalidrawLib !== 'undefined') return window.ExcalidrawLib;
                return null;
            }

            function ensureExcalidrawVendorLoaded() {
                if (window.React && window.ReactDOM && getExcalidrawLib()) {
                    return Promise.resolve();
                }
                if (excVendorPromise) return excVendorPromise;
                var bases = Array.isArray(mxExcalidrawBases) ? mxExcalidrawBases : [];
                if (!bases.length) bases = ['/mx-vendor/excalidraw/', '/vendor/excalidraw/'];

                function loadFromBase(basePath) {
                    var root = String(basePath || '').replace(/\/?$/, '/');
                    window.EXCALIDRAW_ASSET_PATH = root + 'dist/';
                    var prefix = mxAbsAssetUrl(root);
                    return loadScriptSequential(prefix + 'react.production.min.js')
                        .then(function() { return loadScriptSequential(prefix + 'react-dom.production.min.js'); })
                        .then(function() { return loadScriptSequential(prefix + 'dist/excalidraw.production.min.js'); })
                        .then(function() {
                            if (!window.React || !window.ReactDOM || !getExcalidrawLib()) {
                                throw new Error('تعذّر تعريف مكوّنات Muallimx Whiteboard بعد التحميل');
                            }
                        });
                }

                function tryNext(i) {
                    if (i >= bases.length) {
                        return Promise.reject(new Error('فشل كل مسارات التحميل. تأكد من وجود public/vendor/excalidraw ومسار Laravel /mx-vendor/excalidraw'));
                    }
                    return loadFromBase(bases[i]).catch(function() { return tryNext(i + 1); });
                }

                excVendorPromise = tryNext(0).catch(function(e) {
                    excVendorPromise = null;
                    throw e;
                });
                return excVendorPromise;
            }

            function mountClassroomExcalidrawOnce() {
                if (excMounted) return Promise.resolve();
                if (excMountPromise) return excMountPromise;
                if (!excRoot) return Promise.reject(new Error('no excalidraw root'));
                excShowLoading(true);

                function failMount(err) {
                    console.error('[Muallimx Whiteboard]', err);
                    excMountPromise = null;
                    excShowLoading(false);
                    if (excLoading) {
                        var detail = (err && err.message) ? String(err.message) : '';
                        if (detail.length > 240) detail = detail.slice(0, 237) + '…';
                        excLoading.textContent = 'تعذّر تهيئة Muallimx Whiteboard.' + (detail ? (' ' + detail) : '') + ' — Network: جرّب ‎/mx-vendor/excalidraw/react.production.min.js‎ أو ‎/vendor/excalidraw/…‎ برمز 200.';
                        excLoading.style.display = 'flex';
                    }
                }

                excMountPromise = ensureExcalidrawVendorLoaded()
                    .then(function() {
                        return new Promise(function(resolve, reject) {
                            var deadline = Date.now() + 5000;
                            function tryMount() {
                                var Lib = getExcalidrawLib();
                                var ReactMod = window.React;
                                var ReactDOM = window.ReactDOM;
                                if (!Lib || !ReactMod || !ReactDOM) {
                                    failMount(new Error('المكتبات غير متاحة بعد التحميل'));
                                    reject(new Error('missing after load'));
                                    return;
                                }
                                var rect = excRoot.getBoundingClientRect();
                                if (rect.width < 8 || rect.height < 8) {
                                    if (Date.now() > deadline) {
                                        failMount(new Error('الحاوية بلا أبعاد كافية بعد فتح النافذة.'));
                                        reject(new Error('container size'));
                                        return;
                                    }
                                    requestAnimationFrame(tryMount);
                                    return;
                                }
                                try {
                                    var Excalidraw = Lib.Excalidraw;
                                    var createRoot = ReactDOM.createRoot;
                                    // مكوّن اللوحة مُصدَّر كـ React.memo — typeof يكون "object" وليس "function"
                                    if (Excalidraw == null || (typeof Excalidraw !== 'function' && typeof Excalidraw !== 'object')) {
                                        throw new Error('حزمة Muallimx Whiteboard غير صالحة (مكوّن اللوحة).');
                                    }
                                    if (typeof createRoot !== 'function') {
                                        throw new Error('ReactDOM.createRoot غير متاح (تحقق من react-dom 18).');
                                    }
                                    var viewOnly = excRoot.getAttribute('data-view-only') === '1';
                                    var lang = excRoot.getAttribute('data-lang') || '';
                                    var props = {
                                        viewModeEnabled: viewOnly,
                                        excalidrawAPI: function(api) {
                                            window.__mxClassroomExcalidrawAPI = api;
                                        }
                                    };
                                    if (lang.indexOf('ar') === 0) props.langCode = 'ar-SA';
                                    excReactRoot = createRoot(excRoot);
                                    excReactRoot.render(ReactMod.createElement(Excalidraw, props));
                                    excMounted = true;
                                    excShowLoading(false);
                                    nudgeClassroomExLayout();
                                    resolve();
                                } catch (err) {
                                    failMount(err);
                                    reject(err);
                                }
                            }
                            requestAnimationFrame(tryMount);
                        });
                    })
                    .catch(function(err) {
                        failMount(err);
                        excMountPromise = null;
                        return Promise.reject(err);
                    });

                return excMountPromise;
            }

            function mergeExcalidrawToMain(done) {
                var api = window.__mxClassroomExcalidrawAPI;
                if (!api || !wbCanvas || !wbCtx) {
                    if (done) done();
                    return;
                }
                function runExport() {
                    var Lib = getExcalidrawLib();
                    var exportToBlob = Lib && Lib.exportToBlob;
                    if (typeof exportToBlob !== 'function') {
                        if (done) done();
                        return;
                    }
                    exportToBlob({
                        elements: api.getSceneElements(),
                        appState: api.getAppState(),
                        files: api.getFiles ? api.getFiles() : null,
                        mimeType: 'image/png',
                        exportWithDarkMode: false,
                        exportBackground: true
                    }).then(function(blob) {
                        if (!blob) {
                            if (done) done();
                            return;
                        }
                        var url = URL.createObjectURL(blob);
                        var img = new Image();
                        img.onload = function() {
                            resizeWbCanvas();
                            wbCtx.save();
                            wbCtx.setTransform(1, 0, 0, 1, 0, 0);
                            wbCtx.clearRect(0, 0, wbCanvas.width, wbCanvas.height);
                            wbCtx.drawImage(img, 0, 0, wbCanvas.width, wbCanvas.height);
                            wbCtx.restore();
                            var dpr = window.devicePixelRatio || 1;
                            wbCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
                            wbCtx.lineCap = 'round';
                            wbCtx.lineJoin = 'round';
                            URL.revokeObjectURL(url);
                            if (done) done();
                        };
                        img.onerror = function() {
                            URL.revokeObjectURL(url);
                            if (done) done();
                        };
                        img.src = url;
                    }).catch(function() {
                        if (done) done();
                    });
                }
                if (getExcalidrawLib()) {
                    runExport();
                } else {
                    ensureExcalidrawVendorLoaded().then(runExport).catch(function() { if (done) done(); });
                }
            }

            function openWbPopup() {
                if (!wbPopup) return;
                wbPopup.removeAttribute('inert');
                wbPopup.classList.remove('hidden');
                wbPopup.classList.add('is-open');
                wbPopup.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
                mountClassroomExcalidrawOnce().then(function() {
                    setTimeout(nudgeClassroomExLayout, 80);
                    setTimeout(nudgeClassroomExLayout, 400);
                }).catch(function() {});
            }

            function closeWbPopup() {
                if (wbPopupClosing) return;
                if (!wbPopup || wbPopup.classList.contains('hidden')) return;
                wbPopupClosing = true;

                function detachWhiteboardFromMeetingUi() {
                    var ae = document.activeElement;
                    if (ae && typeof ae.blur === 'function' && wbPopup.contains(ae)) {
                        ae.blur();
                    }
                    try {
                        var sel = window.getSelection && window.getSelection();
                        if (sel && typeof sel.removeAllRanges === 'function') {
                            sel.removeAllRanges();
                        }
                    } catch (eSel) {}

                    wbPopup.classList.add('hidden');
                    wbPopup.classList.remove('is-open');
                    wbPopup.setAttribute('aria-hidden', 'true');
                    wbPopup.setAttribute('inert', '');
                    document.body.style.overflow = '';

                    var reopenBtn = document.getElementById('btn-wb-popup-open');
                    if (reopenBtn && typeof reopenBtn.focus === 'function') {
                        try {
                            reopenBtn.focus({ preventScroll: true });
                        } catch (eF) {
                            try { reopenBtn.focus(); } catch (eF2) {}
                        }
                    }

                    wbPopupClosing = false;
                }

                function runClosePipeline() {
                    mergeExcalidrawToMain(function() {
                        detachWhiteboardFromMeetingUi();
                    });
                }

                var fsEl = document.fullscreenElement;
                if (fsEl && wbPopup.contains(fsEl)) {
                    var p = document.exitFullscreen && document.exitFullscreen();
                    if (p && typeof p.then === 'function') {
                        p.then(runClosePipeline).catch(runClosePipeline);
                    } else {
                        runClosePipeline();
                    }
                } else {
                    runClosePipeline();
                }
            }

            function resizeWbCanvas() {}

            if (wbPopup) {
                var wbOpenPopupBtn = document.getElementById('btn-wb-popup-open');
                if (wbOpenPopupBtn) wbOpenPopupBtn.addEventListener('click', openWbPopup);
                var wbClosePopupBtn = document.getElementById('wb-popup-close');
                if (wbClosePopupBtn) wbClosePopupBtn.addEventListener('click', closeWbPopup);
                var wbBackdropEl = document.getElementById('wb-popup-backdrop');
                if (wbBackdropEl) wbBackdropEl.addEventListener('click', closeWbPopup);
                var wbFsBtn = document.getElementById('btn-wb-popup-fullscreen');
                if (wbFsBtn && wbPopupPanel) {
                    wbFsBtn.addEventListener('click', function() {
                        if (!document.fullscreenElement) {
                            wbPopupPanel.requestFullscreen().catch(function() {});
                        } else {
                            try { document.exitFullscreen(); } catch (ex) {}
                        }
                    });
                }

                document.addEventListener('keydown', function(ev) {
                    if (ev.key === 'Escape' && wbPopup && !wbPopup.classList.contains('hidden')) {
                        closeWbPopup();
                    }
                });

                if (wbPopupStage && typeof ResizeObserver !== 'undefined') {
                    new ResizeObserver(function() {
                        if (wbPopup && !wbPopup.classList.contains('hidden')) {
                            nudgeClassroomExLayout();
                        }
                    }).observe(wbPopupStage);
                }
            }

            function showError() {
                if (loadingEl) loadingEl.classList.add('hidden');
                if (errorEl) { errorEl.style.display = 'flex'; errorEl.classList.add('flex'); }
            }

            function setRecordButtonState(recording) {
                if (!recordBtn) return;
                if (recording) {
                    recordBtn.classList.remove('bg-slate-700/80');
                    recordBtn.classList.add('bg-rose-600/90', 'text-white');
                    if (recordIcon) recordIcon.className = 'fas fa-stop';
                    if (recordLabel) recordLabel.textContent = 'إيقاف التسجيل';
                } else {
                    recordBtn.classList.add('bg-slate-700/80');
                    recordBtn.classList.remove('bg-rose-600/90', 'text-white');
                    if (recordIcon) recordIcon.className = 'fas fa-circle-dot text-rose-400';
                    if (recordLabel) recordLabel.textContent = 'تسجيل المحاضرة';
                }
            }

            function setRecordButtonBusy(isBusy) {
                if (!recordBtn) return;
                recordBtn.disabled = isBusy;
                recordBtn.classList.toggle('opacity-70', isBusy);
                recordBtn.classList.toggle('cursor-not-allowed', isBusy);
            }

            function setRecordStatus(message, isError) {
                if (!recordStatusChip) return;
                if (!message) {
                    recordStatusChip.classList.add('hidden');
                    recordStatusChip.textContent = '';
                    return;
                }
                recordStatusChip.classList.remove('hidden');
                recordStatusChip.textContent = message;
                recordStatusChip.classList.remove('bg-sky-500/20', 'border-sky-500/30', 'text-sky-200', 'bg-rose-600/20', 'border-rose-500/30', 'text-rose-200');
                if (isError) {
                    recordStatusChip.classList.add('bg-rose-600/20', 'border-rose-500/30', 'text-rose-200');
                } else {
                    recordStatusChip.classList.add('bg-sky-500/20', 'border-sky-500/30', 'text-sky-200');
                }
            }

            function stopCaptureTracks(stream) {
                if (!stream) return;
                try {
                    stream.getTracks().forEach(function(track) { track.stop(); });
                } catch (err) {
                    console.warn('Track stop warning:', err);
                }
            }

            function pickMediaRecorderOptions() {
                var candidates = [
                    'video/webm;codecs=vp9,opus',
                    'video/webm;codecs=vp8,opus',
                    'video/webm'
                ];
                var mimeType = '';
                for (var i = 0; i < candidates.length; i++) {
                    if (MediaRecorder.isTypeSupported(candidates[i])) {
                        mimeType = candidates[i];
                        break;
                    }
                }
                var opts = { videoBitsPerSecond: 1500000, audioBitsPerSecond: 96000 };
                if (mimeType) {
                    opts.mimeType = mimeType;
                }
                return opts;
            }

            function pickAudioRecorderOptions() {
                var candidates = [
                    'audio/webm;codecs=opus',
                    'audio/webm',
                    'audio/ogg;codecs=opus',
                    'audio/ogg',
                    'audio/mp4'
                ];
                for (var i = 0; i < candidates.length; i++) {
                    if (MediaRecorder.isTypeSupported(candidates[i])) {
                        return { mimeType: candidates[i], audioBitsPerSecond: 96000 };
                    }
                }
                return { audioBitsPerSecond: 96000 };
            }

            function formatBytes(n) {
                var x = Number(n) || 0;
                if (x < 1024) {
                    return x + ' B';
                }
                if (x < 1048576) {
                    return (x / 1024).toFixed(1) + ' KB';
                }
                if (x < 1073741824) {
                    return (x / 1048576).toFixed(1) + ' MB';
                }
                return (x / 1073741824).toFixed(2) + ' GB';
            }

            async function buildRecordingStream() {
                var displayStream = await navigator.mediaDevices.getDisplayMedia({
                    video: true,
                    audio: true
                });

                var tracks = [];
                displayStream.getVideoTracks().forEach(function(track) { tracks.push(track); });
                displayStream.getAudioTracks().forEach(function(track) { tracks.push(track); });

                // نضيف الميكروفون أيضاً لأن بعض المتصفحات لا تُرجع صوت النظام/التبويب دائماً.
                try {
                    micStream = await navigator.mediaDevices.getUserMedia({ audio: true, video: false });
                    micStream.getAudioTracks().forEach(function(track) { tracks.push(track); });
                } catch (micErr) {
                    console.warn('Microphone stream unavailable:', micErr);
                }

                return new MediaStream(tracks);
            }

            function uploadRecordedBlobViaFormData(blob, durationSeconds) {
                return new Promise(function(resolve, reject) {
                    var xhr = new XMLHttpRequest();
                    xhr.open('POST', uploadRecordingUrl, true);
                    xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                    xhr.timeout = 0;

                    xhr.upload.onprogress = function(e) {
                        if (e.lengthComputable && e.total > 0) {
                            var p = Math.min(100, Math.round((e.loaded / e.total) * 100));
                            setRecordStatus('جاري الرفع عبر الخادم ' + p + '% — لا تغلق الصفحة.', false);
                        } else if (e.loaded) {
                            setRecordStatus('جاري الرفع عبر الخادم... ' + formatBytes(e.loaded) + ' — لا تغلق الصفحة.', false);
                        }
                    };

                    xhr.onerror = function() {
                        reject(new Error('فشل الاتصال أثناء الرفع. تحقق من الإنترنت وحاول مرة أخرى.'));
                    };
                    xhr.ontimeout = function() {
                        reject(new Error('انتهت مهلة الرفع. جرّب شبكة أسرع أو قسّم المحاضرة إلى جزئين.'));
                    };

                    xhr.onload = function() {
                        var raw = xhr.responseText || '';
                        var data = {};
                        try {
                            data = raw ? JSON.parse(raw) : {};
                        } catch (parseErr) {
                            if (xhr.status === 413) {
                                reject(new Error('حجم الملف يتجاوز حد السيرفر (PHP/nginx). عادةً يُرفع التسجيل مباشرة إلى Cloudflare R2؛ إن ظهرت هذه الرسالة فتحقق من CORS لدلوكل R2 أو زِد upload_max_filesize و post_max_size و client_max_body_size من الاستضافة.'));
                                return;
                            }
                            reject(new Error('استجابة غير متوقعة من الخادم (رمز ' + xhr.status + ').'));
                            return;
                        }

                        if (xhr.status >= 200 && xhr.status < 300) {
                            resolve({ ok: true, data: data });
                            return;
                        }

                        var msg = (data && data.message) ? data.message : 'فشل رفع التسجيل.';
                        if (data && data.errors) {
                            var firstKey = Object.keys(data.errors)[0];
                            if (firstKey && data.errors[firstKey] && data.errors[firstKey][0]) {
                                msg = data.errors[firstKey][0];
                            }
                        }
                        if (xhr.status === 413) {
                            msg = 'حجم الملف كبير جداً لإعدادات السيرفر الحالية.';
                        }
                        reject(new Error(msg));
                    };

                    var formData = new FormData();
                    formData.append('recording', blob, 'meeting-recording.webm');
                    formData.append('duration_seconds', String(durationSeconds || 0));
                    xhr.send(formData);
                });
            }

            async function uploadRecordedBlob(blob, durationSeconds) {
                var putSucceeded = false;
                try {
                    var presignRes = await fetch(presignRecordingUrl, {
                        method: 'POST',
                        credentials: 'same-origin',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json',
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            content_type: blob.type || 'video/webm',
                        }),
                    });
                    var presignData = {};
                    try {
                        presignData = await presignRes.json();
                    } catch (je) {
                        presignData = {};
                    }

                    if (presignRes.ok && presignData.direct_upload === false) {
                        return uploadRecordedBlobViaFormData(blob, durationSeconds);
                    }

                    if (presignRes.ok && presignData.upload_url && presignData.upload_token && presignData.content_type) {
                        setRecordStatus('جاري الرفع مباشرة إلى Cloudflare (' + formatBytes(blob.size) + ')... لا تغلق الصفحة.', false);
                        var putRes = await fetch(presignData.upload_url, {
                            method: 'PUT',
                            headers: { 'Content-Type': presignData.content_type },
                            body: blob,
                        });
                        if (!putRes.ok) {
                            var putErr = 'فشل الرفع إلى التخزين السحابي (HTTP ' + putRes.status + '). من Cloudflare R2 → إعدادات الـ bucket → CORS: اسمح بـ PUT و Origin لنطاق موقعك.';
                            throw new Error(putErr);
                        }
                        putSucceeded = true;

                        var completeRes = await fetch(completeRecordingUrl, {
                            method: 'POST',
                            credentials: 'same-origin',
                            headers: {
                                'X-CSRF-TOKEN': csrfToken,
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                upload_token: presignData.upload_token,
                                duration_seconds: durationSeconds || 0,
                            }),
                        });
                        var completeData = {};
                        try {
                            completeData = await completeRes.json();
                        } catch (je2) {
                            completeData = {};
                        }
                        if (!completeRes.ok) {
                            var cmsg = (completeData && completeData.message) ? completeData.message : 'فشل ربط الملف بالاجتماع بعد الرفع.';
                            throw new Error(cmsg);
                        }
                        return { ok: true, data: completeData };
                    }
                } catch (err) {
                    if (putSucceeded) {
                        throw err;
                    }
                    console.warn('Direct R2 upload path skipped or failed, using server upload:', err);
                }
                return uploadRecordedBlobViaFormData(blob, durationSeconds);
            }

            async function uploadAudioBlob(blob, durationSeconds) {
                function uploadAudioBlobViaFormData() {
                    return new Promise(function(resolve, reject) {
                        var formData = new FormData();
                        formData.append('recording_audio', blob, 'meeting-audio.webm');
                        formData.append('duration_seconds', String(durationSeconds || 0));

                        var xhr = new XMLHttpRequest();
                        xhr.open('POST', uploadAudioUrl, true);
                        xhr.setRequestHeader('X-CSRF-TOKEN', csrfToken);
                        xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                        xhr.onload = function() {
                            var data = {};
                            try { data = xhr.responseText ? JSON.parse(xhr.responseText) : {}; } catch (e) {}
                            if (xhr.status >= 200 && xhr.status < 300) {
                                resolve({ ok: true, data: data });
                                return;
                            }
                            reject(new Error((data && data.message) ? data.message : 'فشل رفع ملف الصوت عبر السيرفر.'));
                        };
                        xhr.onerror = function() {
                            reject(new Error('فشل الاتصال أثناء رفع ملف الصوت.'));
                        };
                        xhr.send(formData);
                    });
                }

                var presignRes = await fetch(presignAudioUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        content_type: blob.type || 'audio/webm',
                    }),
                });
                var presignData = {};
                try {
                    presignData = await presignRes.json();
                } catch (je) {
                    presignData = {};
                }

                if (presignRes.ok && presignData.direct_upload === false) {
                    return uploadAudioBlobViaFormData();
                }

                if (!presignRes.ok || !presignData.upload_url || !presignData.upload_token || !presignData.content_type) {
                    return uploadAudioBlobViaFormData();
                }

                var putRes = await fetch(presignData.upload_url, {
                    method: 'PUT',
                    headers: { 'Content-Type': presignData.content_type },
                    body: blob,
                });
                if (!putRes.ok) {
                    throw new Error('فشل رفع ملف الصوت إلى Cloudflare (HTTP ' + putRes.status + ').');
                }

                var completeRes = await fetch(completeAudioUrl, {
                    method: 'POST',
                    credentials: 'same-origin',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        upload_token: presignData.upload_token,
                        duration_seconds: durationSeconds || 0,
                    }),
                });
                var completeData = {};
                try {
                    completeData = await completeRes.json();
                } catch (je2) {
                    completeData = {};
                }
                if (!completeRes.ok) {
                    throw new Error((completeData && completeData.message) ? completeData.message : 'فشل حفظ ملف الصوت.');
                }
                return { ok: true, data: completeData };
            }

            async function startBrowserRecording() {
                if (!navigator.mediaDevices || typeof navigator.mediaDevices.getDisplayMedia !== 'function') {
                    alert('هذا المتصفح لا يدعم تسجيل الشاشة من المتصفح.');
                    return;
                }
                if (!hasJoinedConference) {
                    alert('ادخل الغرفة أولاً ثم أعد محاولة التسجيل.');
                    return;
                }

                setRecordButtonBusy(true);

                try {
                    activeRecordingStream = await buildRecordingStream();
                } catch (err) {
                    setRecordButtonBusy(false);
                    alert('تم إلغاء مشاركة الشاشة أو لم يتم منح الصلاحية.');
                    return;
                }

                var recorderOpts = pickMediaRecorderOptions();
                try {
                    mediaRecorder = new MediaRecorder(activeRecordingStream, recorderOpts);
                } catch (err) {
                    try {
                        var fallback = recorderOpts.mimeType ? { mimeType: recorderOpts.mimeType } : {};
                        mediaRecorder = new MediaRecorder(activeRecordingStream, fallback);
                    } catch (err2) {
                        stopCaptureTracks(activeRecordingStream);
                        activeRecordingStream = null;
                        setRecordButtonBusy(false);
                        alert('تعذر بدء التسجيل. جرّب متصفح Chrome أو Edge بإصدار حديث.');
                        return;
                    }
                }

                recordedChunks = [];
                recordedAudioChunks = [];
                recordingStartedAt = Date.now();

                mediaRecorder.addEventListener('dataavailable', function(event) {
                    if (event.data && event.data.size > 0) {
                        recordedChunks.push(event.data);
                    }
                });

                // تسجيل صوتي منفصل (Mic-only) بالتوازي مع الفيديو.
                try {
                    if (micStream && micStream.getAudioTracks && micStream.getAudioTracks().length > 0) {
                        var audioTracks = micStream.getAudioTracks().map(function(t) { return t.clone(); });
                        audioOnlyStream = new MediaStream(audioTracks);
                        audioRecorder = new MediaRecorder(audioOnlyStream, pickAudioRecorderOptions());
                        audioRecorder.addEventListener('dataavailable', function(event) {
                            if (event.data && event.data.size > 0) {
                                recordedAudioChunks.push(event.data);
                            }
                        });
                        audioRecorder.start(4000);
                    }
                } catch (audioErr) {
                    console.warn('Audio-only recorder failed:', audioErr);
                    audioRecorder = null;
                    stopCaptureTracks(audioOnlyStream);
                    audioOnlyStream = null;
                }

                mediaRecorder.addEventListener('stop', async function onRecorderStopped() {
                    isRecording = false;
                    setRecordButtonState(false);

                    stopCaptureTracks(activeRecordingStream);
                    activeRecordingStream = null;
                    stopCaptureTracks(micStream);
                    micStream = null;

                    var durationSeconds = recordingStartedAt ? Math.max(1, Math.round((Date.now() - recordingStartedAt) / 1000)) : 0;
                    var outType = (mediaRecorder && mediaRecorder.mimeType) ? mediaRecorder.mimeType : 'video/webm';
                    var blob = new Blob(recordedChunks, { type: outType });
                    var audioType = (audioRecorder && audioRecorder.mimeType) ? audioRecorder.mimeType : 'audio/webm';
                    var audioBlob = new Blob(recordedAudioChunks, { type: audioType });

                    if (!blob.size) {
                        setRecordButtonBusy(false);
                        setRecordStatus('لا يوجد محتوى في التسجيل.', true);
                        alert('لا يوجد محتوى في التسجيل. إن استمر ذلك بعد محاضرة طويلة، جرّب Chrome/Edge ولا تغلق تبويب مشاركة الشاشة قبل الضغط على «إيقاف التسجيل».');
                        recordedChunks = [];
                        return;
                    }

                    try {
                        setRecordStatus('جاري رفع التسجيل (' + formatBytes(blob.size) + ')... لا تغلق الصفحة.', false);
                        await uploadRecordedBlob(blob, durationSeconds);
                        if (audioBlob && audioBlob.size > 0) {
                            setRecordStatus('تم رفع الفيديو. جاري رفع ملف الصوت المنفصل...', false);
                            await uploadAudioBlob(audioBlob, durationSeconds);
                        }
                        setRecordStatus('تم رفع التسجيل بنجاح.', false);
                        alert('تم رفع الفيديو وملف الصوت بنجاح إلى Cloudflare. ستظهر روابط التحميل في صفحة الاجتماع بعد إنهائه.');
                    } catch (uploadError) {
                        console.error('Upload recording error:', uploadError);
                        setRecordStatus('فشل رفع التسجيل. أعد المحاولة.', true);
                        alert(uploadError && uploadError.message ? uploadError.message : 'فشل رفع التسجيل.');
                    } finally {
                        recordedChunks = [];
                        recordedAudioChunks = [];
                        audioRecorder = null;
                        stopCaptureTracks(audioOnlyStream);
                        audioOnlyStream = null;
                        setRecordButtonBusy(false);
                    }
                });

                activeRecordingStream.getVideoTracks().forEach(function(track) {
                    track.addEventListener('ended', function() {
                        if (mediaRecorder && mediaRecorder.state === 'recording') {
                            setRecordButtonBusy(true);
                            setRecordStatus('انتهت مشاركة الشاشة. جاري إنهاء الملف والرفع...', false);
                            try {
                                if (typeof mediaRecorder.requestData === 'function') {
                                    mediaRecorder.requestData();
                                }
                            } catch (e) {}
                            mediaRecorder.stop();
                        }
                    });
                });

                /* كل 4 ثوانٍ: أقل عدّد مقاطع من timeslice=1s يقلل الضغط على الذاكرة في التسجيلات الطويلة */
                mediaRecorder.start(4000);
                isRecording = true;
                setRecordButtonState(true);
                setRecordStatus('جاري التسجيل الآن...', false);
                setRecordButtonBusy(false);
            }

            function stopBrowserRecording() {
                if (!mediaRecorder || mediaRecorder.state !== 'recording') {
                    return;
                }
                setRecordButtonBusy(true);
                setRecordStatus('جاري إنهاء التسجيل ودمج المقاطع... لا تغلق مشاركة الشاشة بعد.', false);
                try {
                    if (typeof mediaRecorder.requestData === 'function') {
                        mediaRecorder.requestData();
                    }
                    if (audioRecorder && audioRecorder.state === 'recording' && typeof audioRecorder.requestData === 'function') {
                        audioRecorder.requestData();
                    }
                } catch (reqErr) {
                    console.warn('requestData:', reqErr);
                }
                if (audioRecorder && audioRecorder.state === 'recording') {
                    audioRecorder.stop();
                }
                mediaRecorder.stop();
                isRecording = false;
                setRecordButtonState(false);
            }

            async function handleRecordButtonClick() {
                if (isRecording) {
                    stopBrowserRecording();
                    return;
                }

                await startBrowserRecording();
            }

            if (recordBtn) {
                recordBtn.addEventListener('click', handleRecordButtonClick);
            }

            function hidePermissionGate() {
                if (!permissionGate) return;
                permissionGate.classList.add('hidden');
            }

            function setPermissionHelp(message, isError) {
                if (!permissionHelp) return;
                permissionHelp.textContent = message || '';
                permissionHelp.className = 'mt-4 text-xs ' + (isError ? 'text-rose-300' : 'text-slate-400');
            }

            function mapMediaErrorToArabic(err) {
                var code = err && err.name ? String(err.name) : '';
                if (code === 'NotAllowedError' || code === 'PermissionDeniedError') {
                    return 'المتصفح رفض الإذن. افتح رمز القفل بجانب الرابط ثم اسمح للكاميرا والميكروفون.';
                }
                if (code === 'NotFoundError' || code === 'DevicesNotFoundError') {
                    return 'لا توجد كاميرا أو ميكروفون متصل بالجهاز.';
                }
                if (code === 'NotReadableError' || code === 'TrackStartError') {
                    return 'تعذر تشغيل الكاميرا/الميكروفون (قد يكون مستخدمًا في تطبيق آخر مثل Zoom/Teams).';
                }
                if (code === 'OverconstrainedError' || code === 'ConstraintNotSatisfiedError') {
                    return 'إعدادات الجهاز غير متوافقة مع طلب الفيديو/الصوت. جرّب إغلاق الكاميرا من التطبيقات الأخرى.';
                }
                if (code === 'SecurityError') {
                    return 'حظر أمني من المتصفح. تأكد من فتح الموقع عبر HTTPS أو localhost.';
                }
                return 'تعذر الوصول للكاميرا أو الميكروفون. جرّب مرة أخرى أو تحقق من إعدادات المتصفح.';
            }

            async function requestMediaPermission() {
                if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                    setPermissionHelp('المتصفح لا يدعم طلب الأذونات تلقائياً. سنحاول الدخول مباشرة.', true);
                    hidePermissionGate();
                    initJitsi();
                    return;
                }

                // على غير HTTPS قد يفشل طلب الإذن (عدا localhost)
                if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                    setPermissionHelp('المتصفح يشترط HTTPS لطلب إذن الميكروفون والكاميرا.', true);
                    hidePermissionGate();
                    initJitsi();
                    return;
                }

                try {
                    if (requestMediaBtn) {
                        requestMediaBtn.disabled = true;
                        requestMediaBtn.classList.add('opacity-70', 'cursor-not-allowed');
                    }
                    setPermissionHelp('جاري طلب الإذن من المتصفح...', false);

                    var stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
                    stream.getTracks().forEach(function(track) { track.stop(); });

                    setPermissionHelp('تم منح الإذن بنجاح. جاري فتح الاجتماع...', false);
                    hidePermissionGate();
                    initJitsi();
                } catch (err) {
                    console.error('Media permission error:', err);
                    setPermissionHelp(mapMediaErrorToArabic(err), true);
                    if (requestMediaBtn) {
                        requestMediaBtn.disabled = false;
                        requestMediaBtn.classList.remove('opacity-70', 'cursor-not-allowed');
                    }
                }
            }

            function initJitsi() {
                if (typeof JitsiMeetExternalAPI === 'undefined') {
                    showError();
                    return;
                }
                try {
                    container.innerHTML = '';
                    if (typeof muallimxEnsureJitsiIframeMediaAllow === 'function') {
                        muallimxEnsureJitsiIframeMediaAllow(container);
                    }
                    var options = {
                        roomName: roomName,
                        parentNode: container,
                        width: '100%',
                        height: '100%',
                        userInfo: { displayName: userName, email: userEmail },
                        configOverwrite: {
                            prejoinConfig: { enabled: false },
                            prejoinPageEnabled: false,
                            enableLobby: false,
                            requireDisplayName: false,
                            enableWelcomePage: false,
                            disableDeepLinking: true,
                            enableRecording: true,
                            startWithAudioMuted: true,
                            startWithVideoMuted: true,
                            disableAudioLevels: false,
                            enableNoisyMicDetection: false,
                        },
                        interfaceConfigOverwrite: {
                            APP_NAME: 'Muallimx Classroom',
                            NATIVE_APP_NAME: 'Muallimx Classroom',
                            PROVIDER_NAME: 'Muallimx',
                            TOOLBAR_BUTTONS: [
                                'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                                'fodeviceselection', 'hangup', 'chat', 'recording',
                                'raisehand', 'invite', 'tileview', 'videoquality', 'filmstrip',
                                'whiteboard'
                            ],
                            SHOW_JITSI_WATERMARK: false,
                            SHOW_WATERMARK_FOR_GUESTS: false,
                            SHOW_BRAND_WATERMARK: false,
                            SHOW_POWERED_BY: false,
                            MOBILE_APP_PROMO: false,
                            DEFAULT_BACKGROUND: '#0f172a',
                            DISABLE_JOIN_LEAVE_NOTIFICATIONS: false,
                            FILM_STRIP_MAX_HEIGHT: 100,
                        }
                    };
                    api = new JitsiMeetExternalAPI(jitsiDomain, options);

                    if (loadingEl) loadingEl.classList.add('hidden');
                    setTimeout(resizeWbCanvas, 300);
                    setTimeout(resizeWbCanvas, 1200);

                    api.addEventListener('readyToClose', function() {
                        if (isRecording) {
                            stopBrowserRecording();
                        }
                        window.location.href = roomExitUrl;
                    });

                    api.addEventListener('videoConferenceJoined', function() {
                        hasJoinedConference = true;
                        resizeWbCanvas();
                        setTimeout(resizeWbCanvas, 500);
                    });
                } catch (e) {
                    console.error('Jitsi init error:', e);
                    showError();
                }
            }

            function tickMeetingTimer() {
                if (!meetingEndsAt || !timerChip) return;
                var end = new Date(meetingEndsAt).getTime();
                var nowTs = Date.now();
                var diff = end - nowTs;
                if (diff <= 0) {
                    timerChip.textContent = 'انتهت المدة المسموح بها';
                    timerChip.classList.remove('bg-amber-500/20', 'border-amber-500/30', 'text-amber-200');
                    timerChip.classList.add('bg-rose-600/20', 'border-rose-500/30', 'text-rose-200');
                    window.location.href = roomExitUrl;
                    return;
                }
                var mins = Math.floor(diff / 60000);
                var secs = Math.floor((diff % 60000) / 1000);
                timerChip.textContent = 'الوقت المتبقي: ' + mins + ':' + String(secs).padStart(2, '0');
            }
            setInterval(tickMeetingTimer, 1000);
            tickMeetingTimer();

            var script = document.createElement('script');
            script.src = 'https://' + jitsiDomain + '/external_api.js';
            script.async = false;
            script.onload = function() {
                if (requestMediaBtn) {
                    requestMediaBtn.addEventListener('click', requestMediaPermission);
                }
                if (joinWithoutMediaBtn) {
                    joinWithoutMediaBtn.addEventListener('click', function() {
                        hidePermissionGate();
                        initJitsi();
                    });
                }
            };
            script.onerror = function() {
                console.error('Failed to load Jitsi external_api.js from ' + script.src);
                showError();
            };
            document.head.appendChild(script);
        })();
    </script>
    <script>
        (function () {
            var wrap = document.getElementById('pkg-features-dd-wrap');
            var btn = document.getElementById('pkg-features-dd-btn');
            var panel = document.getElementById('pkg-features-dd-panel');
            var chev = document.getElementById('pkg-features-dd-chevron');
            if (!wrap || !btn || !panel) return;
            function setOpen(open) {
                panel.classList.toggle('hidden', !open);
                btn.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (chev) chev.style.transform = open ? 'rotate(180deg)' : '';
            }
            btn.addEventListener('click', function (e) {
                e.stopPropagation();
                setOpen(panel.classList.contains('hidden'));
            });
            wrap.addEventListener('click', function (e) {
                e.stopPropagation();
            });
            document.addEventListener('click', function () {
                setOpen(false);
            });
            document.addEventListener('keydown', function (e) {
                if (e.key === 'Escape') setOpen(false);
            });
        })();
    </script>
</body>
</html>

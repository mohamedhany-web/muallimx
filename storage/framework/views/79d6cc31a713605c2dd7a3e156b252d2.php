<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Muallimx Classroom — <?php echo e($meeting->title ?: $meeting->code); ?></title>
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
        #wb-canvas { position: absolute; inset: 0; z-index: 12; pointer-events: none; touch-action: none; }
        #wb-canvas.wb-active { pointer-events: auto; cursor: crosshair; }
        #wb-toolbar { display: none; }
        #wb-toolbar.wb-visible { display: flex; }
        #wb-popup { z-index: 140; }
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
<?php
    $academicObserverMode = !empty($academicObserverMode);
    $rp = ($useInstructorRoutes ?? false) ? 'instructor.' : 'student.';
    if ($academicObserverMode) {
        $roomExitUrl = $academicObserverExitUrl ?? route('employee.dashboard');
    } elseif (($useInstructorRoutes ?? false)) {
        $roomExitUrl = $meeting->consultation_request_id ? route('instructor.consultations.show', $meeting->consultation_request_id) : route('instructor.consultations.index');
    } else {
        $roomExitUrl = route('student.classroom.index');
    }
?>
    
    <header class="h-[72px] bg-gradient-to-l from-slate-900 to-slate-800 border-b border-slate-700/50 flex items-center justify-between px-4 sm:px-6 shadow-lg">
        <div class="flex items-center gap-4">
            <a href="<?php echo e($roomExitUrl); ?>" class="flex items-center gap-2 text-slate-300 hover:text-white transition-colors">
                <span class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    <i class="fas fa-video text-lg"></i>
                </span>
                <span class="font-bold text-white hidden sm:inline">Muallimx</span>
            </a>
            <span class="w-px h-6 bg-slate-600 hidden sm:block"></span>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse shadow-lg shadow-emerald-400/50"></span>
                <span class="text-white font-semibold text-sm"><?php echo e($meeting->title ?: 'غرفة ' . $meeting->code); ?></span>
                <span class="text-slate-400 text-xs px-2 py-0.5 rounded-md bg-slate-700/80 font-mono"><?php echo e($meeting->code); ?></span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-slate-300 text-xs px-2 py-1 rounded-md bg-slate-700/80">
                الحد الأقصى للطلاب: <?php echo e((int) ($meeting->max_participants ?? 25)); ?>

            </span>
            <span class="text-amber-200 text-xs px-2 py-1 rounded-md bg-amber-500/20 border border-amber-500/30" id="meeting-timer-chip">
                مدة الاجتماع: <?php echo e((int) $effectiveDurationMinutes); ?> دقيقة (حد الباقة <?php echo e((int) $maxDurationMinutes); ?>)
            </span>
            <span class="hidden text-sky-200 text-xs px-2 py-1 rounded-md bg-sky-500/20 border border-sky-500/30" id="record-status-chip"></span>
            <?php if (! ($academicObserverMode)): ?>
            <button type="button" id="btn-wb-toggle" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600" title="تفعيل القلم والرسم على الشاشة فوق الاجتماع">
                <i class="fas fa-pen-nib text-amber-400" id="wb-toggle-icon"></i>
                <span id="wb-toggle-label" class="hidden sm:inline">لوحة فوق الفيديو</span>
            </button>
            <button type="button" id="btn-wb-popup-open" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-amber-600/25 hover:bg-amber-600/35 text-amber-100 text-sm font-medium transition-colors border border-amber-500/40" title="فتح لوحة بيضاء كبيرة في نافذة منبثقة">
                <i class="fas fa-expand text-amber-300"></i>
                <span class="hidden sm:inline">لوحة كبيرة</span>
            </button>
            <button type="button" id="btn-record" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600" title="تسجيل المحاضرة">
                <i class="fas fa-circle-dot text-rose-400" id="record-icon"></i>
                <span id="record-label">تسجيل المحاضرة</span>
            </button>
            <button type="button" onclick="navigator.clipboard.writeText('<?php echo e(url('classroom/join/' . $meeting->code)); ?>'); this.innerHTML='<i class=\'fas fa-check ml-1\'></i> تم النسخ'; setTimeout(()=>{ this.innerHTML='<i class=\'fas fa-link ml-1\'></i> مشاركة الرابط'; }, 2000)" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600">
                <i class="fas fa-link ml-1"></i> مشاركة الرابط
            </button>
            <form method="POST" action="<?php echo e(route($rp.'classroom.end', $meeting)); ?>" class="inline" onsubmit="return confirm('إنهاء الاجتماع للجميع؟');">
                <?php echo csrf_field(); ?>
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-colors shadow-lg shadow-rose-500/20">
                    <i class="fas fa-stop"></i> إنهاء الاجتماع
                </button>
            </form>
            <?php else: ?>
            <span class="text-amber-200 text-xs px-3 py-2 rounded-lg bg-amber-500/15 border border-amber-500/30 font-semibold">
                <i class="fas fa-eye ml-1"></i> وضع مراقبة (قراءة فقط)
            </span>
            <?php endif; ?>
        </div>
    </header>

    <div class="room-body">
    
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

    
    <?php if(!empty($isDemoJitsi)): ?>
    <div class="bg-amber-500/15 border-b border-amber-500/40 px-4 py-2 flex items-center justify-between gap-3 text-amber-800 dark:text-amber-200 text-sm flex-shrink-0">
        <span class="flex items-center gap-2">
            <i class="fas fa-exclamation-triangle"></i>
            <strong>للاختبار فقط:</strong> استخدام meet.jit.si يُقطع المكالمة بعد 5 دقائق. للإنتاج: من لوحة الإدارة → <strong>جلسات البث المباشر والمعلمين → إعدادات نظام اللايف</strong> غيّر نطاق Jitsi إلى سيرفر خاص أو Jitsi as a Service.
        </span>
        <button type="button" onclick="this.parentElement.remove()" class="text-amber-600 hover:text-amber-800 p-1" aria-label="إغلاق"><i class="fas fa-times"></i></button>
    </div>
    <?php endif; ?>

    
    <div id="media-tip" class="bg-slate-700/80 border-b border-slate-600 px-4 py-2 text-slate-300 text-xs flex items-center justify-between gap-2 flex-shrink-0">
        <span><i class="fas fa-info-circle text-cyan-400 ml-1"></i> عند طلب المتصفح استخدام <strong>الميكروفون أو الكاميرا</strong> اختر «السماح». يمكنك تفعيل الصوت والفيديو من الشريط بعد الدخول.</span>
        <button type="button" onclick="document.getElementById('media-tip').remove()" class="text-slate-400 hover:text-white p-1" aria-label="إغلاق"><i class="fas fa-times"></i></button>
    </div>

    
    <div id="meeting-stage" class="flex-1 min-h-0 relative w-full">
        <main id="jitsi-container" class="flex-1 min-h-0 relative w-full" role="application" aria-label="غرفة الاجتماع">
            <div id="jitsi-loading" class="flex flex-col items-center justify-center h-full text-slate-400 text-sm gap-3">
                <i class="fas fa-spinner fa-spin text-2xl text-cyan-400"></i>
                <span>جاري تحميل غرفة الاجتماع…</span>
            </div>
            <div id="jitsi-error" class="hidden flex-col items-center justify-center h-full p-6 text-center max-w-lg mx-auto" style="display: none;">
                <i class="fas fa-exclamation-triangle text-amber-500 text-4xl mb-3"></i>
                <p class="font-bold text-slate-200 mb-2">لا يمكن تحميل غرفة الاجتماع</p>
                <p class="text-slate-400 text-sm mb-3">المتصفح لم يستطع الاتصال بـ <strong class="text-slate-300"><?php echo e($jitsiDomain); ?></strong>.</p>
                <ul class="text-right text-slate-400 text-sm mb-4 list-none space-y-1">
                    <li>• النطاق يجب أن يكون <strong class="text-slate-300">النطاق الذي يعمل عليه Jitsi Meet</strong> (مثلاً <code class="bg-slate-700 px-1 rounded">meet.muallimx.com</code> وليس بالضرورة الموقع الرئيسي).</li>
                    <li>• جرّب فتح <a href="https://<?php echo e($jitsiDomain); ?>/external_api.js" target="_blank" rel="noopener" class="text-cyan-400 hover:underline">هذا الرابط</a> في تاب جديد — إن لم يُحمّل، فـ Jitsi غير مُثبت على هذا النطاق أو النطاق غير متاح من جهازك.</li>
                    <li>• إن كان Jitsi على نطاق فرعي (مثل meet.muallimx.com)، غيّر النطاق من: <strong>لوحة الإدارة → سيرفرات البث</strong> ثم «استخدام كنطاق افتراضي» للسيرفر الصحيح.</li>
                </ul>
                <a href="https://<?php echo e($jitsiDomain); ?>/<?php echo e($meeting->room_name); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cyan-500 hover:bg-cyan-600 text-white font-semibold transition-colors">
                    <i class="fas fa-external-link-alt"></i> فتح الغرفة في نافذة جديدة
                </a>
            </div>
        </main>
        <canvas id="wb-canvas" aria-hidden="true"></canvas>
        <div id="wb-toolbar" class="absolute bottom-3 left-1/2 -translate-x-1/2 z-[13] items-center gap-2 flex-wrap justify-center px-3 py-2 rounded-xl bg-slate-900/95 border border-slate-600 shadow-xl max-w-[95vw]">
            <label class="flex items-center gap-1.5 text-slate-300 text-xs">
                <span>لون</span>
                <input type="color" id="wb-color" value="#fbbf24" class="h-8 w-10 rounded border border-slate-500 cursor-pointer bg-slate-800 p-0.5" title="لون القلم">
            </label>
            <label class="flex items-center gap-1.5 text-slate-300 text-xs">
                <span>سمك</span>
                <input type="range" id="wb-width" min="1" max="16" value="4" class="w-24 align-middle" title="سمك الخط">
            </label>
            <button type="button" id="wb-clear" class="px-3 py-1.5 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-100 text-xs font-medium border border-slate-500">مسح اللوحة</button>
            <span class="text-slate-500 text-[10px] max-w-[200px] leading-tight hidden md:inline">الرسم يظهر على جهازك فقط؛ عطّل «لوحة بيضاء» للنقر داخل الاجتماع.</span>
        </div>
    </div>
    </div>

    
    <div id="wb-popup" class="hidden fixed inset-0 flex items-center justify-center p-2 sm:p-4" aria-hidden="true" role="dialog" aria-labelledby="wb-popup-title">
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
                    <strong class="text-slate-200">Muallimx Whiteboard</strong> — أدوات رسم كاملة (أشكال، نص، تصدير PNG/SVG من القائمة).
                    عند الإغلاق تُحدَّث معاينة على «لوحة فوق الفيديو». الرسم محلي على جهازك فقط.
                </span>
            </div>
        </div>
    </div>

    <?php echo $__env->make('partials.jitsi-iframe-media-allow', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php
        $mxBp = rtrim((string) request()->getBasePath(), '/');
        $mxP = $mxBp !== '' ? $mxBp : '';
        $mxExBases = array_values(array_unique(array_filter([
            $mxP . '/mx-vendor/excalidraw/',
            '/mx-vendor/excalidraw/',
            $mxP . '/vendor/excalidraw/',
            '/vendor/excalidraw/',
        ])));
    ?>
    
    <script>
        (function() {
            var jitsiDomain = '<?php echo e($jitsiDomain); ?>';
            var roomName = '<?php echo e($meeting->room_name); ?>';
            var userName = <?php echo json_encode($jitsiDisplayName ?? $user->name); ?>;
            var userEmail = <?php echo json_encode($user->email ?? ''); ?>;
            var container = document.getElementById('jitsi-container');
            var loadingEl = document.getElementById('jitsi-loading');
            var errorEl = document.getElementById('jitsi-error');
            var meetingEndsAt = <?php echo json_encode(optional($meetingEndsAt)->toIso8601String()); ?>;
            var timerChip = document.getElementById('meeting-timer-chip');
            var recordBtn = document.getElementById('btn-record');
            var recordIcon = document.getElementById('record-icon');
            var recordLabel = document.getElementById('record-label');
            var recordStatusChip = document.getElementById('record-status-chip');
            var uploadRecordingUrl = '<?php echo e(route($rp . 'classroom.recording.upload', $meeting)); ?>';
            var presignRecordingUrl = '<?php echo e(route($rp . 'classroom.recording.presign', $meeting)); ?>';
            var completeRecordingUrl = '<?php echo e(route($rp . 'classroom.recording.complete', $meeting)); ?>';
            var presignAudioUrl = '<?php echo e(route($rp . 'classroom.recording-audio.presign', $meeting)); ?>';
            var uploadAudioUrl = '<?php echo e(route($rp . 'classroom.recording-audio.upload', $meeting)); ?>';
            var completeAudioUrl = '<?php echo e(route($rp . 'classroom.recording-audio.complete', $meeting)); ?>';
            var csrfToken = '<?php echo e(csrf_token()); ?>';
            var roomExitUrl = <?php echo json_encode($roomExitUrl); ?>;
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

            var wbCanvas = document.getElementById('wb-canvas');
            var wbToolbar = document.getElementById('wb-toolbar');
            var wbToggle = document.getElementById('btn-wb-toggle');
            var wbCtx = wbCanvas && wbCanvas.getContext ? wbCanvas.getContext('2d') : null;
            var wbDrawing = false;
            var wbMode = false;
            var wbLast = null;
            var wbCssW = 0;
            var wbCssH = 0;

            var wbPopup = document.getElementById('wb-popup');
            var wbPopupStage = document.getElementById('wb-popup-stage');
            var wbPopupPanel = document.getElementById('wb-popup-panel');
            var excRoot = document.getElementById('classroom-excalidraw-root');
            var excLoading = document.getElementById('classroom-excalidraw-loading');
            var excReactRoot = null;
            var excMounted = false;
            var excMountPromise = null;
            var wbPopupClosing = false;
            var mxExcalidrawBases = <?php echo json_encode($mxExBases); ?>;
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
                wbPopup.classList.remove('hidden');
                wbPopup.classList.add('flex');
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
                mergeExcalidrawToMain(function() {
                    wbPopupClosing = false;
                    wbPopup.classList.add('hidden');
                    wbPopup.classList.remove('flex');
                    wbPopup.setAttribute('aria-hidden', 'true');
                    document.body.style.overflow = '';
                    try {
                        if (document.fullscreenElement) document.exitFullscreen();
                    } catch (fe) {}
                });
            }

            function resizeWbCanvas() {
                if (!wbCanvas || !wbCtx) return;
                var stage = document.getElementById('meeting-stage');
                if (!stage) return;
                var rect = stage.getBoundingClientRect();
                var w = Math.max(1, Math.floor(rect.width));
                var h = Math.max(1, Math.floor(rect.height));
                if (w === wbCssW && h === wbCssH && wbCanvas.width > 0) return;
                wbCssW = w;
                wbCssH = h;
                var dpr = window.devicePixelRatio || 1;
                wbCanvas.width = Math.floor(w * dpr);
                wbCanvas.height = Math.floor(h * dpr);
                wbCanvas.style.width = w + 'px';
                wbCanvas.style.height = h + 'px';
                wbCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
                wbCtx.lineCap = 'round';
                wbCtx.lineJoin = 'round';
            }

            function getWbPos(ev) {
                var rect = wbCanvas.getBoundingClientRect();
                var cx = ev.clientX;
                var cy = ev.clientY;
                if (ev.touches && ev.touches[0]) {
                    cx = ev.touches[0].clientX;
                    cy = ev.touches[0].clientY;
                }
                return { x: cx - rect.left, y: cy - rect.top };
            }

            function wbStart(ev) {
                if (!wbMode) return;
                wbDrawing = true;
                wbLast = getWbPos(ev);
                if (ev.preventDefault) ev.preventDefault();
            }

            function wbMove(ev) {
                if (!wbDrawing || wbLast === null || !wbCtx) return;
                var p = getWbPos(ev);
                var colorEl = document.getElementById('wb-color');
                var widthEl = document.getElementById('wb-width');
                wbCtx.strokeStyle = colorEl ? colorEl.value : '#fbbf24';
                wbCtx.lineWidth = widthEl ? parseInt(widthEl.value, 10) || 4 : 4;
                wbCtx.beginPath();
                wbCtx.moveTo(wbLast.x, wbLast.y);
                wbCtx.lineTo(p.x, p.y);
                wbCtx.stroke();
                wbLast = p;
                if (ev.preventDefault) ev.preventDefault();
            }

            function wbEnd(ev) {
                wbDrawing = false;
                wbLast = null;
                if (ev && ev.preventDefault) ev.preventDefault();
            }

            function setWbMode(on) {
                wbMode = on;
                if (wbCanvas) wbCanvas.classList.toggle('wb-active', on);
                if (wbToolbar) wbToolbar.classList.toggle('wb-visible', on);
                if (wbToggle) {
                    wbToggle.classList.toggle('ring-2', on);
                    wbToggle.classList.toggle('ring-amber-400', on);
                    wbToggle.setAttribute('aria-pressed', on ? 'true' : 'false');
                }
            }

            if (wbToggle && wbCanvas && wbCtx) {
                wbToggle.addEventListener('click', function() {
                    setWbMode(!wbMode);
                    resizeWbCanvas();
                });
                var wbClearBtn = document.getElementById('wb-clear');
                if (wbClearBtn) {
                    wbClearBtn.addEventListener('click', function() {
                        if (!wbCtx || !wbCanvas) return;
                        wbCtx.setTransform(1, 0, 0, 1, 0, 0);
                        wbCtx.clearRect(0, 0, wbCanvas.width, wbCanvas.height);
                        var dpr = window.devicePixelRatio || 1;
                        wbCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
                        wbCtx.lineCap = 'round';
                        wbCtx.lineJoin = 'round';
                    });
                }
                wbCanvas.addEventListener('mousedown', wbStart);
                wbCanvas.addEventListener('mousemove', wbMove);
                wbCanvas.addEventListener('mouseup', wbEnd);
                wbCanvas.addEventListener('mouseleave', wbEnd);
                wbCanvas.addEventListener('touchstart', wbStart, { passive: false });
                wbCanvas.addEventListener('touchmove', wbMove, { passive: false });
                wbCanvas.addEventListener('touchend', wbEnd);
                wbCanvas.addEventListener('touchcancel', wbEnd);
                window.addEventListener('resize', resizeWbCanvas);
                var meetingStageEl = document.getElementById('meeting-stage');
                if (meetingStageEl && typeof ResizeObserver !== 'undefined') {
                    new ResizeObserver(resizeWbCanvas).observe(meetingStageEl);
                }
                resizeWbCanvas();

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
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/student/classroom/room.blade.php ENDPATH**/ ?>
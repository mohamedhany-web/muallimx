<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>انضم إلى Muallimx Classroom — {{ $code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; }
        body { margin: 0; padding: 0; background: #0c1222; min-height: 100vh; }
        .room-body { position: relative; display: flex; flex-direction: column; height: calc(100vh - 72px); }
        #jitsi-container { width: 100%; flex: 1; min-height: 0; background: #0f172a; }
        #jitsi-container iframe { width: 100% !important; height: 100% !important; border: none; }
        #guest-wb-popup { z-index: 80; }
        #guest-wb-popup.is-open { display: flex !important; }
        .guest-excalidraw-host { position: absolute; inset: 0; width: 100%; height: 100%; }
        .guest-excalidraw-host .excalidraw { height: 100% !important; --color-surface-lowest: #0f172a; }
        #btn-guest-whiteboard.mx-guest-wb-pulse {
            animation: mxGuestWbPulse 0.8s ease-in-out 3;
            box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.55);
        }
        @keyframes mxGuestWbPulse {
            0% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0.55); }
            70% { box-shadow: 0 0 0 10px rgba(245, 158, 11, 0); }
            100% { box-shadow: 0 0 0 0 rgba(245, 158, 11, 0); }
        }
        #btn-guest-whiteboard.mx-guest-wb-writable {
            background: rgba(217, 119, 6, 0.4);
            border-color: rgba(251, 191, 36, 0.7);
        }
        .mx-muallimx-whiteboard .excalidraw .layer-ui__library,
        .mx-muallimx-whiteboard .excalidraw .library-menu,
        .mx-muallimx-whiteboard .excalidraw [data-testid="collab-button"],
        .mx-muallimx-whiteboard .excalidraw .ExcalidrawLogo,
        .mx-muallimx-whiteboard .excalidraw .welcome-screen-center__logo {
            display: none !important;
            pointer-events: none !important;
        }
        #guest-excalidraw-loading {
            position: absolute; inset: 0; z-index: 5; display: none;
            align-items: center; justify-content: center;
            background: rgba(15, 23, 42, 0.7); color: #94a3b8; font-size: 14px; text-align: center; padding: 1rem;
        }
        /* Virtual background panel (guest dark theme) */
        #mx-vbg-panel {
            position: fixed; z-index: 260; left: 50%; bottom: 24px; transform: translateX(-50%);
            width: min(420px, calc(100vw - 24px)); max-height: min(70vh, 520px); overflow: auto;
            border-radius: 12px; border: 1px solid #334155; background: #0f172a; color: #e2e8f0;
            box-shadow: 0 0 60px rgba(0,0,0,.35); padding: 12px; display: none; flex-direction: column; gap: 10px; direction: rtl;
        }
        #mx-vbg-panel.is-open { display: flex; }
        .mx-vbg-head { display: flex; align-items: center; justify-content: space-between; gap: 8px; }
        .mx-vbg-head strong { font-size: 13px; }
        .mx-vbg-close { width: 32px; height: 32px; border-radius: 8px; border: 1px solid #475569; background: #1e293b; color: #e2e8f0; cursor: pointer; font-size: 18px; }
        .mx-vbg-hint { margin: 0; font-size: 11px; color: #94a3b8; line-height: 1.5; }
        .mx-vbg-actions { display: flex; flex-wrap: wrap; gap: 6px; }
        .mx-vbg-chip { border: 1px solid #475569; background: #1e293b; color: #e2e8f0; border-radius: 8px; padding: 7px 10px; font-size: 11px; font-weight: 600; cursor: pointer; }
        .mx-vbg-chip:hover, .mx-vbg-chip.is-selected { border-color: #0065fd; background: rgba(0,101,253,.2); color: #93c5fd; }
        .mx-vbg-grid { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); gap: 8px; }
        .mx-vbg-preset { border: 2px solid transparent; border-radius: 10px; overflow: hidden; padding: 0; background: #111827; cursor: pointer; display: flex; flex-direction: column; }
        .mx-vbg-preset img { width: 100%; height: 52px; object-fit: cover; display: block; }
        .mx-vbg-preset span { font-size: 10px; padding: 4px 2px; text-align: center; background: #1e293b; color: #e2e8f0; }
        .mx-vbg-preset.is-selected, .mx-vbg-preset:hover { border-color: #0065fd; }
        .mx-vbg-upload { display: flex; align-items: center; justify-content: center; gap: 8px; border: 1px dashed #0065fd; border-radius: 10px; padding: 10px; cursor: pointer; font-size: 12px; font-weight: 600; color: #93c5fd; background: rgba(0,101,253,.15); }
        .mx-vbg-fallback { border: 0; background: transparent; color: #94a3b8; font-size: 11px; text-decoration: underline; cursor: pointer; padding: 4px; }
        @media (max-width: 640px) { .mx-vbg-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
        /* Guest permanent whiteboard tools */
        .mx-wb-tools { display:flex; flex-direction:column; gap:6px; width:100%; padding:8px 10px 10px; border-bottom:1px solid #334155; background:linear-gradient(180deg,#1e293b 0%,#0f172a 100%); flex-shrink:0; }
        .mx-wb-tools-hint { margin:0; font-size:10px; line-height:1.4; color:#94a3b8; text-align:right; }
        .mx-wb-tools-group { display:flex; flex-wrap:wrap; gap:6px; align-items:center; justify-content:flex-end; }
        .mx-wb-tool-btn { display:inline-flex; align-items:center; gap:6px; min-height:36px; padding:7px 10px; border-radius:10px; border:1px solid #475569; background:#1e293b; color:#e2e8f0; font-size:11px; font-weight:600; cursor:pointer; }
        .mx-wb-tool-btn:hover, .mx-wb-tool-btn.is-active { background:rgba(0,101,253,.25); border-color:#38bdf8; color:#7dd3fc; }
        .mx-wb-tool-btn--danger { border-color:rgba(248,113,113,.45); color:#fca5a5; }
        .mx-wb-tools.is-disabled { opacity:.55; pointer-events:none; }
        /* Guest share zoom (mobile) */
        #mx-share-zoom-viewport {
            position: relative; flex: 1; min-height: 0; width: 100%;
            overflow: hidden; background: #0f172a; touch-action: none;
        }
        #mx-share-zoom-viewport #jitsi-container {
            width: 100%; height: 100%; transform-origin: center center;
        }
        #mx-share-zoom-hud {
            position: absolute; z-index: 40; left: 50%; bottom: 16px; transform: translateX(-50%);
            display: none !important; align-items: center; gap: 6px; padding: 6px 8px;
            border-radius: 12px; background: rgba(15,23,42,.92); border: 1px solid #334155;
            box-shadow: 0 8px 24px rgba(0,0,0,.35);
        }
        #mx-share-zoom-hud.is-on { display: none !important; }
        #mx-guest-opts-wrap { position: relative; }
        #mx-guest-opts-panel {
            display: none; position: absolute; top: calc(100% + 6px); left: 0; z-index: 50;
            min-width: 11rem; padding: 6px; border-radius: 12px; border: 1px solid #334155;
            background: #0f172a; box-shadow: 0 12px 28px rgba(0,0,0,.4);
        }
        #mx-guest-opts-panel.is-open { display: block; }
        #mx-guest-opts-panel button {
            display: flex; width: 100%; align-items: center; gap: 8px;
            padding: 8px 10px; border: 0; border-radius: 8px; background: transparent;
            color: #e2e8f0; font-size: 12px; font-weight: 600; cursor: pointer; text-align: right;
        }
        #mx-guest-opts-panel button:hover { background: rgba(0,101,253,.2); color: #7dd3fc; }
        #mx-guest-opts-panel .mx-opts-sep { height: 1px; background: #334155; margin: 4px 0; }
        #mx-wb-tools-guest:empty::before {
            content: 'جاري تحميل أدوات السبورة…';
            display: block; padding: 8px 12px; font-size: 11px; color: #94a3b8;
        }
    </style>
    <meta name="mx-asset-base" content="{{ rtrim(asset(''), '/') }}">
    <script>window.MX_ASSET_BASE = @json(rtrim(asset(''), '/'));</script>
    @php
        $mxVbgJsFile = public_path('js/classroom-virtual-background.js');
        $mxVbgJs = is_readable($mxVbgJsFile) ? file_get_contents($mxVbgJsFile) : '';
        $mxNoiseJsFile = public_path('js/classroom-noise-isolation.js');
        $mxNoiseJs = is_readable($mxNoiseJsFile) ? file_get_contents($mxNoiseJsFile) : '';
        $mxWbToolsJsFile = public_path('js/classroom-wb-tools.js');
        $mxWbToolsJs = is_readable($mxWbToolsJsFile) ? file_get_contents($mxWbToolsJsFile) : '';
        $mxShareZoomJsFile = public_path('js/classroom-share-zoom.js');
        $mxShareZoomJs = is_readable($mxShareZoomJsFile) ? file_get_contents($mxShareZoomJsFile) : '';
    @endphp
    @if($mxVbgJs !== '')
    <script id="mx-classroom-vbg-js">{!! $mxVbgJs !!}</script>
    @endif
    @if($mxNoiseJs !== '')
    <script id="mx-classroom-noise-js">{!! $mxNoiseJs !!}</script>
    @endif
    @if($mxWbToolsJs !== '')
    <script id="mx-classroom-wb-tools-js">{!! $mxWbToolsJs !!}</script>
    @endif
    @if($mxShareZoomJs !== '')
    <script id="mx-classroom-share-zoom-js">{!! $mxShareZoomJs !!}</script>
    @endif
</head>
<body class="bg-slate-950 text-white">
    <div id="join-screen" class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl bg-slate-800/90 border border-slate-600 p-6 shadow-2xl shadow-black/30">
            @if(!empty($meetingEnded))
                <div class="text-center mb-2">
                    <div class="w-16 h-16 rounded-2xl bg-slate-600/40 text-slate-400 flex items-center justify-center mx-auto mb-4">
                        <i class="fas fa-door-closed text-3xl"></i>
                    </div>
                    <h1 class="text-xl font-bold text-white">انتهى الاجتماع</h1>
                    <p class="text-slate-400 text-sm mt-3 leading-relaxed">قام منظم الاجتماع بإنهائه. لا يمكن إعادة فتح الغرفة أو الانضمام مرة أخرى من هذا الرابط.</p>
                </div>
                @if($meeting && $meeting->title)
                    <p class="text-slate-500 text-sm mb-4 text-center">{{ $meeting->title }}</p>
                @endif
                <p class="text-slate-500 text-xs text-center">كود الغرفة: <span class="font-mono text-slate-400">{{ $code }}</span></p>
            @else
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-video text-3xl"></i>
                </div>
                <h1 class="text-xl font-bold text-white">Muallimx Classroom</h1>
                <p class="text-slate-400 text-sm mt-1">انضم إلى الاجتماع باستخدام الكود أو الرابط</p>
            </div>
            @if($meeting && $meeting->title)
                <p class="text-slate-300 text-sm mb-4 text-center">{{ $meeting->title }}</p>
            @endif
            <p class="text-slate-400 text-xs mb-4 text-center">كود الغرفة: <span class="font-mono font-bold text-cyan-400 text-lg">{{ $code }}</span></p>
            <p class="text-slate-400 text-xs mb-4 text-center">الحد الأقصى للمشاركين: <span class="font-bold text-amber-300">{{ $maxParticipants }}</span></p>
            <div class="space-y-3" id="guest-join-name-block">
                <label class="block text-sm font-medium text-slate-300">اسمك (يظهر للمشاركين)</label>
                <input type="text" id="guest-name" placeholder="أدخل اسمك" value="" class="w-full px-4 py-3 rounded-xl bg-slate-700 border border-slate-600 text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
            </div>
            <div class="mt-6" id="guest-join-actions">
                <button type="button" id="btn-join" class="w-full px-6 py-3 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold transition-colors">
                    <i class="fas fa-video ml-2"></i>
                    انضم الآن
                </button>
            </div>
            <div id="guest-av-gate" class="hidden mt-4 space-y-4 text-center">
                <div class="w-14 h-14 mx-auto rounded-2xl bg-cyan-500/20 text-cyan-300 flex items-center justify-center">
                    <i class="fas fa-microphone-lines text-xl"></i>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-white mb-2">فحص الصوت والكاميرا إلزامي</h2>
                    <p class="text-slate-400 text-sm leading-6">
                        قبل الدخول يجب تفعيل <strong class="text-slate-200">الميكروفون والكاميرا</strong>.
                        لا يمكن تخطي هذه الخطوة.
                    </p>
                </div>
                <button type="button" id="btn-guest-av-check" class="w-full px-6 py-3 rounded-xl bg-cyan-600 hover:bg-cyan-500 text-white font-bold transition-colors">
                    <i class="fas fa-shield-check ml-2"></i>
                    تفعيل الأجهزة والمتابعة
                </button>
                <button type="button" id="btn-guest-av-back" class="w-full px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-600 text-slate-200 text-sm font-semibold transition-colors">
                    رجوع
                </button>
                <p id="guest-av-help" class="text-xs text-slate-500"></p>
            </div>
            <p class="text-slate-500 text-xs mt-4 text-center" id="guest-join-footnote">لا تحتاج إلى حساب. ادخل باسمك ثم فعّل الأجهزة للانضمام.</p>
            @endif
        </div>
    </div>

    <div id="meeting-screen" class="hidden h-screen flex flex-col">
        <header class="h-[72px] bg-gradient-to-l from-slate-900 to-slate-800 border-b border-slate-700/50 flex items-center justify-between px-4 sm:px-6 shadow-lg flex-shrink-0 gap-2">
            <div class="flex items-center gap-3 min-w-0">
                <span class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center shrink-0">
                    <i class="fas fa-video text-lg"></i>
                </span>
                <span class="font-bold text-white truncate">Muallimx Classroom</span>
                <span class="text-slate-400 text-sm shrink-0">— {{ $code }}</span>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <div id="mx-guest-opts-wrap">
                    <button type="button" id="mx-guest-opts-btn" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-100 text-sm font-semibold transition-colors border border-slate-600" title="خيارات العرض" aria-expanded="false" aria-controls="mx-guest-opts-panel">
                        <i class="fas fa-sliders text-cyan-300"></i>
                        <span class="hidden sm:inline">خيارات</span>
                    </button>
                    <div id="mx-guest-opts-panel" role="menu">
                        <button type="button" data-zoom-in title="تكبير الشاشة المشتركة"><i class="fas fa-search-plus"></i> تكبير</button>
                        <button type="button" data-zoom-out title="تصغير"><i class="fas fa-search-minus"></i> تصغير</button>
                        <button type="button" data-zoom-reset title="إعادة العرض"><i class="fas fa-compress"></i> إعادة العرض <span data-zoom-label class="ms-auto text-slate-400">100%</span></button>
                        <div class="mx-opts-sep"></div>
                        <p class="px-2 py-1 text-[10px] text-slate-500 m-0">يمكنك أيضاً التكبير بقرص إصبعين على الجوال</p>
                    </div>
                </div>
                <button type="button" id="mx-ml-btn-noise" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-cyan-600/25 hover:bg-cyan-600/40 text-cyan-100 text-sm font-semibold transition-colors border border-cyan-500/40" title="عزل الضوضاء: مفعّل (صوت نقي)" aria-pressed="true">
                    <i class="fas fa-ear-listen text-cyan-300"></i>
                    <span class="hidden sm:inline">عزل الضوضاء</span>
                </button>
                <button type="button" id="mx-ml-btn-bg" class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-100 text-sm font-semibold transition-colors border border-slate-600" title="خلفية الكاميرا" aria-expanded="false" aria-controls="mx-vbg-panel">
                    <i class="fas fa-image text-cyan-300"></i>
                    <span class="hidden sm:inline">خلفية</span>
                </button>
                <div id="mx-guest-wb-wrap" class="hidden">
                    <button type="button" id="btn-guest-whiteboard"
                            class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-amber-600/25 hover:bg-amber-600/35 text-amber-100 text-sm font-semibold transition-colors border border-amber-500/40"
                            title="شاهد السبورة المشتركة مع المعلم (الكتابة عند تفعيل الصلاحية)">
                        <i class="fas fa-pen text-amber-300"></i>
                        <span class="hidden sm:inline" id="btn-guest-whiteboard-label">السبورة</span>
                    </button>
                </div>
                <button type="button" id="btn-leave" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-colors shadow-lg shadow-rose-500/20">
                    <i class="fas fa-sign-out-alt"></i> مغادرة
                </button>
            </div>
        </header>
        <div class="room-body">
            <div id="mx-share-zoom-viewport">
                <main id="jitsi-container" class="flex-1 min-h-0 relative" role="application" aria-label="غرفة الاجتماع"></main>
                <div id="mx-share-zoom-hud" class="hidden" aria-hidden="true" hidden></div>
            </div>
        </div>
    </div>

    {{-- سبورة مشتركة حية — نفس مشهد المعلم --}}
    <div id="guest-wb-popup" class="hidden fixed inset-0 p-2 sm:p-4 flex items-center justify-center" inert aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="guest-wb-title">
        <div id="guest-wb-backdrop" class="absolute inset-0 bg-slate-950/85 backdrop-blur-sm cursor-pointer" aria-hidden="true"></div>
        <div class="relative z-10 flex flex-col w-full max-w-[min(1680px,99vw)] h-[min(92vh,calc(100dvh-1rem))] rounded-2xl border border-slate-600 bg-slate-900 shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-700 bg-slate-800/95 shrink-0">
                <h2 id="guest-wb-title" class="text-base font-bold text-white m-0 flex items-center gap-2">
                    <i class="fas fa-pen text-amber-400"></i>
                    السبورة المشتركة
                </h2>
                <p id="guest-wb-mode-hint" class="text-[11px] text-slate-400 m-0 hidden sm:block">مشاهدة حية لسبورة المعلم — الكتابة عند التفعيل</p>
                <button type="button" id="guest-wb-close" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-medium border border-slate-600">
                    <i class="fas fa-times"></i> إغلاق
                </button>
            </div>
            <div id="mx-wb-tools-guest"></div>
            <div class="relative flex-1 min-h-0 bg-slate-950">
                <div id="guest-excalidraw-root" class="guest-excalidraw-host mx-muallimx-whiteboard" data-lang="ar"></div>
                <div id="guest-excalidraw-loading">جاري تحميل السبورة المشتركة…</div>
            </div>
        </div>
    </div>

    @if(empty($meetingEnded))
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
    @include('partials.jitsi-iframe-media-allow')
    @include('partials.mx-classroom-wb-sync')
    <script src="https://{{ $jitsiDomain }}/external_api.js"></script>
    <script>
        const domain = '{{ $jitsiDomain }}';
        const roomName = '{{ $roomName }}';
        const code = '{{ $code }}';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        const wbSceneGetUrl = @json(route('classroom.join.whiteboard-scene', $code));
        const wbScenePostUrl = @json(route('classroom.join.whiteboard-scene.push', $code));
        const mxExcalidrawBases = {!! json_encode($mxExBases) !!};
        let api = null;
        let joinToken = null;
        let heartbeatTimer = null;
        let guestWbAllowed = false;
        let guestPerms = {
            allow_participant_whiteboard: false,
            allow_participant_screen_share: false,
            allow_participant_chat: true,
            allow_participant_raise_hand: true,
            allow_participant_virtual_background: true,
        };
        let guestExcMounted = false;
        let guestExcMountPromise = null;
        let guestExcVendorPromise = null;
        let guestExcReactRoot = null;
        let guestWbSync = null;
        let pendingGuestName = '';
        let guestAvPassed = false;
        let guestLocalScreenSharing = false;
        let guestLastPermFingerprint = '';

        function applyGuestWhiteboardAllowed(on) {
            var prev = !!guestWbAllowed;
            guestWbAllowed = !!on;
            guestPerms.allow_participant_whiteboard = guestWbAllowed;
            // Button stays visible after join for view-only; write unlocks tools.
            var wrap = document.getElementById('mx-guest-wb-wrap');
            if (wrap && joinToken) {
                wrap.classList.remove('hidden');
            }
            var btn = document.getElementById('btn-guest-whiteboard');
            var label = document.getElementById('btn-guest-whiteboard-label');
            var hint = document.getElementById('guest-wb-mode-hint');
            if (btn) {
                btn.title = guestWbAllowed
                    ? 'اكتب على السبورة المشتركة مع المعلم (نفس اللوح)'
                    : 'شاهد السبورة المشتركة (المعلم لم يُتح الكتابة بعد)';
                if (guestWbAllowed) {
                    btn.classList.add('mx-guest-wb-writable');
                } else {
                    btn.classList.remove('mx-guest-wb-writable');
                }
            }
            if (label) {
                label.textContent = guestWbAllowed ? 'قلم السبورة' : 'السبورة';
            }
            if (hint) {
                hint.textContent = guestWbAllowed
                    ? 'تكتب على نفس لوح المعلم — مباشرة وبشكل حي'
                    : 'مشاهدة حية لسبورة المعلم — اطلب تفعيل الكتابة من المعلم';
            }
            try {
                if (window.__mxGuestWbTools && typeof window.__mxGuestWbTools.setEnabled === 'function') {
                    window.__mxGuestWbTools.setEnabled(!!guestWbAllowed);
                }
            } catch (eEn) {}
            // Remount when write permission flips while board is open so viewModeEnabled matches.
            if (prev !== guestWbAllowed) {
                var popupOpen = document.getElementById('guest-wb-popup');
                if (popupOpen && popupOpen.classList.contains('is-open')) {
                    try {
                        if (guestExcReactRoot && typeof guestExcReactRoot.unmount === 'function') {
                            guestExcReactRoot.unmount();
                        }
                    } catch (eUm) {}
                    guestExcReactRoot = null;
                    guestExcMounted = false;
                    guestExcMountPromise = null;
                    window.__mxGuestExcalidrawAPI = null;
                    window.__mxGuestWbTools = null;
                    var toolsGuest = document.getElementById('mx-wb-tools-guest');
                    if (toolsGuest) {
                        toolsGuest.innerHTML = '';
                        delete toolsGuest.dataset.bound;
                    }
                    mountGuestExcalidraw().then(function () {
                        if (guestWbSync) {
                            if (typeof guestWbSync.setActive === 'function') guestWbSync.setActive(true);
                            else guestWbSync.start();
                        }
                    }).catch(function () {});
                }
            }
            if (guestWbAllowed && !prev) {
                try {
                    var t = document.getElementById('mx-guest-noise-toast');
                    if (!t) {
                        t = document.createElement('div');
                        t.id = 'mx-guest-noise-toast';
                        t.style.cssText = 'position:fixed;bottom:88px;left:50%;transform:translateX(-50%);z-index:300;background:#171717;color:#fff;padding:10px 16px;border-radius:10px;font-size:12px;font-weight:600;opacity:0;transition:opacity .2s;pointer-events:none;';
                        document.body.appendChild(t);
                    }
                    t.textContent = 'تم إتاحة الكتابة — اضغط «قلم السبورة» للتعديل على اللوح';
                    t.style.opacity = '1';
                    clearTimeout(window.__mxGuestWbToastTimer);
                    window.__mxGuestWbToastTimer = setTimeout(function () { t.style.opacity = '0'; }, 3200);
                    if (btn) {
                        btn.classList.add('mx-guest-wb-pulse');
                        setTimeout(function () { btn.classList.remove('mx-guest-wb-pulse'); }, 2400);
                    }
                } catch (eT) {}
            }
            // Revoking write: keep board open in view mode; do not close or stop sync pull.
            if (!guestWbAllowed && prev) {
                try {
                    var t2 = document.getElementById('mx-guest-noise-toast');
                    if (t2) {
                        t2.textContent = 'أُوقفت الكتابة — يمكنك متابعة مشاهدة السبورة';
                        t2.style.opacity = '1';
                        clearTimeout(window.__mxGuestWbToastTimer);
                        window.__mxGuestWbToastTimer = setTimeout(function () { t2.style.opacity = '0'; }, 2800);
                    }
                } catch (eT2) {}
            }
        }

        function showGuestWbButtonAfterJoin() {
            var wrap = document.getElementById('mx-guest-wb-wrap');
            if (wrap) wrap.classList.remove('hidden');
            applyGuestWhiteboardAllowed(!!guestWbAllowed);
        }

        function buildGuestToolbarButtons(perms) {
            var buttons = ['microphone', 'camera', 'closedcaptions'];
            if (perms.allow_participant_screen_share) buttons.push('desktop');
            buttons.push('fullscreen', 'fodeviceselection', 'hangup', 'tileview', 'videoquality', 'filmstrip');
            if (perms.allow_participant_chat) buttons.push('chat');
            if (perms.allow_participant_raise_hand) buttons.push('raisehand');
            if (perms.allow_participant_virtual_background) buttons.push('select-background');
            return buttons;
        }

        function applyGuestPermissions(data) {
            if (!data || typeof data !== 'object') return;
            [
                'allow_participant_whiteboard',
                'allow_participant_screen_share',
                'allow_participant_chat',
                'allow_participant_raise_hand',
                'allow_participant_virtual_background',
            ].forEach(function (k) {
                if (typeof data[k] !== 'undefined') guestPerms[k] = !!data[k];
            });
            applyGuestWhiteboardAllowed(!!guestPerms.allow_participant_whiteboard);

            var bgBtn = document.getElementById('mx-ml-btn-bg');
            if (bgBtn) {
                if (guestPerms.allow_participant_virtual_background) bgBtn.classList.remove('hidden');
                else {
                    bgBtn.classList.add('hidden');
                    try {
                        if (window.__mxVbgUi && typeof window.__mxVbgUi.closePanel === 'function') {
                            window.__mxVbgUi.closePanel();
                        }
                    } catch (eBg) {}
                }
            }

            var fp = [
                guestPerms.allow_participant_whiteboard,
                guestPerms.allow_participant_screen_share,
                guestPerms.allow_participant_chat,
                guestPerms.allow_participant_raise_hand,
                guestPerms.allow_participant_virtual_background,
            ].join('|');

            if (api && typeof api.executeCommand === 'function') {
                // حدّث شريط الأدوات فقط عند تغيّر الصلاحيات (تجنب أوامر متكررة كل heartbeat)
                if (fp !== guestLastPermFingerprint) {
                    guestLastPermFingerprint = fp;
                    try {
                        api.executeCommand('overwriteConfig', {
                            toolbarButtons: buildGuestToolbarButtons(guestPerms),
                            disableVirtualBackground: !guestPerms.allow_participant_virtual_background,
                        });
                    } catch (eCfg) {}
                }
                // مهم: لا تستدعِ toggleShareScreen لإيقاف الصلاحية — Jitsi يفتح نافذة «Choose what to share»
                // أوقف الشير فقط إذا كان الطالب يشارك فعلياً ثم ألغى المعلم الإذن.
                if (!guestPerms.allow_participant_screen_share && guestLocalScreenSharing) {
                    try {
                        api.executeCommand('toggleShareScreen');
                    } catch (eSs) {}
                }
            }
        }

        function mapGuestMediaError(err) {
            var code = err && err.name ? String(err.name) : '';
            if (code === 'NotAllowedError' || code === 'PermissionDeniedError') {
                return 'المتصفح رفض الإذن. افتح رمز القفل بجانب الرابط ثم اسمح للكاميرا والميكروفون.';
            }
            if (code === 'NotFoundError' || code === 'DevicesNotFoundError') {
                return 'لا توجد كاميرا أو ميكروفون متصل بالجهاز.';
            }
            if (code === 'NotReadableError' || code === 'TrackStartError') {
                return 'تعذر تشغيل الكاميرا/الميكروفون (قد يكون مستخدماً في تطبيق آخر).';
            }
            if (code === 'SecurityError') {
                return 'حظر أمني من المتصفح. افتح الرابط عبر HTTPS.';
            }
            return 'تعذر الوصول للكاميرا أو الميكروفون. أصلح الإعدادات ثم أعد المحاولة.';
        }

        function showGuestAvGate(show) {
            var nameBlock = document.getElementById('guest-join-name-block');
            var actions = document.getElementById('guest-join-actions');
            var gate = document.getElementById('guest-av-gate');
            var footnote = document.getElementById('guest-join-footnote');
            if (show) {
                if (nameBlock) nameBlock.classList.add('hidden');
                if (actions) actions.classList.add('hidden');
                if (gate) gate.classList.remove('hidden');
                if (footnote) footnote.textContent = 'فحص الأجهزة مطلوب — لا يمكن تخطيه.';
            } else {
                if (nameBlock) nameBlock.classList.remove('hidden');
                if (actions) actions.classList.remove('hidden');
                if (gate) gate.classList.add('hidden');
                if (footnote) footnote.textContent = 'لا تحتاج إلى حساب. ادخل باسمك ثم فعّل الأجهزة للانضمام.';
            }
        }

        function setGuestAvHelp(msg, isError) {
            var el = document.getElementById('guest-av-help');
            if (!el) return;
            el.textContent = msg || '';
            el.className = 'text-xs ' + (isError ? 'text-rose-300' : 'text-slate-400');
        }

        function loadScriptSequential(url) {
            return new Promise(function(resolve, reject) {
                var s = document.createElement('script');
                s.src = url;
                s.async = false;
                s.onerror = function() { reject(new Error('فشل تحميل: ' + url)); };
                s.onload = function() { resolve(); };
                (document.head || document.documentElement).appendChild(s);
            });
        }
        function mxAbsAssetUrl(basePath) {
            var b = String(basePath || '').replace(/\/?$/, '/');
            if (b.indexOf('http') === 0) return b;
            if (b.charAt(0) !== '/') b = '/' + b;
            return window.location.origin + b;
        }
        function getExcalidrawLib() {
            return (typeof ExcalidrawLib !== 'undefined') ? ExcalidrawLib : (window.ExcalidrawLib || null);
        }
        function ensureGuestExVendor() {
            if (window.React && window.ReactDOM && getExcalidrawLib()) return Promise.resolve();
            if (guestExcVendorPromise) return guestExcVendorPromise;
            var bases = Array.isArray(mxExcalidrawBases) ? mxExcalidrawBases : ['/mx-vendor/excalidraw/', '/vendor/excalidraw/'];
            function loadFromBase(basePath) {
                var root = String(basePath || '').replace(/\/?$/, '/');
                window.EXCALIDRAW_ASSET_PATH = root + 'dist/';
                var prefix = mxAbsAssetUrl(root);
                return loadScriptSequential(prefix + 'react.production.min.js')
                    .then(function() { return loadScriptSequential(prefix + 'react-dom.production.min.js'); })
                    .then(function() { return loadScriptSequential(prefix + 'dist/excalidraw.production.min.js'); })
                    .then(function() {
                        if (!window.React || !window.ReactDOM || !getExcalidrawLib()) {
                            throw new Error('تعذّر تعريف الوايت بورد');
                        }
                    });
            }
            function tryNext(i) {
                if (i >= bases.length) return Promise.reject(new Error('فشل تحميل الوايت بورد'));
                return loadFromBase(bases[i]).catch(function() { return tryNext(i + 1); });
            }
            guestExcVendorPromise = tryNext(0).catch(function(e) {
                guestExcVendorPromise = null;
                throw e;
            });
            return guestExcVendorPromise;
        }

        function mountGuestExcalidraw() {
            if (guestExcMounted) return Promise.resolve();
            if (guestExcMountPromise) return guestExcMountPromise;
            var root = document.getElementById('guest-excalidraw-root');
            var loading = document.getElementById('guest-excalidraw-loading');
            if (!root) return Promise.reject(new Error('no root'));
            if (loading) loading.style.display = 'flex';
            try {
                if (guestExcReactRoot && typeof guestExcReactRoot.unmount === 'function') {
                    guestExcReactRoot.unmount();
                }
            } catch (eUm2) {}
            guestExcReactRoot = null;
            try { root.innerHTML = ''; } catch (eClr) {}

            guestExcMountPromise = ensureGuestExVendor().then(function() {
                return new Promise(function(resolve, reject) {
                    var Lib = getExcalidrawLib();
                    var ReactMod = window.React;
                    var ReactDOM = window.ReactDOM;
                    if (!Lib || !ReactMod || !ReactDOM) {
                        reject(new Error('libs missing'));
                        return;
                    }
                    try {
                        var createRoot = ReactDOM.createRoot;
                        var props = {
                            langCode: 'ar-SA',
                            viewModeEnabled: !guestWbAllowed,
                            excalidrawAPI: function(exApi) {
                                window.__mxGuestExcalidrawAPI = exApi;
                            },
                            onChange: function() {
                                if (guestWbSync) guestWbSync.onLocalChange();
                            }
                        };
                        guestExcReactRoot = createRoot(root);
                        guestExcReactRoot.render(ReactMod.createElement(Lib.Excalidraw, props));
                        guestExcMounted = true;
                        if (loading) loading.style.display = 'none';
                        window.dispatchEvent(new Event('resize'));
                        try {
                            if (!window.__mxGuestWbTools && window.MxClassroomWbTools && typeof window.MxClassroomWbTools.bindToolbar === 'function') {
                                var toolsGuest = document.getElementById('mx-wb-tools-guest');
                                if (toolsGuest && !toolsGuest.dataset.bound) {
                                    toolsGuest.dataset.bound = '1';
                                    window.__mxGuestWbTools = window.MxClassroomWbTools.bindToolbar({
                                        mountEl: toolsGuest,
                                        theme: 'dark',
                                        hintText: 'تكتب على سبورة المعلم المشتركة — استخدم القلم أو النص أو الممحاة',
                                        getApi: function () { return window.__mxGuestExcalidrawAPI || null; },
                                        canWrite: function () { return !!guestWbAllowed && !!joinToken; },
                                        onAfterChange: function () {
                                            if (guestWbSync && typeof guestWbSync.pushNow === 'function') guestWbSync.pushNow();
                                            else if (guestWbSync && typeof guestWbSync.onLocalChange === 'function') guestWbSync.onLocalChange();
                                        },
                                    });
                                }
                            }
                            if (window.__mxGuestWbTools && typeof window.__mxGuestWbTools.setEnabled === 'function') {
                                window.__mxGuestWbTools.setEnabled(!!guestWbAllowed);
                            }
                        } catch (eToolsG) {}
                        resolve();
                    } catch (err) {
                        if (loading) {
                            loading.style.display = 'flex';
                            loading.textContent = 'تعذّر فتح الوايت بورد.';
                        }
                        guestExcMountPromise = null;
                        reject(err);
                    }
                });
            }).catch(function(err) {
                guestExcMountPromise = null;
                if (loading) {
                    loading.style.display = 'flex';
                    loading.textContent = 'تعذّر تحميل الوايت بورد.';
                }
                return Promise.reject(err);
            });
            return guestExcMountPromise;
        }

        function ensureGuestWbSync() {
            if (guestWbSync || !window.MxClassroomWbSync) return guestWbSync;
            guestWbSync = window.MxClassroomWbSync.attach({
                getApi: function() { return window.__mxGuestExcalidrawAPI || null; },
                getUrl: wbSceneGetUrl,
                postUrl: wbScenePostUrl,
                csrfToken: csrfToken,
                getExtraBody: function() { return { token: joinToken || '' }; },
                canWrite: function() { return !!guestWbAllowed && !!joinToken; },
                pollMs: 1600,
                idlePollMs: 8000,
                isActive: function () {
                    var popup = document.getElementById('guest-wb-popup');
                    return !!(popup && popup.classList.contains('is-open'));
                },
                onDenied: function(data) {
                    // Pull is always allowed for view; 422 here means meeting ended / invalid token.
                    var msg = (data && data.message) ? data.message : 'تعذر مزامنة السبورة.';
                    if (data && data.ended) {
                        alert(msg);
                    }
                }
            });
            return guestWbSync;
        }

        function openGuestWb() {
            if (!joinToken) {
                alert('انضم للاجتماع أولاً ثم افتح السبورة.');
                return;
            }
            var popup = document.getElementById('guest-wb-popup');
            if (!popup) return;
            popup.classList.remove('hidden');
            popup.classList.add('is-open');
            popup.removeAttribute('inert');
            popup.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            var loading = document.getElementById('guest-excalidraw-loading');
            if (loading) {
                loading.style.display = 'flex';
                loading.textContent = guestWbAllowed
                    ? 'جاري تحميل السبورة المشتركة…'
                    : 'جاري تحميل السبورة للمشاهدة…';
            }
            mountGuestExcalidraw().then(function() {
                var sync = ensureGuestWbSync();
                if (sync) {
                    if (typeof sync.setActive === 'function') sync.setActive(true);
                    else sync.start();
                }
                try {
                    if (window.__mxGuestWbTools && typeof window.__mxGuestWbTools.setEnabled === 'function') {
                        window.__mxGuestWbTools.setEnabled(!!guestWbAllowed);
                    }
                } catch (eEn2) {}
                setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 100);
                setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 400);
            }).catch(function(err) {
                console.error('Guest whiteboard mount failed', err);
                if (loading) {
                    loading.style.display = 'flex';
                    loading.textContent = 'تعذّر تحميل السبورة. حدّث الصفحة أو جرّب متصفحاً أحدث (Chrome/Edge).';
                }
                alert('تعذّر فتح السبورة المشتركة. حدّث الصفحة ثم أعد المحاولة.');
            });
        }

        function closeGuestWb() {
            var popup = document.getElementById('guest-wb-popup');
            if (!popup) return;
            if (guestWbSync) guestWbSync.pushNow();
            if (guestWbSync && typeof guestWbSync.setActive === 'function') {
                guestWbSync.setActive(false);
            }
            popup.classList.add('hidden');
            popup.classList.remove('is-open');
            popup.setAttribute('inert', '');
            popup.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.getElementById('btn-guest-whiteboard') && document.getElementById('btn-guest-whiteboard').addEventListener('click', openGuestWb);
        document.getElementById('guest-wb-close') && document.getElementById('guest-wb-close').addEventListener('click', closeGuestWb);
        document.getElementById('guest-wb-backdrop') && document.getElementById('guest-wb-backdrop').addEventListener('click', closeGuestWb);

        document.getElementById('btn-join').addEventListener('click', function() {
            pendingGuestName = document.getElementById('guest-name').value.trim() || 'ضيف';
            guestAvPassed = false;
            setGuestAvHelp('', false);
            showGuestAvGate(true);
        });

        document.getElementById('btn-guest-av-back') && document.getElementById('btn-guest-av-back').addEventListener('click', function () {
            showGuestAvGate(false);
            var btn = document.getElementById('btn-join');
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-video ml-2"></i> انضم الآن';
            }
        });

        document.getElementById('btn-guest-av-check') && document.getElementById('btn-guest-av-check').addEventListener('click', async function () {
            var avBtn = document.getElementById('btn-guest-av-check');
            var name = pendingGuestName || document.getElementById('guest-name').value.trim() || 'ضيف';

            if (!navigator.mediaDevices || typeof navigator.mediaDevices.getUserMedia !== 'function') {
                setGuestAvHelp('المتصفح لا يدعم الوصول للأجهزة. استخدم متصفحاً حديثاً عبر HTTPS.', true);
                return;
            }
            if (!window.isSecureContext && location.hostname !== 'localhost' && location.hostname !== '127.0.0.1') {
                setGuestAvHelp('المتصفح يشترط HTTPS لتفعيل الميكروفون والكاميرا. لا يمكن الدخول بدون ذلك.', true);
                return;
            }

            if (avBtn) {
                avBtn.disabled = true;
                avBtn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري الفحص...';
            }
            setGuestAvHelp('جاري فحص الميكروفون والكاميرا...', false);

            try {
                var stream = await navigator.mediaDevices.getUserMedia({ audio: true, video: true });
                var hasAudio = stream.getAudioTracks().some(function (t) { return t.readyState === 'live'; });
                var hasVideo = stream.getVideoTracks().some(function (t) { return t.readyState === 'live'; });
                stream.getTracks().forEach(function (track) { track.stop(); });
                if (!hasAudio || !hasVideo) {
                    setGuestAvHelp('يجب تفعيل الميكروفون والكاميرا معاً قبل الدخول.', true);
                    if (avBtn) {
                        avBtn.disabled = false;
                        avBtn.innerHTML = '<i class="fas fa-shield-check ml-2"></i> تفعيل الأجهزة والمتابعة';
                    }
                    return;
                }
            } catch (err) {
                setGuestAvHelp(mapGuestMediaError(err), true);
                if (avBtn) {
                    avBtn.disabled = false;
                    avBtn.innerHTML = '<i class="fas fa-shield-check ml-2"></i> تفعيل الأجهزة والمتابعة';
                }
                return;
            }

            guestAvPassed = true;
            setGuestAvHelp('تم التحقق. جاري الانضمام...', false);

            try {
                const enterResp = await fetch(`/classroom/join/${code}/enter`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ display_name: name })
                });
                const enterData = await enterResp.json();
                if (!enterResp.ok || !enterData.ok) {
                    alert(enterData.message || 'لا يمكن الانضمام الآن.');
                    if (avBtn) {
                        avBtn.disabled = false;
                        avBtn.innerHTML = '<i class="fas fa-shield-check ml-2"></i> تفعيل الأجهزة والمتابعة';
                    }
                    setGuestAvHelp(enterData.message || 'لا يمكن الانضمام الآن.', true);
                    return;
                }
                joinToken = enterData.token;
                applyGuestPermissions(enterData);
                showGuestWbButtonAfterJoin();
            } catch (e) {
                alert('تعذر الاتصال بالخادم. حاول مرة أخرى.');
                if (avBtn) {
                    avBtn.disabled = false;
                    avBtn.innerHTML = '<i class="fas fa-shield-check ml-2"></i> تفعيل الأجهزة والمتابعة';
                }
                setGuestAvHelp('تعذر الاتصال بالخادم.', true);
                return;
            }

            document.getElementById('join-screen').classList.add('hidden');
            document.getElementById('meeting-screen').classList.remove('hidden');

            const jitsiRoot = document.querySelector('#jitsi-container');
            if (typeof muallimxEnsureJitsiIframeMediaAllow === 'function') {
                muallimxEnsureJitsiIframeMediaAllow(jitsiRoot);
            }

            const options = {
                roomName: roomName,
                parentNode: jitsiRoot,
                width: '100%',
                height: '100%',
                userInfo: { displayName: name },
                configOverwrite: (function () {
                    var base = {
                        prejoinConfig: { enabled: false },
                        prejoinPageEnabled: false,
                        enableLobby: false,
                        requireDisplayName: false,
                        enableWelcomePage: false,
                        disableDeepLinking: true,
                        enableRecording: false,
                        startWithAudioMuted: true,
                        startWithVideoMuted: true,
                        enableLayerSuspension: true,
                        maxFullResolutionParticipants: 1,
                        disableVirtualBackground: !guestPerms.allow_participant_virtual_background,
                        toolbarButtons: buildGuestToolbarButtons(guestPerms),
                        // الضيف يغادر فقط — لا طرد/إعطاء مشرف/إنهاء للجميع
                        disableRemoteMute: true,
                        remoteVideoMenu: {
                            disableKick: true,
                            disableGrantModerator: true,
                        },
                        // إن وُجدت قائمة Hangup: امنع تنفيذ «إنهاء الاجتماع للجميع»
                        buttonsWithNotifyClick: [
                            { key: 'end-meeting', preventExecution: true },
                            { key: 'hangup', preventExecution: false },
                        ],
                    };
                    var audioPatch = (window.MxClassroomNoiseIsolation && typeof window.MxClassroomNoiseIsolation.getJitsiAudioConfigPatch === 'function')
                        ? window.MxClassroomNoiseIsolation.getJitsiAudioConfigPatch()
                        : {
                            disableAP: false,
                            disableAEC: false,
                            disableNS: false,
                            disableAGC: false,
                            disableHPF: false,
                            enableNoisyMicDetection: true,
                            enableOpusRed: true,
                            constraints: {
                                audio: {
                                    echoCancellation: true,
                                    noiseSuppression: true,
                                    autoGainControl: true,
                                },
                            },
                        };
                    return Object.assign({}, base, audioPatch);
                })(),
                interfaceConfigOverwrite: {
                    APP_NAME: 'Muallimx Classroom',
                    NATIVE_APP_NAME: 'Muallimx Classroom',
                    PROVIDER_NAME: 'Muallimx',
                    JITSI_WATERMARK_LINK: '',
                    HIDE_DEEP_LINKING_LOGO: true,
                    TOOLBAR_BUTTONS: buildGuestToolbarButtons(guestPerms),
                    SHOW_JITSI_WATERMARK: false,
                    SHOW_WATERMARK_FOR_GUESTS: false,
                    SHOW_BRAND_WATERMARK: false,
                    SHOW_POWERED_BY: false,
                    MOBILE_APP_PROMO: false,
                    DEFAULT_BACKGROUND: '#0f172a',
                    FILM_STRIP_MAX_HEIGHT: 100,
                }
            };
            api = new JitsiMeetExternalAPI(domain, options);
            window.__mxClassroomJitsiApi = api;

            (function bindGuestShareZoom() {
                if (!window.MxClassroomShareZoom || typeof window.MxClassroomShareZoom.bind !== 'function') return;
                var viewport = document.getElementById('mx-share-zoom-viewport');
                var target = document.getElementById('jitsi-container');
                var hud = document.getElementById('mx-guest-opts-panel');
                if (!viewport || !target) return;
                window.__mxGuestShareZoom = window.MxClassroomShareZoom.bind({
                    viewport: viewport,
                    target: target,
                    hud: hud,
                    onToast: function (msg) {
                        try { console.info(msg); } catch (e) {}
                    },
                });
                // Always allow zoom controls for guests (pinch + options menu)
                try { window.__mxGuestShareZoom.setActive(true); } catch (eAct) {}

                var optsBtn = document.getElementById('mx-guest-opts-btn');
                var optsPanel = document.getElementById('mx-guest-opts-panel');
                var optsWrap = document.getElementById('mx-guest-opts-wrap');
                if (optsBtn && optsPanel) {
                    optsBtn.addEventListener('click', function (e) {
                        e.stopPropagation();
                        var open = !optsPanel.classList.contains('is-open');
                        optsPanel.classList.toggle('is-open', open);
                        optsBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                    });
                    document.addEventListener('click', function (e) {
                        if (optsWrap && !optsWrap.contains(e.target)) {
                            optsPanel.classList.remove('is-open');
                            optsBtn.setAttribute('aria-expanded', 'false');
                        }
                    });
                }
            })();

            (function bindGuestNoiseIsolation() {
                function tryBind() {
                    if (!window.MxClassroomNoiseIsolation || typeof window.MxClassroomNoiseIsolation.bindUi !== 'function') return false;
                    var btnNoise = document.getElementById('mx-ml-btn-noise');
                    if (!btnNoise) return true;
                    window.__mxNoiseUi = window.MxClassroomNoiseIsolation.bindUi({
                        toggleBtn: btnNoise,
                        theme: 'dark',
                        getApi: function () { return window.__mxClassroomJitsiApi || api; },
                        onToast: function (msg) {
                            try { console.info(msg); } catch (e) {}
                            var t = document.getElementById('mx-guest-noise-toast');
                            if (!t) {
                                t = document.createElement('div');
                                t.id = 'mx-guest-noise-toast';
                                t.style.cssText = 'position:fixed;bottom:88px;left:50%;transform:translateX(-50%);z-index:300;background:#171717;color:#fff;padding:10px 16px;border-radius:10px;font-size:12px;font-weight:600;opacity:0;transition:opacity .2s;pointer-events:none;';
                                document.body.appendChild(t);
                            }
                            t.textContent = msg;
                            t.style.opacity = '1';
                            clearTimeout(window.__mxGuestNoiseToastTimer);
                            window.__mxGuestNoiseToastTimer = setTimeout(function () { t.style.opacity = '0'; }, 2200);
                        },
                    });
                    return true;
                }
                if (!tryBind()) {
                    var n = 0;
                    var t = setInterval(function () {
                        n += 1;
                        if (tryBind() || n > 40) clearInterval(t);
                    }, 150);
                }
            })();

            (function bindGuestVirtualBg() {
                function tryBind() {
                    if (!window.MxClassroomVirtualBackground || typeof window.MxClassroomVirtualBackground.bindUi !== 'function') return false;
                    var btnBg = document.getElementById('mx-ml-btn-bg');
                    if (!btnBg) return true;
                    window.__mxVbgUi = window.MxClassroomVirtualBackground.bindUi({
                        theme: 'dark',
                        toggleBtn: btnBg,
                        mountEl: document.body,
                        getApi: function () { return window.__mxClassroomJitsiApi || api; },
                        onToast: function (msg) {
                            try { console.info(msg); } catch (e) {}
                            var t = document.getElementById('mx-guest-vbg-toast');
                            if (!t) {
                                t = document.createElement('div');
                                t.id = 'mx-guest-vbg-toast';
                                t.style.cssText = 'position:fixed;bottom:88px;left:50%;transform:translateX(-50%);z-index:300;background:#171717;color:#fff;padding:10px 16px;border-radius:10px;font-size:12px;font-weight:600;opacity:0;transition:opacity .2s;pointer-events:none;';
                                document.body.appendChild(t);
                            }
                            t.textContent = msg;
                            t.style.opacity = '1';
                            clearTimeout(window.__mxGuestVbgToastTimer);
                            window.__mxGuestVbgToastTimer = setTimeout(function () { t.style.opacity = '0'; }, 2200);
                        },
                        ensureCameraOn: function () {
                            return new Promise(function (resolve) {
                                var j = window.__mxClassroomJitsiApi || api;
                                if (!j) { resolve(false); return; }
                                function afterCheck(muted) {
                                    if (!muted) { resolve(true); return; }
                                    try { j.executeCommand('toggleVideo'); } catch (e) { resolve(false); return; }
                                    setTimeout(function () {
                                        try {
                                            var p2 = typeof j.isVideoMuted === 'function' ? j.isVideoMuted() : false;
                                            if (p2 && typeof p2.then === 'function') p2.then(function (m2) { resolve(!m2); });
                                            else resolve(!p2);
                                        } catch (e2) { resolve(true); }
                                    }, 350);
                                }
                                try {
                                    if (typeof j.isVideoMuted === 'function') {
                                        var p = j.isVideoMuted();
                                        if (p && typeof p.then === 'function') p.then(afterCheck);
                                        else afterCheck(!!p);
                                    } else resolve(true);
                                } catch (e3) { resolve(true); }
                            });
                        },
                    });
                    return true;
                }
                if (!tryBind()) {
                    var n = 0;
                    var t = setInterval(function () {
                        n += 1;
                        if (tryBind() || n > 40) clearInterval(t);
                    }, 150);
                }
            })();

            api.addEventListener('videoConferenceJoined', function () {
                try {
                    if (window.__mxVbgUi && typeof window.__mxVbgUi.restoreSaved === 'function') {
                        setTimeout(function () { window.__mxVbgUi.restoreSaved(); }, 800);
                    }
                } catch (e) {}
                try {
                    if (window.__mxNoiseUi && typeof window.__mxNoiseUi.markJoined === 'function') {
                        window.__mxNoiseUi.markJoined();
                    }
                    if (window.__mxNoiseUi && typeof window.__mxNoiseUi.enableOnJoin === 'function') {
                        window.__mxNoiseUi.enableOnJoin();
                    }
                } catch (eNs) {}
            });
            api.addEventListener('audioMuteStatusChanged', function (e) {
                if (e && e.muted === false) {
                    try {
                        if (window.__mxNoiseUi && typeof window.__mxNoiseUi.enableOnJoin === 'function') {
                            window.__mxNoiseUi.enableOnJoin();
                        }
                    } catch (errNs) {}
                }
            });
            api.addEventListener('screenSharingStatusChanged', function (e) {
                var on = !!(e && (e.on === true || e.on === 'true'));
                // حالة مشاركة الشاشة المحلية للطالب فقط — لا نستدعِ toggle هنا أبداً
                // (toggleShareScreen يفتح نافذة المتصفح «Choose what to share»)
                guestLocalScreenSharing = on;
                try {
                    if (window.__mxNoiseUi && typeof window.__mxNoiseUi.onScreenShareChanged === 'function') {
                        window.__mxNoiseUi.onScreenShareChanged(on);
                    } else if (window.MxClassroomNoiseIsolation && typeof window.MxClassroomNoiseIsolation.reattachNoiseAfterTrackChange === 'function') {
                        var wantNs = window.MxClassroomNoiseIsolation.readSavedEnabled
                            ? window.MxClassroomNoiseIsolation.readSavedEnabled()
                            : true;
                        window.MxClassroomNoiseIsolation.reattachNoiseAfterTrackChange(
                            window.__mxClassroomJitsiApi || api,
                            wantNs
                        );
                    }
                } catch (eNsShare) {}
            });
            api.addEventListener('videoMuteStatusChanged', function (e) {
                if (e && e.muted === false) {
                    try {
                        if (window.__mxVbgUi && typeof window.__mxVbgUi.restoreSaved === 'function') {
                            setTimeout(function () { window.__mxVbgUi.restoreSaved(); }, 400);
                        }
                    } catch (err) {}
                }
            });

            heartbeatTimer = setInterval(async function() {
                if (!joinToken) return;
                try {
                    const hbRes = await fetch(`/classroom/join/${code}/heartbeat`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ token: joinToken })
                    });
                    const hbData = await hbRes.json().catch(function () { return {}; });
                    if (!hbRes.ok || hbData.ended) {
                        if (api) {
                            try { api.executeCommand('hangup'); } catch (e) {}
                        }
                        alert(hbData.message || 'انتهت الجلسة.');
                        leaveMeetingAndReload();
                        return;
                    }
                    if (typeof hbData.allow_participant_whiteboard !== 'undefined'
                        || typeof hbData.allow_participant_screen_share !== 'undefined'
                        || typeof hbData.allow_participant_chat !== 'undefined'
                        || typeof hbData.allow_participant_raise_hand !== 'undefined'
                        || typeof hbData.allow_participant_virtual_background !== 'undefined') {
                        applyGuestPermissions(hbData);
                    }
                } catch (e) {}
            }, 3000);

            api.addEventListener('readyToClose', function() {
                leaveMeetingAndReload();
            });

            document.getElementById('btn-leave').onclick = function() {
                // مغادرة الضيف فقط — لا يستدعي إنهاء الاجتماع في Laravel
                if (api) {
                    try { api.executeCommand('hangup'); } catch (e) {}
                } else {
                    leaveMeetingAndReload();
                }
            };

            // لو ظهرت قائمة «إنهاء للجميع» من Jitsi — نتجاهلها ونغادر فقط
            api.addEventListener('toolbarButtonClicked', function (e) {
                var key = e && (e.key || e.buttonName || '');
                if (key === 'end-meeting' || key === 'endmeeting') {
                    try { api.executeCommand('hangup'); } catch (err) {}
                }
            });
        });

        async function leaveMeetingAndReload() {
            if (heartbeatTimer) clearInterval(heartbeatTimer);
            if (guestWbSync) guestWbSync.stop();
            closeGuestWb();
            if (joinToken) {
                try {
                    await fetch(`/classroom/join/${code}/leave`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ token: joinToken, _token: csrfToken })
                    });
                } catch (e) {}
            }
            window.location.reload();
        }

        window.addEventListener('beforeunload', function() {
            if (!joinToken) return;
            navigator.sendBeacon(`/classroom/join/${code}/leave`, new Blob([JSON.stringify({ token: joinToken, _token: csrfToken })], { type: 'application/json' }));
        });
    </script>
    @endif
</body>
</html>

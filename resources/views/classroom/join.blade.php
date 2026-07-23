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
    </style>
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
            <div class="space-y-3">
                <label class="block text-sm font-medium text-slate-300">اسمك (يظهر للمشاركين)</label>
                <input type="text" id="guest-name" placeholder="أدخل اسمك" value="" class="w-full px-4 py-3 rounded-xl bg-slate-700 border border-slate-600 text-white placeholder-slate-500 focus:ring-2 focus:ring-cyan-500 focus:border-transparent">
            </div>
            <div class="mt-6">
                <button type="button" id="btn-join" class="w-full px-6 py-3 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold transition-colors">
                    <i class="fas fa-video ml-2"></i>
                    انضم الآن
                </button>
            </div>
            <p class="text-slate-500 text-xs mt-4 text-center">لا تحتاج إلى حساب. ادخل باسمك وانضم مباشرة.</p>
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
                <div id="mx-guest-wb-wrap" class="hidden">
                    <button type="button" id="btn-guest-whiteboard"
                            class="inline-flex items-center gap-2 px-3 sm:px-4 py-2 rounded-xl bg-amber-600/25 hover:bg-amber-600/35 text-amber-100 text-sm font-semibold transition-colors border border-amber-500/40"
                            title="افتح الوايت بورد للكتابة والرسم (لوحة منفصلة عن فيديو الاجتماع)">
                        <i class="fas fa-chalkboard text-amber-300"></i>
                        <span class="hidden sm:inline">الوايت بورد</span>
                    </button>
                </div>
                <button type="button" id="btn-leave" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-colors shadow-lg shadow-rose-500/20">
                    <i class="fas fa-sign-out-alt"></i> مغادرة
                </button>
            </div>
        </header>
        <div class="room-body">
            <main id="jitsi-container" class="flex-1 min-h-0 relative" role="application" aria-label="غرفة الاجتماع"></main>
        </div>
    </div>

    {{-- لوحة الوايت بورد للضيف — منفصلة تماماً عن فيديو الاجتماع --}}
    <div id="guest-wb-popup" class="hidden fixed inset-0 p-2 sm:p-4 flex items-center justify-center" inert aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="guest-wb-title">
        <div id="guest-wb-backdrop" class="absolute inset-0 bg-slate-950/85 backdrop-blur-sm cursor-pointer" aria-hidden="true"></div>
        <div class="relative z-10 flex flex-col w-full max-w-[min(1680px,99vw)] h-[min(92vh,calc(100dvh-1rem))] rounded-2xl border border-slate-600 bg-slate-900 shadow-2xl overflow-hidden">
            <div class="flex items-center justify-between gap-3 px-4 py-3 border-b border-slate-700 bg-slate-800/95 shrink-0">
                <h2 id="guest-wb-title" class="text-base font-bold text-white m-0 flex items-center gap-2">
                    <i class="fas fa-chalkboard text-amber-400"></i>
                    الوايت بورد
                </h2>
                <p class="text-[11px] text-slate-400 m-0 hidden sm:block">اكتب هنا على اللوح — وليس على فيديو الاجتماع</p>
                <button type="button" id="guest-wb-close" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-slate-700 hover:bg-slate-600 text-slate-200 text-xs font-medium border border-slate-600">
                    <i class="fas fa-times"></i> إغلاق
                </button>
            </div>
            <div class="relative flex-1 min-h-0 bg-slate-950">
                <div id="guest-excalidraw-root" class="guest-excalidraw-host mx-muallimx-whiteboard" data-lang="ar"></div>
                <div id="guest-excalidraw-loading">جاري تحميل الوايت بورد…</div>
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
        let guestExcMounted = false;
        let guestExcMountPromise = null;
        let guestExcVendorPromise = null;
        let guestWbSync = null;

        function applyGuestWhiteboardAllowed(on) {
            guestWbAllowed = !!on;
            var wrap = document.getElementById('mx-guest-wb-wrap');
            if (wrap) {
                if (guestWbAllowed) wrap.classList.remove('hidden');
                else wrap.classList.add('hidden');
            }
            if (!guestWbAllowed) {
                closeGuestWb();
                if (guestWbSync) guestWbSync.stop();
            }
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
                            viewModeEnabled: false,
                            excalidrawAPI: function(exApi) {
                                window.__mxGuestExcalidrawAPI = exApi;
                            },
                            onChange: function() {
                                if (guestWbSync) guestWbSync.onLocalChange();
                            }
                        };
                        createRoot(root).render(ReactMod.createElement(Lib.Excalidraw, props));
                        guestExcMounted = true;
                        if (loading) loading.style.display = 'none';
                        window.dispatchEvent(new Event('resize'));
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
                onDenied: function() {
                    applyGuestWhiteboardAllowed(false);
                    alert('المعلم أوقف إتاحة الكتابة على الوايت بورد.');
                }
            });
            return guestWbSync;
        }

        function openGuestWb() {
            if (!guestWbAllowed) return;
            var popup = document.getElementById('guest-wb-popup');
            if (!popup) return;
            popup.classList.remove('hidden');
            popup.classList.add('is-open');
            popup.removeAttribute('inert');
            popup.setAttribute('aria-hidden', 'false');
            document.body.style.overflow = 'hidden';
            mountGuestExcalidraw().then(function() {
                var sync = ensureGuestWbSync();
                if (sync) sync.start();
                setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 100);
                setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 400);
            }).catch(function() {});
        }

        function closeGuestWb() {
            var popup = document.getElementById('guest-wb-popup');
            if (!popup) return;
            if (guestWbSync) guestWbSync.pushNow();
            popup.classList.add('hidden');
            popup.classList.remove('is-open');
            popup.setAttribute('inert', '');
            popup.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.getElementById('btn-guest-whiteboard') && document.getElementById('btn-guest-whiteboard').addEventListener('click', openGuestWb);
        document.getElementById('guest-wb-close') && document.getElementById('guest-wb-close').addEventListener('click', closeGuestWb);
        document.getElementById('guest-wb-backdrop') && document.getElementById('guest-wb-backdrop').addEventListener('click', closeGuestWb);

        document.getElementById('btn-join').addEventListener('click', async function() {
            const name = document.getElementById('guest-name').value.trim() || 'ضيف';
            const btn = document.getElementById('btn-join');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin ml-2"></i> جاري التحقق...';

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
                    btn.disabled = false;
                    btn.innerHTML = '<i class="fas fa-video ml-2"></i> انضم الآن';
                    return;
                }
                joinToken = enterData.token;
                applyGuestWhiteboardAllowed(!!enterData.allow_participant_whiteboard);
            } catch (e) {
                alert('تعذر الاتصال بالخادم. حاول مرة أخرى.');
                btn.disabled = false;
                btn.innerHTML = '<i class="fas fa-video ml-2"></i> انضم الآن';
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
                configOverwrite: {
                    prejoinConfig: { enabled: false },
                    prejoinPageEnabled: false,
                    enableLobby: false,
                    requireDisplayName: false,
                    enableWelcomePage: false,
                    disableDeepLinking: true,
                    enableRecording: false,
                    startWithAudioMuted: true,
                    startWithVideoMuted: true,
                    enableNoisyMicDetection: false,
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
                },
                interfaceConfigOverwrite: {
                    APP_NAME: 'Muallimx Classroom',
                    NATIVE_APP_NAME: 'Muallimx Classroom',
                    PROVIDER_NAME: 'Muallimx',
                    JITSI_WATERMARK_LINK: '',
                    HIDE_DEEP_LINKING_LOGO: true,
                    TOOLBAR_BUTTONS: [
                        'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                        'fodeviceselection', 'hangup', 'chat',
                        'raisehand', 'tileview', 'videoquality', 'filmstrip'
                    ],
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
                    if (typeof hbData.allow_participant_whiteboard !== 'undefined') {
                        applyGuestWhiteboardAllowed(!!hbData.allow_participant_whiteboard);
                    }
                } catch (e) {}
            }, 15000);

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

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>انضم LiveKit — {{ $code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    @php
        $mxMeetlineCssFile = public_path('css/classroom-meetline.css');
        $mxMeetlineCss = is_readable($mxMeetlineCssFile) ? file_get_contents($mxMeetlineCssFile) : '';
        $mxLkCssFile = public_path('css/classroom-livekit.css');
        $mxLkCss = is_readable($mxLkCssFile) ? file_get_contents($mxLkCssFile) : '';
        $mxLkRoomJsFile = public_path('js/classroom-livekit-room.js');
        $mxLkRoomJs = is_readable($mxLkRoomJsFile) ? file_get_contents($mxLkRoomJsFile) : '';
    @endphp
    @if($mxMeetlineCss !== '')
    <style id="mx-classroom-meetline-css">{!! $mxMeetlineCss !!}</style>
    @endif
    @if($mxLkCss !== '')
    <style id="mx-classroom-livekit-css">{!! $mxLkCss !!}</style>
    @endif
    <style>
        * { font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; }
        body.join-lobby { background: #0b1220; color: #e2e8f0; }
        #mx-ml-quality { display: inline-flex; align-items: center; gap: 6px; font-size: 11px; color: #525252; padding: 4px 8px; border-radius: 8px; border: 1px solid #e9e9e9; background: #fff; }
        .mx-ml-quality-bars { display: inline-flex; align-items: flex-end; gap: 2px; height: 12px; }
        .mx-ml-quality-bars i { display: block; width: 3px; border-radius: 1px; background: #d4d4d4; }
        .mx-ml-quality-bars i:nth-child(1) { height: 4px; }
        .mx-ml-quality-bars i:nth-child(2) { height: 6px; }
        .mx-ml-quality-bars i:nth-child(3) { height: 9px; }
        .mx-ml-quality-bars i:nth-child(4) { height: 12px; }
        #mx-ml-quality[data-level="1"] .mx-ml-quality-bars i:nth-child(-n+1) { background: #f87171; }
        #mx-ml-quality[data-level="2"] .mx-ml-quality-bars i:nth-child(-n+2) { background: #fb923c; }
        #mx-ml-quality[data-level="3"] .mx-ml-quality-bars i:nth-child(-n+3) { background: #38bdf8; }
        #mx-ml-quality[data-level="4"] .mx-ml-quality-bars i { background: #4ade80; }
        #btn-guest-whiteboard.mx-guest-wb-writable { background: #eef5ff; border-color: #93c5fd; }
    </style>
    @include('partials.mx-classroom-wb-sync')
    @if($mxLkRoomJs !== '')
    <script id="mx-classroom-livekit-room-js">{!! $mxLkRoomJs !!}</script>
    @endif
</head>
<body class="join-lobby">
    <div id="join-screen" class="min-h-screen flex flex-col items-center justify-center p-4">
        <div id="join-form-card" class="w-full max-w-md rounded-2xl bg-slate-800/90 border border-slate-600 p-6 shadow-2xl">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-3xl"></i>
                </div>
                <h1 class="text-xl font-bold">Muallimx · LiveKit</h1>
                <p class="text-slate-400 text-sm mt-1">Classroom Meet.Line</p>
            </div>
            @if($meeting && $meeting->title)
                <p class="text-slate-300 text-sm mb-4 text-center">{{ $meeting->title }}</p>
            @endif
            <p class="text-slate-400 text-xs mb-4 text-center">كود: <span class="font-mono font-bold text-emerald-300 text-lg">{{ $code }}</span></p>
            <label class="block text-sm font-medium text-slate-300 mb-1">اسمك</label>
            <input type="text" id="guest-name" placeholder="أدخل اسمك" class="w-full px-4 py-3 rounded-xl bg-slate-700 border border-slate-600 text-white mb-4">
            <button type="button" id="btn-join" class="w-full px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">انضم الآن</button>
            <p id="join-help" class="text-xs text-slate-500 mt-4 text-center"></p>
        </div>

        <div id="waiting-room-card" class="mx-lk-waiting-card hidden">
            <div class="mx-lk-waiting-pulse"><i class="fas fa-hourglass-half text-2xl"></i></div>
            <h2 class="text-lg font-bold text-white m-0">غرفة الانتظار</h2>
            <p class="text-slate-300 text-sm mt-2 mb-1" id="waiting-room-title">{{ $meeting->title ?? ('غرفة ' . $code) }}</p>
            <p class="text-slate-400 text-xs leading-relaxed" id="waiting-room-msg">انتظر حتى يبدأ المضيف الجلسة أو يقبلك للدخول…</p>
            <p class="text-sky-300 text-xs mt-4 font-semibold" id="waiting-room-tick">جاري الانتظار…</p>
            <button type="button" id="btn-waiting-cancel" class="mt-5 px-4 py-2 rounded-xl border border-slate-600 text-slate-300 text-sm hover:bg-slate-800">إلغاء</button>
        </div>
    </div>

    <div id="meeting-screen" class="hidden">
        <div class="mx-ml-shell">
            <header class="mx-ml-top">
                <div class="flex items-center gap-2 min-w-0 flex-1">
                    <div class="min-w-0">
                        <p class="text-[11px] font-semibold text-[#93c5fd] m-0">LiveKit · طالب</p>
                        <h1 class="mx-ml-title truncate">{{ $meeting->title ?? $code }}</h1>
                    </div>
                    <span id="mx-ml-quality" title="جودة الاتصال">
                        <span class="mx-ml-quality-bars" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                        <span id="mx-ml-quality-label">—</span>
                    </span>
                    <span class="text-xs">متصل: <strong id="mx-lk-count">1</strong></span>
                    <span id="lk-status" class="text-xs">…</span>
                </div>
                <button type="button" id="btn-guest-leave" class="mx-ml-end-btn">مغادرة</button>
            </header>

            <div class="mx-ml-main-lk">
                <div id="lk-stage"></div>
                <aside id="mx-lk-people-panel" class="mx-lk-side hidden" dir="rtl">
                    <div class="mx-lk-side-head"><span>المشاركون</span></div>
                    <div class="mx-lk-side-body" id="mx-lk-people-list"></div>
                </aside>
                <aside id="mx-lk-chat-panel" class="mx-lk-side hidden" dir="rtl">
                    <div class="mx-lk-side-head"><span>الدردشة</span></div>
                    <div class="mx-lk-side-body" id="mx-lk-chat-log"></div>
                    <div class="mx-lk-chat-compose">
                        <input type="text" id="mx-lk-chat-input" placeholder="اكتب رسالة…" maxlength="500">
                        <button type="button" id="mx-lk-chat-send">إرسال</button>
                    </div>
                </aside>
            </div>

            <div class="mx-ml-dock">
                <div class="flex flex-wrap items-center justify-center gap-1.5">
                    <button type="button" id="mx-ml-btn-mic" class="mx-ml-icon-btn" title="ميكروفون"><i class="fas fa-microphone-slash text-[#fd0000]" id="mx-ml-mic-icon"></i></button>
                    <button type="button" id="mx-ml-btn-noise" class="mx-ml-icon-btn is-active" title="عزل الضوضاء" aria-pressed="true"><i class="fas fa-ear-listen text-[#0065fd]"></i></button>
                    <button type="button" id="mx-ml-btn-cam" class="mx-ml-icon-btn" title="كاميرا"><i class="fas fa-video-slash text-[#fd0000]" id="mx-ml-cam-icon"></i></button>
                    <span class="mx-ml-dock-sep" aria-hidden="true"></span>
                    <button type="button" id="mx-ml-btn-share" class="mx-ml-icon-btn" title="شير"><i class="fas fa-desktop" id="mx-ml-share-icon"></i></button>
                    <button type="button" id="mx-ml-btn-annotate" class="mx-ml-icon-btn" title="اكتب على الشاشة" disabled><i class="fas fa-pen-fancy"></i></button>
                    <button type="button" id="mx-ml-btn-laser" class="mx-ml-icon-btn" title="مؤشر ليزر" disabled><i class="fas fa-location-crosshairs"></i></button>
                    <button type="button" id="btn-guest-whiteboard" class="mx-ml-icon-btn" title="السبورة"><i class="fas fa-pen"></i></button>
                    <div id="mx-ml-react-wrap">
                        <button type="button" id="mx-ml-btn-react" class="mx-ml-icon-btn" title="رفع اليد / تفاعل"><i class="fas fa-hand-paper"></i></button>
                    </div>
                    <span class="mx-ml-dock-sep" aria-hidden="true"></span>
                    <button type="button" id="mx-ml-btn-tile" class="mx-ml-icon-btn is-active" title="شبكة/متحدث"><i class="fas fa-th-large"></i></button>
                    <button type="button" id="mx-ml-btn-focus" class="mx-ml-icon-btn" title="تركيز"><i class="fas fa-compress"></i></button>
                    <button type="button" id="mx-ml-btn-pip" class="mx-ml-icon-btn" title="نافذة المشاركين العائمة (Always-on-top)" aria-pressed="false" aria-label="فتح نافذة المشاركين العائمة"><i class="fas fa-window-restore"></i></button>
                    <button type="button" id="mx-ml-btn-people" class="mx-ml-icon-btn" title="مشاركون"><i class="fas fa-users"></i></button>
                    <button type="button" id="mx-ml-btn-chat" class="mx-ml-icon-btn" title="دردشة"><i class="fas fa-comments"></i></button>
                    <button type="button" id="mx-ml-btn-bg" class="mx-ml-icon-btn" title="خلفية"><i class="fas fa-image"></i></button>
                </div>
            </div>
        </div>
    </div>

    <div id="mx-share-ann-hold" hidden>
    @include('partials.mx-share-annotation-overlay', [
        'mxAnnRole' => 'classroom_guest_emit',
        'mxAnnPostUrl' => route('classroom.join.share-annotation', $code),
    ])
    </div>
    <div id="mx-lk-wb-popup" aria-hidden="true">
        <div class="mx-lk-wb-card">
            <div class="flex items-center justify-between gap-2 px-3 py-2 border-b border-slate-700 text-white">
                <strong class="text-sm">السبورة</strong>
                <button type="button" id="mx-lk-wb-close" class="px-3 py-1 rounded-lg bg-slate-700 text-sm">إغلاق</button>
            </div>
            <div id="mx-lk-wb-host" class="mx-muallimx-whiteboard"></div>
        </div>
    </div>
    <div id="mx-lk-toast" role="status"></div>
    <div id="mx-share-float" aria-hidden="true">
        <button type="button" class="mx-sf-btn" id="mx-sf-mic" title="ميك"><i class="fas fa-microphone"></i></button>
        <button type="button" class="mx-sf-btn is-active" id="mx-sf-noise" title="عزل"><i class="fas fa-ear-listen"></i></button>
        <button type="button" class="mx-sf-btn" id="mx-sf-cam" title="كاميرا"><i class="fas fa-video"></i></button>
        <button type="button" class="mx-sf-btn" id="mx-sf-tile" title="شبكة"><i class="fas fa-th-large"></i></button>
        <button type="button" class="mx-sf-btn" id="mx-sf-people" title="نافذة المشاركين العائمة" aria-pressed="false" aria-label="فتح نافذة المشاركين"><i class="fas fa-users"></i></button>
        <button type="button" class="mx-sf-btn is-danger" id="mx-sf-stop-share" title="إيقاف الشير"><i class="fas fa-desktop"></i></button>
    </div>

@php
    $mxGuestPermDefaults = [
        'allow_participant_whiteboard' => false,
        'allow_participant_screen_share' => false,
        'allow_participant_chat' => true,
        'allow_participant_raise_hand' => true,
        'allow_participant_virtual_background' => true,
    ];
    $mxGuestPerms = $meeting
        ? ($meeting->guestPermissionsPayload() ?: $mxGuestPermDefaults)
        : $mxGuestPermDefaults;
@endphp
<script type="module">
import * as LivekitClient from 'https://cdn.jsdelivr.net/npm/livekit-client@2.9.8/dist/livekit-client.esm.mjs';

const code = @json($code);
const csrf = document.querySelector('meta[name="csrf-token"]').content;
const helpEl = document.getElementById('join-help');
let joinToken = '';
let guestWbApi = null;
let guestWbSync = null;
let lkApi = null;
let currentPerms = @json($mxGuestPerms);

function setHelp(msg, isErr) {
    helpEl.textContent = msg || '';
    helpEl.className = 'text-xs mt-4 text-center ' + (isErr ? 'text-rose-400' : 'text-slate-500');
}

function loadScript(src) {
    return new Promise((resolve, reject) => {
        const s = document.createElement('script');
        s.src = src;
        s.onload = resolve;
        s.onerror = reject;
        document.head.appendChild(s);
    });
}

async function ensureWb() {
    if (guestWbApi) return guestWbApi;
    await loadScript('https://unpkg.com/react@18.2.0/umd/react.production.min.js');
    await loadScript('https://unpkg.com/react-dom@18.2.0/umd/react-dom.production.min.js');
    await loadScript('https://unpkg.com/@excalidraw/excalidraw@0.17.6/dist/excalidraw.production.min.js');
    const host = document.getElementById('mx-lk-wb-host');
    const root = window.ReactDOM.createRoot(host);
    root.render(window.React.createElement(window.ExcalidrawLib.Excalidraw, {
        langCode: 'ar',
        theme: 'dark',
        viewModeEnabled: !currentPerms.allow_participant_whiteboard,
        UIOptions: { canvasActions: { loadScene: false, export: false } },
        excalidrawAPI: (api) => { guestWbApi = api; },
    }));
    await new Promise((r) => setTimeout(r, 250));
    if (window.MxClassroomWbSync && !guestWbSync) {
        guestWbSync = window.MxClassroomWbSync.attach({
            getApi: () => guestWbApi,
            getUrl: @json(route('classroom.join.whiteboard-scene', $code)),
            postUrl: @json(route('classroom.join.whiteboard-scene.push', $code)),
            csrfToken: csrf,
            getExtraBody: () => ({ token: joinToken }),
            canWrite: () => !!currentPerms.allow_participant_whiteboard,
            isActive: () => document.getElementById('mx-lk-wb-popup').classList.contains('is-open'),
        });
    }
    return guestWbApi;
}

document.getElementById('btn-guest-whiteboard')?.addEventListener('click', async () => {
    const popup = document.getElementById('mx-lk-wb-popup');
    popup.classList.add('is-open');
    await ensureWb();
    guestWbSync?.setActive?.(true);
});
document.getElementById('mx-lk-wb-close')?.addEventListener('click', () => {
    guestWbSync?.pushNow?.();
    guestWbSync?.setActive?.(false);
    document.getElementById('mx-lk-wb-popup').classList.remove('is-open');
});

let waitingTimer = null;
let waitingDots = 0;
let waitingToken = null;
let guestDisplayName = '';

function waitingMessageForReason(reason, fallback) {
    if (reason === 'host_admit_pending') return 'بانتظار قبول المضيف للدخول…';
    if (reason === 'meeting_not_started') return 'المعلم لم يبدأ الجلسة بعد. سنُدخلك تلقائياً فور البدء…';
    return fallback || 'جاري الانتظار…';
}

function showWaitingRoom(msg, reason) {
    document.getElementById('join-form-card')?.classList.add('hidden');
    const card = document.getElementById('waiting-room-card');
    card?.classList.remove('hidden');
    const el = document.getElementById('waiting-room-msg');
    if (el) el.textContent = waitingMessageForReason(reason, msg);
}

function hideWaitingRoom() {
    document.getElementById('waiting-room-card')?.classList.add('hidden');
    document.getElementById('join-form-card')?.classList.remove('hidden');
    if (waitingTimer) {
        clearInterval(waitingTimer);
        waitingTimer = null;
    }
    waitingToken = null;
}

async function cancelWaitingRequest() {
    if (waitingToken) {
        try {
            await fetch(`/classroom/join/${code}/waiting-cancel`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ waiting_token: waitingToken }),
            });
        } catch (_) {}
    }
    hideWaitingRoom();
    const btn = document.getElementById('btn-join');
    if (btn) {
        btn.disabled = false;
        btn.textContent = 'انضم الآن';
    }
    setHelp('', false);
}

document.getElementById('btn-waiting-cancel')?.addEventListener('click', () => {
    cancelWaitingRequest();
});

async function enterMeeting(name, opts = {}) {
    const body = { display_name: name };
    if (opts.waitingToken || waitingToken) {
        body.waiting_token = opts.waitingToken || waitingToken;
    }
    const enterResp = await fetch(`/classroom/join/${code}/enter`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            Accept: 'application/json',
        },
        body: JSON.stringify(body),
    });
    const enterData = await enterResp.json();
    if (enterResp.ok && enterData.ok && enterData.livekit?.token) {
        return { waiting: false, enterData };
    }
    if (enterData.denied) {
        throw new Error(enterData.message || 'رفض المضيف طلب دخولك.');
    }
    if (enterData.waiting || enterResp.status === 422) {
        if (enterData.waiting_token) waitingToken = enterData.waiting_token;
        return {
            waiting: true,
            reason: enterData.reason || (enterData.waiting_token ? 'host_admit_pending' : 'meeting_not_started'),
            message: enterData.message,
        };
    }
    throw new Error(enterData.message || 'لا يمكن الانضمام');
}

async function pollWaitingStatus() {
    if (!waitingToken) return { waiting: true, reason: 'meeting_not_started' };
    const resp = await fetch(`/classroom/join/${code}/waiting-status`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrf,
            Accept: 'application/json',
        },
        body: JSON.stringify({ waiting_token: waitingToken }),
    });
    const data = await resp.json();
    if (resp.ok && data.ok && data.livekit?.token) {
        return { waiting: false, enterData: data };
    }
    if (data.denied) {
        throw new Error(data.message || 'رفض المضيف طلب دخولك.');
    }
    if (data.cancelled) {
        throw new Error(data.message || 'تم إلغاء طلب الانتظار.');
    }
    if (data.ended) {
        throw new Error(data.message || 'انتهى الاجتماع.');
    }
    return {
        waiting: true,
        reason: data.reason || 'host_admit_pending',
        message: data.message,
    };
}

async function bootGuestMeeting(enterData, name) {
    joinToken = enterData.token;
    if (typeof window.__mxShareAnnSetGuestToken === 'function') {
        window.__mxShareAnnSetGuestToken(joinToken);
    }
    currentPerms = Object.assign(currentPerms, {
        allow_participant_whiteboard: !!enterData.allow_participant_whiteboard,
        allow_participant_screen_share: !!enterData.allow_participant_screen_share,
        allow_participant_chat: !!enterData.allow_participant_chat,
        allow_participant_raise_hand: !!enterData.allow_participant_raise_hand,
        allow_participant_virtual_background: !!enterData.allow_participant_virtual_background,
    });

    document.getElementById('join-screen').classList.add('hidden');
    document.getElementById('meeting-screen').classList.remove('hidden');
    document.body.className = 'mx-meetline mx-lk-room';

    lkApi = await window.MxLiveKitClassroom.boot(LivekitClient, {
        isHost: false,
        url: enterData.livekit.url,
        token: enterData.livekit.token,
        csrfToken: csrf,
        meetingCode: code,
        permissions: currentPerms,
        onPermissions: (p) => {
            currentPerms = Object.assign(currentPerms, p || {});
            document.getElementById('btn-guest-whiteboard')?.classList.toggle(
                'mx-guest-wb-writable',
                !!currentPerms.allow_participant_whiteboard
            );
        },
        onMeetingEnded: () => { location.reload(); },
        onKicked: () => { location.reload(); },
    });

    setInterval(async () => {
        if (!joinToken) return;
        try {
            const r = await fetch(`/classroom/join/${code}/heartbeat`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ token: joinToken }),
            });
            const d = await r.json();
            if (d.ended) {
                location.reload();
                return;
            }
            if (d.ok) {
                const next = {
                    allow_participant_whiteboard: !!d.allow_participant_whiteboard,
                    allow_participant_screen_share: !!d.allow_participant_screen_share,
                    allow_participant_chat: !!d.allow_participant_chat,
                    allow_participant_raise_hand: !!d.allow_participant_raise_hand,
                    allow_participant_virtual_background: !!d.allow_participant_virtual_background,
                };
                lkApi.applyPermissions(next);
            }
        } catch (_) {}
    }, 12000);
}

function startWaitingPoll(name) {
    guestDisplayName = name;
    showWaitingRoom('', waitingToken ? 'host_admit_pending' : 'meeting_not_started');
    const tick = document.getElementById('waiting-room-tick');
    if (waitingTimer) clearInterval(waitingTimer);
    waitingTimer = setInterval(async () => {
        waitingDots = (waitingDots + 1) % 4;
        if (tick) tick.textContent = 'جاري الانتظار' + '.'.repeat(waitingDots + 1);
        try {
            let result;
            if (waitingToken) {
                result = await pollWaitingStatus();
            } else {
                result = await enterMeeting(guestDisplayName || name);
            }
            if (result.waiting) {
                showWaitingRoom(result.message, result.reason);
                return;
            }
            hideWaitingRoom();
            await bootGuestMeeting(result.enterData, guestDisplayName || name);
        } catch (e) {
            hideWaitingRoom();
            setHelp(e.message || String(e), true);
            const btn = document.getElementById('btn-join');
            if (btn) { btn.disabled = false; btn.textContent = 'انضم الآن'; }
        }
    }, 3000);
}

document.getElementById('btn-join').addEventListener('click', async () => {
    const name = document.getElementById('guest-name').value.trim() || 'ضيف';
    guestDisplayName = name;
    const btn = document.getElementById('btn-join');
    btn.disabled = true;
    btn.textContent = 'جاري الانضمام…';
    setHelp('جاري التحقق…', false);
    try {
        const result = await enterMeeting(name);
        if (result.waiting) {
            setHelp('', false);
            startWaitingPoll(name);
            return;
        }
        await bootGuestMeeting(result.enterData, name);
    } catch (e) {
        setHelp(e.message || String(e), true);
        btn.disabled = false;
        btn.textContent = 'انضم الآن';
    }
});

document.getElementById('btn-guest-leave')?.addEventListener('click', async () => {
    try {
        if (joinToken) {
            await fetch(`/classroom/join/${code}/leave`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    Accept: 'application/json',
                },
                body: JSON.stringify({ token: joinToken }),
            });
        }
    } catch (_) {}
    try { lkApi?.disconnect?.(); } catch (_) {}
    location.reload();
});
</script>
</body>
</html>

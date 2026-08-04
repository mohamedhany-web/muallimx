<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Muallimx Classroom — {{ $meeting->title ?: $meeting->code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&family=Poppins:wght@500;600&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    @php
        $mxMeetlineCssFile = public_path('css/classroom-meetline.css');
        $mxMeetlineCss = is_readable($mxMeetlineCssFile) ? file_get_contents($mxMeetlineCssFile) : '';
        $mxLkCssFile = public_path('css/classroom-livekit.css');
        $mxLkCss = is_readable($mxLkCssFile) ? file_get_contents($mxLkCssFile) : '';
        $mxLkRoomJsFile = public_path('js/classroom-livekit-room.js');
        $mxLkRoomJs = is_readable($mxLkRoomJsFile) ? file_get_contents($mxLkRoomJsFile) : '';
        $mxWbToolsJsFile = public_path('js/classroom-wb-tools.js');
        $mxWbToolsJs = is_readable($mxWbToolsJsFile) ? file_get_contents($mxWbToolsJsFile) : '';
        $mxVbgJsFile = public_path('js/classroom-virtual-background.js');
        $mxVbgJs = is_readable($mxVbgJsFile) ? file_get_contents($mxVbgJsFile) : '';
        $mxNoiseJsFile = public_path('js/classroom-noise-isolation.js');
        $mxNoiseJs = is_readable($mxNoiseJsFile) ? file_get_contents($mxNoiseJsFile) : '';
        $rp = ($useInstructorRoutes ?? false) ? 'instructor.' : 'student.';
        if ($useInstructorRoutes ?? false) {
            $roomExitUrl = $meeting->consultation_request_id
                ? route('instructor.consultations.show', $meeting->consultation_request_id)
                : route('instructor.consultations.index');
        } else {
            $roomExitUrl = route('student.classroom.index');
        }
    @endphp
    @if($mxMeetlineCss !== '')
    <style id="mx-classroom-meetline-css">{!! $mxMeetlineCss !!}</style>
    @endif
    @if($mxLkCss !== '')
    <style id="mx-classroom-livekit-css">{!! $mxLkCss !!}</style>
    @endif
    <style>
        * { font-family: 'IBM Plex Sans Arabic', 'Poppins', system-ui, sans-serif; }
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
    </style>
    @include('partials.mx-classroom-wb-sync')
    @if($mxLkRoomJs !== '')
    <script id="mx-classroom-livekit-room-js">{!! $mxLkRoomJs !!}</script>
    @endif
    @if($mxWbToolsJs !== '')
    <script id="mx-classroom-wb-tools-js">{!! $mxWbToolsJs !!}</script>
    @endif
    @if($mxVbgJs !== '')
    <script id="mx-classroom-vbg-js">{!! $mxVbgJs !!}</script>
    @endif
    @if($mxNoiseJs !== '')
    <script id="mx-classroom-noise-js">{!! $mxNoiseJs !!}</script>
    @endif
</head>
<body class="mx-meetline mx-lk-room">
<div class="mx-ml-shell">
    <header class="mx-ml-top">
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <a href="{{ $roomExitUrl }}" class="shrink-0 inline-flex items-center justify-center w-9 h-9 rounded-lg border border-[#e9e9e9] bg-white text-[#0065fd] hover:bg-[#eef5ff]" title="خروج">
                <i class="fas fa-arrow-right"></i>
            </a>
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-[#93c5fd] m-0">LiveKit · Meet.Line</p>
                <h1 class="mx-ml-title truncate">{{ $meeting->title ?: ('غرفة ' . $meeting->code) }}</h1>
            </div>
            <span id="mx-live-rec-badge" class="hidden mx-ml-record-chip"><i class="fas fa-circle text-[#fd0000] text-[8px]"></i> REC</span>
            <span id="mx-ml-quality" title="جودة الاتصال">
                <span class="mx-ml-quality-bars" aria-hidden="true"><i></i><i></i><i></i><i></i></span>
                <span id="mx-ml-quality-label">—</span>
            </span>
            <span class="text-xs">متصل: <strong id="mx-lk-count">1</strong></span>
            <span id="lk-status" class="text-xs">…</span>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <button type="button" id="btn-classroom-copy-join" class="mx-ml-code-pill" title="نسخ رابط الانضمام" data-join-url="{{ url('classroom/join/' . $meeting->code) }}">
                <i class="fas fa-link text-[10px] me-1"></i>
                <span class="font-mono">{{ $meeting->code }}</span>
            </button>
            <form method="POST" action="{{ route($rp.'classroom.end', $meeting) }}" class="inline shrink-0" id="mx-end-meeting-form" onsubmit="return confirm('إنهاء الاجتماع للجميع؟');">
                @csrf
                <button type="submit" id="mx-end-meeting-btn" class="mx-ml-end-btn">إنهاء</button>
            </form>
        </div>
    </header>

    <div class="mx-ml-main-lk">
        <div id="lk-stage"></div>

        <aside id="mx-lk-people-panel" class="mx-lk-side hidden" dir="rtl">
            <div class="mx-lk-side-head">
                <span>المشاركون</span>
                <button type="button" id="mx-lk-mute-all">كتم الجميع</button>
            </div>
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
        <div id="mx-classroom-toolbar-inner" class="flex w-full flex-wrap items-center justify-center gap-1.5 md:gap-2">
            <button type="button" id="mx-ml-btn-mic" class="mx-ml-icon-btn" title="ميكروفون" aria-pressed="false">
                <i class="fas fa-microphone-slash text-[#fd0000]" id="mx-ml-mic-icon"></i>
            </button>
            <button type="button" id="mx-ml-btn-noise" class="mx-ml-icon-btn" title="عزل الضوضاء" aria-pressed="false">
                <i class="fas fa-ear-listen text-[#171717]"></i>
            </button>
            <button type="button" id="mx-ml-btn-cam" class="mx-ml-icon-btn" title="الكاميرا" aria-pressed="false">
                <i class="fas fa-video-slash text-[#fd0000]" id="mx-ml-cam-icon"></i>
            </button>
            <button type="button" id="mx-ml-btn-bg" class="mx-ml-icon-btn" title="خلفية الكاميرا" aria-expanded="false">
                <i class="fas fa-image text-[#171717]"></i>
            </button>
            <span class="mx-ml-dock-sep" aria-hidden="true"></span>
            <button type="button" id="mx-ml-btn-share" class="mx-ml-icon-btn" title="مشاركة الشاشة" aria-pressed="false">
                <i class="fas fa-desktop text-[#171717]" id="mx-ml-share-icon"></i>
            </button>
            <button type="button" id="mx-ml-btn-annotate" class="mx-ml-icon-btn" title="كتابة الطلاب على الشاشة" disabled>
                <i class="fas fa-pen-fancy text-[#171717]"></i>
            </button>
            <button type="button" id="mx-ml-btn-laser" class="mx-ml-icon-btn" title="مؤشر ليزر على الشاشة" disabled>
                <i class="fas fa-location-crosshairs text-[#171717]"></i>
            </button>
            <button type="button" id="btn-wb-popup-open" class="mx-ml-icon-btn" title="السبورة" aria-pressed="false">
                <i class="fas fa-pen text-[#171717]"></i>
            </button>
            <button type="button" id="mx-ml-btn-react" class="mx-ml-icon-btn" title="رفع اليد">
                <i class="fas fa-hand-paper text-[#171717]"></i>
            </button>
            <span class="mx-ml-dock-sep" aria-hidden="true"></span>
            <button type="button" id="mx-ml-btn-people" class="mx-ml-icon-btn" title="المشاركون">
                <i class="fas fa-users text-[#171717]"></i>
            </button>
            <button type="button" id="mx-ml-btn-chat" class="mx-ml-icon-btn" title="الدردشة">
                <i class="fas fa-comments text-[#171717]"></i>
            </button>
            <span class="mx-ml-dock-sep" aria-hidden="true"></span>
            <div class="relative inline-flex" id="mx-guest-perms-wrap">
                <button type="button" id="mx-guest-perms-btn" class="classroom-room-toolbar-btn" title="صلاحيات الطلاب" aria-expanded="false" aria-controls="mx-guest-perms-panel">
                    <i class="fas fa-user-shield text-[12px] text-[#0065fd]"></i>
                    <span class="hidden sm:inline text-[11px]">صلاحيات</span>
                </button>
                <div id="mx-guest-perms-panel" class="hidden absolute bottom-full mb-2 end-0 z-40 w-[17.5rem] rounded-xl border border-[#e9e9e9] bg-white shadow-xl p-3 text-start" role="menu" dir="rtl">
                    <p class="text-[11px] font-bold text-[#171717] mb-2">ما يُسمح للطلاب</p>
                    <div class="space-y-2 text-[12px] text-[#171717]">
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="rounded border-[#e9e9e9] text-[#0065fd]" data-perm-key="allow_participant_whiteboard" {{ $meeting->allowsParticipantWhiteboard() ? 'checked' : '' }}><span>السبورة + الكتابة على الشاشة</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="rounded border-[#e9e9e9] text-[#0065fd]" data-perm-key="allow_participant_screen_share" {{ $meeting->allowsParticipantScreenShare() ? 'checked' : '' }}><span>شير الشاشة</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="rounded border-[#e9e9e9] text-[#0065fd]" data-perm-key="allow_participant_chat" {{ $meeting->allowsParticipantChat() ? 'checked' : '' }}><span>الدردشة</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="rounded border-[#e9e9e9] text-[#0065fd]" data-perm-key="allow_participant_raise_hand" {{ $meeting->allowsParticipantRaiseHand() ? 'checked' : '' }}><span>رفع اليد</span></label>
                        <label class="flex items-center gap-2 cursor-pointer"><input type="checkbox" class="rounded border-[#e9e9e9] text-[#0065fd]" data-perm-key="allow_participant_virtual_background" {{ $meeting->allowsParticipantVirtualBackground() ? 'checked' : '' }}><span>خلفية الكاميرا</span></label>
                    </div>
                </div>
            </div>
            <div class="relative inline-flex items-center gap-1" id="mx-record-dd-wrap">
                <div id="mx-record-idle-wrap" class="inline-flex items-center overflow-hidden rounded-[12px] border border-[#e9e9e9] bg-white">
                    <button type="button" id="btn-record-menu" class="classroom-room-toolbar-btn border-0 rounded-none" aria-expanded="false">
                        <i class="fas fa-circle-dot text-[#fd0000] text-[12px]"></i>
                        <span class="truncate max-w-[7rem]">تسجيل</span>
                        <i class="fas fa-chevron-down text-[9px] text-[#717171]"></i>
                    </button>
                </div>
                <button type="button" id="btn-record-stop" class="hidden classroom-room-toolbar-btn bg-[#fd0000] text-white border-[#c50000]">إيقاف</button>
                <div id="mx-record-dd-panel" class="hidden w-[min(100vw-1.5rem,20rem)] rounded-xl border border-[#e9e9e9] bg-white overflow-hidden" role="menu">
                    <div class="mx-ml-record-menu-head"><strong>تسجيل محلي</strong><p>يُرفع إلى السحابة بعد الإيقاف (بدون Egress).</p></div>
                    <button type="button" role="menuitem" data-mx-rec-mode="lecture" class="mx-ml-record-menu-item"><span class="mx-ml-rec-title">تسجيل الجلسة</span><span class="mx-ml-rec-desc">فيديو/صوت محلي</span></button>
                    <button type="button" role="menuitem" data-mx-rec-mode="report" class="mx-ml-record-menu-item"><span class="mx-ml-rec-title">تقرير صوتي فقط</span><span class="mx-ml-rec-desc">ميكروفون للتقرير</span></button>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="mx-share-ann-hold" hidden>
@include('partials.mx-share-annotation-overlay', [
    'mxAnnRole' => 'viewer_poll',
    'mxAnnPollUrl' => route($rp.'classroom.share-annotations', $meeting),
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

<script type="module">
import * as LivekitClient from 'https://cdn.jsdelivr.net/npm/livekit-client@2.9.8/dist/livekit-client.esm.mjs';

const csrf = document.querySelector('meta[name="csrf-token"]').content;
const permsBtn = document.getElementById('mx-guest-perms-btn');
const permsPanel = document.getElementById('mx-guest-perms-panel');
if (permsBtn && permsPanel) {
    permsBtn.addEventListener('click', () => {
        permsPanel.classList.toggle('hidden');
        permsBtn.setAttribute('aria-expanded', permsPanel.classList.contains('hidden') ? 'false' : 'true');
    });
}

let hostWbApi = null;
let hostWbSync = null;
const wbPopup = document.getElementById('mx-lk-wb-popup');
const wbHost = document.getElementById('mx-lk-wb-host');

async function ensureWb() {
    if (hostWbApi) return hostWbApi;
    if (!window.ExcalidrawLib && !window.React) {
        await loadScript('https://unpkg.com/react@18.2.0/umd/react.production.min.js');
        await loadScript('https://unpkg.com/react-dom@18.2.0/umd/react-dom.production.min.js');
        await loadScript('https://unpkg.com/@excalidraw/excalidraw@0.17.6/dist/excalidraw.production.min.js');
    }
    const root = window.ReactDOM.createRoot(wbHost);
    let apiRef = null;
    root.render(window.React.createElement(window.ExcalidrawLib.Excalidraw, {
        langCode: 'ar',
        theme: 'dark',
        UIOptions: { canvasActions: { loadScene: false, export: false, saveAsImage: true } },
        excalidrawAPI: (api) => { apiRef = api; hostWbApi = api; },
    }));
    await new Promise((r) => setTimeout(r, 200));
    if (window.MxClassroomWbSync && !hostWbSync) {
        hostWbSync = window.MxClassroomWbSync.attach({
            getApi: () => hostWbApi,
            getUrl: @json(route($rp.'classroom.whiteboard-scene', $meeting)),
            postUrl: @json(route($rp.'classroom.whiteboard-scene.push', $meeting)),
            csrfToken: csrf,
            canWrite: () => true,
            isActive: () => wbPopup.classList.contains('is-open'),
        });
    }
    return apiRef;
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
document.getElementById('btn-wb-popup-open')?.addEventListener('click', async () => {
    wbPopup.classList.add('is-open');
    wbPopup.setAttribute('aria-hidden', 'false');
    await ensureWb();
    hostWbSync?.setActive?.(true);
});
document.getElementById('mx-lk-wb-close')?.addEventListener('click', () => {
    hostWbSync?.pushNow?.();
    hostWbSync?.setActive?.(false);
    wbPopup.classList.remove('is-open');
    wbPopup.setAttribute('aria-hidden', 'true');
});

const api = await window.MxLiveKitClassroom.boot(LivekitClient, {
    isHost: true,
    url: @json($livekitUrl),
    token: @json($livekitToken),
    csrfToken: csrf,
    meetingCode: @json($meeting->code),
    joinUrl: @json(url('classroom/join/'.$meeting->code)),
    exitUrl: @json($roomExitUrl),
    permissionsUrl: @json(route($rp.'classroom.participant-whiteboard', $meeting)),
    permissions: @json($meeting->guestPermissionsPayload()),
    presignUrl: @json(route($rp.'classroom.recording.presign', $meeting)),
    completeUrl: @json(route($rp.'classroom.recording.complete', $meeting)),
    audioPresignUrl: @json(route($rp.'classroom.recording-audio.presign', $meeting)),
    audioCompleteUrl: @json(route($rp.'classroom.recording-audio.complete', $meeting)),
    uploadTabUrl: @json(route($rp.'classroom.recording.upload-tab', $meeting)),
    onMeetingEnded: () => { location.href = @json($roomExitUrl); },
});

// Best-effort: browser noise constraints + VBG hooks if scripts present
document.getElementById('mx-ml-btn-noise')?.addEventListener('click', async () => {
    try {
        const track = api.getLocalAudioTrack?.()?.mediaStreamTrack;
        if (!track || !track.applyConstraints) return;
        const cur = track.getConstraints?.() || {};
        const on = !(cur.noiseSuppression === true);
        await track.applyConstraints({ noiseSuppression: on, echoCancellation: true, autoGainControl: true });
        document.getElementById('mx-ml-btn-noise')?.classList.toggle('is-active', on);
    } catch (e) {}
});
document.getElementById('mx-ml-btn-bg')?.addEventListener('click', () => {
    alert('خلفيات الكاميرا المتقدمة تُفعَّل تدريجياً على LiveKit. استخدم تمويه النظام من إعدادات المتصفح إن لزم.');
});
</script>
</body>
</html>

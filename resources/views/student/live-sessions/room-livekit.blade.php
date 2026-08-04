<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>انضم للبث — {{ $liveSession->title }}</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    @php
        $mxMeetlineCss = is_readable(public_path('css/classroom-meetline.css')) ? file_get_contents(public_path('css/classroom-meetline.css')) : '';
        $mxLkCss = is_readable(public_path('css/classroom-livekit.css')) ? file_get_contents(public_path('css/classroom-livekit.css')) : '';
        $mxLkRoomJs = is_readable(public_path('js/classroom-livekit-room.js')) ? file_get_contents(public_path('js/classroom-livekit-room.js')) : '';
        $exitUrl = route('student.live-sessions.leave', $liveSession);
    @endphp
    @if($mxMeetlineCss !== '')<style id="mx-classroom-meetline-css">{!! $mxMeetlineCss !!}</style>@endif
    @if($mxLkCss !== '')<style id="mx-classroom-livekit-css">{!! $mxLkCss !!}</style>@endif
    <style>*{font-family:'IBM Plex Sans Arabic',system-ui,sans-serif}</style>
    @if($mxLkRoomJs !== '')<script id="mx-classroom-livekit-room-js">{!! $mxLkRoomJs !!}</script>@endif
</head>
<body class="mx-meetline mx-lk-room">
<div class="mx-ml-shell">
    <header class="mx-ml-top">
        <div class="flex items-center gap-2 min-w-0 flex-1">
            <div class="min-w-0">
                <p class="text-[11px] font-semibold text-[#93c5fd] m-0">LiveKit · طالب</p>
                <h1 class="mx-ml-title truncate">{{ $liveSession->title }}</h1>
            </div>
            <span id="mx-ml-quality" title="جودة"><span class="mx-ml-quality-bars"><i></i><i></i><i></i><i></i></span><span id="mx-ml-quality-label">—</span></span>
            <span class="text-xs">متصل: <strong id="mx-lk-count">1</strong></span>
            <span id="lk-status" class="text-xs">…</span>
        </div>
        <form method="POST" action="{{ route('student.live-sessions.leave', $liveSession) }}">@csrf<button class="mx-ml-end-btn" type="submit">مغادرة</button></form>
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
                <input type="text" id="mx-lk-chat-input" placeholder="رسالة…" maxlength="500">
                <button type="button" id="mx-lk-chat-send">إرسال</button>
            </div>
        </aside>
    </div>
    <div class="mx-ml-dock">
        <div class="flex flex-wrap items-center justify-center gap-1.5">
            <button type="button" id="mx-ml-btn-mic" class="mx-ml-icon-btn"><i class="fas fa-microphone-slash text-[#fd0000]" id="mx-ml-mic-icon"></i></button>
            <button type="button" id="mx-ml-btn-cam" class="mx-ml-icon-btn"><i class="fas fa-video-slash text-[#fd0000]" id="mx-ml-cam-icon"></i></button>
            <button type="button" id="mx-ml-btn-share" class="mx-ml-icon-btn" {{ $liveSession->allow_screen_share ? '' : 'disabled' }}><i class="fas fa-desktop" id="mx-ml-share-icon"></i></button>
            <button type="button" id="mx-ml-btn-react" class="mx-ml-icon-btn"><i class="fas fa-hand-paper"></i></button>
            <button type="button" id="mx-ml-btn-people" class="mx-ml-icon-btn"><i class="fas fa-users"></i></button>
            <button type="button" id="mx-ml-btn-chat" class="mx-ml-icon-btn" {{ $liveSession->allow_chat ? '' : 'disabled' }}><i class="fas fa-comments"></i></button>
        </div>
    </div>
</div>
<div id="mx-lk-toast" role="status"></div>
<script type="module">
import * as LivekitClient from 'https://cdn.jsdelivr.net/npm/livekit-client@2.9.8/dist/livekit-client.esm.mjs';
await window.MxLiveKitClassroom.boot(LivekitClient, {
    isHost: false,
    url: @json($livekitUrl),
    token: @json($livekitToken),
    csrfToken: document.querySelector('meta[name="csrf-token"]').content,
    exitUrl: @json(route('student.live-sessions.index')),
    permissions: {
        allow_participant_whiteboard: @json((bool) $allowStudentWhiteboard),
        allow_participant_screen_share: @json((bool) $liveSession->allow_screen_share),
        allow_participant_chat: @json((bool) $liveSession->allow_chat),
        allow_participant_raise_hand: true,
        allow_participant_virtual_background: true,
    },
    onMeetingEnded: () => { location.href = @json(route('student.live-sessions.index')); },
});
</script>
</body>
</html>

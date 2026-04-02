<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $liveSession->title }} — بث مباشر</title>
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
    </style>
</head>
<body class="bg-slate-950">
    {{-- شريط MuallimX العلوي --}}
    <header class="h-[72px] bg-gradient-to-l from-slate-900 to-slate-800 border-b border-slate-700/50 flex items-center justify-between px-4 sm:px-6 shadow-lg">
        <div class="flex items-center gap-4">
            <a href="{{ route('student.live-sessions.index') }}" class="flex items-center gap-2 text-slate-300 hover:text-white transition-colors">
                <span class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    <i class="fas fa-broadcast-tower text-lg"></i>
                </span>
                <span class="font-bold text-white hidden sm:inline">MuallimX</span>
            </a>
            <span class="w-px h-6 bg-slate-600 hidden sm:block"></span>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse shadow-lg shadow-red-500/50"></span>
                <span class="text-white font-semibold text-sm">{{ $liveSession->title }}</span>
                @if($liveSession->instructor)
                <span class="text-slate-400 text-xs hidden sm:inline">{{ $liveSession->instructor->name }}</span>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2">
            <form method="POST" action="{{ route('student.live-sessions.leave', $liveSession) }}" class="inline">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600">
                    <i class="fas fa-sign-out-alt"></i> مغادرة
                </button>
            </form>
        </div>
    </header>

    <div class="room-body">
        <main id="jitsi-container" role="application" aria-label="غرفة البث المباشر"></main>
    </div>

    @include('partials.live-whiteboard')
    @include('partials.jitsi-iframe-media-allow')
    <script src="https://{{ $jitsiDomain }}/external_api.js"></script>
    <script>
        const domain = '{{ $jitsiDomain }}';
        const jitsiRoot = document.querySelector('#jitsi-container');
        if (typeof muallimxEnsureJitsiIframeMediaAllow === 'function') {
            muallimxEnsureJitsiIframeMediaAllow(jitsiRoot);
        }
        const options = {
            roomName: '{{ $liveSession->room_name }}',
            parentNode: jitsiRoot,
            width: '100%',
            height: '100%',
            userInfo: {
                displayName: '{{ $user->name }}',
                email: '{{ $user->email }}'
            },
            configOverwrite: {
                prejoinConfig: { enabled: false },
                prejoinPageEnabled: false,
                enableLobby: false,
                requireDisplayName: false,
                enableWelcomePage: false,
                disableDeepLinking: true,
                startWithAudioMuted: {{ $liveSession->mute_on_join ? 'true' : 'false' }},
                startWithVideoMuted: {{ $liveSession->video_off_on_join ? 'true' : 'false' }},
                enableNoisyMicDetection: false,
                @if(!$liveSession->allow_chat)
                disableChat: true,
                @endif
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'chat',
                    'raisehand', 'tileview', 'fullscreen', 'whiteboard',
                    @if($liveSession->allow_screen_share)
                    'desktop',
                    @endif
                ],
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                DEFAULT_BACKGROUND: '#0f172a',
                DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
                FILM_STRIP_MAX_HEIGHT: 100,
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);

        api.addEventListener('readyToClose', function() {
            window.location.href = '{{ route("student.live-sessions.index") }}';
        });
    </script>
</body>
</html>

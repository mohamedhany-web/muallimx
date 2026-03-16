<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $liveSession->title }} — بث مباشر</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background: #0f172a; overflow: hidden; height: 100vh; }
        #jitsi-container { width: 100%; height: calc(100vh - 56px); }
        #jitsi-container iframe { width: 100% !important; height: 100% !important; border: none; }
    </style>
</head>
<body class="bg-slate-900">
    {{-- Top Bar --}}
    <div class="h-14 bg-slate-800 border-b border-slate-700 flex items-center justify-between px-4">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
            <span class="text-white font-bold text-sm">{{ $liveSession->title }}</span>
            <span class="text-slate-400 text-xs hidden sm:inline">{{ $liveSession->instructor?->name }}</span>
        </div>
        <form method="POST" action="{{ route('student.live-sessions.leave', $liveSession) }}">
            @csrf
            <button class="px-4 py-1.5 bg-slate-700 hover:bg-slate-600 text-slate-300 rounded-lg text-sm font-medium transition-colors">
                <i class="fas fa-sign-out-alt ml-1"></i> مغادرة
            </button>
        </form>
    </div>

    {{-- Jitsi Embed --}}
    <div id="jitsi-container"></div>

    <script src="https://{{ $jitsiDomain }}/external_api.js"></script>
    <script>
        const domain = '{{ $jitsiDomain }}';
        const options = {
            roomName: '{{ $liveSession->room_name }}',
            parentNode: document.querySelector('#jitsi-container'),
            width: '100%',
            height: '100%',
            userInfo: {
                displayName: '{{ $user->name }}',
                email: '{{ $user->email }}'
            },
            configOverwrite: {
                startWithAudioMuted: {{ $liveSession->mute_on_join ? 'true' : 'false' }},
                startWithVideoMuted: {{ $liveSession->video_off_on_join ? 'true' : 'false' }},
                prejoinConfig: { enabled: false },
                disableDeepLinking: true,
                @if(!$liveSession->allow_chat)
                disableChat: true,
                @endif
            },
            interfaceConfigOverwrite: {
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'chat',
                    'raisehand', 'tileview', 'fullscreen',
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

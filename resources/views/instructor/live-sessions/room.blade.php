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
            <a href="{{ route('instructor.live-sessions.index') }}" class="flex items-center gap-2 text-slate-300 hover:text-white transition-colors">
                <span class="w-10 h-10 rounded-xl bg-rose-500/20 text-rose-400 flex items-center justify-center">
                    <i class="fas fa-broadcast-tower text-lg"></i>
                </span>
                <span class="font-bold text-white hidden sm:inline">MuallimX</span>
            </a>
            <span class="w-px h-6 bg-slate-600 hidden sm:block"></span>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse shadow-lg shadow-red-500/50"></span>
                <span class="text-white font-semibold text-sm">{{ $liveSession->title }}</span>
                <span class="text-slate-400 text-xs px-2 py-0.5 rounded-md bg-slate-700/80 font-mono hidden sm:inline">{{ $liveSession->room_name }}</span>
            </div>
            <span class="text-slate-400 text-xs font-mono hidden md:inline" id="timer">00:00:00</span>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-record" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600" title="تسجيل المحاضرة">
                <i class="fas fa-circle-dot text-rose-400" id="record-icon"></i>
                <span id="record-label">تسجيل المحاضرة</span>
            </button>
            <form method="POST" action="{{ route('instructor.live-sessions.end', $liveSession) }}" class="inline" onsubmit="return confirm('هل تريد إنهاء البث المباشر؟');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-colors shadow-lg shadow-rose-500/20">
                    <i class="fas fa-stop"></i> إنهاء البث
                </button>
            </form>
        </div>
    </header>

    <div class="room-body">
        {{-- منطقة البث --}}
        <main id="jitsi-container" role="application" aria-label="غرفة البث المباشر"></main>
    </div>

    <script src="https://{{ $jitsiDomain }}/external_api.js"></script>
    <script>
        const domain = '{{ $jitsiDomain }}';
        const options = {
            roomName: '{{ $liveSession->room_name }}',
            parentNode: document.querySelector('#jitsi-container'),
            width: '100%',
            height: '100%',
            userInfo: {
                displayName: '{{ $user->name }} (مدرب)',
                email: '{{ $user->email }}'
            },
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
                enableNoisyMicDetection: false,
                @if(!$liveSession->allow_chat)
                disableChat: true,
                @endif
            },
            interfaceConfigOverwrite: {
                APP_NAME: 'MuallimX',
                TOOLBAR_BUTTONS: [
                    'microphone', 'camera', 'desktop', 'chat',
                    'raisehand', 'participants-pane', 'tileview',
                    'fullscreen', 'hangup', 'recording',
                    'settings', 'select-background',
                ],
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                DEFAULT_BACKGROUND: '#0f172a',
                DISABLE_JOIN_LEAVE_NOTIFICATIONS: false,
                FILM_STRIP_MAX_HEIGHT: 120,
            }
        };
        const api = new JitsiMeetExternalAPI(domain, options);
        let isRecording = false;

        // تايمر البث
        const startTime = new Date('{{ $liveSession->started_at->toISOString() }}');
        function updateTimer() {
            const diff = Math.floor((Date.now() - startTime) / 1000);
            const h = Math.floor(diff / 3600);
            const m = Math.floor((diff % 3600) / 60);
            const s = diff % 60;
            var el = document.getElementById('timer');
            if (el) el.textContent = h.toString().padStart(2,'0') + ':' + m.toString().padStart(2,'0') + ':' + s.toString().padStart(2,'0');
        }
        setInterval(updateTimer, 1000);
        updateTimer();

        api.addEventListener('videoConferenceJoined', function() {
            document.getElementById('btn-record').addEventListener('click', function() {
                if (isRecording) {
                    api.executeCommand('stopRecording', 'file');
                } else {
                    api.executeCommand('startRecording', { mode: 'file' });
                }
            });
        });

        api.addEventListener('recordingStatusChanged', function(data) {
            isRecording = data && (data.on === true || data.status === 'on');
            var btn = document.getElementById('btn-record');
            var icon = document.getElementById('record-icon');
            var label = document.getElementById('record-label');
            if (isRecording) {
                btn.classList.remove('bg-slate-700/80');
                btn.classList.add('bg-rose-600/90', 'text-white');
                icon.className = 'fas fa-stop';
                label.textContent = 'إيقاف التسجيل';
            } else {
                btn.classList.add('bg-slate-700/80');
                btn.classList.remove('bg-rose-600/90', 'text-white');
                icon.className = 'fas fa-circle-dot text-rose-400';
                label.textContent = 'تسجيل المحاضرة';
            }
        });
    </script>
</body>
</html>

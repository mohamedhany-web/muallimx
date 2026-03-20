<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MuallimX Classroom — {{ $meeting->title ?: $meeting->code }}</title>
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
    {{-- شريط MuallimX العلوي — تصميم المنصة فقط --}}
    <header class="h-[72px] bg-gradient-to-l from-slate-900 to-slate-800 border-b border-slate-700/50 flex items-center justify-between px-4 sm:px-6 shadow-lg">
        <div class="flex items-center gap-4">
            <a href="{{ route('student.classroom.index') }}" class="flex items-center gap-2 text-slate-300 hover:text-white transition-colors">
                <span class="w-10 h-10 rounded-xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center">
                    <i class="fas fa-video text-lg"></i>
                </span>
                <span class="font-bold text-white hidden sm:inline">MuallimX</span>
            </a>
            <span class="w-px h-6 bg-slate-600 hidden sm:block"></span>
            <div class="flex items-center gap-2">
                <span class="w-2.5 h-2.5 bg-emerald-400 rounded-full animate-pulse shadow-lg shadow-emerald-400/50"></span>
                <span class="text-white font-semibold text-sm">{{ $meeting->title ?: 'غرفة ' . $meeting->code }}</span>
                <span class="text-slate-400 text-xs px-2 py-0.5 rounded-md bg-slate-700/80 font-mono">{{ $meeting->code }}</span>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="btn-record" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600" title="تسجيل المحاضرة">
                <i class="fas fa-circle-dot text-rose-400" id="record-icon"></i>
                <span id="record-label">تسجيل المحاضرة</span>
            </button>
            <button type="button" onclick="navigator.clipboard.writeText('{{ url('classroom/join/' . $meeting->code) }}'); this.innerHTML='<i class=\'fas fa-check ml-1\'></i> تم النسخ'; setTimeout(()=>{ this.innerHTML='<i class=\'fas fa-link ml-1\'></i> مشاركة الرابط'; }, 2000)" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600">
                <i class="fas fa-link ml-1"></i> مشاركة الرابط
            </button>
            <form method="POST" action="{{ route('student.classroom.end', $meeting) }}" class="inline" onsubmit="return confirm('إنهاء الاجتماع للجميع؟');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-colors shadow-lg shadow-rose-500/20">
                    <i class="fas fa-stop"></i> إنهاء الاجتماع
                </button>
            </form>
        </div>
    </header>

    <div class="room-body">
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

    {{-- تذكير بصلاحيات الميكروفون/الكاميرا — يقلل التباس "Error obtaining microphone permission" --}}
    <div id="media-tip" class="bg-slate-700/80 border-b border-slate-600 px-4 py-2 text-slate-300 text-xs flex items-center justify-between gap-2 flex-shrink-0">
        <span><i class="fas fa-info-circle text-cyan-400 ml-1"></i> عند طلب المتصفح استخدام <strong>الميكروفون أو الكاميرا</strong> اختر «السماح». يمكنك تفعيل الصوت والفيديو من الشريط بعد الدخول.</span>
        <button type="button" onclick="document.getElementById('media-tip').remove()" class="text-slate-400 hover:text-white p-1" aria-label="إغلاق"><i class="fas fa-times"></i></button>
    </div>

    {{-- منطقة الاجتماع --}}
    <main id="jitsi-container" role="application" aria-label="غرفة الاجتماع">
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
    </div>

    <script>
        (function() {
            var jitsiDomain = '{{ $jitsiDomain }}';
            var roomName = '{{ $meeting->room_name }}';
            var userName = {!! json_encode($user->name) !!};
            var userEmail = {!! json_encode($user->email ?? '') !!};
            var container = document.getElementById('jitsi-container');
            var loadingEl = document.getElementById('jitsi-loading');
            var errorEl = document.getElementById('jitsi-error');

            function showError() {
                if (loadingEl) loadingEl.classList.add('hidden');
                if (errorEl) { errorEl.style.display = 'flex'; errorEl.classList.add('flex'); }
            }

            function initJitsi() {
                if (typeof JitsiMeetExternalAPI === 'undefined') {
                    showError();
                    return;
                }
                try {
                    container.innerHTML = '';
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
                            APP_NAME: 'MuallimX Classroom',
                            NATIVE_APP_NAME: 'MuallimX Classroom',
                            PROVIDER_NAME: 'MuallimX',
                            TOOLBAR_BUTTONS: [
                                'microphone', 'camera', 'closedcaptions', 'desktop', 'fullscreen',
                                'fodeviceselection', 'hangup', 'chat', 'recording',
                                'raisehand', 'invite', 'tileview', 'videoquality', 'filmstrip'
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
                    var api = new JitsiMeetExternalAPI(jitsiDomain, options);
                    var isRecording = false;

                    if (loadingEl) loadingEl.classList.add('hidden');

                    api.addEventListener('readyToClose', function() {
                        window.location.href = '{{ route("student.classroom.index") }}';
                    });

                    api.addEventListener('videoConferenceJoined', function() {
                        var btn = document.getElementById('btn-record');
                        if (btn) btn.addEventListener('click', function() {
                            if (isRecording) api.executeCommand('stopRecording', 'file');
                            else api.executeCommand('startRecording', { mode: 'file' });
                        });
                    });

                    api.addEventListener('recordingStatusChanged', function(data) {
                        isRecording = data && (data.on === true || data.status === 'on');
                        var btn = document.getElementById('btn-record');
                        var icon = document.getElementById('record-icon');
                        var label = document.getElementById('record-label');
                        if (!btn) return;
                        if (isRecording) {
                            btn.classList.remove('bg-slate-700/80');
                            btn.classList.add('bg-rose-600/90', 'text-white');
                            if (icon) icon.className = 'fas fa-stop';
                            if (label) label.textContent = 'إيقاف التسجيل';
                        } else {
                            btn.classList.add('bg-slate-700/80');
                            btn.classList.remove('bg-rose-600/90', 'text-white');
                            if (icon) icon.className = 'fas fa-circle-dot text-rose-400';
                            if (label) label.textContent = 'تسجيل المحاضرة';
                        }
                    });
                } catch (e) {
                    console.error('Jitsi init error:', e);
                    showError();
                }
            }

            var script = document.createElement('script');
            script.src = 'https://' + jitsiDomain + '/external_api.js';
            script.async = false;
            script.onload = function() { initJitsi(); };
            script.onerror = function() {
                console.error('Failed to load Jitsi external_api.js from ' + script.src);
                showError();
            };
            document.head.appendChild(script);
        })();
    </script>
</body>
</html>

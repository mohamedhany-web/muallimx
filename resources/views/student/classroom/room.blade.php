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
@php
    $rp = ($useInstructorRoutes ?? false) ? 'instructor.' : 'student.';
    $roomExitUrl = ($useInstructorRoutes ?? false)
        ? ($meeting->consultation_request_id ? route('instructor.consultations.show', $meeting->consultation_request_id) : route('instructor.consultations.index'))
        : route('student.classroom.index');
@endphp
    {{-- شريط MuallimX العلوي — تصميم المنصة فقط --}}
    <header class="h-[72px] bg-gradient-to-l from-slate-900 to-slate-800 border-b border-slate-700/50 flex items-center justify-between px-4 sm:px-6 shadow-lg">
        <div class="flex items-center gap-4">
            <a href="{{ $roomExitUrl }}" class="flex items-center gap-2 text-slate-300 hover:text-white transition-colors">
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
            <span class="text-slate-300 text-xs px-2 py-1 rounded-md bg-slate-700/80">
                الحد الأقصى للطلاب: {{ (int) ($meeting->max_participants ?? 25) }}
            </span>
            <span class="text-amber-200 text-xs px-2 py-1 rounded-md bg-amber-500/20 border border-amber-500/30" id="meeting-timer-chip">
                مدة الاجتماع: {{ (int) $effectiveDurationMinutes }} دقيقة (حد الباقة {{ (int) $maxDurationMinutes }})
            </span>
            <button type="button" id="btn-record" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600" title="تسجيل المحاضرة">
                <i class="fas fa-circle-dot text-rose-400" id="record-icon"></i>
                <span id="record-label">تسجيل المحاضرة</span>
            </button>
            <button type="button" onclick="navigator.clipboard.writeText('{{ url('classroom/join/' . $meeting->code) }}'); this.innerHTML='<i class=\'fas fa-check ml-1\'></i> تم النسخ'; setTimeout(()=>{ this.innerHTML='<i class=\'fas fa-link ml-1\'></i> مشاركة الرابط'; }, 2000)" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-700/80 hover:bg-slate-600 text-slate-200 text-sm font-medium transition-colors border border-slate-600">
                <i class="fas fa-link ml-1"></i> مشاركة الرابط
            </button>
            <form method="POST" action="{{ route($rp.'classroom.end', $meeting) }}" class="inline" onsubmit="return confirm('إنهاء الاجتماع للجميع؟');">
                @csrf
                <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 hover:bg-rose-500 text-white text-sm font-semibold transition-colors shadow-lg shadow-rose-500/20">
                    <i class="fas fa-stop"></i> إنهاء الاجتماع
                </button>
            </form>
        </div>
    </header>

    <div class="room-body">
    {{-- بوابة إذن الميكروفون/الكاميرا قبل تحميل Jitsi (تحل مشكلة بعض الأجهزة التي لا تُظهر الطلب تلقائياً) --}}
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

    @include('partials.jitsi-iframe-media-allow')
    <script>
        (function() {
            var jitsiDomain = '{{ $jitsiDomain }}';
            var roomName = '{{ $meeting->room_name }}';
            var userName = {!! json_encode($user->name) !!};
            var userEmail = {!! json_encode($user->email ?? '') !!};
            var container = document.getElementById('jitsi-container');
            var loadingEl = document.getElementById('jitsi-loading');
            var errorEl = document.getElementById('jitsi-error');
            var meetingEndsAt = {!! json_encode(optional($meetingEndsAt)->toIso8601String()) !!};
            var timerChip = document.getElementById('meeting-timer-chip');
            var recordBtn = document.getElementById('btn-record');
            var recordIcon = document.getElementById('record-icon');
            var recordLabel = document.getElementById('record-label');
            var permissionGate = document.getElementById('permission-gate');
            var permissionHelp = document.getElementById('permission-help');
            var requestMediaBtn = document.getElementById('btn-request-media');
            var joinWithoutMediaBtn = document.getElementById('btn-join-without-media');
            var api = null;
            var hasJoinedConference = false;
            var isRecording = false;
            var recordActionTimeout = null;

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

            function clearRecordActionTimeout() {
                if (recordActionTimeout) {
                    clearTimeout(recordActionTimeout);
                    recordActionTimeout = null;
                }
            }

            function handleRecordButtonClick() {
                if (!api) {
                    alert('جاري تهيئة غرفة الاجتماع... حاول بعد ثوانٍ.');
                    return;
                }
                if (!hasJoinedConference) {
                    alert('ادخل الغرفة أولاً ثم أعد محاولة التسجيل.');
                    return;
                }

                setRecordButtonBusy(true);

                // في بعض إعدادات Jitsi قد لا يتوفر التسجيل (Jibri/JaaS)؛
                // نُظهر رسالة واضحة بدل أن يبدو الزر "لا يعمل".
                clearRecordActionTimeout();
                recordActionTimeout = setTimeout(function() {
                    setRecordButtonBusy(false);
                    alert('تعذر بدء/إيقاف التسجيل من السيرفر الحالي. تأكد من تفعيل خدمة التسجيل (Jibri) على خادم Jitsi.');
                }, 4000);

                try {
                    if (isRecording) {
                        api.executeCommand('stopRecording', { mode: 'file' });
                    } else {
                        api.executeCommand('startRecording', { mode: 'file' });
                    }
                } catch (err) {
                    clearRecordActionTimeout();
                    setRecordButtonBusy(false);
                    console.error('Recording command error:', err);
                    alert('حدث خطأ أثناء تنفيذ أمر التسجيل.');
                }
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
                    api = new JitsiMeetExternalAPI(jitsiDomain, options);

                    if (loadingEl) loadingEl.classList.add('hidden');

                    api.addEventListener('readyToClose', function() {
                        window.location.href = '{{ route("student.classroom.index") }}';
                    });

                    api.addEventListener('videoConferenceJoined', function() {
                        hasJoinedConference = true;
                    });

                    api.addEventListener('recordingStatusChanged', function(data) {
                        clearRecordActionTimeout();
                        setRecordButtonBusy(false);
                        isRecording = data && (data.on === true || data.status === 'on');
                        setRecordButtonState(isRecording);
                    });

                    api.addEventListener('errorOccurred', function(event) {
                        var name = event && event.name ? String(event.name) : '';
                        if (name.toLowerCase().includes('record')) {
                            clearRecordActionTimeout();
                            setRecordButtonBusy(false);
                            alert('فشل التسجيل: تأكد أن حسابك لديه صلاحية التسجيل وأن خدمة التسجيل مفعلة على الخادم.');
                        }
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
                    window.location.href = '{{ route("student.classroom.index") }}';
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

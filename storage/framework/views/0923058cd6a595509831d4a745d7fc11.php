<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo e($liveSession->title); ?> — بث مباشر</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        body { margin: 0; padding: 0; background: #0f172a; overflow: hidden; height: 100vh; }
        #jitsi-container { width: 100%; height: calc(100vh - 56px); }
        #jitsi-container iframe { width: 100% !important; height: 100% !important; border: none; }
    </style>
</head>
<body class="bg-slate-900">
    
    <div class="h-14 bg-slate-800 border-b border-slate-700 flex items-center justify-between px-4">
        <div class="flex items-center gap-3">
            <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span>
            <span class="text-white font-bold text-sm"><?php echo e($liveSession->title); ?></span>
            <span class="text-slate-400 text-xs font-mono hidden sm:inline"><?php echo e($liveSession->room_name); ?></span>
        </div>
        <div class="flex items-center gap-3">
            <span class="text-slate-400 text-xs hidden sm:inline" id="timer"></span>
            <form method="POST" action="<?php echo e(route('instructor.live-sessions.end', $liveSession)); ?>" onsubmit="return confirm('هل تريد إنهاء البث المباشر؟')">
                <?php echo csrf_field(); ?>
                <button class="px-4 py-1.5 bg-red-500 hover:bg-red-600 text-white rounded-lg text-sm font-semibold transition-colors">
                    <i class="fas fa-stop ml-1"></i> إنهاء البث
                </button>
            </form>
        </div>
    </div>

    
    <div id="jitsi-container"></div>

    <script src="https://<?php echo e($jitsiDomain); ?>/external_api.js"></script>
    <script>
        const domain = '<?php echo e($jitsiDomain); ?>';
        const options = {
            roomName: '<?php echo e($liveSession->room_name); ?>',
            parentNode: document.querySelector('#jitsi-container'),
            width: '100%',
            height: '100%',
            userInfo: {
                displayName: '<?php echo e($user->name); ?> (مدرب)',
                email: '<?php echo e($user->email); ?>'
            },
            configOverwrite: {
                startWithAudioMuted: false,
                startWithVideoMuted: false,
                prejoinConfig: { enabled: false },
                disableDeepLinking: true,
                <?php if(!$liveSession->allow_chat): ?>
                disableChat: true,
                <?php endif; ?>
            },
            interfaceConfigOverwrite: {
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

        // Timer
        const startTime = new Date('<?php echo e($liveSession->started_at->toISOString()); ?>');
        function updateTimer() {
            const diff = Math.floor((Date.now() - startTime) / 1000);
            const h = Math.floor(diff / 3600);
            const m = Math.floor((diff % 3600) / 60);
            const s = diff % 60;
            document.getElementById('timer').textContent =
                `${h.toString().padStart(2,'0')}:${m.toString().padStart(2,'0')}:${s.toString().padStart(2,'0')}`;
        }
        setInterval(updateTimer, 1000);
        updateTimer();
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/instructor/live-sessions/room.blade.php ENDPATH**/ ?>
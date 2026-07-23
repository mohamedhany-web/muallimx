<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>مشاركون — <?php echo e($meeting->title ?: $meeting->code); ?></title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --ml-bg: #111827;
            --ml-panel: #171717;
            --ml-primary: #0065fd;
            --ml-stroke: #525252;
            --ml-text: #fdfdfd;
        }
        * { box-sizing: border-box; }
        html, body {
            margin: 0;
            height: 100%;
            background: var(--ml-bg);
            color: var(--ml-text);
            font-family: "IBM Plex Sans Arabic", Tahoma, sans-serif;
            overflow: hidden;
        }
        .pip-shell {
            display: flex;
            flex-direction: column;
            height: 100%;
            min-height: 0;
        }
        .pip-bar {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            background: var(--ml-panel);
            border-bottom: 1px solid var(--ml-stroke);
            flex-shrink: 0;
            -webkit-app-region: drag;
        }
        .pip-bar strong {
            flex: 1;
            font-size: 12px;
            font-weight: 600;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .pip-bar button {
            -webkit-app-region: no-drag;
            width: 28px;
            height: 28px;
            border-radius: 6px;
            border: 1px solid var(--ml-stroke);
            background: #292929;
            color: #fff;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }
        .pip-bar button:hover { background: var(--ml-primary); border-color: var(--ml-primary); }
        .pip-bar button.is-danger:hover { background: #fd0000; border-color: #c50000; }
        #jitsi-pip {
            flex: 1;
            min-height: 0;
            width: 100%;
            background: #0f172a;
        }
        #jitsi-pip iframe { width: 100% !important; height: 100% !important; border: 0 !important; }
        .pip-hint {
            font-size: 10px;
            color: #a3a3a3;
            padding: 4px 8px;
            background: #0b1220;
            border-top: 1px solid #1f2937;
            flex-shrink: 0;
        }
    </style>
</head>
<body>
<div class="pip-shell">
    <div class="pip-bar">
        <strong id="pip-title">المشاركون</strong>
        <button type="button" id="pip-btn-tile" title="عرض الشبكة"><i class="fas fa-th-large"></i></button>
        <button type="button" id="pip-btn-film" title="شريط المشاركين"><i class="fas fa-users"></i></button>
        <button type="button" id="pip-btn-close" class="is-danger" title="إخفاء النافذة"><i class="fas fa-times"></i></button>
    </div>
    <div id="jitsi-pip"></div>
    <div class="pip-hint">اسحب زاوية النافذة للتكبير/التصغير · تبقى فوق التطبيقات الأخرى في Chrome/Edge</div>
</div>
<script>
(function () {
    var jitsiDomain = <?php echo json_encode($jitsiDomain, 15, 512) ?>;
    var roomName = <?php echo json_encode($meeting->room_name, 15, 512) ?>;
    var userName = <?php echo json_encode(($user->name ?: 'معلم') . ' · نافذة', 15, 512) ?>;
    var userEmail = <?php echo json_encode($user->email ?? '', 15, 512) ?>;
    var api = null;

    function closePip() {
        try {
            if (window.opener && !window.opener.closed) {
                window.opener.postMessage({ type: 'mx-pip-closed' }, window.location.origin);
            }
        } catch (e) {}
        try {
            if (window.documentPictureInPicture && window.documentPictureInPicture.window === window) {
                window.close();
                return;
            }
        } catch (e2) {}
        window.close();
    }

    document.getElementById('pip-btn-close').addEventListener('click', closePip);
    document.getElementById('pip-btn-tile').addEventListener('click', function () {
        if (api) try { api.executeCommand('toggleTileView'); } catch (e) {}
    });
    document.getElementById('pip-btn-film').addEventListener('click', function () {
        if (api) try { api.executeCommand('toggleFilmStrip'); } catch (e) {}
    });

    function boot() {
        if (typeof JitsiMeetExternalAPI !== 'function') {
            document.getElementById('jitsi-pip').innerHTML = '<p style="padding:12px;font-size:12px">تعذر تحميل الاجتماع</p>';
            return;
        }
        api = new JitsiMeetExternalAPI(jitsiDomain, {
            roomName: roomName,
            parentNode: document.getElementById('jitsi-pip'),
            width: '100%',
            height: '100%',
            userInfo: { displayName: userName, email: userEmail },
            configOverwrite: {
                prejoinConfig: { enabled: false },
                prejoinPageEnabled: false,
                disableDeepLinking: true,
                startWithAudioMuted: true,
                startWithVideoMuted: true,
                startSilent: true,
                iAmRecorder: true,
                toolbarButtons: [],
                disableFocusIndicator: true,
                hideConferenceTimer: true,
                notifications: [],
            },
            interfaceConfigOverwrite: {
                APP_NAME: 'Muallimx',
                TOOLBAR_BUTTONS: [],
                SHOW_JITSI_WATERMARK: false,
                SHOW_WATERMARK_FOR_GUESTS: false,
                SHOW_BRAND_WATERMARK: false,
                SHOW_POWERED_BY: false,
                MOBILE_APP_PROMO: false,
                DISABLE_JOIN_LEAVE_NOTIFICATIONS: true,
                FILM_STRIP_MAX_HEIGHT: 160,
                VERTICAL_FILMSTRIP: false,
                DEFAULT_BACKGROUND: '#111827',
            }
        });
        api.addEventListener('videoConferenceJoined', function () {
            try { api.executeCommand('setTileView', true); } catch (e) {}
            try { api.executeCommand('toggleFilmStrip'); } catch (e2) {}
        });
        api.addEventListener('participantJoined', function () {
            var n = 1;
            try { n = api.getNumberOfParticipants() || 1; } catch (e) {}
            document.getElementById('pip-title').textContent = 'المشاركون (' + n + ')';
        });
        api.addEventListener('participantLeft', function () {
            var n = 1;
            try { n = api.getNumberOfParticipants() || 1; } catch (e) {}
            document.getElementById('pip-title').textContent = 'المشاركون (' + n + ')';
        });
    }

    var s = document.createElement('script');
    s.src = 'https://' + jitsiDomain + '/external_api.js';
    s.onload = boot;
    s.onerror = function () {
        document.getElementById('jitsi-pip').innerHTML = '<p style="padding:12px;font-size:12px">فشل تحميل Jitsi</p>';
    };
    document.head.appendChild(s);

    window.addEventListener('message', function (ev) {
        if (ev.origin !== window.location.origin) return;
        if (ev.data && ev.data.type === 'mx-pip-close') closePip();
    });
})();
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/student/classroom/room-pip.blade.php ENDPATH**/ ?>
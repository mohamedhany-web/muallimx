<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>LiveKit Pilot — {{ $meeting->title ?: $meeting->code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; }
        html, body { height: 100%; margin: 0; background: #0b1220; color: #e2e8f0; overflow: hidden; }
        #lk-stage { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); align-content: start; }
        .lk-tile { position: relative; background: #111827; border: 1px solid #334155; border-radius: 14px; overflow: hidden; min-height: 160px; aspect-ratio: 16/10; }
        .lk-tile video { width: 100%; height: 100%; object-fit: contain; background: #020617; display: block; }
        .lk-tile .label { position: absolute; left: 8px; bottom: 8px; z-index: 2; font-size: 11px; font-weight: 600; background: rgba(15,23,42,.85); border: 1px solid #475569; padding: 3px 8px; border-radius: 8px; }
        .lk-tile.is-screen {
            grid-column: 1 / -1;
            min-height: min(72vh, 820px);
            aspect-ratio: auto;
            height: min(72vh, 820px);
        }
        .lk-tile.is-screen video { object-fit: contain; }
        .ctrl-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid #475569; background: #1e293b; color: #e2e8f0; font-size: 13px; font-weight: 600; cursor: pointer; }
        .ctrl-btn:hover { border-color: #38bdf8; color: #7dd3fc; }
        .ctrl-btn.is-off { border-color: #f87171; color: #fecaca; background: rgba(127,29,29,.35); }
        .ctrl-btn.is-on { border-color: #34d399; color: #a7f3d0; }
    </style>
</head>
<body>
@php
    $rp = ($useInstructorRoutes ?? false) ? 'instructor.' : 'student.';
    if ($useInstructorRoutes ?? false) {
        $backUrl = $meeting->consultation_request_id
            ? route('instructor.consultations.show', $meeting->consultation_request_id)
            : route('instructor.consultations.index');
    } else {
        $backUrl = route('student.classroom.show', $meeting);
    }
@endphp
<div class="h-screen flex flex-col">
    <header class="shrink-0 h-16 px-4 sm:px-6 flex items-center justify-between gap-3 border-b border-slate-700 bg-slate-900/90">
        <div class="min-w-0">
            <p class="text-xs text-cyan-300 font-semibold">LiveKit · تجربة جودة</p>
            <h1 class="text-sm sm:text-base font-bold truncate">{{ $meeting->title ?: ('غرفة '.$meeting->code) }}</h1>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <span id="lk-status" class="hidden sm:inline text-xs text-slate-400">جاري الاتصال…</span>
            <a href="{{ $backUrl }}" class="ctrl-btn text-xs py-2">خروج</a>
            <form method="POST" action="{{ route($rp.'classroom.end', $meeting) }}" onsubmit="return confirm('إنهاء الاجتماع للجميع؟');">
                @csrf
                <button type="submit" class="ctrl-btn text-xs py-2" style="border-color:#f87171;color:#fecaca;">إنهاء</button>
            </form>
        </div>
    </header>

    <main class="flex-1 min-h-0 overflow-auto p-3 sm:p-4">
        <div id="lk-stage"></div>
        <p id="lk-empty" class="text-center text-slate-500 text-sm mt-10">لا يوجد فيديو بعد — فعّل الكاميرا أو انتظر المشاركين.</p>
    </main>

    <footer class="shrink-0 border-t border-slate-700 bg-slate-900/95 px-3 sm:px-6 py-3 flex flex-wrap items-center justify-center gap-2">
        <button type="button" id="btn-mic" class="ctrl-btn is-off"><i class="fas fa-microphone-slash"></i> ميكروفون</button>
        <button type="button" id="btn-cam" class="ctrl-btn is-off"><i class="fas fa-video-slash"></i> كاميرا</button>
        <button type="button" id="btn-share" class="ctrl-btn"><i class="fas fa-desktop"></i> مشاركة شاشة</button>
        <button type="button" id="btn-copy" class="ctrl-btn"><i class="fas fa-link"></i> رابط الطلاب</button>
    </footer>
</div>

<script type="module">
import {
    Room,
    RoomEvent,
    Track,
    createLocalTracks,
    createLocalScreenTracks,
    VideoPresets,
    VideoQuality,
    ScreenSharePresets,
} from 'https://cdn.jsdelivr.net/npm/livekit-client@2.9.8/dist/livekit-client.esm.mjs';

window.VideoQuality = VideoQuality;

const LK_URL = @json($livekitUrl);
const LK_TOKEN = @json($livekitToken);
const JOIN_URL = @json(url('classroom/join/'.$meeting->code));
const stage = document.getElementById('lk-stage');
const emptyEl = document.getElementById('lk-empty');
const statusEl = document.getElementById('lk-status');

const room = new Room({
    // OFF: adaptiveStream was crushing screen-share to the small tile size
    adaptiveStream: false,
    dynacast: false,
    videoCaptureDefaults: {
        resolution: VideoPresets.h720.resolution,
    },
    publishDefaults: {
        videoCodec: 'vp8',
        videoSimulcastLayers: [VideoPresets.h180, VideoPresets.h360],
        screenShareEncoding: {
            maxBitrate: 6_000_000,
            maxFramerate: 30,
        },
        // No screenshare simulcast — always send the full crisp layer
        screenShareSimulcastLayers: [],
    },
});

const tileMap = new Map();

function setStatus(text) {
    if (statusEl) statusEl.textContent = text;
}

function refreshEmpty() {
    if (emptyEl) emptyEl.style.display = stage.children.length ? 'none' : '';
}

function tileKey(participant, source) {
    return participant.identity + ':' + source;
}

function ensureTile(participant, source) {
    const key = tileKey(participant, source);
    let tile = tileMap.get(key);
    if (tile) return tile;
    tile = document.createElement('div');
    tile.className = 'lk-tile' + (source === Track.Source.ScreenShare ? ' is-screen' : '');
    tile.dataset.key = key;
    const video = document.createElement('video');
    video.autoplay = true;
    video.playsInline = true;
    video.muted = participant.isLocal;
    const label = document.createElement('div');
    label.className = 'label';
    label.textContent = (participant.name || participant.identity) + (source === Track.Source.ScreenShare ? ' · شاشة' : '');
    tile.appendChild(video);
    tile.appendChild(label);
    if (source === Track.Source.ScreenShare && stage.firstChild) {
        stage.insertBefore(tile, stage.firstChild);
    } else {
        stage.appendChild(tile);
    }
    tileMap.set(key, tile);
    refreshEmpty();
    return tile;
}

function removeTile(participant, source) {
    const key = tileKey(participant, source);
    const tile = tileMap.get(key);
    if (!tile) return;
    tile.remove();
    tileMap.delete(key);
    refreshEmpty();
}

function forceHighQuality(participant, source) {
    try {
        const pub = participant.getTrackPublication?.(source)
            || [...(participant.trackPublications?.values?.() || [])].find((p) => p.source === source);
        if (pub && typeof pub.setVideoQuality === 'function') {
            pub.setVideoQuality(VideoQuality.HIGH);
        }
    } catch (_) {}
}

function attachTrack(track, participant) {
    if (track.kind !== Track.Kind.Video && track.kind !== Track.Kind.Audio) return;
    if (track.kind === Track.Kind.Audio && participant.isLocal) return;
    if (track.kind === Track.Kind.Audio) {
        const el = track.attach();
        el.style.display = 'none';
        document.body.appendChild(el);
        return;
    }
    if (track.source === Track.Source.ScreenShare) {
        try {
            if (track.mediaStreamTrack) {
                track.mediaStreamTrack.contentHint = 'detail';
            }
        } catch (_) {}
        forceHighQuality(participant, Track.Source.ScreenShare);
    }
    const tile = ensureTile(participant, track.source);
    const video = tile.querySelector('video');
    track.attach(video);
}

function detachTrack(track, participant) {
    track.detach().forEach((el) => el.remove());
    if (track.kind === Track.Kind.Video) {
        removeTile(participant, track.source);
    }
}

room
    .on(RoomEvent.TrackSubscribed, (track, publication, participant) => {
        if (publication && track.source === Track.Source.ScreenShare) {
            try { publication.setVideoQuality(VideoQuality.HIGH); } catch (_) {}
        }
        attachTrack(track, participant);
    })
    .on(RoomEvent.TrackUnsubscribed, (track, _pub, participant) => detachTrack(track, participant))
    .on(RoomEvent.LocalTrackPublished, (pub, participant) => {
        if (pub.track) attachTrack(pub.track, participant);
    })
    .on(RoomEvent.LocalTrackUnpublished, (pub, participant) => {
        if (pub.track) detachTrack(pub.track, participant);
    })
    .on(RoomEvent.Disconnected, () => setStatus('انقطع الاتصال'))
    .on(RoomEvent.Reconnecting, () => setStatus('إعادة اتصال…'))
    .on(RoomEvent.Reconnected, () => setStatus('متصل'));

let micOn = false;
let camOn = false;
let shareOn = false;
let localAudio;
let localVideo;
let localScreenTracks = [];

function paintMic() {
    const btn = document.getElementById('btn-mic');
    btn.classList.toggle('is-off', !micOn);
    btn.classList.toggle('is-on', micOn);
    btn.innerHTML = micOn
        ? '<i class="fas fa-microphone"></i> ميكروفون'
        : '<i class="fas fa-microphone-slash"></i> ميكروفون';
}
function paintCam() {
    const btn = document.getElementById('btn-cam');
    btn.classList.toggle('is-off', !camOn);
    btn.classList.toggle('is-on', camOn);
    btn.innerHTML = camOn
        ? '<i class="fas fa-video"></i> كاميرا'
        : '<i class="fas fa-video-slash"></i> كاميرا';
}
function paintShare() {
    const btn = document.getElementById('btn-share');
    btn.classList.toggle('is-on', shareOn);
    btn.innerHTML = shareOn
        ? '<i class="fas fa-desktop"></i> إيقاف المشاركة'
        : '<i class="fas fa-desktop"></i> مشاركة شاشة';
}

document.getElementById('btn-mic').addEventListener('click', async () => {
    if (!localAudio) {
        const tracks = await createLocalTracks({ audio: true, video: false });
        localAudio = tracks[0];
        await room.localParticipant.publishTrack(localAudio);
        micOn = true;
    } else {
        micOn = !micOn;
        await localAudio.setEnabled(micOn);
    }
    paintMic();
});

document.getElementById('btn-cam').addEventListener('click', async () => {
    if (!localVideo) {
        const tracks = await createLocalTracks({
            audio: false,
            video: { resolution: VideoPresets.h720.resolution },
        });
        localVideo = tracks[0];
        await room.localParticipant.publishTrack(localVideo);
        camOn = true;
    } else {
        camOn = !camOn;
        await localVideo.setEnabled(camOn);
    }
    paintCam();
});

document.getElementById('btn-share').addEventListener('click', async () => {
    try {
        if (!shareOn) {
            const tracks = await createLocalScreenTracks({
                audio: false,
                resolution: ScreenSharePresets.h1080fps30.resolution,
                contentHint: 'detail',
            });
            localScreenTracks = tracks;
            for (const t of tracks) {
                try {
                    if (t.mediaStreamTrack) t.mediaStreamTrack.contentHint = 'detail';
                } catch (_) {}
                await room.localParticipant.publishTrack(t, {
                    source: Track.Source.ScreenShare,
                    name: 'screen',
                    simulcast: false,
                    videoCodec: 'vp8',
                    screenShareEncoding: {
                        maxBitrate: 6_000_000,
                        maxFramerate: 30,
                    },
                });
            }
            shareOn = true;
            setStatus('شير شاشة · 1080p / 6Mbps');
        } else {
            for (const t of localScreenTracks) {
                try { await room.localParticipant.unpublishTrack(t); } catch (_) {}
                try { t.stop(); } catch (_) {}
            }
            localScreenTracks = [];
            try { await room.localParticipant.setScreenShareEnabled(false); } catch (_) {}
            shareOn = false;
            setStatus('متصل');
        }
        paintShare();
    } catch (e) {
        alert('تعذر مشاركة الشاشة: ' + (e?.message || e));
    }
});

document.getElementById('btn-copy').addEventListener('click', async () => {
    try {
        await navigator.clipboard.writeText(JOIN_URL);
        setStatus('تم نسخ رابط الطلاب');
    } catch (_) {
        prompt('انسخ الرابط:', JOIN_URL);
    }
});

(async function connect() {
    try {
        setStatus('جاري الاتصال بـ LiveKit…');
        await room.connect(LK_URL, LK_TOKEN);
        setStatus('متصل · ' + (room.numParticipants || 1) + ' مشارك');
        room.remoteParticipants.forEach((p) => {
            p.trackPublications.forEach((pub) => {
                if (pub.track) attachTrack(pub.track, p);
                if (pub.source === Track.Source.ScreenShare) {
                    try { pub.setVideoQuality(VideoQuality.HIGH); } catch (_) {}
                }
            });
        });
        paintMic();
        paintCam();
        paintShare();
        refreshEmpty();
    } catch (e) {
        console.error(e);
        setStatus('فشل الاتصال');
        alert('فشل الاتصال بـ LiveKit. تأكد من LIVEKIT_URL=wss://live.muallimx.com/livekit في .env ثم امسح الكاش.\n' + (e?.message || e));
    }
})();

window.addEventListener('beforeunload', () => {
    try { room.disconnect(); } catch (_) {}
});
</script>
</body>
</html>

<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>انضم LiveKit — {{ $code }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>
        * { font-family: 'IBM Plex Sans Arabic', system-ui, sans-serif; }
        html, body { height: 100%; margin: 0; background: #0b1220; color: #e2e8f0; }
        body.in-room { overflow: hidden; }
        #lk-stage { display: grid; gap: 10px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); align-content: start; }
        .lk-tile { position: relative; background: #111827; border: 1px solid #334155; border-radius: 14px; overflow: hidden; min-height: 150px; aspect-ratio: 16/10; }
        .lk-tile video { width: 100%; height: 100%; object-fit: contain; background: #020617; display: block; }
        .lk-tile .label { position: absolute; left: 8px; bottom: 8px; z-index: 2; font-size: 11px; font-weight: 600; background: rgba(15,23,42,.85); border: 1px solid #475569; padding: 3px 8px; border-radius: 8px; }
        .lk-tile.is-screen { grid-column: 1 / -1; min-height: 280px; aspect-ratio: 16/9; }
        .ctrl-btn { display: inline-flex; align-items: center; gap: 8px; padding: 10px 14px; border-radius: 12px; border: 1px solid #475569; background: #1e293b; color: #e2e8f0; font-size: 13px; font-weight: 600; cursor: pointer; }
        .ctrl-btn:hover { border-color: #38bdf8; color: #7dd3fc; }
        .ctrl-btn.is-off { border-color: #f87171; color: #fecaca; background: rgba(127,29,29,.35); }
        .ctrl-btn.is-on { border-color: #34d399; color: #a7f3d0; }
    </style>
</head>
<body>
    <div id="join-screen" class="min-h-screen flex flex-col items-center justify-center p-4">
        <div class="w-full max-w-md rounded-2xl bg-slate-800/90 border border-slate-600 p-6 shadow-2xl">
            <div class="text-center mb-6">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/20 text-emerald-300 flex items-center justify-center mx-auto mb-4">
                    <i class="fas fa-bolt text-3xl"></i>
                </div>
                <h1 class="text-xl font-bold">Muallimx · LiveKit</h1>
                <p class="text-slate-400 text-sm mt-1">تجربة جودة — نفس السيرفر بجانب Jitsi</p>
            </div>
            @if($meeting && $meeting->title)
                <p class="text-slate-300 text-sm mb-4 text-center">{{ $meeting->title }}</p>
            @endif
            <p class="text-slate-400 text-xs mb-4 text-center">كود: <span class="font-mono font-bold text-emerald-300 text-lg">{{ $code }}</span></p>
            <label class="block text-sm font-medium text-slate-300 mb-1">اسمك</label>
            <input type="text" id="guest-name" placeholder="أدخل اسمك" class="w-full px-4 py-3 rounded-xl bg-slate-700 border border-slate-600 text-white mb-4">
            <button type="button" id="btn-join" class="w-full px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-bold">
                انضم الآن
            </button>
            <p id="join-help" class="text-xs text-slate-500 mt-4 text-center"></p>
        </div>
    </div>

    <div id="meeting-screen" class="hidden h-screen flex flex-col">
        <header class="h-14 shrink-0 px-4 flex items-center justify-between border-b border-slate-700 bg-slate-900">
            <div class="min-w-0">
                <p class="text-xs text-emerald-300 font-semibold">LiveKit</p>
                <p class="text-sm font-bold truncate">{{ $meeting->title ?? $code }}</p>
            </div>
            <span id="lk-status" class="text-xs text-slate-400">…</span>
        </header>
        <main class="flex-1 min-h-0 overflow-auto p-3">
            <div id="lk-stage"></div>
        </main>
        <footer class="shrink-0 border-t border-slate-700 bg-slate-900 px-3 py-3 flex flex-wrap justify-center gap-2">
            <button type="button" id="btn-mic" class="ctrl-btn is-off"><i class="fas fa-microphone-slash"></i> ميكروفون</button>
            <button type="button" id="btn-cam" class="ctrl-btn is-off"><i class="fas fa-video-slash"></i> كاميرا</button>
            <button type="button" id="btn-share" class="ctrl-btn"><i class="fas fa-desktop"></i> شاشة</button>
            <button type="button" id="btn-leave" class="ctrl-btn" style="border-color:#f87171;color:#fecaca;">مغادرة</button>
        </footer>
    </div>

<script type="module">
import {
    Room,
    RoomEvent,
    Track,
    createLocalTracks,
    VideoPresets,
} from 'https://cdn.jsdelivr.net/npm/livekit-client@2.9.8/dist/livekit-client.esm.mjs';

const code = @json($code);
const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
const stage = document.getElementById('lk-stage');
const statusEl = document.getElementById('lk-status');
const helpEl = document.getElementById('join-help');

let room;
let joinToken = '';
let micOn = false;
let camOn = false;
let shareOn = false;
let localAudio;
let localVideo;
const tileMap = new Map();

function setHelp(msg, isErr) {
    helpEl.textContent = msg || '';
    helpEl.className = 'text-xs mt-4 text-center ' + (isErr ? 'text-rose-400' : 'text-slate-500');
}
function setStatus(t) { if (statusEl) statusEl.textContent = t; }

function tileKey(p, source) { return p.identity + ':' + source; }
function ensureTile(participant, source) {
    const key = tileKey(participant, source);
    let tile = tileMap.get(key);
    if (tile) return tile;
    tile = document.createElement('div');
    tile.className = 'lk-tile' + (source === Track.Source.ScreenShare ? ' is-screen' : '');
    const video = document.createElement('video');
    video.autoplay = true;
    video.playsInline = true;
    video.muted = participant.isLocal;
    const label = document.createElement('div');
    label.className = 'label';
    label.textContent = (participant.name || participant.identity) + (source === Track.Source.ScreenShare ? ' · شاشة' : '');
    tile.appendChild(video);
    tile.appendChild(label);
    stage.appendChild(tile);
    tileMap.set(key, tile);
    return tile;
}
function removeTile(participant, source) {
    const key = tileKey(participant, source);
    const tile = tileMap.get(key);
    if (!tile) return;
    tile.remove();
    tileMap.delete(key);
}
function attachTrack(track, participant) {
    if (track.kind === Track.Kind.Audio) {
        if (participant.isLocal) return;
        const el = track.attach();
        el.style.display = 'none';
        document.body.appendChild(el);
        return;
    }
    if (track.kind !== Track.Kind.Video) return;
    const tile = ensureTile(participant, track.source);
    track.attach(tile.querySelector('video'));
}
function detachTrack(track, participant) {
    track.detach().forEach((el) => el.remove());
    if (track.kind === Track.Kind.Video) removeTile(participant, track.source);
}

function paintMic() {
    const btn = document.getElementById('btn-mic');
    btn.classList.toggle('is-off', !micOn);
    btn.classList.toggle('is-on', micOn);
    btn.innerHTML = micOn ? '<i class="fas fa-microphone"></i> ميكروفون' : '<i class="fas fa-microphone-slash"></i> ميكروفون';
}
function paintCam() {
    const btn = document.getElementById('btn-cam');
    btn.classList.toggle('is-off', !camOn);
    btn.classList.toggle('is-on', camOn);
    btn.innerHTML = camOn ? '<i class="fas fa-video"></i> كاميرا' : '<i class="fas fa-video-slash"></i> كاميرا';
}
function paintShare() {
    const btn = document.getElementById('btn-share');
    btn.classList.toggle('is-on', shareOn);
    btn.innerHTML = shareOn ? '<i class="fas fa-desktop"></i> إيقاف' : '<i class="fas fa-desktop"></i> شاشة';
}

async function connectLiveKit(url, token) {
    room = new Room({
        adaptiveStream: true,
        dynacast: true,
        videoCaptureDefaults: { resolution: VideoPresets.h720.resolution },
        publishDefaults: {
            videoSimulcastLayers: [VideoPresets.h180, VideoPresets.h360],
            screenShareEncoding: { maxBitrate: 3_500_000, maxFramerate: 30 },
            videoCodec: 'vp8',
        },
    });
    room
        .on(RoomEvent.TrackSubscribed, (track, _pub, p) => attachTrack(track, p))
        .on(RoomEvent.TrackUnsubscribed, (track, _pub, p) => detachTrack(track, p))
        .on(RoomEvent.LocalTrackPublished, (pub, p) => { if (pub.track) attachTrack(pub.track, p); })
        .on(RoomEvent.LocalTrackUnpublished, (pub, p) => { if (pub.track) detachTrack(pub.track, p); })
        .on(RoomEvent.Disconnected, () => setStatus('انقطع'))
        .on(RoomEvent.Reconnecting, () => setStatus('إعادة اتصال…'))
        .on(RoomEvent.Reconnected, () => setStatus('متصل'));

    await room.connect(url, token);
    setStatus('متصل');
    room.remoteParticipants.forEach((p) => {
        p.trackPublications.forEach((pub) => { if (pub.track) attachTrack(pub.track, p); });
    });
}

document.getElementById('btn-join').addEventListener('click', async () => {
    const name = document.getElementById('guest-name').value.trim() || 'ضيف';
    const btn = document.getElementById('btn-join');
    btn.disabled = true;
    btn.textContent = 'جاري الانضمام…';
    setHelp('جاري التحقق…', false);
    try {
        const enterResp = await fetch(`/classroom/join/${code}/enter`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ display_name: name }),
        });
        const enterData = await enterResp.json();
        if (!enterResp.ok || !enterData.ok) {
            throw new Error(enterData.message || 'لا يمكن الانضمام');
        }
        if (!enterData.livekit || !enterData.livekit.token) {
            throw new Error('لم يُرجع الخادم توكن LiveKit');
        }
        joinToken = enterData.token;
        document.getElementById('join-screen').classList.add('hidden');
        document.getElementById('meeting-screen').classList.remove('hidden');
        document.body.classList.add('in-room');
        await connectLiveKit(enterData.livekit.url, enterData.livekit.token);
        paintMic(); paintCam(); paintShare();
    } catch (e) {
        setHelp(e.message || String(e), true);
        btn.disabled = false;
        btn.innerHTML = 'انضم الآن';
    }
});

document.getElementById('btn-mic').addEventListener('click', async () => {
    if (!room) return;
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
    if (!room) return;
    if (!localVideo) {
        const tracks = await createLocalTracks({ audio: false, video: { resolution: VideoPresets.h720.resolution } });
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
    if (!room) return;
    try {
        if (!shareOn) {
            await room.localParticipant.setScreenShareEnabled(true, {
                audio: false,
                resolution: { width: 1920, height: 1080, frameRate: 30 },
            });
            shareOn = true;
        } else {
            await room.localParticipant.setScreenShareEnabled(false);
            shareOn = false;
        }
        paintShare();
    } catch (e) {
        alert('تعذر مشاركة الشاشة');
    }
});

document.getElementById('btn-leave').addEventListener('click', async () => {
    try {
        if (joinToken) {
            await fetch(`/classroom/join/${code}/leave`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ token: joinToken }),
            });
        }
    } catch (_) {}
    try { room && room.disconnect(); } catch (_) {}
    location.reload();
});

window.addEventListener('beforeunload', () => {
    try { room && room.disconnect(); } catch (_) {}
});
</script>
</body>
</html>

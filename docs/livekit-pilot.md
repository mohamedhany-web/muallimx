# LiveKit Classroom (Meet.Line parity)

Site-wide engine switch: Admin → سيرفرات البث → **محرك الفيديو للموقع** (`jitsi` | `livekit`).

## Signaling

Preferred client URL:

```env
LIVEKIT_URL=wss://live.muallimx.com/livekit
LIVEKIT_API_KEY=...
LIVEKIT_API_SECRET=...
LIVEKIT_ENABLED=true
```

Keys live on the meet VPS at `/opt/livekit/KEYS.env` — copy into the **app** `.env`, then `php artisan config:clear`.

## What LiveKit Classroom includes now

| Feature | Status |
|---------|--------|
| Meet.Line shell (host + guest) | Done |
| Mic / cam / 1080p screen share | Done |
| Participants list + mute/kick (host) | Done (data-channel fallback) |
| Chat + raise hand (DataChannel) | Done |
| Guest permissions + heartbeat | Done |
| Token `canPublishSources` | Done |
| Excalidraw whiteboard sync | Done (same HTTP APIs) |
| Local MediaRecorder + R2 upload | Done (no Egress) |
| Connection quality meter | Done |
| Noise constraint toggle | Best-effort browser |
| Full VBG ML / LiveKit Egress | Deferred |

## Code map

- Shared client: `public/js/classroom-livekit-room.js` (inlined into blades)
- Host: `resources/views/student/classroom/room-livekit.blade.php`
- Guest: `resources/views/classroom/join-livekit.blade.php`
- Styles: `public/css/classroom-livekit.css` + Meet.Line CSS
- Tokens: `app/Services/LiveKitTokenService.php`

## Deferred

- LiveKit Egress / server-side recording (needs stronger VPS)
- Document PiP, closed captions
- Live Sessions product (non-Classroom) on LiveKit

# LiveKit site-wide (Jitsi retired from app)

## App

- Provider is **always LiveKit** (`LiveSetting::usesLiveKit()` → true).
- Classroom host/guest, Live Sessions, and academic observers use LiveKit rooms.
- PiP Jitsi window redirects to the main LiveKit room.
- Requires on **app** `.env`:

```env
LIVEKIT_ENABLED=true
LIVEKIT_URL=wss://live.muallimx.com/livekit
LIVEKIT_API_KEY=...
LIVEKIT_API_SECRET=...
```

Then: `php artisan migrate` + `php artisan config:clear` + `php artisan view:clear`.

## Features shipping now

| Feature | Status |
|---------|--------|
| Meet.Line immersive Classroom | Done |
| Mic / cam / screen + side filmstrip | Done |
| Grid / speaker layout + focus + PiP float | Done |
| Share float chrome while presenting | Done |
| Chat / hand / emoji reactions | Done |
| Laser + annotate on share | Done |
| Krisp (when supported) + WebRTC noise | Done |
| Guest waiting room (auto-enter when host starts) | Done |
| Local lecture + audio report → R2 | Done |
| Live Sessions on LiveKit | Done (A/V/chat/share) |
| Academic observer (subscribe-only) | Done |
| Guest permissions | Done |

## Next LiveKit upgrades (optional)

| Feature | Effort | Notes |
|---------|--------|-------|
| Server Egress recording | L | Needs egress worker on VPS |
| Full VBG (`@livekit/track-processors`) | M | Replace browser alert |
| Host admit/deny lobby (beyond wait-for-start) | M | LiveKit Server API |
| Breakout rooms | L | Multi-room tokens |
| Closed captions | L | STT pipeline |
| True RoomService mute | M | Server API |
| Browser Document PiP | M | Over LiveKit tiles |

## Remove Jitsi from meet VPS

See `docs/remove-jitsi-keep-livekit.md`. **Only after** production smoke on LiveKit succeeds.

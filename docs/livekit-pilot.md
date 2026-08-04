# LiveKit pilot (alongside Jitsi)

Trial setup on the **same** meet VPS (`live.muallimx.com`, 2 vCPU / 8 GB) to A/B quality vs Jitsi. Jitsi stays the default; LiveKit is optional per Classroom meeting.

## Server (already on meet VPS)

| Item | Value |
|------|--------|
| Compose | `/opt/livekit/docker-compose.yml` |
| Config | `/opt/livekit/livekit.yaml` |
| Keys | `/opt/livekit/KEYS.env` (chmod 600) |
| Container | `mx-livekit` (`network_mode: host`) |
| Signaling TLS | `https://live.muallimx.com:8443` → `127.0.0.1:7880` |
| WebSocket for clients | `wss://live.muallimx.com:8443` |
| RTC UDP | `50000–50100` |
| TURN | existing coturn on `3478` (no second TURN in LiveKit) |

Health checks:

```bash
docker ps --filter name=livekit
curl -sk -o /dev/null -w '%{http_code}\n' https://127.0.0.1:8443/
curl -sk -o /dev/null -w '%{http_code}\n' https://live.muallimx.com/
```

## App `.env` (muallimx.com app server — not the meet VPS)

Copy API key/secret from `/opt/livekit/KEYS.env` on the meet box into the **Laravel** `.env`:

```env
LIVEKIT_ENABLED=true
LIVEKIT_URL=wss://live.muallimx.com:8443
LIVEKIT_API_KEY=...
LIVEKIT_API_SECRET=...
```

Then:

```bash
php artisan config:clear
```

Without these, creating a LiveKit meeting fails with a clear error, and guests cannot mint tokens.

## How to try

1. Classroom → إنشاء اجتماع → **محرك الفيديو: LiveKit**.
2. Host opens the LiveKit room UI (mic / cam / screen share).
3. Share `/classroom/join/{code}` — guests get the LiveKit join page automatically.
4. Create a second meeting with **Jitsi** and compare share clarity / stability.

## Limits (same VPS)

- Trial only: a few small rooms.
- Avoid heavy Jibri recording + LiveKit load at the same time.
- Prefer a dedicated LiveKit VPS before production traffic.

## Code map

- `App\Services\LiveKitTokenService` — HS256 token mint
- `config/services.php` → `livekit`
- `ClassroomMeeting::liveProvider()` / `usesLiveKit()` via `settings.live_provider`
- Host: `resources/views/student/classroom/room-livekit.blade.php`
- Guest: `resources/views/classroom/join-livekit.blade.php`

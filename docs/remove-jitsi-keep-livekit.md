# Remove Jitsi on live.muallimx.com — keep LiveKit

Run on the meet VPS **after** the Laravel app is LiveKit-only and smoke-tested.

## Keep

- `/opt/livekit/` + `mx-livekit` container
- nginx TLS for `live.muallimx.com` and `location ^~ /livekit/`
- coturn / TURN
- certificates (Jitsi cert path may still be referenced — copy/symlink before purge)

## Stop Jitsi stack (Debian/Ubuntu packages typical)

```bash
# Snapshot / backup first
systemctl stop jitsi-videobridge2 jicofo prosody jitsi-meet 2>/dev/null || true
systemctl disable jitsi-videobridge2 jicofo prosody 2>/dev/null || true

# Optional Jibri
systemctl stop jibri jibri2 2>/dev/null || true
systemctl disable jibri jibri2 2>/dev/null || true

apt-get purge -y 'jitsi-*' 'jicofo' 'jitsi-meet*' 'jitsi-videobridge*' 2>/dev/null || true
# Do NOT remove nginx, certbot, coturn, docker, livekit

# Backup then remove configs
mkdir -p /root/backup-jitsi-$(date +%F)
cp -a /etc/jitsi /root/backup-jitsi-$(date +%F)/ 2>/dev/null || true
# After verify LiveKit: rm -rf /etc/jitsi /usr/share/jitsi-meet
```

## Nginx

Keep `/livekit/` proxy to `127.0.0.1:7880`. Remove only Jitsi `external_api.js` / meet web roots if separate.

## Verify

```bash
docker ps | grep -i livekit
curl -sI https://live.muallimx.com/livekit/ | head -5
ss -ulnp | grep -E '50000|7880' || true
```

From app: open Classroom host + guest join + Live Session room.

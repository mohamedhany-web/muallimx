#!/usr/bin/env bash
# مثال سكربت finalize لـ Jibri — انسخه إلى السيرفر وعدّل القيم.
# الاستخدام: mx-classroom-jibri-finalize.sh <recording_file> [room_name] [classroom_meeting_id] [duration_seconds]
set -euo pipefail

RECORDING_FILE="${1:-}"
ROOM_NAME="${2:-}"
MEETING_ID="${3:-}"
DURATION="${4:-0}"

if [[ -z "$RECORDING_FILE" || ! -f "$RECORDING_FILE" ]]; then
  echo "usage: $0 <recording_file> [room_name] [classroom_meeting_id] [duration_seconds]" >&2
  exit 1
fi

: "${MX_R2_BUCKET:?set MX_R2_BUCKET}"
: "${MX_R2_ENDPOINT:?set MX_R2_ENDPOINT}"
: "${MX_WEBHOOK_URL:?set MX_WEBHOOK_URL}"
: "${MX_WEBHOOK_TOKEN:?set MX_WEBHOOK_TOKEN}"

STAMP="$(date +%Y/%m)"
BASE="$(basename "$RECORDING_FILE")"
SAFE_ROOM="$(echo "${ROOM_NAME:-meeting}" | tr -cd 'A-Za-z0-9._-')"
KEY="classroom-recordings/${STAMP}/${SAFE_ROOM}-$(date +%Y%m%d-%H%M%S)-${BASE}"

aws s3 cp "$RECORDING_FILE" "s3://${MX_R2_BUCKET}/${KEY}" --endpoint-url "${MX_R2_ENDPOINT}"

SIZE="$(stat -c%s "$RECORDING_FILE" 2>/dev/null || wc -c < "$RECORDING_FILE" | tr -d ' ')"
MIME="video/mp4"
case "${BASE,,}" in
  *.webm) MIME="video/webm" ;;
  *.mkv) MIME="video/x-matroska" ;;
esac

if [[ -n "$MEETING_ID" && "$MEETING_ID" != "null" ]]; then
  ID_JSON="$MEETING_ID"
else
  ID_JSON="null"
fi

curl -sS -X POST "$MX_WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -H "X-Webhook-Token: ${MX_WEBHOOK_TOKEN}" \
  -d "{\"classroom_meeting_id\":${ID_JSON},\"room_name\":\"${ROOM_NAME}\",\"file_path\":\"${KEY}\",\"mime_type\":\"${MIME}\",\"duration_seconds\":${DURATION},\"file_size\":${SIZE}}"

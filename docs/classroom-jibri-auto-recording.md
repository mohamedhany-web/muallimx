# تسجيل محاضرات Classroom تلقائياً عبر Jibri (بدون مشاركة تبويب)

هذا الدليل يفعّل التسجيل من سيرفر الاجتماع `live.muallimx.com` بحيث المدرب يضغط «تسجيل المحاضرة» فقط، ويُسجَّل الفيديو + أصوات الجميع بدون اختيار تبويب وبدون أي إجراء من المشاركين.

## ماذا يفعل تطبيق Muallimx؟

1. عند «تسجيل المحاضرة» يستدعي `startRecording` بوضع `file`.
2. عند الإيقاف يستدعي `stopRecording`.
3. بعد انتهاء Jibri ورفع الملف إلى R2، يستدعي السكربت ويب هوك:
   - `POST https://muallimx.com/api/classroom-recordings/register`
   - هيدر: `X-Webhook-Token: <LIVE_RECORDINGS_WEBHOOK_TOKEN>`
   - جسم JSON:

```json
{
  "classroom_meeting_id": 123,
  "room_name": "mx-room-code",
  "file_path": "classroom-recordings/2026/07/meeting-123-jibri-....mp4",
  "mime_type": "video/mp4",
  "duration_seconds": 600,
  "file_size": 12345678
}
```

يمكن إرسال `classroom_meeting_id` أو `room_name` (أو الاثنين). التطبيق يمرّرهما في `extraMetadata` عند بدء التسجيل.

## إعداد `.env` على Muallimx

```env
LIVE_RECORDINGS_WEBHOOK_TOKEN=ضع_رمزا_قويا_هنا
```

نفس التوكن يُستخدم أيضاً لويب هوك جلسات البث القديمة (`/api/live-recordings/register`).

ثم:

```bash
php artisan config:clear
```

## على سيرفر live.muallimx.com

### 1) تثبيت Jibri وربطه بـ Jitsi

اتبع دليل Jitsi الرسمي لتثبيت Jibri على نفس الـ VPS أو آلة منفصلة، مع:

- حساب XMPP للمسجّل (`recorder@...` / `jibri@...`)
- `hiddenDomain` للمشاركين المخفيين
- تفعيل التسجيل في Prosody / Jicofo

### 2) تفعيل الإعدادات في config.js

في `/etc/jitsi/meet/live.muallimx.com-config.js` ألغِ التعليق وفعّل تقريباً:

```js
recordingService: {
    enabled: true,
    sharingEnabled: false,
    hideStorageWarning: true,
},
fileRecordingsEnabled: true,
fileRecordingsServiceEnabled: true,
```

أعد تحميل خدمات Jitsi بعد التعديل.

### 3) سكربت finalize (رفع R2 + ويب هوك)

مثال مبسّط لـ `/usr/local/bin/mx-classroom-jibri-finalize.sh` (عدّل المسارات والمفاتيح):

```bash
#!/usr/bin/env bash
set -euo pipefail

RECORDING_FILE="${1:-}"
ROOM_NAME="${2:-}"
MEETING_ID="${3:-}"
DURATION="${4:-0}"

[[ -n "$RECORDING_FILE" && -f "$RECORDING_FILE" ]] || exit 1

BUCKET="muallimx"
KEY="classroom-recordings/$(date +%Y/%m)/${ROOM_NAME:-meeting}-$(date +%Y%m%d-%H%M%S)-$(basename "$RECORDING_FILE")"
WEBHOOK_URL="https://muallimx.com/api/classroom-recordings/register"
WEBHOOK_TOKEN="REPLACE_WITH_LIVE_RECORDINGS_WEBHOOK_TOKEN"

# رفع إلى Cloudflare R2 (متوافق مع S3)
aws s3 cp "$RECORDING_FILE" "s3://${BUCKET}/${KEY}" \
  --endpoint-url "https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com"

SIZE=$(stat -c%s "$RECORDING_FILE" 2>/dev/null || wc -c < "$RECORDING_FILE")

payload=$(cat <<EOF
{
  "classroom_meeting_id": ${MEETING_ID:-null},
  "room_name": "${ROOM_NAME}",
  "file_path": "${KEY}",
  "mime_type": "video/mp4",
  "duration_seconds": ${DURATION},
  "file_size": ${SIZE}
}
EOF
)

curl -sS -X POST "$WEBHOOK_URL" \
  -H "Content-Type: application/json" \
  -H "X-Webhook-Token: ${WEBHOOK_TOKEN}" \
  -d "$payload"
```

اربط هذا السكربت من إعدادات Jibri `finalize_recording_script_path` (أو الـ hook المعتمد في إصداركم)، ومرّروا `room_name` ومعرّف الاجتماع من metadata إن وُجد.

### 4) تجربة سريعة

1. ادخل غرفة Classroom كمدرب.
2. اضغط تسجيل المحاضرة — **لا** يجب أن تظهر رسالة اختيار تبويب.
3. تأكد أن Jibri انضم للغرفة (مشارك مخفي).
4. أوقف التسجيل.
5. بعد دقائق يظهر الملف في صفحة الاجتماع عبر الويب هوك.

## ملاحظات

- بدون Jibri يعمل التطبيق بمحاولة احتياطية محلية صامتة؛ إن فشلت يظهر تنبيه قصير يطلب تفعيل تسجيل السيرفر.
- المشاركون لا يفعلون شيئاً في كل الأحوال.
- عزل الضوضاء يبقى مفعّلاً تلقائياً من واجهة الغرفة.

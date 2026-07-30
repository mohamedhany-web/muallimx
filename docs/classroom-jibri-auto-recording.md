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

## حالة السيرفر (محدَّثة)

على `live.muallimx.com` تم تثبيت وربط Jibri:

| عنصر | الحالة |
|------|--------|
| خدمة `jibri` | `active` — IDLE / HEALTHY |
| Brewery MUC | `jibribrewery@internal.auth.live.muallimx.com` |
| `config.js` | `recordingService.enabled` + `hiddenDomain: recorder.live.muallimx.com` |
| ALSA loopback | مفعّل (`snd-aloop`) |
| Finalize | `/usr/local/bin/mx-classroom-jibri-finalize.sh` → R2 |
| أسرار Finalize | `/etc/jitsi/jibri/mx-finalize.env` (صلاحيات 600) |

اختبار دخان ناجح: بدء تسجيل → ملف MP4 → رفع إلى `s3://muallimx/classroom-recordings/...`.

**ما زال مطلوباً على استضافة Laravel (cPanel / `muallimx.com`):**

```env
LIVE_RECORDINGS_WEBHOOK_TOKEN=<نفس القيمة في .env المحلي / mx-finalize.env>
```

ثم `php artisan config:clear` على الاستضافة. بدون هذا السطر يبقى رفع R2 ناجحاً لكن ربط الملف بالاجتماع يردّ `401 Unauthorized`.

فحص سريع للخدمة:

```bash
systemctl status jibri --no-pager
curl -sS http://127.0.0.1:2222/jibri/api/v1.0/health
curl -sS https://live.muallimx.com/config.js | grep -E 'MX_JIBRI|hiddenDomain|recordingService'
```

### مرجع إعداد (إن احتجت إعادة التثبيت)

- حسابات Prosody: `jibri@auth.live.muallimx.com` و `recorder@recorder.live.muallimx.com`
- Jicofo: `jibri { brewery-jid = "JibriBrewery@internal.auth.live.muallimx.com" }`
- مثال السكربت: `tools/mx-classroom-jibri-finalize.example.sh`

### تجربة من Classroom

1. ادخل غرفة Classroom كمدرب.
2. اضغط تسجيل المحاضرة — **لا** يجب أن تظهر رسالة اختيار تبويب.
3. تأكد أن Jibri انضم للغرفة (مشارك مخفي).
4. أوقف التسجيل.
5. بعد انتهاء الرفع يظهر الملف عبر الويب هوك (بعد ضبط التوكن على الإنتاج).

## ملاحظات

- السيرفر الحالي ~2 vCPU؛ التسجيل يعمل، لكن تجنّب تسجيلات متزامنة كثيرة على نفس الجهاز.
- بدون Jibri يعمل التطبيق بمحاولة احتياطية محلية صامتة؛ إن فشلت يظهر تنبيه قصير.
- المشاركون لا يفعلون شيئاً في كل الأحوال.
- عزل الضوضاء يبقى مفعّلاً تلقائياً من واجهة الغرفة.
- يُفضّل تدوير كلمة مرور SSH للـ VPS إن سبق مشاركتها في محادثة.

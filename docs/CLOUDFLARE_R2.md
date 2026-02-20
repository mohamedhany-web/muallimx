# رفع ملفات المجتمع (مساهمون + أدمن) على Cloudflare R2

ملفات مجموعات البيانات من المساهمين والأدمن تُرفع إلى القرص المُعرّف في `config/filesystems.php` تحت مفتاح **community_disk**. يمكن أن يكون `local` (التطوير) أو `r2` (الإنتاج).

## الخطوات

### 1. إنشاء API Token من Cloudflare

- الدخول إلى: **Cloudflare Dashboard → R2 → Manage R2 API Tokens**
- **Create API Token** → اختيار **R2 Token**
- الصلاحيات: **Object Read & Write**
- حفظ: **Access Key ID** و **Secret Access Key** و **Endpoint**

### 2. إعداد ملف `.env`

```env
# قرص ملفات المجتمع: استخدم r2 لتفعيل R2
FILESYSTEM_DISK_COMMUNITY=r2

# بيانات R2 (نفس أسماء متغيرات AWS لأن R2 متوافق مع S3)
AWS_ACCESS_KEY_ID=ضع_access_key_هنا
AWS_SECRET_ACCESS_KEY=ضع_secret_key_هنا
AWS_DEFAULT_REGION=auto
AWS_BUCKET=اسم_bucket_الذي_أنشأته
AWS_ENDPOINT=https://YOUR_ACCOUNT_ID.r2.cloudflarestorage.com
AWS_USE_PATH_STYLE_ENDPOINT=true
```

(استبدل `YOUR_ACCOUNT_ID` بمعرّف الحساب من لوحة Cloudflare.)

### 3. مسح الكاش

```bash
php artisan config:clear
php artisan cache:clear
```

### 4. التجربة

- من لوحة المساهم: رفع مجموعة بيانات (ملف).
- من أدمن المجتمع: مراجعة التقديمات → عرض البيانات / تحميل الملف.

الملفات المُرفعة سابقاً على `local` تبقى في `storage/app/private/community_datasets/`. بعد التبديل إلى `r2` الملفات الجديدة فقط ستُرفع على R2.

## رابط تحميل عام من R2 (اختياري)

إذا فعّلت **Public Access** على الـ bucket وربطت دومين (مثلاً عبر R2 custom domain)، يمكنك تعيين:

```env
AWS_URL=https://your-public-bucket-url.r2.dev
```

للاستخدام مع `Storage::disk('r2')->url($path)` إن أردت روابط تحميل مباشرة لاحقاً.

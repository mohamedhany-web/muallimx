<?php

namespace App\Services;

use App\Models\Setting;

/**
 * إعدادات أمان المنصة المخزنة في جدول settings (مع احترام .env كقيمة افتراضية).
 */
class PlatformSecuritySettings
{
    public const SETTING_KEY_ADMIN_2FA_REQUIRED = 'admin_2fa_required';

    public static function isAdminTwoFactorRequired(): bool
    {
        $v = Setting::getValue(self::SETTING_KEY_ADMIN_2FA_REQUIRED);
        if ($v === null || $v === '') {
            return (bool) config('app.admin_2fa_required', false);
        }

        return in_array(strtolower(trim($v)), ['1', 'true', 'yes', 'on'], true);
    }

    public static function setAdminTwoFactorRequired(bool $enabled): void
    {
        Setting::setValue(self::SETTING_KEY_ADMIN_2FA_REQUIRED, $enabled ? '1' : '0');
    }
}

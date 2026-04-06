<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use App\Services\AdminPanelBranding;
use App\Services\PublicFooterSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Throwable;

class SystemSettingsController extends Controller
{
    public function __construct()
    {
        $this->middleware('can:manage.system-settings');
    }

    public function edit(): View
    {
        $defaults = PublicFooterSettings::defaults();
        $values = [];
        foreach (array_keys($defaults) as $key) {
            $stored = Setting::getValue($key);
            $values[$key] = $stored ?? '';
        }

        $adminPanelLogoUrl = AdminPanelBranding::logoPublicUrl();

        return view('admin.system-settings.edit', compact('defaults', 'values', 'adminPanelLogoUrl'));
    }

    public function update(Request $request): RedirectResponse
    {
        $footerRules = [
            'footer_brand_tagline' => ['nullable', 'string', 'max:160'],
            'footer_blurb' => ['nullable', 'string', 'max:600'],
            'footer_email' => ['nullable', 'email', 'max:190'],
            'footer_phone' => ['nullable', 'string', 'max:80'],
            'footer_whatsapp_url' => ['nullable', 'string', 'max:500'],
            'footer_bottom_tagline' => ['nullable', 'string', 'max:200'],
            'social_facebook_url' => ['nullable', 'string', 'max:500'],
            'social_x_url' => ['nullable', 'string', 'max:500'],
            'social_instagram_url' => ['nullable', 'string', 'max:500'],
            'social_youtube_url' => ['nullable', 'string', 'max:500'],
            'social_linkedin_url' => ['nullable', 'string', 'max:500'],
            'social_tiktok_url' => ['nullable', 'string', 'max:500'],
            'social_telegram_url' => ['nullable', 'string', 'max:500'],
            'social_snapchat_url' => ['nullable', 'string', 'max:500'],
        ];

        $footerData = [];
        foreach (PublicFooterSettings::editableKeys() as $key) {
            $v = $request->input($key, '');
            $footerData[$key] = ($v === null || $v === '') ? null : (is_string($v) ? trim($v) : $v);
        }

        $validated = Validator::make(
            array_merge($footerData, [
                'admin_panel_logo' => $request->file('admin_panel_logo'),
                'remove_admin_panel_logo' => $request->boolean('remove_admin_panel_logo'),
            ]),
            array_merge($footerRules, [
                'admin_panel_logo' => ['nullable', 'image', 'max:'.config('upload_limits.max_upload_kb'), 'mimes:jpg,jpeg,png,webp,gif'],
                'remove_admin_panel_logo' => ['nullable', 'boolean'],
            ])
        )->validate();

        foreach (PublicFooterSettings::editableKeys() as $key) {
            $raw = isset($validated[$key]) && $validated[$key] !== null ? trim((string) $validated[$key]) : '';
            if ($raw !== '' && str_starts_with($key, 'social_') && str_ends_with($key, '_url')) {
                if (! filter_var($raw, FILTER_VALIDATE_URL)) {
                    return back()->withErrors([$key => 'رابط غير صالح.'])->withInput();
                }
            }
            if ($raw !== '' && $key === 'footer_whatsapp_url' && ! filter_var($raw, FILTER_VALIDATE_URL)) {
                return back()->withErrors(['footer_whatsapp_url' => 'رابط واتساب غير صالح.'])->withInput();
            }
        }

        try {
            if ($request->boolean('remove_admin_panel_logo')) {
                AdminPanelBranding::removeLogo();
            }
            if ($request->hasFile('admin_panel_logo')) {
                AdminPanelBranding::storeLogo($request->file('admin_panel_logo'));
            }
        } catch (Throwable $e) {
            report($e);

            return back()->withErrors([
                'admin_panel_logo' => 'تعذّر رفع أو حذف الشعار. إن كنت تستخدم Cloudflare R2 فتأكد من AWS_* و AWS_URL ثم نفّذ php artisan config:clear.',
            ])->withInput();
        }

        foreach (PublicFooterSettings::editableKeys() as $key) {
            $raw = isset($validated[$key]) && $validated[$key] !== null ? trim((string) $validated[$key]) : '';
            Setting::setValue($key, $raw !== '' ? $raw : null);
        }

        PublicFooterSettings::forgetCache();

        return redirect()->route('admin.system-settings.edit')->with('success', 'تم حفظ إعدادات النظام بنجاح.');
    }
}

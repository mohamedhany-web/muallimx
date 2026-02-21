<?php

namespace App\Http\Controllers\Community;

use App\Http\Controllers\Controller;
use App\Http\Requests\CommunityLoginRequest;
use App\Http\Requests\CommunityRegisterRequest;
use App\Models\User;
use App\Services\Community\CommunityRegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * مصادقة مجتمع البيانات: تسجيل دخول وإنشاء حساب منفصلين عن المنصة الرئيسية.
 * يستخدم نفس جدول المستخدمين لتمكين المستخدمين الحاليين من الدخول بدون إنشاء حساب جديد.
 */
class AuthController extends Controller
{
    public function __construct(
        protected CommunityRegistrationService $registrationService
    ) {}

    public function showLogin(): View
    {
        return view('community.auth.login');
    }

    public function showRegister(): View
    {
        $data = $this->registrationService->getPhoneCountriesData();
        return view('community.auth.register', $data);
    }

    public function login(CommunityLoginRequest $request): RedirectResponse
    {
        $key = $request->rateLimitKey();
        if (RateLimiter::tooManyAttempts($key, 5)) {
            $seconds = RateLimiter::availableIn($key);
            return back()->withErrors([
                'email' => "تم تجاوز عدد المحاولات. يرجى المحاولة بعد {$seconds} ثانية.",
            ])->withInput();
        }

        $email = trim($request->validated('email'));
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($email)])->first();

        if (!$user) {
            RateLimiter::hit($key, 15 * 60);
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])->withInput();
        }

        if (!$user->is_active) {
            RateLimiter::hit($key, 15 * 60);
            return back()->withErrors(['email' => 'حسابك غير نشط. يرجى التواصل مع الإدارة.'])->withInput();
        }

        if (!Hash::check($request->validated('password'), $user->password)) {
            RateLimiter::hit($key, 15 * 60);
            return back()->withErrors(['email' => 'بيانات الدخول غير صحيحة.'])->withInput();
        }

        RateLimiter::clear($key);

        // مجتمع الذكاء الاصطناعي: لا نفرض 2FA — الدخول مباشرة إلى لوحة المجتمع
        Auth::login($user, $request->boolean('remember'));
        $request->session()->regenerate();
        $user->update(['last_login_at' => now()]);

        // إزالة أي توجيه سابق محفوظ لصفحة المنصة الرئيسية حتى لا يُعاد التوجيه لـ /login
        $request->session()->forget('url.intended');

        // توجيه فوري إلى لوحة تحكم المجتمع (دائماً من صفحة community/login)
        if ($user->is_community_contributor) {
            return redirect()->route('community.contributor.dashboard');
        }
        return redirect()->route('community.dashboard');
    }

    public function register(CommunityRegisterRequest $request): RedirectResponse
    {
        try {
            $user = $this->registrationService->validateAndCreate($request);
        } catch (ValidationException $e) {
            $data = $this->registrationService->getPhoneCountriesData();
            return back()->withErrors($e->errors())->withInput()->with($data);
        }

        Auth::login($user);
        $request->session()->regenerate();

        $request->session()->forget('url.intended');
        return redirect()->route('community.dashboard');
    }
}

<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\TeacherFeaturesController;
use App\Models\SubscriptionRequest;
use App\Models\Wallet;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class SubscriptionCheckoutController extends Controller
{
    protected const VALID_PLANS = ['teacher_starter', 'teacher_pro', 'teacher_premium'];

    /**
     * عرض صفحة دفع اشتراك الباقة (تحويل المبلغ + رفع إيصال الدفع)
     */
    public function show(string $plan)
    {
        if (!in_array($plan, self::VALID_PLANS, true)) {
            return redirect()->route('public.pricing')->with('error', 'الباقة غير صحيحة.');
        }

        if (!Auth::check()) {
            return redirect()->guest(route('login', ['intended' => url()->current()]))
                ->with('info', 'يرجى تسجيل الدخول أولاً لدفع اشتراك الباقة.');
        }

        $featuresController = new TeacherFeaturesController();
        $settings = $featuresController->getSettings();
        $planConfig = $settings[$plan] ?? null;

        if (!$planConfig) {
            $planConfig = [
                'label' => SubscriptionRequest::planDefaults($plan)['plan_name'] ?? $plan,
                'price' => SubscriptionRequest::planDefaults($plan)['price'] ?? 0,
                'billing_cycle' => SubscriptionRequest::planDefaults($plan)['billing_cycle'] ?? 'monthly',
                'features' => SubscriptionRequest::planDefaults($plan)['features'] ?? [],
            ];
        } else {
            $planConfig['label'] = $planConfig['label'] ?? ($plan === 'teacher_starter' ? 'باقة البداية' : ($plan === 'teacher_pro' ? 'باقة المعلم المحترف' : 'باقة المعلم المميز'));
        }

        $billingLabel = [
            'monthly' => 'شهري',
            'quarterly' => 'كل 3 أشهر',
            'yearly' => 'سنوي',
        ][$planConfig['billing_cycle'] ?? 'monthly'] ?? $planConfig['billing_cycle'];

        $wallets = Wallet::where('is_active', true)
            ->whereNotNull('type')
            ->whereIn('type', ['vodafone_cash', 'instapay', 'bank_transfer'])
            ->where(function ($q) {
                $q->whereNotNull('account_number')->orWhereNotNull('name');
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return view('public.subscription-checkout', [
            'planKey' => $plan,
            'plan' => $planConfig,
            'billingLabel' => $billingLabel,
            'wallets' => $wallets,
        ]);
    }

    /**
     * استلام دفع الاشتراك: رفع إيصال الدفع وإنشاء طلب اشتراك يظهر في لوحة الأدمن للمراجعة
     */
    public function store(Request $request)
    {
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً.');
        }

        $plan = $request->input('plan');
        if (!in_array($plan, self::VALID_PLANS, true)) {
            return redirect()->route('public.pricing')->with('error', 'الباقة غير صحيحة.');
        }

        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,wallet',
            'wallet_id' => [
                'nullable',
                'required_if:payment_method,wallet',
                Rule::exists('wallets', 'id')->where('is_active', true)->whereIn('type', ['vodafone_cash', 'instapay', 'bank_transfer']),
            ],
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:2048',
            'notes' => 'nullable|string|max:1000',
        ], [
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'payment_proof.required' => 'صورة إيصال الدفع مطلوبة بعد تحويل المبلغ',
            'payment_proof.image' => 'يجب أن يكون الملف صورة',
            'payment_proof.mimes' => 'صيغة الصورة: jpeg, png أو jpg',
            'payment_proof.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
            'wallet_id.required_if' => 'يجب اختيار المحفظة التي تم التحويل إليها',
        ]);

        $featuresController = new TeacherFeaturesController();
        $settings = $featuresController->getSettings();
        $planConfig = $settings[$plan] ?? SubscriptionRequest::planDefaults($plan);

        $planName = $planConfig['label'] ?? $planConfig['plan_name'] ?? 'باقة المعلم';
        $price = (float) ($planConfig['price'] ?? 0);
        $billingCycle = $planConfig['billing_cycle'] ?? 'monthly';

        $existing = SubscriptionRequest::where('user_id', Auth::id())
            ->where('teacher_plan_key', $plan)
            ->where('status', SubscriptionRequest::STATUS_PENDING)
            ->first();

        if ($existing) {
            return redirect()->route('dashboard')
                ->with('info', 'لديك بالفعل طلب اشتراك قيد المراجعة لهذه الباقة.');
        }

        $paymentProofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

        SubscriptionRequest::create([
            'user_id' => Auth::id(),
            'teacher_plan_key' => $plan,
            'plan_name' => $planName,
            'price' => $price,
            'billing_cycle' => $billingCycle,
            'payment_method' => $validated['payment_method'],
            'payment_proof' => $paymentProofPath,
            'wallet_id' => $validated['wallet_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => SubscriptionRequest::STATUS_PENDING,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'تم استلام إيصال الدفع بنجاح. سيتم مراجعة التحويل وتفعيل اشتراكك خلال أقرب وقت.');
    }
}

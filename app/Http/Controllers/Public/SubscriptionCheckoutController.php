<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Admin\TeacherFeaturesController;
use App\Models\SubscriptionRequest;
use App\Models\Wallet;
use App\Services\FawaterakApiService;
use App\Services\FawaterakService;
use App\Services\PaymentGatewaySettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class SubscriptionCheckoutController extends Controller
{
    protected const VALID_PLANS = ['teacher_starter', 'teacher_pro', 'teacher_premium'];
    protected const PLAN_RANK = ['teacher_starter' => 1, 'teacher_pro' => 2, 'teacher_premium' => 3];

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

        $fawaterakGatewayOn = PaymentGatewaySettings::isFawaterakEnabled();
        $fawaterakApi = app(FawaterakApiService::class);
        $fawaterakIframe = app(FawaterakService::class);
        $fawaterakIntegration = $fawaterakApi->integrationMode();
        $fawaterakReady = $fawaterakIntegration === 'api'
            ? $fawaterakApi->isConfigured()
            : $fawaterakIframe->isConfigured();
        $fawaterakUseGateway = $fawaterakGatewayOn && $fawaterakReady;
        $fawaterakMisconfigured = $fawaterakGatewayOn && ! $fawaterakReady;

        return view('public.subscription-checkout', [
            'planKey' => $plan,
            'plan' => $planConfig,
            'billingLabel' => $billingLabel,
            'wallets' => $wallets,
            'upgrade' => (bool) request()->boolean('upgrade'),
            'fromSubscriptionId' => request()->input('from'),
            'fawaterakUseGateway' => $fawaterakUseGateway,
            'fawaterakMisconfigured' => $fawaterakMisconfigured,
            'fawaterakIntegration' => $fawaterakIntegration,
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

        $upgrade = (bool) $request->boolean('upgrade');
        $fromSubscriptionId = $request->input('from');

        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,wallet',
            'wallet_id' => [
                'nullable',
                'required_if:payment_method,wallet',
                Rule::exists('wallets', 'id')->where('is_active', true)->whereIn('type', ['vodafone_cash', 'instapay', 'bank_transfer']),
            ],
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:'.config('upload_limits.max_upload_kb'),
            'notes' => 'nullable|string|max:1000',
            'upgrade' => 'nullable|boolean',
            'from' => 'nullable',
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

        // ترقية: تحقق من الاشتراك الحالي + منع الداونجريد
        $fromSubscription = null;
        if ($upgrade) {
            $fromSubscription = Auth::user()->activeSubscription();
            if (!$fromSubscription) {
                return redirect()->route('public.pricing')->with('error', 'لا يوجد اشتراك نشط للترقية منه.');
            }

            if (!empty($fromSubscriptionId) && (int) $fromSubscriptionId !== (int) $fromSubscription->id) {
                return redirect()->route('student.my-subscription')->with('error', 'بيانات الترقية غير صحيحة. حاول مرة أخرى.');
            }

            $fromKey = (string) ($fromSubscription->teacher_plan_key ?? '');
            $fromRank = self::PLAN_RANK[$fromKey] ?? 0;
            $toRank = self::PLAN_RANK[$plan] ?? 0;
            if ($fromRank <= 0 || $toRank <= 0) {
                return redirect()->route('student.my-subscription')->with('error', 'لا يمكن ترقية هذه الباقة حالياً.');
            }
            if ($toRank <= $fromRank) {
                return redirect()->route('student.my-subscription')->with('error', 'يسمح بالترقية إلى باقة أعلى فقط.');
            }
        }

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
            'from_teacher_plan_key' => $upgrade ? ($fromSubscription?->teacher_plan_key) : null,
            'plan_name' => $planName,
            'price' => $price,
            'billing_cycle' => $billingCycle,
            'request_type' => $upgrade ? 'upgrade' : 'new',
            'payment_method' => $validated['payment_method'],
            'payment_proof' => $paymentProofPath,
            'wallet_id' => $validated['wallet_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'status' => SubscriptionRequest::STATUS_PENDING,
            'from_subscription_id' => $upgrade ? ($fromSubscription?->id) : null,
        ]);

        return redirect()->route('dashboard')
            ->with('success', 'تم استلام إيصال الدفع بنجاح. سيتم مراجعة التحويل وتفعيل اشتراكك خلال أقرب وقت.');
    }

    /**
     * تجهيز دفع اشتراك الباقة عبر فواتيرك (IFrame أو API).
     */
    public function fawaterakPrepare(Request $request, string $plan): JsonResponse
    {
        if (! in_array($plan, self::VALID_PLANS, true)) {
            return response()->json(['message' => 'الباقة غير صحيحة.'], 404);
        }

        if (! PaymentGatewaySettings::isFawaterakEnabled()) {
            return response()->json(['message' => 'بوابة الدفع الإلكترونية غير مفعّلة.'], 403);
        }

        $api = app(FawaterakApiService::class);
        $iframe = app(FawaterakService::class);
        $integration = $api->integrationMode();

        if ($integration === 'api') {
            if (! $api->isConfigured()) {
                return response()->json(['message' => 'رمز Bearer لفواتيرك (FAWATERAK_API_TOKEN) غير مضبوط في ملف البيئة.'], 503);
            }
        } elseif (! $iframe->isConfigured()) {
            return response()->json(['message' => 'مفاتيح فواتيرك (Vendor/Provider) غير مضبوطة في ملف البيئة.'], 503);
        }

        if (! Auth::check()) {
            return response()->json(['message' => 'يجب تسجيل الدخول.'], 401);
        }

        $upgradeMsg = $this->upgradeValidationMessage($request, $plan);
        if ($upgradeMsg !== null) {
            return response()->json(['message' => $upgradeMsg], 422);
        }

        $featuresController = new TeacherFeaturesController();
        $settings = $featuresController->getSettings();
        $planConfig = $settings[$plan] ?? SubscriptionRequest::planDefaults($plan);
        $planName = $planConfig['label'] ?? $planConfig['plan_name'] ?? 'باقة المعلم';
        $price = (float) ($planConfig['price'] ?? 0);
        $billingCycle = $planConfig['billing_cycle'] ?? 'monthly';

        if ($price < 0.01) {
            return response()->json(['message' => 'هذه الباقة غير صالحة للدفع الإلكتروني.'], 422);
        }

        $upgrade = (bool) $request->boolean('upgrade');
        $fromSubscription = $upgrade ? Auth::user()->activeSubscription() : null;

        $existing = SubscriptionRequest::where('user_id', Auth::id())
            ->where('teacher_plan_key', $plan)
            ->where('status', SubscriptionRequest::STATUS_PENDING)
            ->first();

        if ($existing && $existing->payment_proof !== null) {
            return response()->json(['message' => 'لديك طلب قيد المراجعة برفع إيصال يدوي لهذه الباقة.'], 409);
        }

        $payload = [
            'user_id' => Auth::id(),
            'teacher_plan_key' => $plan,
            'from_teacher_plan_key' => $upgrade ? ($fromSubscription?->teacher_plan_key) : null,
            'plan_name' => $planName,
            'price' => $price,
            'billing_cycle' => $billingCycle,
            'request_type' => $upgrade ? 'upgrade' : 'new',
            'payment_method' => 'online',
            'payment_proof' => null,
            'wallet_id' => null,
            'notes' => null,
            'status' => SubscriptionRequest::STATUS_PENDING,
            'from_subscription_id' => $upgrade ? ($fromSubscription?->id) : null,
        ];

        if ($existing) {
            $existing->update($payload);
            $subRequest = $existing->fresh();
        } else {
            $subRequest = SubscriptionRequest::create($payload);
        }

        $request->session()->forget('fawaterak_order_id');
        $request->session()->put('fawaterak_subscription_request_id', $subRequest->id);

        $user = Auth::user();
        $email = trim((string) ($user->email ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'يرجى إضافة بريد إلكتروني صالح في ملفك الشخصي قبل الدفع.'], 422);
        }

        if ($integration === 'api') {
            return response()->json([
                'mode' => 'api',
                'methodsUrl' => route('public.subscription.checkout.fawaterak.methods', $plan),
                'payUrl' => route('public.subscription.checkout.fawaterak.pay', $plan),
            ]);
        }

        $fullName = trim((string) ($user->name ?? ''));
        $nameParts = preg_split('/\s+/u', $fullName, 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? $firstName;

        $phone = preg_replace('/\D/', '', (string) ($user->phone ?? ''));
        if ($phone === '') {
            $phone = '0000000000';
        }

        $currency = (string) config('fawaterak.currency', 'EGP');
        $cartTotal = number_format($price, 2, '.', '');
        $itemPrice = $cartTotal;

        $bearer = trim((string) config('fawaterak.plugin_bearer_token', ''));
        if ($bearer === '') {
            $bearer = trim((string) config('fawaterak.vendor_key', ''));
        }

        $pluginConfig = [
            'envType' => $iframe->envType(),
            'hashKey' => $iframe->generateHashKey(),
            'token' => $bearer,
            'style' => [
                'listing' => 'horizontal',
            ],
            'requestBody' => [
                'cartTotal' => $cartTotal,
                'currency' => $currency,
                'redirectOutIframe' => true,
                'customer' => [
                    'customer_unique_id' => (string) $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'email' => $email,
                    'phone' => $phone,
                    'address' => '',
                ],
                'redirectionUrls' => [
                    'successUrl' => route('public.checkout.fawaterak.return', ['status' => 'success']),
                    'failUrl' => route('public.checkout.fawaterak.return', ['status' => 'fail']),
                    'pendingUrl' => route('public.checkout.fawaterak.return', ['status' => 'pending']),
                ],
                'cartItems' => [
                    [
                        'name' => Str::limit($planName.' — اشتراك معلم', 120),
                        'price' => $itemPrice,
                        'quantity' => '1',
                    ],
                ],
                'payLoad' => [
                    'subscription_request_id' => (string) $subRequest->id,
                    'user_id' => (string) $user->id,
                    'teacher_plan_key' => $plan,
                ],
            ],
        ];

        $version = $iframe->versionString();
        if ($version !== '' && $version !== '0') {
            $pluginConfig['version'] = $version;
        }

        return response()->json([
            'mode' => 'iframe',
            'pluginScriptUrl' => route('public.fawaterk.plugin', [], true),
            'pluginConfig' => $pluginConfig,
        ]);
    }

    public function fawaterakPaymentMethods(Request $request, string $plan): JsonResponse
    {
        if (! PaymentGatewaySettings::isFawaterakEnabled()) {
            return response()->json(['message' => 'بوابة الدفع الإلكترونية غير مفعّلة.'], 403);
        }

        $api = app(FawaterakApiService::class);
        if ($api->integrationMode() !== 'api' || ! $api->isConfigured()) {
            return response()->json(['message' => 'وضع API غير مفعّل أو الرمز غير مضبوط.'], 503);
        }

        $resolved = $this->fawaterakResolvePendingSubscriptionRequest($request, $plan);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }

        $result = $api->getPaymentMethods();
        if (! $result['ok'] || ! is_array($result['json'])) {
            Log::warning('Fawaterak subscription getPaymentmethods failed', [
                'status' => $result['status'],
                'body' => Str::limit($result['body'], 500),
            ]);

            return response()->json(['message' => 'تعذّر جلب وسائل الدفع من فواتيرك. حاول لاحقاً أو راجع الإعدادات.'], 502);
        }

        return response()->json($result['json']);
    }

    public function fawaterakPay(Request $request, string $plan): JsonResponse
    {
        if (! PaymentGatewaySettings::isFawaterakEnabled()) {
            return response()->json(['message' => 'بوابة الدفع الإلكترونية غير مفعّلة.'], 403);
        }

        $api = app(FawaterakApiService::class);
        if ($api->integrationMode() !== 'api' || ! $api->isConfigured()) {
            return response()->json(['message' => 'وضع API غير مفعّل أو الرمز غير مضبوط.'], 503);
        }

        $validated = $request->validate([
            'payment_method_id' => 'required|integer|min:1',
            'mobile_wallet_number' => 'nullable|string|max:32',
        ]);

        $resolved = $this->fawaterakResolvePendingSubscriptionRequest($request, $plan);
        if ($resolved instanceof JsonResponse) {
            return $resolved;
        }
        $subRequest = $resolved;

        $user = Auth::user();
        $fullName = trim((string) ($user->name ?? ''));
        $nameParts = preg_split('/\s+/u', $fullName, 2, PREG_SPLIT_NO_EMPTY) ?: [];
        $firstName = $nameParts[0] ?? 'Customer';
        $lastName = $nameParts[1] ?? $firstName;
        $email = trim((string) ($user->email ?? ''));
        if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return response()->json(['message' => 'يرجى إضافة بريد إلكتروني صالح في ملفك الشخصي قبل الدفع.'], 422);
        }

        $phone = preg_replace('/\D/', '', (string) ($user->phone ?? ''));
        if ($phone === '') {
            $phone = '0000000000';
        }

        $amount = (float) $subRequest->price;
        $currency = (string) config('fawaterak.currency', 'EGP');
        $cartTotal = number_format($amount, 2, '.', '');
        $itemPrice = $cartTotal;
        $planName = $subRequest->plan_name;

        $payload = [
            'payment_method_id' => (int) $validated['payment_method_id'],
            'cartTotal' => $cartTotal,
            'currency' => $currency,
            'invoice_number' => 'SUBREQ-'.$subRequest->id,
            'customer' => [
                'first_name' => $firstName,
                'last_name' => $lastName,
                'email' => $email,
                'phone' => $phone,
                'address' => '',
            ],
            'redirectionUrls' => [
                'successUrl' => route('public.checkout.fawaterak.return', ['status' => 'success']),
                'failUrl' => route('public.checkout.fawaterak.return', ['status' => 'fail']),
                'pendingUrl' => route('public.checkout.fawaterak.return', ['status' => 'pending']),
            ],
            'cartItems' => [
                [
                    'name' => Str::limit($planName.' — اشتراك معلم', 120),
                    'price' => $itemPrice,
                    'quantity' => '1',
                ],
            ],
            'payLoad' => [
                'subscription_request_id' => (string) $subRequest->id,
                'user_id' => (string) $user->id,
                'teacher_plan_key' => $plan,
            ],
            'lang' => app()->getLocale() === 'ar' ? 'ar' : 'en',
        ];

        if ($subRequest->fawaterak_invoice_id !== null && $subRequest->fawaterak_invoice_id !== '') {
            $payload['invoice_id'] = (int) $subRequest->fawaterak_invoice_id;
        }

        $walletRaw = preg_replace('/\D/', '', (string) ($validated['mobile_wallet_number'] ?? ''));
        if ($walletRaw !== '') {
            $payload['mobileWalletNumber'] = $walletRaw;
        }

        $result = $api->invoiceInitPay($payload);
        if (! $result['ok'] || ! is_array($result['json'])) {
            Log::warning('Fawaterak subscription invoiceInitPay failed', [
                'status' => $result['status'],
                'body' => Str::limit($result['body'], 800),
            ]);

            return response()->json([
                'message' => 'تعذّر بدء الدفع لدى فواتيرك. حاول مرة أخرى أو راجع السجلات.',
            ], 502);
        }

        $json = $result['json'];
        if (($json['status'] ?? '') !== 'success') {
            $msg = is_string($json['message'] ?? null) ? $json['message'] : 'رفضت فواتيرك الطلب.';

            return response()->json(['message' => $msg, 'details' => $json], 422);
        }

        $extInvoiceId = data_get($json, 'data.invoice_id');
        if ($extInvoiceId !== null && $extInvoiceId !== '') {
            $subRequest->update(['fawaterak_invoice_id' => (string) $extInvoiceId]);
        }

        return response()->json($json);
    }

    /**
     * @return SubscriptionRequest|JsonResponse
     */
    private function fawaterakResolvePendingSubscriptionRequest(Request $request, string $plan)
    {
        if (! Auth::check()) {
            return response()->json(['message' => 'يجب تسجيل الدخول.'], 401);
        }

        $id = (int) $request->session()->get('fawaterak_subscription_request_id');
        if ($id < 1) {
            return response()->json(['message' => 'لا يوجد طلب دفع نشط. حدّث الصفحة وأعد فتح الدفع.'], 422);
        }

        $row = SubscriptionRequest::query()->whereKey($id)->first();
        if (! $row || $row->user_id !== Auth::id()) {
            return response()->json(['message' => 'طلب غير صالح.'], 422);
        }

        if ((string) $row->teacher_plan_key !== $plan) {
            return response()->json(['message' => 'الطلب لا يطابق هذه الباقة.'], 422);
        }

        if ($row->status !== SubscriptionRequest::STATUS_PENDING) {
            return response()->json(['message' => 'تمت معالجة هذا الطلب مسبقاً.'], 409);
        }

        if ($row->payment_method !== 'online' || $row->payment_proof !== null) {
            return response()->json(['message' => 'هذا الطلب لا يصلح للدفع الإلكتروني.'], 422);
        }

        return $row;
    }

    private function upgradeValidationMessage(Request $request, string $plan): ?string
    {
        $upgrade = (bool) $request->boolean('upgrade');
        if (! $upgrade) {
            return null;
        }

        $fromSubscription = Auth::user()->activeSubscription();
        if (! $fromSubscription) {
            return 'لا يوجد اشتراك نشط للترقية منه.';
        }

        $fromSubscriptionId = $request->input('from');
        if (! empty($fromSubscriptionId) && (int) $fromSubscriptionId !== (int) $fromSubscription->id) {
            return 'بيانات الترقية غير صحيحة. حاول مرة أخرى.';
        }

        $fromKey = (string) ($fromSubscription->teacher_plan_key ?? '');
        $fromRank = self::PLAN_RANK[$fromKey] ?? 0;
        $toRank = self::PLAN_RANK[$plan] ?? 0;
        if ($fromRank <= 0 || $toRank <= 0) {
            return 'لا يمكن ترقية هذه الباقة حالياً.';
        }
        if ($toRank <= $fromRank) {
            return 'يسمح بالترقية إلى باقة أعلى فقط.';
        }

        return null;
    }
}

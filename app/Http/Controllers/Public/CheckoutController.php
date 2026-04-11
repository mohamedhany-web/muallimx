<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\AdvancedCourse;
use App\Models\AcademicYear;
use App\Models\StudentCourseEnrollment;
use App\Models\LearningPathEnrollment;
use App\Services\InstructorCoursePercentageService;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\Payment;
use App\Models\Transaction;
use App\Services\AdminPanelBranding;
use App\Services\FawaterakService;
use App\Services\KashierService;
use App\Services\PaymentGatewaySettings;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class CheckoutController extends Controller
{
    /**
     * عرض صفحة إتمام الطلب
     */
    public function show($courseId)
    {
        // التحقق من تسجيل الدخول - سيتم حفظ URL الحالي تلقائياً
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('info', 'يرجى تسجيل الدخول أولاً لإتمام عملية الشراء');
        }

        $course = AdvancedCourse::where('id', $courseId)
            ->where('is_active', true)
            ->with(['academicSubject', 'academicYear'])
            ->firstOrFail();

        // التحقق من التسجيل السابق
        $isEnrolled = StudentCourseEnrollment::where('user_id', Auth::id())
            ->where('advanced_course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($isEnrolled) {
            return redirect()->route('public.course.show', $course->id)
                ->with('info', 'أنت مسجل بالفعل في هذا الكورس');
        }

        // التحقق من وجود طلب قيد الانتظار (يُستثنى طلب دفع أونلاين قيد إكمال فواتيرك)
        $existingOrder = Order::where('user_id', Auth::id())
            ->where('advanced_course_id', $course->id)
            ->where('status', Order::STATUS_PENDING)
            ->first();

        if ($existingOrder) {
            $fawaterakRetry = $existingOrder->payment_method === 'online'
                && $existingOrder->payment_proof === null;
            if (! $fawaterakRetry) {
                return redirect()->route('public.course.show', $course->id)
                    ->with('info', 'لديك طلب قيد الانتظار لهذا الكورس');
            }
        }

        // جلب المحافظ الإلكترونية النشطة
        $wallets = \App\Models\Wallet::where('is_active', true)
            ->whereNotNull('type')
            ->whereIn('type', ['vodafone_cash', 'instapay', 'bank_transfer'])
            ->where(function($query) {
                $query->whereNotNull('account_number')
                      ->orWhereNotNull('name');
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $fawaterakGatewayOn = PaymentGatewaySettings::isFawaterakEnabled();
        $fawaterakConfigured = app(FawaterakService::class)->isConfigured();
        $fawaterakUseGateway = $fawaterakGatewayOn && $fawaterakConfigured;
        $fawaterakMisconfigured = $fawaterakGatewayOn && ! $fawaterakConfigured;
        $platformLogoUrl = AdminPanelBranding::logoPublicUrl();

        return view('public.checkout', compact('course', 'wallets', 'fawaterakUseGateway', 'fawaterakMisconfigured', 'platformLogoUrl'));
    }

    /**
     * التوجيه لبوابة الدفع كاشير (كورس)
     */
    public function redirectToKashier($courseId)
    {
        return redirect()->route('orders.index')
            ->with('info', 'تم تعطيل بوابة الدفع أونلاين حالياً. يرجى إكمال الطلب بالطريقة اليدوية ورفع إيصال الدفع.');
    }

    /**
     * التوجيه لبوابة الدفع كاشير (مسار تعليمي)
     */
    public function redirectToKashierLearningPath($slug)
    {
        return redirect()->route('orders.index')
            ->with('info', 'تم تعطيل بوابة الدفع أونلاين حالياً. يرجى إكمال الطلب بالطريقة اليدوية ورفع إيصال الدفع.');
    }

    /**
     * رابط العودة من كاشير بعد الدفع (يجب أن يكون URL صالحًا — كاشير قد يرفض localhost أو غير https).
     */
    private function getKashierCallbackUrl(): string
    {
        $configured = config('kashier.merchant_redirect_url');
        if (!empty($configured)) {
            return rtrim($configured, '/');
        }
        return url()->route('public.checkout.kashier.callback');
    }

    /**
     * استقبال callback من بوابة كاشير بعد إتمام الدفع
     */
    public function kashierCallback(Request $request)
    {
        return redirect()->route('orders.index')
            ->with('info', 'تم تعطيل بوابة الدفع أونلاين. يمكنك متابعة حالة طلباتك من هذه الصفحة.');

        $kashier = app(KashierService::class);
        $query = $request->query();

        if (!$kashier->validateCallback($query)) {
            Log::warning('Kashier callback: invalid signature', ['query_keys' => array_keys($query)]);
            return redirect()->route('public.courses')->with('error', 'فشل التحقق من الدفع. يرجى التواصل مع الدعم.');
        }

        $merchantOrderId = $query['merchantOrderId'] ?? null;
        if (!$merchantOrderId || !ctype_digit((string) $merchantOrderId)) {
            Log::warning('Kashier callback: invalid merchantOrderId', ['merchantOrderId' => $merchantOrderId]);
            return redirect()->route('public.courses')->with('error', 'بيانات الطلب غير صحيحة.');
        }

        $order = Order::with(['course', 'learningPath'])->find($merchantOrderId);
        if (!$order || $order->status !== Order::STATUS_PENDING) {
            Log::warning('Kashier callback: order not found or not pending', ['order_id' => $merchantOrderId]);
            return redirect()->route('public.courses')->with('error', 'الطلب غير موجود أو تم معالجته مسبقاً.');
        }

        if (!$kashier->isPaymentSuccess($query)) {
            if ($order->academic_year_id) {
                $slug = Str::slug($order->learningPath->name ?? '');
                return redirect()->route('public.learning-path.show', $slug)
                    ->with('error', 'لم يتم إتمام الدفع. يمكنك المحاولة مرة أخرى.');
            }
            return redirect()->route('public.course.show', $order->advanced_course_id)
                ->with('error', 'لم يتم إتمام الدفع. يمكنك المحاولة مرة أخرى.');
        }

        DB::beginTransaction();
        try {
            $isLearningPath = !empty($order->academic_year_id);
            $orderTitle = $isLearningPath
                ? ($order->learningPath->name ?? 'مسار تعليمي')
                : ($order->course->title ?? 'كورس');

            $invoiceNumber = 'INV-' . str_pad(Invoice::count() + 1, 8, '0', STR_PAD_LEFT);
            $invoice = Invoice::create([
                'invoice_number' => $invoiceNumber,
                'user_id' => $order->user_id,
                'type' => $isLearningPath ? 'learning_path' : 'course',
                'description' => $isLearningPath ? 'تسجيل في المسار: ' . $orderTitle : 'تسجيل في الكورس: ' . $orderTitle,
                'subtotal' => $order->amount,
                'tax_amount' => 0,
                'discount_amount' => 0,
                'total_amount' => $order->amount,
                'status' => 'paid',
                'due_date' => now(),
                'paid_at' => now(),
                'notes' => 'دفع عبر كاشير - طلب #' . $order->id,
                'items' => [
                    [
                        'description' => $isLearningPath ? 'المسار: ' . $orderTitle : 'الكورس: ' . $orderTitle,
                        'quantity' => 1,
                        'price' => $order->amount,
                        'total' => $order->amount,
                    ],
                ],
            ]);

            $paymentNumber = 'PAY-' . str_pad(Payment::count() + 1, 8, '0', STR_PAD_LEFT);
            $payment = Payment::create([
                'payment_number' => $paymentNumber,
                'invoice_id' => $invoice->id,
                'user_id' => $order->user_id,
                'payment_method' => 'online',
                'payment_gateway' => 'kashier',
                'amount' => $order->amount,
                'currency' => 'EGP',
                'status' => 'completed',
                'transaction_id' => $query['transactionId'] ?? null,
                'gateway_response' => $query,
                'paid_at' => now(),
                'notes' => 'دفع عبر كاشير - طلب #' . $order->id,
            ]);

            $transactionNumber = 'TXN-' . str_pad(Transaction::count() + 1, 8, '0', STR_PAD_LEFT);
            Transaction::create([
                'transaction_number' => $transactionNumber,
                'user_id' => $order->user_id,
                'payment_id' => $payment->id,
                'invoice_id' => $invoice->id,
                'expense_id' => null,
                'subscription_id' => null,
                'type' => 'credit',
                'category' => 'course_payment',
                'amount' => $order->amount,
                'currency' => 'EGP',
                'description' => ($isLearningPath ? 'دفع مسار: ' : 'دفع كورس: ') . $orderTitle . ' - طلب #' . $order->id,
                'status' => 'completed',
                'metadata' => [
                    'order_id' => $order->id,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                    'course_id' => $order->advanced_course_id,
                    'academic_year_id' => $order->academic_year_id,
                ],
            ]);

            $order->update([
                'status' => Order::STATUS_APPROVED,
                'approved_at' => now(),
                'approved_by' => null,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
            ]);

            if ($order->academic_year_id) {
                $existingPath = LearningPathEnrollment::where('user_id', $order->user_id)
                    ->where('academic_year_id', $order->academic_year_id)
                    ->first();
                if (!$existingPath) {
                    $pathEnrollment = LearningPathEnrollment::create([
                        'user_id' => $order->user_id,
                        'academic_year_id' => $order->academic_year_id,
                        'status' => 'active',
                        'enrolled_at' => now(),
                        'activated_at' => now(),
                        'activated_by' => $order->user_id,
                        'progress' => 0,
                    ]);
                    $this->enrollInPathCourses($pathEnrollment);
                } else {
                    if ($existingPath->status !== 'active') {
                        $existingPath->update([
                            'status' => 'active',
                            'activated_at' => now(),
                            'activated_by' => $order->user_id,
                        ]);
                        $this->enrollInPathCourses($existingPath);
                    }
                }
            }

            if ($order->advanced_course_id) {
                $existingEnrollment = StudentCourseEnrollment::where('user_id', $order->user_id)
                    ->where('advanced_course_id', $order->advanced_course_id)
                    ->first();
                if (!$existingEnrollment) {
                    StudentCourseEnrollment::create([
                        'user_id' => $order->user_id,
                        'advanced_course_id' => $order->advanced_course_id,
                        'enrolled_at' => now(),
                        'activated_at' => now(),
                        'activated_by' => $order->user_id,
                        'status' => 'active',
                        'progress' => 0,
                        'invoice_id' => $invoice->id,
                        'payment_id' => $payment->id,
                        'payment_method' => 'online',
                        'final_price' => $order->amount,
                    ]);
                } else {
                    $existingEnrollment->update([
                        'status' => 'active',
                        'activated_at' => now(),
                        'activated_by' => $order->user_id,
                        'invoice_id' => $invoice->id,
                        'payment_id' => $payment->id,
                        'payment_method' => 'online',
                        'final_price' => $order->amount,
                    ]);
                }
                $enrollment = StudentCourseEnrollment::where('user_id', $order->user_id)
                    ->where('advanced_course_id', $order->advanced_course_id)
                    ->first();
                if ($enrollment) {
                    InstructorCoursePercentageService::processEnrollmentActivation($enrollment);
                }
            }

            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Kashier callback: approval failed', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('public.courses')->with('error', 'حدث خطأ أثناء تفعيل الطلب. يرجى التواصل مع الدعم.');
        }

        if ($order->academic_year_id) {
            $slug = Str::slug($order->learningPath->name ?? '');
            return redirect()->route('public.learning-path.show', $slug)
                ->with('success', 'تم الدفع بنجاح! تم تفعيل المسار التعليمي على حسابك.');
        }

        return redirect()->route('public.course.show', $order->advanced_course_id)
            ->with('success', 'تم الدفع بنجاح! تم تفعيل الكورس على حسابك.');
    }

    /**
     * تجهيز جلسة دفع فواتيرك (IFrame): إنشاء طلب معلّق وإرجاع إعدادات الإضافة.
     */
    public function fawaterakPrepare(Request $request, int $courseId): JsonResponse
    {
        if (! PaymentGatewaySettings::isFawaterakEnabled()) {
            return response()->json(['message' => 'بوابة الدفع الإلكترونية غير مفعّلة.'], 403);
        }

        $fawaterak = app(FawaterakService::class);
        if (! $fawaterak->isConfigured()) {
            return response()->json(['message' => 'مفاتيح فواتيرك غير مضبوطة في ملف البيئة.'], 503);
        }

        if (! Auth::check()) {
            return response()->json(['message' => 'يجب تسجيل الدخول.'], 401);
        }

        $course = AdvancedCourse::where('id', $courseId)
            ->where('is_active', true)
            ->firstOrFail();

        $isEnrolled = StudentCourseEnrollment::where('user_id', Auth::id())
            ->where('advanced_course_id', $course->id)
            ->where('status', 'active')
            ->exists();
        if ($isEnrolled) {
            return response()->json(['message' => 'أنت مسجل بالفعل في هذا الكورس.'], 422);
        }

        $amount = (float) ($course->price ?? 0);
        if ($amount <= 0) {
            return response()->json(['message' => 'هذا الكورس لا يتطلب دفعاً عبر البوابة.'], 422);
        }

        $existingOrder = Order::where('user_id', Auth::id())
            ->where('advanced_course_id', $course->id)
            ->where('status', Order::STATUS_PENDING)
            ->first();

        if ($existingOrder) {
            if ($existingOrder->payment_method !== 'online' || $existingOrder->payment_proof !== null) {
                return response()->json(['message' => 'لديك طلب قيد المراجعة لهذا الكورس.'], 409);
            }
            $order = $existingOrder;
        } else {
            $order = Order::create([
                'user_id' => Auth::id(),
                'advanced_course_id' => $course->id,
                'original_amount' => $amount,
                'discount_amount' => 0,
                'amount' => $amount,
                'payment_method' => 'online',
                'payment_proof' => null,
                'wallet_id' => null,
                'notes' => '',
                'status' => Order::STATUS_PENDING,
            ]);
        }

        $request->session()->put('fawaterak_order_id', $order->id);

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

        $currency = (string) config('fawaterak.currency', 'EGP');
        $cartTotal = number_format($amount, 2, '.', '');
        $itemPrice = $cartTotal;

        $pluginConfig = [
            'envType' => $fawaterak->envType(),
            'hashKey' => $fawaterak->generateHashKey(),
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
                        'name' => Str::limit($course->title ?? 'كورس', 120),
                        'price' => $itemPrice,
                        'quantity' => '1',
                    ],
                ],
                'payLoad' => [
                    'order_id' => (string) $order->id,
                    'user_id' => (string) $user->id,
                    'course_id' => (string) $course->id,
                ],
            ],
        ];

        $version = $fawaterak->versionString();
        if ($version !== '' && $version !== '0') {
            $pluginConfig['version'] = $version;
        }

        return response()->json([
            'pluginScriptUrl' => $fawaterak->pluginScriptUrl(),
            'pluginConfig' => $pluginConfig,
        ]);
    }

    /**
     * عودة المستخدم من فواتيرك بعد الدفع (داخل نفس الجلسة).
     */
    public function fawaterakReturn(Request $request, string $status): RedirectResponse
    {
        $orderId = (int) $request->session()->get('fawaterak_order_id');
        if ($orderId < 1) {
            return redirect()->route('orders.index')
                ->with('error', 'انتهت جلسة الدفع. إن اكتمل الدفع لدى فواتيرك ولم يُفعَّل الكورس، تواصل مع الدعم برقم الطلب.');
        }

        $order = Order::with(['course', 'learningPath'])->find($orderId);
        if (! $order || $order->user_id !== Auth::id()) {
            $request->session()->forget('fawaterak_order_id');

            return redirect()->route('orders.index')->with('error', 'طلب غير صالح.');
        }

        if ($status === 'fail') {
            $request->session()->forget('fawaterak_order_id');

            return redirect()->route('public.course.checkout', $order->advanced_course_id)
                ->with('error', 'لم يتم إتمام الدفع. يمكنك المحاولة مرة أخرى.');
        }

        if ($status === 'pending') {
            return redirect()->route('public.course.checkout', $order->advanced_course_id)
                ->with('info', 'دفعتك قيد المعالجة لدى فواتيرك. عند اكتمالها سيُفعَّل الكورس تلقائياً أو يمكنك تحديث الصفحة لاحقاً.');
        }

        $lockedOrder = null;
        DB::beginTransaction();
        try {
            $lockedOrder = Order::whereKey($orderId)
                ->lockForUpdate()
                ->with(['course', 'learningPath'])
                ->first();

            if (! $lockedOrder || $lockedOrder->user_id !== Auth::id()) {
                DB::rollBack();
                $request->session()->forget('fawaterak_order_id');

                return redirect()->route('orders.index')->with('error', 'طلب غير صالح.');
            }

            if ($lockedOrder->status !== Order::STATUS_PENDING) {
                DB::rollBack();
                $request->session()->forget('fawaterak_order_id');

                return redirect()->route('public.course.show', $lockedOrder->advanced_course_id)
                    ->with('info', 'تمت معالجة هذا الطلب مسبقاً.');
            }

            $invoice = $this->approveOrderAfterOnlinePayment(
                $lockedOrder,
                'other',
                $request->query('transactionId') ?? $request->query('invoice_id'),
                $request->query(),
                'فواتيرك (Fawaterak)'
            );
            DB::commit();
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Fawaterak return: approval failed', [
                'order_id' => $orderId,
                'message' => $e->getMessage(),
            ]);

            $courseIdForCheckout = $lockedOrder?->advanced_course_id
                ?? Order::query()->whereKey($orderId)->value('advanced_course_id');
            if ($courseIdForCheckout) {
                return redirect()->route('public.course.checkout', $courseIdForCheckout)
                    ->with('error', 'تعذّر تفعيل الطلب بعد الدفع. يرجى التواصل مع الدعم مع رقم الطلب.');
            }

            return redirect()->route('orders.index')
                ->with('error', 'تعذّر تفعيل الطلب بعد الدفع. يرجى التواصل مع الدعم.');
        }

        $request->session()->forget('fawaterak_order_id');

        $courseId = $lockedOrder->advanced_course_id;
        $successMsg = __('public.checkout_payment_success_with_invoice', [
            'invoice' => $invoice->invoice_number,
        ]);

        return redirect()->route('public.course.show', $courseId)
            ->with('success', $successMsg);
    }

    /**
     * إتمام الفوترة والتسجيل بعد دفع أونلاين (داخل معاملة قاعدة بيانات مفتوحة).
     * يُنشئ فاتورة مدفوعة، وسجل دفع، ومعاملة محاسبية، ويحدّث الطلب إلى مقبول، ويُفعّل التسجيل في الكورس.
     *
     * @param  'kashier'|'other'  $paymentGateway
     */
    private function approveOrderAfterOnlinePayment(
        Order $order,
        string $paymentGateway,
        ?string $transactionId,
        array $gatewayResponse,
        string $gatewayDisplayName
    ): Invoice {
        $order->loadMissing(['course', 'learningPath']);

        $isLearningPath = ! empty($order->academic_year_id);
        $orderTitle = $isLearningPath
            ? ($order->learningPath->name ?? 'مسار تعليمي')
            : ($order->course->title ?? 'كورس');

        $currency = (string) config('fawaterak.currency', 'EGP');

        $invoiceNumber = 'INV-' . str_pad((string) (Invoice::count() + 1), 8, '0', STR_PAD_LEFT);
        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'user_id' => $order->user_id,
            'type' => $isLearningPath ? 'learning_path' : 'course',
            'description' => $isLearningPath ? 'تسجيل في المسار: '.$orderTitle : 'تسجيل في الكورس: '.$orderTitle,
            'subtotal' => $order->amount,
            'tax_amount' => 0,
            'discount_amount' => 0,
            'total_amount' => $order->amount,
            'status' => 'paid',
            'due_date' => now(),
            'paid_at' => now(),
            'notes' => 'دفع عبر '.$gatewayDisplayName.' - طلب #'.$order->id,
            'items' => [
                [
                    'description' => $isLearningPath ? 'المسار: '.$orderTitle : 'الكورس: '.$orderTitle,
                    'quantity' => 1,
                    'price' => $order->amount,
                    'total' => $order->amount,
                ],
            ],
        ]);

        $paymentNumber = 'PAY-' . str_pad((string) (Payment::count() + 1), 8, '0', STR_PAD_LEFT);
        $payment = Payment::create([
            'payment_number' => $paymentNumber,
            'invoice_id' => $invoice->id,
            'user_id' => $order->user_id,
            'payment_method' => 'online',
            'payment_gateway' => $paymentGateway,
            'amount' => $order->amount,
            'currency' => $currency,
            'status' => 'completed',
            'transaction_id' => $transactionId,
            'gateway_response' => $gatewayResponse,
            'paid_at' => now(),
            'notes' => 'دفع عبر '.$gatewayDisplayName.' - طلب #'.$order->id,
        ]);

        $transactionNumber = 'TXN-' . str_pad((string) (Transaction::count() + 1), 8, '0', STR_PAD_LEFT);
        Transaction::create([
            'transaction_number' => $transactionNumber,
            'user_id' => $order->user_id,
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
            'expense_id' => null,
            'subscription_id' => null,
            'type' => 'credit',
            'category' => 'course_payment',
            'amount' => $order->amount,
            'currency' => $currency,
            'description' => ($isLearningPath ? 'دفع مسار: ' : 'دفع كورس: ').$orderTitle.' - طلب #'.$order->id,
            'status' => 'completed',
            'metadata' => [
                'order_id' => $order->id,
                'invoice_id' => $invoice->id,
                'payment_id' => $payment->id,
                'course_id' => $order->advanced_course_id,
                'academic_year_id' => $order->academic_year_id,
            ],
        ]);

        $order->update([
            'status' => Order::STATUS_APPROVED,
            'approved_at' => now(),
            'approved_by' => null,
            'invoice_id' => $invoice->id,
            'payment_id' => $payment->id,
        ]);

        if ($order->academic_year_id) {
            $existingPath = LearningPathEnrollment::where('user_id', $order->user_id)
                ->where('academic_year_id', $order->academic_year_id)
                ->first();
            if (! $existingPath) {
                $pathEnrollment = LearningPathEnrollment::create([
                    'user_id' => $order->user_id,
                    'academic_year_id' => $order->academic_year_id,
                    'status' => 'active',
                    'enrolled_at' => now(),
                    'activated_at' => now(),
                    'activated_by' => $order->user_id,
                    'progress' => 0,
                ]);
                $this->enrollInPathCourses($pathEnrollment);
            } else {
                if ($existingPath->status !== 'active') {
                    $existingPath->update([
                        'status' => 'active',
                        'activated_at' => now(),
                        'activated_by' => $order->user_id,
                    ]);
                    $this->enrollInPathCourses($existingPath);
                }
            }
        }

        if ($order->advanced_course_id) {
            $existingEnrollment = StudentCourseEnrollment::where('user_id', $order->user_id)
                ->where('advanced_course_id', $order->advanced_course_id)
                ->first();
            if (! $existingEnrollment) {
                StudentCourseEnrollment::create([
                    'user_id' => $order->user_id,
                    'advanced_course_id' => $order->advanced_course_id,
                    'enrolled_at' => now(),
                    'activated_at' => now(),
                    'activated_by' => $order->user_id,
                    'status' => 'active',
                    'progress' => 0,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                    'payment_method' => 'online',
                    'final_price' => $order->amount,
                ]);
            } else {
                $existingEnrollment->update([
                    'status' => 'active',
                    'activated_at' => now(),
                    'activated_by' => $order->user_id,
                    'invoice_id' => $invoice->id,
                    'payment_id' => $payment->id,
                    'payment_method' => 'online',
                    'final_price' => $order->amount,
                ]);
            }
            $enrollment = StudentCourseEnrollment::where('user_id', $order->user_id)
                ->where('advanced_course_id', $order->advanced_course_id)
                ->first();
            if ($enrollment) {
                InstructorCoursePercentageService::processEnrollmentActivation($enrollment);
            }
        }

        return $invoice;
    }

    /**
     * تسجيل الطالب في كورسات المسار (للاستخدام من callback كاشير)
     */
    private function enrollInPathCourses(LearningPathEnrollment $enrollment): void
    {
        $learningPath = $enrollment->learningPath()->with(['academicSubjects'])->first();
        if (!$learningPath) {
            return;
        }
        $courses = collect();
        if (\Illuminate\Support\Facades\Schema::hasTable('academic_year_courses')) {
            try {
                $learningPath->load('linkedCourses');
                $linked = $learningPath->linkedCourses()->where('advanced_courses.is_active', true)->get();
                $courses = $courses->merge($linked);
            } catch (\Throwable $e) {
                Log::warning('enrollInPathCourses: linkedCourses failed', ['message' => $e->getMessage()]);
            }
        }
        $subjects = $learningPath->academicSubjects ?? collect();
        foreach ($subjects as $subject) {
            try {
                $subjectCourses = $subject->advancedCourses()->where('is_active', true)->get();
                $courses = $courses->merge($subjectCourses);
            } catch (\Throwable $e) {
                Log::warning('enrollInPathCourses: subject courses failed', ['subject_id' => $subject->id ?? null]);
            }
        }
        $courses = $courses->unique('id');
        foreach ($courses as $course) {
            try {
                $courseEnrollment = StudentCourseEnrollment::firstOrCreate(
                    [
                        'user_id' => $enrollment->user_id,
                        'advanced_course_id' => $course->id,
                    ],
                    [
                        'status' => 'active',
                        'enrolled_at' => now(),
                        'activated_at' => now(),
                        'activated_by' => $enrollment->user_id,
                        'progress' => 0,
                    ]
                );
                if ($courseEnrollment->status === 'active') {
                    InstructorCoursePercentageService::processEnrollmentActivation($courseEnrollment->fresh());
                }
            } catch (\Throwable $e) {
                Log::warning('enrollInPathCourses: firstOrCreate failed', ['course_id' => $course->id ?? null]);
            }
        }
    }

    /**
     * إتمام الطلب
     */
    public function complete(Request $request, $courseId)
    {
        // التحقق من تسجيل الدخول
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً');
        }

        if (PaymentGatewaySettings::isFawaterakEnabled()) {
            return back()->with('error', 'الدفع اليدوي غير متاح حين تكون بوابة الدفع الإلكترونية (فواتيرك) مفعّلة من إعدادات النظام.');
        }

        $course = AdvancedCourse::where('id', $courseId)
            ->where('is_active', true)
            ->firstOrFail();

        // التحقق من التسجيل السابق
        $isEnrolled = StudentCourseEnrollment::where('user_id', Auth::id())
            ->where('advanced_course_id', $course->id)
            ->where('status', 'active')
            ->exists();

        if ($isEnrolled) {
            return redirect()->route('public.course.show', $course->id)
                ->with('info', 'أنت مسجل بالفعل في هذا الكورس');
        }

        // منع طلب مكرر: إذا كان هناك طلب قيد الانتظار لنفس الكورس
        $existingPending = Order::where('user_id', Auth::id())
            ->where('advanced_course_id', $course->id)
            ->where('status', Order::STATUS_PENDING)
            ->first();
        if ($existingPending) {
            return redirect()->route('public.course.show', $course->id)
                ->with('info', 'لديك طلب قيد الانتظار لهذا الكورس. يرجى انتظار المراجعة.');
        }

        // التحقق من صحة البيانات (حساب الاستلام على المنصة إلزامي للتحويل حتى يُسجَّل الإيداع عند الموافقة)
        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,wallet,cash,other',
            'wallet_id' => [
                'nullable',
                'required_if:payment_method,bank_transfer',
                'required_if:payment_method,wallet',
                Rule::exists('wallets', 'id')->where('is_active', true)->whereIn('type', ['vodafone_cash', 'instapay', 'bank_transfer']),
            ],
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:'.config('upload_limits.max_upload_kb'),
            'notes' => 'nullable|string|max:1000',
        ], [
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'payment_method.in' => 'طريقة الدفع غير صحيحة',
            'wallet_id.required_if' => 'يجب اختيار حساب التحويل على المنصة حتى يُسجَّل المبلغ على المحفظة الصحيحة عند الموافقة.',
            'wallet_id.exists' => 'المحفظة المختارة غير صالحة أو غير متاحة. يرجى اختيار محفظة من القائمة.',
            'payment_proof.required' => 'صورة إيصال الدفع مطلوبة',
            'payment_proof.image' => 'يجب أن يكون الملف صورة',
            'payment_proof.mimes' => 'يجب أن تكون الصورة بصيغة jpeg, png أو jpg',
            'payment_proof.max' => 'حجم الصورة يجب ألا يتجاوز 2 ميجابايت',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
        ]);

        DB::beginTransaction();
        try {
            // حساب السعر النهائي
            $originalAmount = $course->price ?? 0;
            $finalAmount = $originalAmount;
            $discountAmount = 0;

            // رفع صورة الإيصال
            $paymentProofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

            // إنشاء الطلب
            $order = Order::create([
                'user_id' => Auth::id(),
                'advanced_course_id' => $course->id,
                'original_amount' => $originalAmount,
                'discount_amount' => $discountAmount,
                'amount' => $finalAmount,
                'payment_method' => $request->payment_method === 'wallet' ? 'bank_transfer' : $request->payment_method,
                'payment_proof' => $paymentProofPath,
                'wallet_id' => $request->payment_method === 'bank_transfer' || $request->payment_method === 'wallet'
                    ? ($request->wallet_id ?: null)
                    : null,
                'notes' => $request->notes ?? '',
                'status' => Order::STATUS_PENDING,
            ]);

            DB::commit();

            return redirect()->route('public.course.show', $course->id)
                ->with('success', 'تم استلام طلبك بنجاح. طلبك قيد المراجعة لهذا الكورس وسيتم تفعيله بعد الموافقة.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Checkout complete error: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'course_id' => $courseId,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء إتمام الطلب. يرجى المحاولة مرة أخرى.')
                ->withInput();
        }
    }

    /**
     * تسجيل مجاني للكورسات المجانية
     */
    public function enrollFree($courseId)
    {
        // التحقق من تسجيل الدخول
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً');
        }

        $course = AdvancedCourse::where('id', $courseId)
            ->where('is_active', true)
            ->firstOrFail();

        // التحقق من أن الكورس مجاني
        if (($course->price ?? 0) > 0 && !($course->is_free ?? false)) {
            return redirect()->route('public.course.show', $course->id)
                ->with('error', 'هذا الكورس ليس مجانياً');
        }

        // التحقق من التسجيل السابق
        $existingEnrollment = StudentCourseEnrollment::where('user_id', Auth::id())
            ->where('advanced_course_id', $course->id)
            ->first();

        if ($existingEnrollment && $existingEnrollment->status === 'active') {
            return redirect()->route('public.course.show', $course->id)
                ->with('info', 'أنت مسجل بالفعل في هذا الكورس');
        }

        DB::beginTransaction();
        try {
            // إذا كان هناك تسجيل غير نشط، تفعيله
            $enrollment = null;
            if ($existingEnrollment) {
                $existingEnrollment->update([
                    'status' => 'active',
                    'activated_at' => now(),
                    'activated_by' => Auth::id(),
                ]);
                $enrollment = $existingEnrollment->fresh();
            } else {
                $enrollment = StudentCourseEnrollment::create([
                    'user_id' => Auth::id(),
                    'advanced_course_id' => $course->id,
                    'enrolled_at' => now(),
                    'activated_at' => now(),
                    'activated_by' => Auth::id(),
                    'status' => 'active',
                    'progress' => 0,
                ]);
            }
            if ($enrollment) {
                InstructorCoursePercentageService::processEnrollmentActivation($enrollment);
            }

            DB::commit();

            return redirect()->route('public.course.show', $course->id)
                ->with('success', 'تم تسجيلك في الكورس بنجاح! يمكنك الآن البدء بالتعلم.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('public.course.show', $course->id)
                ->with('error', 'حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.');
        }
    }

    /**
     * عرض صفحة إتمام الطلب للمسار التعليمي
     */
    public function showLearningPath($slug)
    {
        // التحقق من تسجيل الدخول
        if (!Auth::check()) {
            return redirect()->guest(route('login'))->with('info', 'يرجى تسجيل الدخول أولاً لإتمام عملية الشراء');
        }

        // البحث عن AcademicYear بالاسم (slug)
        $learningPath = AcademicYear::active()
            ->get()
            ->first(function($year) use ($slug) {
                return Str::slug($year->name) === $slug;
            });
        
        if (!$learningPath) {
            abort(404, 'المسار التعليمي غير موجود');
        }

        // التحقق من التسجيل السابق
        $isEnrolled = LearningPathEnrollment::where('user_id', Auth::id())
            ->where('academic_year_id', $learningPath->id)
            ->where('status', 'active')
            ->exists();

        if ($isEnrolled) {
            return redirect()->route('public.learning-path.show', $slug)
                ->with('info', 'أنت مسجل بالفعل في هذا المسار التعليمي');
        }

        // التحقق من وجود طلب قيد الانتظار
        $existingOrder = Order::where('user_id', Auth::id())
            ->where('academic_year_id', $learningPath->id)
            ->where('status', Order::STATUS_PENDING)
            ->first();

        if ($existingOrder) {
            return redirect()->route('public.learning-path.show', $slug)
                ->with('info', 'لديك طلب قيد الانتظار لهذا المسار');
        }

        // جلب المحافظ الإلكترونية النشطة
        $wallets = \App\Models\Wallet::where('is_active', true)
            ->whereNotNull('type')
            ->whereIn('type', ['vodafone_cash', 'instapay', 'bank_transfer'])
            ->where(function($query) {
                $query->whereNotNull('account_number')
                      ->orWhereNotNull('name');
            })
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        $fawaterakUseGateway = false;
        $fawaterakMisconfigured = false;
        $platformLogoUrl = AdminPanelBranding::logoPublicUrl();

        return view('public.checkout', compact('learningPath', 'wallets', 'fawaterakUseGateway', 'fawaterakMisconfigured', 'platformLogoUrl'));
    }

    /**
     * إتمام الطلب للمسار التعليمي
     */
    public function completeLearningPath(Request $request, $slug)
    {
        // التحقق من تسجيل الدخول
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً');
        }

        if (PaymentGatewaySettings::isFawaterakEnabled()) {
            return back()->with('error', 'الدفع اليدوي غير متاح حين تكون بوابة الدفع الإلكترونية (فواتيرك) مفعّلة من إعدادات النظام.');
        }

        // البحث عن AcademicYear بالاسم (slug)
        $learningPath = AcademicYear::active()
            ->get()
            ->first(function($year) use ($slug) {
                return Str::slug($year->name) === $slug;
            });
        
        if (!$learningPath) {
            abort(404, 'المسار التعليمي غير موجود');
        }

        // التحقق من التسجيل السابق
        $isEnrolled = LearningPathEnrollment::where('user_id', Auth::id())
            ->where('academic_year_id', $learningPath->id)
            ->where('status', 'active')
            ->exists();

        if ($isEnrolled) {
            return redirect()->route('public.learning-path.show', $slug)
                ->with('info', 'أنت مسجل بالفعل في هذا المسار التعليمي');
        }

        // التحقق من صحة البيانات (حساب الاستلام إلزامي للتحويل)
        $validated = $request->validate([
            'payment_method' => 'required|in:bank_transfer,wallet,cash,other',
            'wallet_id' => [
                'nullable',
                'required_if:payment_method,bank_transfer',
                'required_if:payment_method,wallet',
                Rule::exists('wallets', 'id')->where('is_active', true)->whereIn('type', ['vodafone_cash', 'instapay', 'bank_transfer']),
            ],
            'payment_proof' => 'required|image|mimes:jpeg,png,jpg|max:'.config('upload_limits.max_upload_kb'),
            'notes' => 'nullable|string|max:1000',
        ], [
            'payment_method.required' => 'طريقة الدفع مطلوبة',
            'payment_method.in' => 'طريقة الدفع غير صحيحة',
            'wallet_id.required_if' => 'يجب اختيار حساب التحويل على المنصة حتى يُسجَّل المبلغ على المحفظة الصحيحة عند الموافقة.',
            'wallet_id.exists' => 'المحفظة المختارة غير صالحة أو غير متاحة. يرجى اختيار محفظة من القائمة.',
            'payment_proof.required' => 'صورة إيصال الدفع مطلوبة',
            'payment_proof.image' => 'يجب أن يكون الملف صورة',
            'payment_proof.mimes' => 'يجب أن تكون الصورة بصيغة jpeg, png أو jpg',
            'payment_proof.max' => 'حجم الصورة يجب ألا تتجاوز 2 ميجابايت',
            'notes.max' => 'الملاحظات يجب ألا تتجاوز 1000 حرف',
        ]);

        // التحقق من وجود طلب قيد الانتظار
        $existingOrder = Order::where('user_id', Auth::id())
            ->where('academic_year_id', $learningPath->id)
            ->where('status', Order::STATUS_PENDING)
            ->first();

        if ($existingOrder) {
            return redirect()->route('public.learning-path.show', $slug)
                ->with('info', 'لديك طلب قيد الانتظار لهذا المسار. يرجى انتظار المراجعة.');
        }

        DB::beginTransaction();
        try {
            // حساب السعر النهائي
            $originalAmount = $learningPath->price ?? 0;
            $finalAmount = $originalAmount;
            $discountAmount = 0;

            // رفع صورة الإيصال
            if (!$request->hasFile('payment_proof')) {
                throw new \Exception('صورة الإيصال مطلوبة');
            }

            $paymentProofPath = $request->file('payment_proof')->store('payment-proofs', 'public');

            // إنشاء الطلب
            $order = Order::create([
                'user_id' => Auth::id(),
                'academic_year_id' => $learningPath->id,
                'original_amount' => $originalAmount,
                'discount_amount' => $discountAmount,
                'amount' => $finalAmount,
                'payment_method' => $request->payment_method === 'wallet' ? 'bank_transfer' : $request->payment_method,
                'payment_proof' => $paymentProofPath,
                'wallet_id' => $request->payment_method === 'bank_transfer' || $request->payment_method === 'wallet'
                    ? ($request->wallet_id ?? null)
                    : null,
                'notes' => $request->notes ?? '',
                'status' => Order::STATUS_PENDING,
            ]);

            DB::commit();

            \Log::info('Order created successfully', [
                'order_id' => $order->id,
                'user_id' => Auth::id(),
                'learning_path_id' => $learningPath->id,
            ]);

            return redirect()->route('public.learning-path.show', $slug)
                ->with('success', 'تم إرسال طلبك بنجاح! سيتم مراجعته وتفعيل المسار تلقائياً بعد الموافقة.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            \Log::error('Validation error in completeLearningPath', [
                'errors' => $e->errors(),
                'user_id' => Auth::id(),
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in completeLearningPath: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'learning_path_id' => $learningPath->id ?? null,
                'trace' => $e->getTraceAsString(),
            ]);
            return back()->with('error', 'حدث خطأ أثناء إتمام الطلب: ' . $e->getMessage())
                ->withInput();
        }
    }

    /**
     * تسجيل مجاني للمسارات المجانية
     */
    public function enrollFreeLearningPath($slug)
    {
        // التحقق من تسجيل الدخول
        if (!Auth::check()) {
            return redirect()->route('login')->with('error', 'يجب تسجيل الدخول أولاً');
        }

        // البحث عن AcademicYear بالاسم (slug)
        $learningPath = AcademicYear::active()
            ->get()
            ->first(function($year) use ($slug) {
                return Str::slug($year->name) === $slug;
            });
        
        if (!$learningPath) {
            abort(404, 'المسار التعليمي غير موجود');
        }

        // التحقق من أن المسار مجاني
        if (($learningPath->price ?? 0) > 0) {
            return redirect()->route('public.learning-path.show', $slug)
                ->with('error', 'هذا المسار ليس مجانياً');
        }

        // التحقق من التسجيل السابق
        $existingEnrollment = LearningPathEnrollment::where('user_id', Auth::id())
            ->where('academic_year_id', $learningPath->id)
            ->first();

        if ($existingEnrollment && $existingEnrollment->status === 'active') {
            return redirect()->route('public.learning-path.show', $slug)
                ->with('info', 'أنت مسجل بالفعل في هذا المسار التعليمي');
        }

        DB::beginTransaction();
        try {
            $enrollment = null;
            // إذا كان هناك تسجيل غير نشط، تفعيله
            if ($existingEnrollment) {
                $existingEnrollment->update([
                    'status' => 'active',
                    'activated_at' => now(),
                    'activated_by' => Auth::id(),
                ]);
                $enrollment = $existingEnrollment;
            } else {
                // إنشاء تسجيل جديد
                $enrollment = LearningPathEnrollment::create([
                    'user_id' => Auth::id(),
                    'academic_year_id' => $learningPath->id,
                    'enrolled_at' => now(),
                    'activated_at' => now(),
                    'activated_by' => Auth::id(),
                    'status' => 'active',
                    'progress' => 0,
                ]);
            }

            // تفعيل جميع الكورسات في المسار للطالب
            $this->enrollInPathCourses($enrollment);

            DB::commit();

            return redirect()->route('public.learning-path.show', $slug)
                ->with('success', 'تم تسجيلك في المسار التعليمي بنجاح! يمكنك الآن البدء بالتعلم.');

        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->route('public.learning-path.show', $slug)
                ->with('error', 'حدث خطأ أثناء التسجيل. يرجى المحاولة مرة أخرى.');
        }
    }
}


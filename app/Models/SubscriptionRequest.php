<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubscriptionRequest extends Model
{
    use HasFactory;

    const STATUS_PENDING = 'pending';
    const STATUS_APPROVED = 'approved';
    const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'user_id',
        'teacher_plan_key',
        'from_teacher_plan_key',
        'plan_name',
        'price',
        'billing_cycle',
        'request_type',
        'payment_method',
        'payment_proof',
        'wallet_id',
        'status',
        'subscription_id',
        'from_subscription_id',
        'notes',
        'approved_at',
        'approved_by',
        'fawaterak_invoice_id',
        'coupon_id',
        'coupon_code',
        'original_price',
        'discount_amount',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'original_price' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'approved_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function subscription()
    {
        return $this->belongsTo(Subscription::class);
    }

    public function approver()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function wallet()
    {
        return $this->belongsTo(Wallet::class);
    }

    public function coupon()
    {
        return $this->belongsTo(Coupon::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * طلبات تنتظر مراجعة الأدمن (تحويل يدوي + إيصال).
     * طلبات الدفع الإلكتروني (فواتيرك) لا تُعرض — تُفعَّل تلقائياً عند العودة من البوابة.
     */
    public function scopePendingManualReview($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->whereNotNull('payment_proof')
            ->where('payment_proof', '!=', '');
    }

    public function requiresManualReview(): bool
    {
        return $this->status === self::STATUS_PENDING
            && filled($this->payment_proof);
    }

    /**
     * طلب دفع إلكتروني عبر البوابة (يُفعَّل تلقائياً عند العودة من فواتيرك).
     */
    public function isOnlineGatewayPayment(): bool
    {
        return $this->payment_method === 'online' && ! filled($this->payment_proof);
    }

    public function scopePendingOnlineGateway($query)
    {
        return $query->where('status', self::STATUS_PENDING)
            ->where('payment_method', 'online')
            ->where(function ($q) {
                $q->whereNull('payment_proof')->orWhere('payment_proof', '');
            });
    }

    public function scopeApproved($query)
    {
        return $query->where('status', self::STATUS_APPROVED);
    }

    /**
     * Default plan config (price, billing_cycle, subscription_type, features).
     */
    public static function planDefaults(string $key): array
    {
        $plans = [
            'teacher_free' => [
                'plan_name' => 'الباقة المجانية',
                'price' => 0,
                'billing_cycle' => 'monthly',
                'subscription_type' => 'monthly',
                'features' => ['library_access', 'ai_tools', 'support'],
            ],
            'teacher_starter' => [
                'plan_name' => 'الباقة الأساسية',
                'price' => 200,
                'billing_cycle' => 'monthly',
                'subscription_type' => 'monthly',
                'features' => ['library_access', 'ai_tools', 'support', 'teacher_profile', 'visible_to_academies', 'can_apply_opportunities', 'full_ai_suite', 'teacher_evaluation', 'recommended_to_academies', 'priority_opportunities', 'direct_support'],
            ],
            'teacher_pro' => [
                'plan_name' => 'الباقة الشاملة',
                'price' => 600,
                'billing_cycle' => 'monthly',
                'subscription_type' => 'monthly',
                'features' => ['library_access', 'ai_tools', 'classroom_access', 'support', 'teacher_profile', 'visible_to_academies', 'can_apply_opportunities', 'full_ai_suite', 'teacher_evaluation', 'recommended_to_academies', 'priority_opportunities', 'direct_support'],
            ],
        ];
        return $plans[$key] ?? $plans['teacher_free'];
    }
}

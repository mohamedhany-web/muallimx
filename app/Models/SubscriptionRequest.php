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
    ];

    protected $casts = [
        'price' => 'decimal:2',
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

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
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
            'teacher_starter' => [
                'plan_name' => 'باقة البداية للمعلمين',
                'price' => 200,
                'billing_cycle' => 'monthly',
                'subscription_type' => 'monthly',
                'features' => ['library_access', 'ai_tools', 'classroom_access', 'support'],
            ],
            'teacher_pro' => [
                'plan_name' => 'باقة المعلم المحترف',
                'price' => 600,
                'billing_cycle' => 'quarterly',
                'subscription_type' => 'quarterly',
                'features' => ['library_access', 'ai_tools', 'classroom_access', 'support', 'teacher_profile', 'visible_to_academies', 'can_apply_opportunities', 'full_ai_suite'],
            ],
            'teacher_premium' => [
                'plan_name' => 'باقة المعلم المميز',
                'price' => 1500,
                'billing_cycle' => 'yearly',
                'subscription_type' => 'yearly',
                'features' => ['library_access', 'ai_tools', 'classroom_access', 'support', 'teacher_profile', 'visible_to_academies', 'can_apply_opportunities', 'full_ai_suite', 'teacher_evaluation', 'recommended_to_academies', 'priority_opportunities', 'direct_support'],
            ],
        ];
        return $plans[$key] ?? $plans['teacher_starter'];
    }
}

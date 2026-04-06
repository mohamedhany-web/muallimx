<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponCommissionAccrual extends Model
{
    public const STATUS_PENDING = 'pending';
    public const STATUS_EXPENSE_PENDING = 'expense_pending';
    public const STATUS_SETTLED = 'settled';
    public const STATUS_CANCELLED = 'cancelled';

    protected $fillable = [
        'coupon_id',
        'beneficiary_user_id',
        'order_id',
        'invoice_id',
        'payment_id',
        'base_amount_egp',
        'commission_percent',
        'commission_amount_egp',
        'status',
        'paid_at',
        'expense_id',
        'notes',
    ];

    protected $casts = [
        'base_amount_egp' => 'decimal:2',
        'commission_percent' => 'decimal:2',
        'commission_amount_egp' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(User::class, 'beneficiary_user_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    public function payment(): BelongsTo
    {
        return $this->belongsTo(Payment::class);
    }

    public function expense(): BelongsTo
    {
        return $this->belongsTo(Expense::class);
    }

    public static function statusLabel(string $status): string
    {
        return match ($status) {
            self::STATUS_PENDING => 'مستحق — بانتظار تسجيل مصروف',
            self::STATUS_EXPENSE_PENDING => 'مصروف معلق للموافقة',
            self::STATUS_SETTLED => 'مسوّى',
            self::STATUS_CANCELLED => 'ملغى',
            default => $status,
        };
    }
}

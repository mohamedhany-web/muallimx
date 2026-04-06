<?php

namespace App\Services;

use App\Models\User;
use App\Models\Referral;
use App\Models\ReferralProgram;
use App\Models\Coupon;
use Illuminate\Support\Str;
use Carbon\Carbon;

class ReferralService
{
    /**
     * إنشاء كود إحالة للمستخدم
     */
    public function generateReferralCode(User $user): string
    {
        if ($user->referral_code) {
            return $user->referral_code;
        }

        // إنشاء كود فريد بناءً على ID المستخدم
        $code = 'REF' . str_pad($user->id, 6, '0', STR_PAD_LEFT) . strtoupper(Str::random(4));
        
        // التأكد من أن الكود فريد
        while (User::where('referral_code', $code)->exists()) {
            $code = 'REF' . str_pad($user->id, 6, '0', STR_PAD_LEFT) . strtoupper(Str::random(4));
        }

        $user->update(['referral_code' => $code]);

        return $code;
    }

    /**
     * معالجة إحالة مستخدم جديد
     */
    public function processReferral(User $referrer, User $referred, string $referralCode = null): ?Referral
    {
        $program = ReferralProgram::currentForNewReferrals();

        if (! $program) {
            return null;
        }

        if ($referralCode && strtoupper(trim($referralCode)) !== strtoupper(trim((string) $referrer->referral_code))) {
            return null;
        }

        if (! $program->canUserRefer($referrer->id)) {
            return null;
        }

        if (! $program->allow_self_referral && $referrer->id === $referred->id) {
            return null;
        }

        $existingForReferred = Referral::where('referred_id', $referred->id)->first();
        if ($existingForReferred) {
            return $existingForReferred;
        }

        // إنشاء الإحالة
        $referral = Referral::create([
            'referral_program_id' => $program->id,
            'referrer_id' => $referrer->id,
            'referred_id' => $referred->id,
            'code' => $referrer->referral_code,
            'referral_code' => $referrer->referral_code,
            'status' => Referral::STATUS_PENDING,
        ]);

        // تحديث معلومات المستخدم المحال
        $referred->update([
            'referred_by' => $referrer->id,
            'referred_at' => now(),
        ]);

        // تحديث إحصائيات المحيل
        $referrer->increment('total_referrals');

        // إنشاء كوبون تلقائي للمحال
        $coupon = $this->createAutoCouponForReferred($referral, $program);

        if ($coupon) {
            $referral->update([
                'auto_coupon_id' => $coupon->id,
                'discount_expires_at' => Carbon::now()->addDays($program->discount_valid_days),
            ]);
        }

        return $referral;
    }

    /**
     * إنشاء كوبون تلقائي للمستخدم المحال
     */
    public function createAutoCouponForReferred(Referral $referral, ReferralProgram $program): ?Coupon
    {
        $referred = $referral->referred;
        $referrer = $referral->referrer;

        // إنشاء كود كوبون فريد
        $couponCode = 'REF-' . strtoupper(Str::random(8));

        // التأكد من أن الكود فريد
        while (Coupon::where('code', $couponCode)->exists()) {
            $couponCode = 'REF-' . strtoupper(Str::random(8));
        }

        // حساب مبلغ الخصم (سنستخدم الحد الأقصى كقيمة افتراضية للكوبون)
        $discountValue = $program->discount_value;
        if ($program->discount_type === 'percentage') {
            // بالنسبة المئوية، نحفظ القيمة كما هي
            $discountValue = $program->discount_value;
        }

        // إنشاء الكوبون
        $coupon = Coupon::create([
            'code' => $couponCode,
            'name' => 'خصم الإحالة - ' . $referrer->name,
            'title' => 'خصم خاص من برنامج الإحالة',
            'description' => "خصم خاص للمستخدم المحال من {$referrer->name}. برنامج: {$program->name}",
            'discount_type' => $program->discount_type,
            'discount_value' => $discountValue,
            'maximum_discount' => $program->maximum_discount,
            'minimum_amount' => $program->minimum_order_amount,
            'usage_limit' => $program->max_discount_uses_per_referred,
            'usage_limit_per_user' => 1, // للمستخدم المحال فقط
            'applicable_user_ids' => [$referred->id], // للمستخدم المحال فقط
            'applicable_to' => 'all', // أو 'courses' حسب الحاجة
            'starts_at' => now(),
            'expires_at' => Carbon::now()->addDays($program->discount_valid_days),
            'is_active' => true,
            'is_public' => false, // كوبون خاص
        ]);

        return $coupon;
    }

    /**
     * تطبيق خصم الإحالة تلقائياً على الطلب
     */
    public function applyReferralDiscount(User $user, $orderAmount): ?Coupon
    {
        // البحث عن إحالة للمستخدم
        $referral = Referral::where('referred_id', $user->id)
            ->where('status', Referral::STATUS_PENDING)
            ->with(['referralProgram', 'autoCoupon'])
            ->first();

        if (!$referral || !$referral->referralProgram) {
            return null;
        }

        // التحقق من صلاحية الخصم
        if (!$referral->canUseDiscount()) {
            return null;
        }

        // التحقق من الكوبون
        if ($referral->autoCoupon && $referral->autoCoupon->isValid() && $referral->autoCoupon->canBeUsedByUser($user->id)) {
            return $referral->autoCoupon;
        }

        return null;
    }

    /**
     * تحديث حالة الإحالة عند اكتمال الطلب
     */
    public function markReferralAsCompleted(Referral $referral, $orderAmount = null): void
    {
        if ($referral->status === Referral::STATUS_COMPLETED) {
            return;
        }

        $referral->update([
            'status' => Referral::STATUS_COMPLETED,
            'completed_at' => now(),
        ]);

        // تحديث إحصائيات المحيل
        $referrer = $referral->referrer;
        $referrer->increment('completed_referrals');

        // حساب المكافأة للمحيل (إذا كان هناك مكافأة)
        $program = $referral->referralProgram;
        if ($program && $program->referrer_reward_value !== null && (float) $program->referrer_reward_value > 0) {
            if ($program->referrer_reward_type === 'points') {
                $referral->update([
                    'reward_points' => (int) round((float) $program->referrer_reward_value),
                    'reward_amount' => 0,
                ]);
            } elseif ($program->referrer_reward_type === 'percentage' && $orderAmount) {
                $rewardAmount = ($orderAmount * (float) $program->referrer_reward_value) / 100;
                $referral->update(['reward_amount' => round($rewardAmount, 2), 'reward_points' => 0]);
            } else {
                $referral->update([
                    'reward_amount' => round((float) $program->referrer_reward_value, 2),
                    'reward_points' => 0,
                ]);
            }
        }
    }

    /**
     * الحصول على كود الإحالة للمستخدم
     */
    public function getUserReferralCode(User $user): string
    {
        if (!$user->referral_code) {
            return $this->generateReferralCode($user);
        }

        return $user->referral_code;
    }
}

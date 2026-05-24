<?php

namespace App\Console\Commands;

use App\Models\Subscription;
use Illuminate\Console\Command;

class ExpireSubscriptionsCommand extends Command
{
    protected $signature = 'subscriptions:expire';

    protected $description = 'تحديث حالة الاشتراكات المنتهية إلى expired عند انتهاء المدة (end_date)';

    public function handle(): int
    {
        $count = Subscription::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereDate('end_date', '<', today())
            ->update(['status' => 'expired']);

        $this->info("تم تحديث {$count} اشتراكاً إلى حالة منتهي.");
        return self::SUCCESS;
    }
}

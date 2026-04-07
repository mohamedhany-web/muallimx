<?php

namespace Database\Seeders;

use App\Models\FAQ;
use App\Support\PlatformFaqDefaults;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

/**
 * يستبدل جميع صفوف جدول faqs بأسئلة مناسبة لمنصة تأهيل المعلمين.
 * تشغيل يدوي: php artisan db:seed --class=TeacherPlatformFaqSeeder
 */
class TeacherPlatformFaqSeeder extends Seeder
{
    public function run(): void
    {
        if (! Schema::hasTable('faqs')) {
            $this->command?->warn('جدول faqs غير موجود، تم التخطي.');

            return;
        }

        FAQ::query()->delete();

        foreach (PlatformFaqDefaults::items() as $order => $row) {
            FAQ::query()->create([
                'question' => $row['question'],
                'answer' => $row['answer'],
                'category' => $row['category'],
                'order' => $order,
                'is_active' => true,
            ]);
        }

        $this->command?->info('تم إدراج '.count(PlatformFaqDefaults::items()).' سؤال شائع للمنصة.');
    }
}

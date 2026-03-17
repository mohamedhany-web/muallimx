<?php

namespace Database\Seeders;

use App\Models\CurriculumLibraryCategory;
use App\Models\CurriculumLibraryItem;
use Illuminate\Database\Seeder;

/**
 * بذور مناهج أكس: 4 أقسام (قرائية، عربي، تجويد، اسلاميك) وعنصر تجريبي واحد معاينة مجانية.
 */
class ManahijXSeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['slug' => 'qeraa', 'name' => 'قرائية', 'description' => 'مناهج القرائية التفاعلية', 'order' => 1],
            ['slug' => 'arabic', 'name' => 'عربي', 'description' => 'لغة عربية وتعبير ونحو', 'order' => 2],
            ['slug' => 'tajweed', 'name' => 'تجويد', 'description' => 'مناهج التجويد والقرآن', 'order' => 3],
            ['slug' => 'islamic', 'name' => 'اسلاميك', 'description' => 'مناهج إسلامية وقيم', 'order' => 4],
        ];

        foreach ($categories as $cat) {
            CurriculumLibraryCategory::firstOrCreate(
                ['slug' => $cat['slug']],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'order' => $cat['order'],
                    'is_active' => true,
                ]
            );
        }

        $qeraa = CurriculumLibraryCategory::where('slug', 'qeraa')->first();
        if ($qeraa && !CurriculumLibraryItem::where('slug', 'manahij-x-sample')->exists()) {
            CurriculumLibraryItem::create([
                'category_id' => $qeraa->id,
                'title' => 'عينة من مناهج أكس — قرائية',
                'slug' => 'manahij-x-sample',
                'description' => 'عنصر تجريبي واحد يمكن للمستخدم فتحه مجاناً قبل الاشتراك. باقي المناهج تتطلب اشتراك مناهج X.',
                'content' => '<h3>أهداف العينة</h3><p>هذا عنصر معاينة مجانية من مكتبة مناهج أكس. يمكنك إرفاق ملف بوربوينت تفاعلي أو وجبة من صفحة التعديل في لوحة الإدارة.</p><p>المناهج متاحة بثلاث لغات: العربية، English، Français.</p>',
                'subject' => 'قرائية',
                'grade_level' => 'ابتدائي',
                'language' => 'ar',
                'item_type' => 'presentation',
                'order' => 1,
                'is_active' => true,
                'is_free_preview' => true,
            ]);
        }
    }
}

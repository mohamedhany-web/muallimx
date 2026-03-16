<?php

namespace Database\Seeders;

use App\Models\CurriculumLibraryCategory;
use App\Models\CurriculumLibraryItem;
use Illuminate\Database\Seeder;

class CurriculumLibrarySeeder extends Seeder
{
    public function run(): void
    {
        $math = CurriculumLibraryCategory::firstOrCreate(
            ['slug' => 'mathematics'],
            ['name' => 'رياضيات', 'description' => 'مناهج ووحدات رياضيات جاهزة', 'order' => 1, 'is_active' => true]
        );

        $arabic = CurriculumLibraryCategory::firstOrCreate(
            ['slug' => 'arabic'],
            ['name' => 'لغة عربية', 'description' => 'قراءة، نحو، تعبير', 'order' => 2, 'is_active' => true]
        );

        $science = CurriculumLibraryCategory::firstOrCreate(
            ['slug' => 'science'],
            ['name' => 'علوم', 'description' => 'مناهج علوم للمراحل المختلفة', 'order' => 3, 'is_active' => true]
        );

        $items = [
            [
                'category_id' => $math->id,
                'title' => 'جدول الضرب التفاعلي',
                'description' => 'وحدة جاهزة لتدريس جدول الضرب مع أنشطة تفاعلية.',
                'content' => "<h3>أهداف الدرس</h3><ul><li>حفظ جدول الضرب من 2 إلى 10</li><li>ربط الضرب بالجمع المتكرر</li><li>حل مسائل حياتية بسيطة</li></ul><h3>الأنشطة المقترحة</h3><p>1. عرض لوحة الضرب والربط البصري.</p><p>2. ألعاب قصيرة (من يجد الناتج أسرع).</p><p>3. ورقة عمل تطبيقية.</p><h3>المدة المقترحة</h3><p>حصة واحدة (45 دقيقة).</p>",
                'subject' => 'رياضيات',
                'grade_level' => 'ابتدائي',
                'order' => 1,
            ],
            [
                'category_id' => $math->id,
                'title' => 'الكسور العادية والعشرية',
                'description' => 'وحدة تعليمية للكسور مع تمارين وتقييم ذاتي.',
                'content' => "<h3>الأهداف</h3><ul><li>تمييز الكسر العادي والعشري</li><li>تحويل بين الصيغتين</li><li>مقارنة وترتيب الكسور</li></ul><h3>المواد المطلوبة</h3><p>ورق مقسم، نماذج كسور، تمارين تفاعلية.</p>",
                'subject' => 'رياضيات',
                'grade_level' => 'متوسط',
                'order' => 2,
            ],
            [
                'category_id' => $arabic->id,
                'title' => 'تحليل النص الأدبي',
                'description' => 'خطوات منظمة لتحليل نثر أو شعر مع الطلاب.',
                'content' => "<h3>الخطوات</h3><ol><li>قراءة النص قراءة صامتة ثم جهرية</li><li>تحديد الفكرة الرئيسية</li><li>استخراج الصور البيانية والأسلوب</li><li>ربط النص بالحياة أو بقيم</li></ol><h3>ورقة عمل نموذجية</h3><p>يمكن توزيع جدول (عنوان، فكرة رئيسية، أجمل جملة، رأيي) لملئه أثناء الدرس.</p>",
                'subject' => 'لغة عربية',
                'grade_level' => 'ثانوي',
                'order' => 1,
            ],
            [
                'category_id' => $science->id,
                'title' => 'الخلية الحية ومكوناتها',
                'description' => 'وحدة علوم عن الخلية والغشاء والنواة والعضيات.',
                'content' => "<h3>أهداف الوحدة</h3><ul><li>التعرف على مكونات الخلية النباتية والحيوانية</li><li>وظيفة كل عضية</li><li>الفرق بين الخليتين</li></ul><h3>أنشطة مقترحة</h3><p>رسم تخطيطي على السبورة، فيديو قصير، مجسم أو صورة تفاعلية.</p>",
                'subject' => 'علوم',
                'grade_level' => 'إعدادي',
                'order' => 1,
            ],
        ];

        $slugs = ['multiplication-table', 'fractions-decimals', 'literary-analysis', 'living-cell'];
        foreach (array_values($items) as $i => $data) {
            $slug = $slugs[$i] ?? 'item-' . ($i + 1);
            CurriculumLibraryItem::firstOrCreate(
                ['slug' => $slug],
                array_merge($data, ['slug' => $slug, 'is_active' => true])
            );
        }
    }
}

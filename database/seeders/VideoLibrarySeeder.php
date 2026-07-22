<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\VideoLibraryCategory;
use App\Models\VideoLibraryVideo;
use App\Services\YouTubeVideoService;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class VideoLibrarySeeder extends Seeder
{
    public function run(): void
    {
        Permission::query()->firstOrCreate(
            ['name' => 'manage.video-library'],
            [
                'display_name' => 'إدارة مكتبة الفيديو',
                'description' => 'إدارة قنوات وفيديوهات يوتيوب داخل المنصة',
                'group' => 'العناصر المدفوعة',
            ]
        );

        $channels = [
            [
                'name' => 'مهارات التدريس',
                'slug' => 'teaching-skills',
                'description' => 'فيديوهات قصيرة لتقنيات التحضير وإدارة الصف.',
                'cover_color' => '#c62828',
                'icon' => 'fa-chalkboard-teacher',
                'order' => 1,
            ],
            [
                'name' => 'اللغة العربية',
                'slug' => 'arabic',
                'description' => 'شروحات وأنشطة صفية للغة العربية.',
                'cover_color' => '#1565c0',
                'icon' => 'fa-language',
                'order' => 2,
            ],
            [
                'name' => 'الرياضيات',
                'slug' => 'math',
                'description' => 'طرق شرح مبسطة لمفاهيم الرياضيات.',
                'cover_color' => '#2e7d32',
                'icon' => 'fa-square-root-alt',
                'order' => 3,
            ],
        ];

        foreach ($channels as $data) {
            VideoLibraryCategory::query()->updateOrCreate(
                ['slug' => $data['slug']],
                array_merge($data, ['is_active' => true])
            );
        }

        // فيديوهات تجريبية عامة (محتوى تعليمي مشهور على يوتيوب — يمكن استبدالها من الأدمن)
        $samples = [
            [
                'category' => 'teaching-skills',
                'title' => 'كيف تجعل الدرس أكثر تفاعلاً',
                'description' => "أفكار عملية لزيادة تفاعل الطلاب داخل الحصة.\nشاهد داخل المنصة ثم طبّق فوراً في صفك.",
                'youtube' => 'https://www.youtube.com/watch?v=rfscVS0vtbw',
                'featured' => true,
                'order' => 1,
            ],
            [
                'category' => 'arabic',
                'title' => 'مقدمة في مهارات القراءة',
                'description' => 'شرح مبسط لمهارات القراءة يمكن استخدامه كمثال داخل الحصة.',
                'youtube' => 'https://www.youtube.com/watch?v=9bZkp7q19f0',
                'featured' => true,
                'order' => 1,
            ],
            [
                'category' => 'math',
                'title' => 'مفهوم القسمة بطريقة مبسطة',
                'description' => 'فيديو داعم لشرح مفهوم القسمة للمراحل الأولى.',
                'youtube' => 'https://www.youtube.com/watch?v=kJQP7kiw5Fk',
                'featured' => false,
                'order' => 1,
            ],
        ];

        foreach ($samples as $sample) {
            $cat = VideoLibraryCategory::query()->where('slug', $sample['category'])->first();
            if (! $cat) {
                continue;
            }

            try {
                $yt = YouTubeVideoService::normalizeFromInput($sample['youtube']);
            } catch (\Throwable) {
                continue;
            }

            VideoLibraryVideo::query()->updateOrCreate(
                ['youtube_id' => $yt['youtube_id']],
                [
                    'category_id' => $cat->id,
                    'title' => $sample['title'],
                    'slug' => Str::slug($sample['title']).'-'.$yt['youtube_id'],
                    'description' => $sample['description'],
                    'youtube_url' => $yt['youtube_url'],
                    'youtube_id' => $yt['youtube_id'],
                    'thumbnail_url' => $yt['thumbnail_url'],
                    'order' => $sample['order'],
                    'is_active' => true,
                    'is_featured' => $sample['featured'],
                    'published_at' => now(),
                ]
            );
        }
    }
}

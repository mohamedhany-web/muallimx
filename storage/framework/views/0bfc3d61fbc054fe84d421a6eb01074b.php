

<?php $__env->startSection('title', 'مكتبة الفيديو'); ?>
<?php $__env->startSection('header', 'مكتبة الفيديو (قنوات التعلم)'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-slate-100">مكتبة الفيديو</h1>
            <p class="text-sm text-slate-500 mt-1">إدارة فيديوهات يوتيوب المعروضة داخل المنصة — منفصلة عن مكتبة المناهج.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="<?php echo e(route('admin.video-library.categories')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-600 text-sm font-semibold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800">
                <i class="fas fa-layer-group"></i> التصنيفات / القنوات
            </a>
            <a href="<?php echo e(route('admin.video-library.videos.create')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-bold hover:bg-rose-700">
                <i class="fas fa-plus"></i> إضافة فيديو
            </a>
        </div>
    </div>

    <form method="GET" class="flex flex-wrap gap-3 items-end bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">بحث</label>
            <input type="text" name="q" value="<?php echo e(request('q')); ?>" placeholder="عنوان أو وصف أو YouTube ID"
                   class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm w-64">
        </div>
        <div>
            <label class="block text-xs font-semibold text-slate-500 mb-1">التصنيف</label>
            <select name="category_id" class="px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm">
                <option value="">الكل</option>
                <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <option value="<?php echo e($cat->id); ?>" <?php if(request('category_id') == $cat->id): echo 'selected'; endif; ?>><?php echo e($cat->name); ?></option>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </select>
        </div>
        <button class="px-4 py-2 rounded-xl bg-slate-900 dark:bg-rose-600 text-white text-sm font-semibold">تصفية</button>
    </form>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-slate-600 dark:text-slate-300">
                    <tr>
                        <th class="px-4 py-3 text-right font-bold">الفيديو</th>
                        <th class="px-4 py-3 text-right font-bold">التصنيف</th>
                        <th class="px-4 py-3 text-right font-bold">المشاهدات</th>
                        <th class="px-4 py-3 text-right font-bold">الحالة</th>
                        <th class="px-4 py-3 text-right font-bold">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php $__empty_1 = true; $__currentLoopData = $videos; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $video): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <img src="<?php echo e($video->displayThumbnail()); ?>" alt="" class="w-28 h-16 object-cover rounded-lg bg-slate-100" loading="lazy"
                                         onerror="this.src='https://img.youtube.com/vi/<?php echo e($video->youtube_id); ?>/hqdefault.jpg'">
                                    <div class="min-w-0">
                                        <p class="font-bold text-slate-900 dark:text-slate-100 truncate"><?php echo e($video->title); ?></p>
                                        <p class="text-xs text-slate-500 font-mono mt-0.5" dir="ltr"><?php echo e($video->youtube_id); ?></p>
                                        <?php if($video->is_featured): ?>
                                            <span class="inline-flex mt-1 text-[10px] font-bold px-2 py-0.5 rounded bg-amber-100 text-amber-800">مميز</span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-slate-600 dark:text-slate-300"><?php echo e($video->category->name ?? '—'); ?></td>
                            <td class="px-4 py-3"><?php echo e(number_format($video->views_count)); ?></td>
                            <td class="px-4 py-3">
                                <?php if($video->is_active): ?>
                                    <span class="text-xs font-bold text-emerald-700 bg-emerald-50 px-2 py-1 rounded-lg">نشط</span>
                                <?php else: ?>
                                    <span class="text-xs font-bold text-slate-500 bg-slate-100 px-2 py-1 rounded-lg">موقوف</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo e(route('admin.video-library.videos.edit', $video)); ?>" class="text-sky-600 hover:underline font-semibold">تعديل</a>
                                    <form method="POST" action="<?php echo e(route('admin.video-library.videos.destroy', $video)); ?>" onsubmit="return confirm('حذف هذا الفيديو؟')">
                                        <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                                        <button class="text-rose-600 hover:underline font-semibold">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="5" class="px-4 py-12 text-center text-slate-500">لا توجد فيديوهات بعد. أضف أول فيديو من يوتيوب.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if($videos->hasPages()): ?>
            <div class="p-4 border-t border-slate-100 dark:border-slate-700"><?php echo e($videos->links()); ?></div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\admin\video-library\index.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'تصنيفات مكتبة الفيديو'); ?>
<?php $__env->startSection('header', 'تصنيفات / قنوات مكتبة الفيديو'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-slate-100">التصنيفات (القنوات)</h1>
            <p class="text-sm text-slate-500 mt-1">كل تصنيف يظهر كقناة في واجهة المعلم مع شبكة فيديوهات.</p>
        </div>
        <div class="flex gap-2">
            <a href="<?php echo e(route('admin.video-library.index')); ?>" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold">الفيديوهات</a>
            <a href="<?php echo e(route('admin.video-library.categories.create')); ?>" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-bold"><i class="fas fa-plus ml-1"></i> تصنيف جديد</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        <?php $__empty_1 = true; $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $category): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="h-2" style="background: <?php echo e($category->cover_color ?: '#c62828'); ?>"></div>
                <div class="p-5">
                    <div class="flex items-start gap-3">
                        <span class="w-11 h-11 rounded-xl flex items-center justify-center text-white" style="background: <?php echo e($category->cover_color ?: '#c62828'); ?>">
                            <i class="fas <?php echo e($category->icon ?: 'fa-play-circle'); ?>"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-slate-900 dark:text-slate-100"><?php echo e($category->name); ?></h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2"><?php echo e($category->description ?: 'بدون وصف'); ?></p>
                            <p class="text-xs font-semibold text-slate-600 mt-2"><?php echo e($category->videos_count); ?> فيديو · ترتيب <?php echo e($category->order); ?></p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-3 text-sm">
                        <?php if($category->is_active): ?>
                            <span class="text-emerald-600 font-bold text-xs">نشط</span>
                        <?php else: ?>
                            <span class="text-slate-400 font-bold text-xs">موقوف</span>
                        <?php endif; ?>
                        <a href="<?php echo e(route('admin.video-library.categories.edit', $category)); ?>" class="text-sky-600 font-semibold hover:underline">تعديل</a>
                        <form method="POST" action="<?php echo e(route('admin.video-library.categories.destroy', $category)); ?>" onsubmit="return confirm('حذف التصنيف؟ الفيديوهات ستبقى بدون تصنيف.')">
                            <?php echo csrf_field(); ?> <?php echo method_field('DELETE'); ?>
                            <button class="text-rose-600 font-semibold hover:underline">حذف</button>
                        </form>
                    </div>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="col-span-full text-center py-16 text-slate-500">لا توجد تصنيفات. أنشئ أول قناة.</div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\admin\video-library\categories.blade.php ENDPATH**/ ?>
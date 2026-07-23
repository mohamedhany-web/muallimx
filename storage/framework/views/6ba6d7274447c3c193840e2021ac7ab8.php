

<?php $__env->startSection('title', $video->title); ?>
<?php $__env->startSection('header', 'مشاهدة فيديو'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-4 sm:px-6 lg:px-8 py-6">
    <nav class="text-sm text-slate-500 mb-4 flex flex-wrap items-center gap-2">
        <a href="<?php echo e(route('video-library.index')); ?>" class="hover:text-rose-600 font-semibold">مكتبة الفيديو</a>
        <?php if($video->category): ?>
            <span>/</span>
            <a href="<?php echo e(route('video-library.category', $video->category)); ?>" class="hover:text-rose-600 font-semibold"><?php echo e($video->category->name); ?></a>
        <?php endif; ?>
        <span>/</span>
        <span class="text-slate-700 dark:text-slate-300 font-semibold truncate max-w-[240px]"><?php echo e($video->title); ?></span>
    </nav>

    <div class="grid grid-cols-1 xl:grid-cols-12 gap-6">
        <div class="xl:col-span-8 space-y-4">
            <div class="rounded-2xl overflow-hidden bg-black shadow-xl ring-1 ring-slate-200 dark:ring-slate-700">
                <div class="relative w-full" style="padding-top: 56.25%;">
                    <?php if(!empty($canWatch)): ?>
                        <iframe
                            src="<?php echo e($video->embedUrl(['origin' => request()->getSchemeAndHttpHost()])); ?>"
                            title="<?php echo e($video->title); ?>"
                            class="absolute inset-0 w-full h-full"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            allowfullscreen
                            referrerpolicy="strict-origin-when-cross-origin"
                            loading="eager"></iframe>
                    <?php else: ?>
                        <div class="absolute inset-0 flex flex-col items-center justify-center bg-slate-900 text-white p-6 text-center">
                            <i class="fas fa-lock text-3xl text-rose-400 mb-3"></i>
                            <p class="font-bold">المشاهدة تتطلب اشتراكاً</p>
                            <a href="<?php echo e(route('student.features.show', ['feature' => 'video_library_access'])); ?>" class="mt-4 px-4 py-2 rounded-xl bg-rose-600 text-sm font-bold">عرض الباقات</a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 sm:p-6">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-50 leading-snug"><?php echo e($video->title); ?></h1>
                <div class="mt-3 flex flex-wrap items-center gap-3 text-sm text-slate-500">
                    <span><i class="fas fa-eye ml-1 text-slate-400"></i><?php echo e(number_format($video->views_count)); ?> مشاهدة</span>
                    <?php if($video->formattedDuration()): ?>
                        <span><i class="fas fa-clock ml-1 text-slate-400"></i><?php echo e($video->formattedDuration()); ?></span>
                    <?php endif; ?>
                    <?php if($video->published_at): ?>
                        <span><i class="fas fa-calendar ml-1 text-slate-400"></i><?php echo e($video->published_at->format('Y/m/d')); ?></span>
                    <?php endif; ?>
                    <?php if($video->is_featured): ?>
                        <span class="text-[11px] font-black bg-amber-100 text-amber-800 px-2 py-0.5 rounded-md">مميز</span>
                    <?php endif; ?>
                </div>

                <?php if($video->category): ?>
                    <a href="<?php echo e(route('video-library.category', $video->category)); ?>"
                       class="mt-5 inline-flex items-center gap-3 rounded-2xl border border-slate-200 dark:border-slate-700 px-3 py-2.5 hover:bg-slate-50 dark:hover:bg-slate-800 transition-colors">
                        <span class="w-11 h-11 rounded-full flex items-center justify-center text-white"
                              style="background: <?php echo e($video->category->cover_color ?: '#c62828'); ?>">
                            <i class="fas <?php echo e($video->category->icon ?: 'fa-play-circle'); ?>"></i>
                        </span>
                        <span class="text-right">
                            <span class="block text-sm font-black text-slate-900 dark:text-slate-100"><?php echo e($video->category->name); ?></span>
                            <span class="block text-xs text-slate-500">قناة التصنيف · اضغط لعرض كل فيديوهاتها</span>
                        </span>
                    </a>
                <?php endif; ?>

                <?php if($video->description): ?>
                    <div class="mt-5 pt-5 border-t border-slate-100 dark:border-slate-700">
                        <h2 class="text-sm font-black text-slate-800 dark:text-slate-200 mb-2">الشرح</h2>
                        <div class="text-sm text-slate-600 dark:text-slate-300 leading-8 whitespace-pre-line"><?php echo e($video->description); ?></div>
                    </div>
                <?php endif; ?>

                <?php if(empty($hasFullAccess) || !$hasFullAccess): ?>
                    <div class="mt-5 rounded-xl bg-amber-50 dark:bg-amber-950/30 border border-amber-200 dark:border-amber-800 px-4 py-3 text-xs text-amber-900 dark:text-amber-100 leading-6">
                        أنت في وضع المعاينة المجانية. للوصول لجميع قنوات الفيديو اشترك في الباقة التي تتضمن «مكتبة الفيديو».
                        <a href="<?php echo e(route('student.features.show', ['feature' => 'video_library_access'])); ?>" class="font-bold underline underline-offset-2 ms-1">التفاصيل</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <aside class="xl:col-span-4 space-y-3">
            <h2 class="text-sm font-black text-slate-800 dark:text-slate-200 px-1">فيديوهات ذات صلة</h2>
            <?php $__empty_1 = true; $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                <?php
                    $relLocked = (empty($hasFullAccess) || !$hasFullAccess)
                        && !empty($usedFreePreview)
                        && (int) ($previewVideoId ?? 0) !== (int) $item->id;
                    $relHref = $relLocked
                        ? route('student.features.show', ['feature' => 'video_library_access'])
                        : route('video-library.show', $item);
                ?>
                <a href="<?php echo e($relHref); ?>" class="flex gap-3 p-2 rounded-xl hover:bg-slate-50 dark:hover:bg-slate-800/80 transition-colors group">
                    <div class="relative w-40 shrink-0 aspect-video rounded-lg overflow-hidden bg-slate-200">
                        <img src="<?php echo e($item->displayThumbnail()); ?>" alt="" class="w-full h-full object-cover" loading="lazy"
                             onerror="this.src='https://img.youtube.com/vi/<?php echo e($item->youtube_id); ?>/hqdefault.jpg'">
                        <?php if($item->formattedDuration()): ?>
                            <span class="absolute bottom-1 end-1 text-[10px] font-bold bg-black/80 text-white px-1 rounded"><?php echo e($item->formattedDuration()); ?></span>
                        <?php endif; ?>
                        <?php if($relLocked): ?>
                            <span class="absolute inset-0 bg-black/40 flex items-center justify-center text-white"><i class="fas fa-lock text-xs"></i></span>
                        <?php endif; ?>
                    </div>
                    <div class="min-w-0 py-0.5">
                        <p class="text-sm font-bold text-slate-900 dark:text-slate-100 line-clamp-2 group-hover:text-rose-600 leading-snug"><?php echo e($item->title); ?></p>
                        <p class="text-xs text-slate-500 mt-1 truncate"><?php echo e($item->category->name ?? 'مكتبة الفيديو'); ?></p>
                        <p class="text-[11px] text-slate-400 mt-0.5"><?php echo e(number_format($item->views_count)); ?> مشاهدة</p>
                    </div>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                <p class="text-sm text-slate-500 px-1">لا توجد فيديوهات أخرى حالياً.</p>
            <?php endif; ?>
        </aside>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\student\video-library\show.blade.php ENDPATH**/ ?>
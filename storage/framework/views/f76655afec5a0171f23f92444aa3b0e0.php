

<?php $__env->startSection('title', 'تفاصيل المشروع - البورتفوليو'); ?>
<?php $__env->startSection('header', 'تفاصيل المشروع'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <?php if(session('success')): ?>
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/25 border border-emerald-200 dark:border-emerald-800/60 px-4 py-3 flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
            <span class="font-semibold text-emerald-800 dark:text-emerald-200"><?php echo e(session('success')); ?></span>
        </div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="rounded-xl bg-red-50 dark:bg-red-900/25 border border-red-200 dark:border-red-800/60 px-4 py-3 flex items-center gap-3">
            <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
            <span class="font-semibold text-red-800 dark:text-red-200"><?php echo e(session('error')); ?></span>
        </div>
    <?php endif; ?>

    <?php
        $statusMap = [
            \App\Models\PortfolioProject::STATUS_PENDING_REVIEW => ['بانتظار المراجعة', 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200', 'fa-hourglass-half'],
            \App\Models\PortfolioProject::STATUS_APPROVED => ['معتمد (غير منشور)', 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200', 'fa-check-circle'],
            \App\Models\PortfolioProject::STATUS_REJECTED => ['مرفوض', 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200', 'fa-times-circle'],
            \App\Models\PortfolioProject::STATUS_PUBLISHED => ['منشور', 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-200', 'fa-globe'],
        ];
        $meta = $statusMap[$project->status] ?? ['غير معروف', 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200', 'fa-question-circle'];
    ?>

    <div class="bg-white dark:bg-slate-800/95 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100 truncate"><?php echo e($project->title); ?></h1>
                <div class="flex flex-wrap items-center gap-2 mt-2">
                    <span class="inline-flex items-center gap-2 text-[11px] font-bold px-3 py-1 rounded-full <?php echo e($meta[1]); ?>">
                        <i class="fas <?php echo e($meta[2]); ?>"></i>
                        <?php echo e($meta[0]); ?>

                    </span>
                    <?php if($project->academicYear): ?>
                        <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
                            المسار: <?php echo e($project->academicYear->name); ?>

                        </span>
                    <?php endif; ?>
                    <?php if($project->advancedCourse): ?>
                        <span class="text-[11px] font-bold px-3 py-1 rounded-full bg-slate-100 text-slate-700 dark:bg-slate-900/40 dark:text-slate-200">
                            الكورس: <?php echo e($project->advancedCourse->title); ?>

                        </span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('student.portfolio.index')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                    رجوع
                </a>
                <?php if(!in_array($project->status, [\App\Models\PortfolioProject::STATUS_APPROVED, \App\Models\PortfolioProject::STATUS_PUBLISHED], true)): ?>
                    <a href="<?php echo e(route('student.portfolio.edit', $project)); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition-colors">
                        <i class="fas fa-edit"></i>
                        تعديل
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <div class="p-6 space-y-6">
            <?php if($project->status === \App\Models\PortfolioProject::STATUS_REJECTED && $project->rejected_reason): ?>
                <div class="rounded-2xl border border-red-200 dark:border-red-800/60 bg-red-50 dark:bg-red-900/20 p-4">
                    <p class="font-bold text-red-800 dark:text-red-200 mb-1">سبب الرفض</p>
                    <p class="text-sm text-red-700 dark:text-red-200/90"><?php echo e($project->rejected_reason); ?></p>
                </div>
            <?php endif; ?>

            <?php if($project->description): ?>
                <div>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">الوصف</p>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line"><?php echo e($project->description); ?></div>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-2">روابط</p>
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fas fa-link ml-2"></i>رابط خارجي</span>
                            <?php if($project->project_url): ?>
                                <a href="<?php echo e($project->project_url); ?>" target="_blank" class="font-bold text-sky-700 dark:text-sky-300 hover:underline">فتح</a>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </div>
                        <div class="flex items-center justify-between gap-3">
                            <span class="font-bold text-slate-700 dark:text-slate-200"><i class="fas fa-video ml-2"></i>فيديو</span>
                            <?php if($project->video_url): ?>
                                <a href="<?php echo e($project->video_url); ?>" target="_blank" class="font-bold text-sky-700 dark:text-sky-300 hover:underline">فتح</a>
                            <?php else: ?>
                                <span class="text-slate-400">—</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-2">المراجعة</p>
                    <div class="space-y-1 text-sm text-slate-700 dark:text-slate-200">
                        <p><span class="font-bold">المراجع:</span> <?php echo e($project->reviewer?->name ?? '—'); ?></p>
                        <p><span class="font-bold">تاريخ المراجعة:</span> <?php echo e($project->reviewed_at ? $project->reviewed_at->format('Y-m-d H:i') : '—'); ?></p>
                        <p><span class="font-bold">تاريخ النشر:</span> <?php echo e($project->published_at ? $project->published_at->format('Y-m-d H:i') : '—'); ?></p>
                    </div>
                </div>
            </div>

            <?php if($project->content_type === \App\Models\PortfolioProject::CONTENT_TEXT && $project->content_text): ?>
                <div>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">المحتوى</p>
                    <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4 text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line"><?php echo e($project->content_text); ?></div>
                </div>
            <?php endif; ?>

            <?php if($project->content_type === \App\Models\PortfolioProject::CONTENT_VIDEO && $project->video_url): ?>
                <?php $embed = $project->videoEmbedUrl(); ?>
                <div>
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">الفيديو</p>
                    <?php if($embed): ?>
                        <div class="rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-black aspect-video">
                            <iframe src="<?php echo e($embed); ?>" class="w-full h-full" allowfullscreen></iframe>
                        </div>
                    <?php else: ?>
                        <a href="<?php echo e($project->video_url); ?>" target="_blank" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold transition-colors">
                            <i class="fas fa-play"></i>
                            فتح الفيديو
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>

            <div>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="text-sm font-bold text-slate-900 dark:text-slate-100">الصور</p>
                    <span class="text-xs text-slate-500 dark:text-slate-400"><?php echo e($project->images->count()); ?>/5</span>
                </div>
                <?php if($project->images->count() === 0): ?>
                    <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-600 p-8 text-center text-slate-500 dark:text-slate-400">
                        لا توجد صور مرفوعة.
                    </div>
                <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <?php $__currentLoopData = $project->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <a href="<?php echo e(asset($img->image_path)); ?>" target="_blank" class="block rounded-2xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-slate-100 dark:bg-slate-800/40">
                                <img src="<?php echo e(asset($img->image_path)); ?>" alt="image" class="w-full h-28 object-cover">
                            </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\student\portfolio\show.blade.php ENDPATH**/ ?>
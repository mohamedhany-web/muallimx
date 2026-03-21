
<?php $__env->startSection('title', 'جلسات البث المباشر'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white">
            <i class="fas fa-broadcast-tower text-red-500 ml-2"></i>جلسات البث المباشر
        </h1>
        <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">جلسات البث المتاحة لكورساتك</p>
    </div>

    
    <?php if($liveSessions->count() > 0): ?>
    <div class="space-y-3">
        <h2 class="text-lg font-bold text-red-600 flex items-center gap-2">
            <span class="w-2.5 h-2.5 bg-red-500 rounded-full animate-pulse"></span> مباشر الآن
        </h2>
        <?php $__currentLoopData = $liveSessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $live): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <div class="bg-gradient-to-l from-red-50 to-white dark:from-red-900/10 dark:to-slate-800 rounded-xl border border-red-200 dark:border-red-800 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full bg-red-100 dark:bg-red-900/40 text-red-600 text-xs font-bold">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse"></span> مباشر
                    </span>
                    <?php if($live->course): ?>
                        <span class="text-xs text-slate-400"><?php echo e($live->course->title); ?></span>
                    <?php endif; ?>
                </div>
                <h3 class="font-bold text-slate-800 dark:text-white text-lg"><?php echo e($live->title); ?></h3>
                <p class="text-sm text-slate-500 mt-1">
                    <i class="fas fa-chalkboard-teacher ml-1"></i> <?php echo e($live->instructor?->name); ?>

                    <span class="text-slate-300 mx-2">•</span>
                    بدأ <?php echo e($live->started_at?->diffForHumans()); ?>

                </p>
            </div>
            <form method="POST" action="<?php echo e(route('student.live-sessions.join', $live)); ?>">
                <?php echo csrf_field(); ?>
                <button class="px-6 py-3 bg-red-500 hover:bg-red-600 text-white rounded-xl font-bold shadow-lg shadow-red-500/25 transition-all text-sm">
                    <i class="fas fa-video ml-1"></i> انضم الآن
                </button>
            </form>
        </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
    <?php endif; ?>

    
    <div>
        <h2 class="text-lg font-bold text-slate-800 dark:text-white mb-3">الجلسات القادمة</h2>
        <div class="space-y-3">
            <?php $__empty_1 = true; $__currentLoopData = $sessions; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $session): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php if($session->status !== 'live'): ?>
            <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 flex flex-col sm:flex-row sm:items-center gap-4">
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="px-2 py-0.5 rounded-full bg-blue-100 dark:bg-blue-900/40 text-blue-600 text-xs font-medium">مجدولة</span>
                        <?php if($session->course): ?>
                            <span class="text-xs text-slate-400"><?php echo e($session->course->title); ?></span>
                        <?php endif; ?>
                    </div>
                    <h3 class="font-bold text-slate-800 dark:text-white"><?php echo e($session->title); ?></h3>
                    <div class="flex items-center gap-4 mt-1 text-xs text-slate-500">
                        <span><i class="fas fa-chalkboard-teacher ml-1"></i> <?php echo e($session->instructor?->name); ?></span>
                        <span><i class="fas fa-calendar ml-1"></i> <?php echo e($session->scheduled_at?->format('Y/m/d')); ?></span>
                        <span><i class="fas fa-clock ml-1"></i> <?php echo e($session->scheduled_at?->format('H:i')); ?></span>
                    </div>
                    <?php if($session->description): ?>
                    <p class="text-sm text-slate-400 mt-2"><?php echo e(Str::limit($session->description, 100)); ?></p>
                    <?php endif; ?>
                </div>
                <div class="flex-shrink-0">
                    <a href="<?php echo e(route('student.live-sessions.show', $session)); ?>" class="px-4 py-2 bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-lg text-sm font-medium hover:bg-slate-200 transition-colors">
                        <i class="fas fa-eye ml-1"></i> التفاصيل
                    </a>
                </div>
            </div>
            <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="text-center py-16 bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700">
                <i class="fas fa-broadcast-tower text-5xl text-slate-300 dark:text-slate-600 mb-4"></i>
                <p class="text-lg font-semibold text-slate-600 dark:text-slate-400 mb-2">لا توجد جلسات بث متاحة حالياً</p>
                <p class="text-sm text-slate-500">ستظهر هنا جلسات البث المباشر عند إنشائها من قبل المدربين</p>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <?php if($sessions->hasPages()): ?>
    <div><?php echo e($sessions->links()); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/student/live-sessions/index.blade.php ENDPATH**/ ?>
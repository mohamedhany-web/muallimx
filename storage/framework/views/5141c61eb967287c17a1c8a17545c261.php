<?php $__env->startSection('title', 'التسويق الشخصي - ملفك التعريفي'); ?>
<?php $__env->startSection('header', 'التسويق الشخصي'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <?php if(session('success')): ?>
    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/25 border border-emerald-200 dark:border-emerald-800/60 px-4 py-3 flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
        <span class="font-semibold text-emerald-800 dark:text-emerald-200"><?php echo e(session('success')); ?></span>
    </div>
    <?php endif; ?>

    <!-- الهيدر -->
    <div class="bg-white dark:bg-slate-800/95 rounded-xl p-5 border border-gray-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-col gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-slate-100 mb-1">التسويق الشخصي للمعلم</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">
                    هذا القسم مخصص لعرض ملفك التعريفي وتسويقك الشخصي كمعلم أونلاين.
                    تم ربطه الآن بخطة اشتراكك لعرض مزايا التسويق المتاحة لك بشكل واضح.
                </p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800/95 rounded-xl p-4 border border-gray-200 dark:border-slate-700 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">الخطة الحالية</p>
            <p class="text-lg font-bold text-slate-900 dark:text-slate-100"><?php echo e($subscription?->plan_name ?? 'بدون باقة مفعلة'); ?></p>
        </div>
        <div class="bg-white dark:bg-slate-800/95 rounded-xl p-4 border border-gray-200 dark:border-slate-700 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">أقسام الملف المتاحة</p>
            <p class="text-lg font-bold text-slate-900 dark:text-slate-100"><?php echo e((int) ($limits['personal_marketing_profile_sections'] ?? 5)); ?></p>
        </div>
        <div class="bg-white dark:bg-slate-800/95 rounded-xl p-4 border border-gray-200 dark:border-slate-700 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">درجة أولوية الظهور</p>
            <p class="text-lg font-bold text-slate-900 dark:text-slate-100"><?php echo e((int) ($limits['personal_marketing_priority_score'] ?? 0)); ?>/100</p>
        </div>
        <div class="bg-white dark:bg-slate-800/95 rounded-xl p-4 border border-gray-200 dark:border-slate-700 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">أيام إبراز الملف شهرياً</p>
            <p class="text-lg font-bold text-slate-900 dark:text-slate-100"><?php echo e((int) ($limits['personal_marketing_monthly_featured_days'] ?? 0)); ?></p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800/95 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70">
            <h3 class="font-bold text-slate-900 dark:text-slate-100">مزايا التسويق المتاحة حسب باقتك</h3>
        </div>
        <div class="p-5 grid grid-cols-1 md:grid-cols-2 gap-3">
            <?php $__currentLoopData = $marketingCapabilities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cap): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="rounded-xl border px-4 py-3 <?php echo e($cap['active'] ? 'border-emerald-200 bg-emerald-50/70 dark:border-emerald-800/60 dark:bg-emerald-900/20' : 'border-slate-200 bg-slate-50 dark:border-slate-700 dark:bg-slate-800/40'); ?>">
                    <div class="flex items-center justify-between gap-3">
                        <p class="text-sm font-semibold <?php echo e($cap['active'] ? 'text-emerald-800 dark:text-emerald-200' : 'text-slate-700 dark:text-slate-300'); ?>"><?php echo e($cap['label']); ?></p>
                        <span class="text-[11px] px-2 py-1 rounded-lg font-semibold <?php echo e($cap['active'] ? 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300' : 'bg-slate-200 text-slate-600 dark:bg-slate-700 dark:text-slate-300'); ?>">
                            <?php echo e($cap['active'] ? 'مفعلة' : 'غير مفعلة'); ?>

                        </span>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800/95 rounded-xl border border-dashed border-gray-200 dark:border-slate-600 p-8 text-center">
        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/40 rounded-2xl flex items-center justify-center mx-auto mb-4 text-emerald-600 dark:text-emerald-400">
            <i class="fas fa-bullhorn text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100 mb-2">نظام التسويق الشخصي متصل باشتراكك</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-4 max-w-2xl mx-auto">
            كل باقة تمنحك مستوى مختلف من الظهور التسويقي والمزايا الإضافية. يمكنك ترقية الاشتراك لزيادة أولوية الظهور وتفعيل مزايا تسويقية أقوى.
        </p>
        <a href="<?php echo e(route('student.my-subscription')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 dark:bg-sky-700 text-white text-sm font-semibold hover:bg-sky-700 dark:hover:bg-sky-600 transition-colors">
            <i class="fas fa-layer-group"></i>
            عرض ومقارنة مزايا اشتراكي
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/student/portfolio/index.blade.php ENDPATH**/ ?>
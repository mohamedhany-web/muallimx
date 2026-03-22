

<?php $__env->startSection('title', 'طلبات الاستشارة'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white">طلبات الاستشارة</h1>
            <p class="text-sm text-slate-500 mt-1">تُدار الجدولة والدفع من الإدارة؛ ستصلك إشعارات عند التفعيل</p>
        </div>
        <a href="<?php echo e(route('instructor.calendar')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 text-white text-sm font-bold">تقويم الاستشارات</a>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs text-slate-600 dark:text-slate-400 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-right">الطالب</th>
                        <th class="px-4 py-3 text-right">المبلغ</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">الموعد</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white"><?php echo e($r->student->name ?? '—'); ?></td>
                            <td class="px-4 py-3"><?php echo e(number_format($r->price_amount, 2)); ?> ج.م</td>
                            <td class="px-4 py-3"><span class="px-2 py-1 rounded-md bg-slate-100 dark:bg-slate-700 text-xs"><?php echo e($r->statusLabel()); ?></span></td>
                            <td class="px-4 py-3 text-xs text-slate-500"><?php echo e($r->scheduled_at?->format('Y-m-d H:i') ?? '—'); ?></td>
                            <td class="px-4 py-3"><a href="<?php echo e(route('instructor.consultations.show', $r)); ?>" class="text-sky-600 font-semibold hover:underline">تفاصيل</a></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="5" class="px-4 py-12 text-center text-slate-500">لا توجد طلبات</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-slate-100 dark:border-slate-700"><?php echo e($requests->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/instructor/consultations/index.blade.php ENDPATH**/ ?>
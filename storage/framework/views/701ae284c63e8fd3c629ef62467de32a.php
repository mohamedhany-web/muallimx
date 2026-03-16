

<?php $__env->startSection('title', 'اشتراكي - الباقة والمدة'); ?>
<?php $__env->startSection('header', 'اشتراكي'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <?php
        $sub = $subscription;
        $durationLabel = \App\Models\Subscription::getDurationLabel($sub->billing_cycle);
    ?>

    
    <div class="rounded-2xl bg-white border border-slate-200 shadow-lg overflow-hidden">
        <div class="bg-gradient-to-l from-sky-50 via-white to-white p-6 border-b border-slate-100">
            <div class="flex flex-wrap items-center gap-3 mb-2">
                <h1 class="text-xl sm:text-2xl font-black text-slate-800"><?php echo e($sub->plan_name); ?></h1>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700 border border-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    نشط
                </span>
            </div>
            <p class="text-sm text-slate-600">مدة الباقة: <strong><?php echo e($durationLabel); ?></strong> · ينتهي عند انتهاء المدة</p>
        </div>
        <div class="p-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">تاريخ التفعيل</p>
                    <p class="mt-1 text-base font-bold text-slate-900"><?php echo e($sub->start_date?->format('Y-m-d') ?? '—'); ?></p>
                </div>
                <div class="p-4 rounded-xl border border-slate-200 bg-slate-50/50">
                    <p class="text-xs font-semibold text-slate-500 uppercase tracking-wide">مدة الباقة</p>
                    <p class="mt-1 text-base font-bold text-slate-900"><?php echo e($durationLabel); ?></p>
                </div>
                <div class="p-4 rounded-xl border border-rose-100 bg-rose-50/50">
                    <p class="text-xs font-semibold text-rose-600 uppercase tracking-wide">ينتهي في</p>
                    <p class="mt-1 text-base font-bold text-slate-900"><?php echo e($sub->end_date?->format('Y-m-d') ?? '—'); ?></p>
                </div>
            </div>
        </div>
    </div>

    
    <?php if(!empty($sub->features) && is_array($sub->features)): ?>
        <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
            <h2 class="text-lg font-black text-slate-900 mb-4">المزايا المتاحة في باقتك</h2>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                <?php $__currentLoopData = $sub->features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li class="flex items-center gap-2 text-sm text-slate-700">
                        <span class="w-5 h-5 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-check text-[10px]"></i>
                        </span>
                        <?php echo e(__("student.subscription_feature.{$featureKey}")); ?>

                    </li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <p class="text-sm text-slate-500">
        عند انتهاء المدة سيُغلق الاشتراك تلقائياً ولن تظهر لك روابط القسم المدفوع حتى تجدد.
        <a href="<?php echo e(route('public.pricing')); ?>" class="text-sky-600 font-semibold hover:underline">عرض الباقات والتجديد</a>
    </p>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/student/my-subscription.blade.php ENDPATH**/ ?>
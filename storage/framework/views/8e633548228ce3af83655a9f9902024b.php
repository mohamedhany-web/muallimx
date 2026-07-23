<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag;

$__newAttributes = [];
$__propNames = \Illuminate\View\ComponentAttributeBag::extractPropNames(([
    'variant' => 'banner',
]));

foreach ($attributes->all() as $__key => $__value) {
    if (in_array($__key, $__propNames)) {
        $$__key = $$__key ?? $__value;
    } else {
        $__newAttributes[$__key] = $__value;
    }
}

$attributes = new \Illuminate\View\ComponentAttributeBag($__newAttributes);

unset($__propNames);
unset($__newAttributes);

foreach (array_filter(([
    'variant' => 'banner',
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
}

$__defined_vars = get_defined_vars();

foreach ($attributes->all() as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
}

unset($__defined_vars, $__key, $__value); ?>

<?php
    $pricingUrl = route('public.pricing');
?>

<?php if($variant === 'compact'): ?>
    <div class="flex flex-wrap items-center gap-2">
        <a href="<?php echo e($pricingUrl); ?>"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-[#FB5607] hover:bg-[#e04d00] text-white text-sm font-bold shadow-md shadow-[#FB5607]/25 transition-colors">
            <i class="fas fa-bolt text-xs"></i>
            <?php echo e(__('student.subscribe_now')); ?>

        </a>
        <a href="<?php echo e($pricingUrl); ?>#plans"
           class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-[#283593]/20 bg-white dark:bg-slate-800 text-[#283593] dark:text-brand-300 text-sm font-bold hover:border-[#283593]/40 hover:bg-[#FFE5F7]/50 dark:hover:bg-slate-700 transition-colors">
            <i class="fas fa-tags text-xs"></i>
            <?php echo e(__('student.view_packages')); ?>

        </a>
    </div>
<?php else: ?>
    <div class="rounded-2xl border-2 border-[#FB5607]/30 bg-gradient-to-l from-[#FFF7ED] via-white to-[#FFE5F7]/40 dark:from-slate-800/90 dark:via-slate-800/95 dark:to-slate-900/90 dark:border-amber-500/30 overflow-hidden">
        <div class="p-5 sm:p-6 flex flex-col lg:flex-row lg:items-center gap-5">
            <div class="flex items-start gap-4 flex-1 min-w-0">
                <span class="w-14 h-14 shrink-0 rounded-2xl bg-gradient-to-br from-[#FB5607] to-[#283593] text-white flex items-center justify-center shadow-lg shadow-[#FB5607]/20">
                    <i class="fas fa-crown text-xl"></i>
                </span>
                <div class="min-w-0">
                    <h2 class="font-heading text-lg sm:text-xl font-black text-slate-800 dark:text-slate-100 mb-1">
                        <?php echo e(__('student.subscribe_cta_title')); ?>

                    </h2>
                    <p class="text-sm text-slate-600 dark:text-slate-400 leading-relaxed max-w-2xl">
                        <?php echo e(__('student.subscribe_cta_description')); ?>

                    </p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-2 shrink-0">
                <a href="<?php echo e($pricingUrl); ?>"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#FB5607] hover:bg-[#e04d00] text-white text-sm font-bold shadow-md shadow-[#FB5607]/25 transition-colors">
                    <i class="fas fa-bolt"></i>
                    <?php echo e(__('student.subscribe_now')); ?>

                </a>
                <a href="<?php echo e($pricingUrl); ?>#plans"
                   class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-[#283593] hover:bg-[#1f2a7a] text-white text-sm font-bold transition-colors">
                    <i class="fas fa-layer-group"></i>
                    <?php echo e(__('student.view_packages')); ?>

                </a>
            </div>
        </div>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\components\dashboard-subscribe-cta.blade.php ENDPATH**/ ?>

<?php $__env->startSection('title', 'إنشاء صفحة هبوط'); ?>
<?php $__env->startSection('header', 'إنشاء صفحة هبوط'); ?>
<?php $__env->startSection('content'); ?>
<?php
    if (old('sections_json')) {
        $decoded = json_decode(old('sections_json'), true);
        $sectionsJson = is_array($decoded) ? $decoded : ($sectionsJson ?? []);
    }
?>
<div class="w-full space-y-6">
    <div class="rounded-3xl bg-white/95 backdrop-blur border border-slate-200 shadow-lg overflow-hidden">
        <div class="px-5 py-6 sm:px-8 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">صفحة هبوط جديدة</h1>
                <p class="text-slate-500 mt-1">
                    <?php if(!empty($useTemplate)): ?>
                        تم تحميل قالب الإعلان — عدّل الفيديو والأزرار ثم احفظ.
                    <?php else: ?>
                        املأ البيانات وأضف الأقسام، أو
                        <a href="<?php echo e(route('admin.landing-pages.create', ['template' => 1])); ?>" class="text-sky-600 font-semibold underline">ابدأ من قالب جاهز</a>.
                    <?php endif; ?>
                </p>
            </div>
            <a href="<?php echo e(route('admin.landing-pages.index')); ?>" class="text-sm font-semibold text-slate-600 hover:text-sky-600">← العودة للقائمة</a>
        </div>
        <form action="<?php echo e(route('admin.landing-pages.store')); ?>" method="POST" enctype="multipart/form-data" class="p-5 sm:p-8 space-y-6">
            <?php echo csrf_field(); ?>
            <?php echo $__env->make('admin.landing-pages._form', ['sectionsJson' => $sectionsJson ?? [], 'landingPage' => null], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gradient-to-r from-sky-500 to-blue-600 text-white font-bold shadow-lg shadow-sky-500/30">
                    <i class="fas fa-save"></i> حفظ الصفحة
                </button>
                <a href="<?php echo e(route('admin.landing-pages.index')); ?>" class="inline-flex items-center px-6 py-3 rounded-xl border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">إلغاء</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\admin\landing-pages\create.blade.php ENDPATH**/ ?>
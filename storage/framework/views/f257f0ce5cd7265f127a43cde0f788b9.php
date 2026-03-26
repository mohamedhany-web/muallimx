<?php
    $pt = $presentationTitle ?? (isset($file) ? ($file->label ?? 'عرض تفاعلي') : 'عرض تفاعلي');
?>

<?php $__env->startSection('title', $pt . ' - ' . $item->title); ?>
<?php $__env->startSection('header', $item->title); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-4">
    <div class="flex flex-wrap items-center gap-3">
        <a href="<?php echo e(route('curriculum-library.show', $item)); ?>" class="inline-flex items-center gap-2 text-indigo-600 hover:text-indigo-700 text-sm font-semibold">
            <i class="fas fa-arrow-right"></i> العودة لصفحة المنهج
        </a>
    </div>
    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="p-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-lg font-black text-slate-800"><?php echo e($pt); ?></h1>
                <p class="text-xs text-slate-500 mt-1">العرض داخل المنصة فقط؛ التحميل غير متاح لهذا النوع.</p>
            </div>
            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-100">
                <i class="fas fa-lock ml-1.5 text-[10px]"></i> بدون تحميل
            </span>
        </div>
        <div class="p-4 bg-slate-50">
            <?php if(!empty($canUseOfficeViewer) && !empty($embedUrl)): ?>
                <div class="aspect-[1410/900] w-full min-h-[480px] rounded-xl border border-slate-200 bg-white overflow-hidden shadow-inner">
                    <iframe title="عرض الشريحة"
                            src="<?php echo e($embedUrl); ?>"
                            class="w-full h-full min-h-[480px]"
                            allowfullscreen></iframe>
                </div>
                <p class="text-xs text-slate-500 mt-3 leading-relaxed">
                    إذا لم يظهر العرض، تأكد أن الملف متاح عبر رابط <strong>HTTPS</strong> عام (مثل بيئة الإنتاج).
                </p>
            <?php else: ?>
                <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-4 text-amber-900">
                    <p class="text-sm font-bold mb-1">لا يمكن فتح العرض التفاعلي حالياً</p>
                    <p class="text-xs leading-relaxed">
                        عرض ملفات PowerPoint يتم عبر عارض Microsoft داخل الصفحة، ويحتاج رابط <strong>HTTPS عام</strong> يمكن الوصول له من الإنترنت.
                        في بيئة التطوير (localhost) أو إذا كان الرابط غير HTTPS قد يفشل العرض.
                    </p>
                    <?php if(!empty($publicUrl)): ?>
                        <p class="text-[11px] text-amber-900/80 mt-2 break-all">الرابط الحالي: <span class="font-mono"><?php echo e($publicUrl); ?></span></p>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/student/curriculum-library/presentation.blade.php ENDPATH**/ ?>
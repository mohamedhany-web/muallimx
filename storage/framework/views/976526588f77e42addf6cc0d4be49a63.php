

<?php $__env->startSection('title', 'عرض التقديم: ' . $dataset->title); ?>
<?php $__env->startSection('header', 'عرض تقديم مجموعة البيانات'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-4 md:p-6 space-y-6">
    <div class="mb-4">
        <a href="<?php echo e(route('admin.community.submissions.index')); ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-semibold">
            <i class="fas fa-arrow-right"></i>
            <span>العودة لقائمة التقديمات المعلقة</span>
        </a>
    </div>

    <div class="bg-white rounded-2xl shadow border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-xl font-black text-slate-900"><?php echo e($dataset->title); ?></h1>
                <p class="text-sm text-slate-600 mt-1">
                    من: <?php echo e($dataset->creator->name ?? '—'); ?> (<?php echo e($dataset->creator->email ?? '—'); ?>) — <?php echo e($dataset->created_at->format('Y-m-d H:i')); ?>

                </p>
                <?php if($dataset->file_size): ?>
                    <p class="text-xs text-slate-500 mt-1">الحجم: <?php echo e($dataset->file_size); ?></p>
                <?php endif; ?>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <?php if($dataset->file_path): ?>
                    <a href="<?php echo e(route('admin.community.submissions.dataset.download', $dataset)); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-cyan-600 text-white font-bold hover:bg-cyan-700 transition-colors text-sm">
                        <i class="fas fa-download"></i>
                        <span>تحميل الملف</span>
                    </a>
                <?php endif; ?>
                <?php if($dataset->file_url): ?>
                    <a href="<?php echo e($dataset->file_url); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-slate-600 text-white font-bold hover:bg-slate-700 transition-colors text-sm">
                        <i class="fas fa-external-link-alt"></i>
                        <span>فتح رابط التحميل</span>
                    </a>
                <?php endif; ?>
                <form action="<?php echo e(route('admin.community.submissions.dataset.approve', $dataset)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-emerald-600 text-white font-bold hover:bg-emerald-700 transition-colors text-sm">
                        <i class="fas fa-check ml-1"></i> موافقة ونشر
                    </button>
                </form>
                <form action="<?php echo e(route('admin.community.submissions.dataset.reject', $dataset)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="px-4 py-2 rounded-xl bg-red-600 text-white font-bold hover:bg-red-700 transition-colors text-sm">
                        <i class="fas fa-times ml-1"></i> رفض
                    </button>
                </form>
            </div>
        </div>

        <?php if($dataset->description): ?>
            <div class="px-6 py-4 border-b border-slate-100">
                <h2 class="text-sm font-bold text-slate-700 mb-2">الوصف</h2>
                <div class="text-slate-600 text-sm leading-relaxed whitespace-pre-line"><?php echo e($dataset->description); ?></div>
            </div>
        <?php endif; ?>

        
        <?php if(!empty($previewHeaders) || !empty($previewRows)): ?>
            <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between flex-wrap gap-2">
                <h2 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-table text-cyan-600"></i>
                    معاينة البيانات
                </h2>
                <span class="text-slate-500 text-sm">أول <?php echo e(count($previewRows)); ?> صف</span>
            </div>
            <div class="overflow-auto max-h-[60vh]">
                <table class="w-full min-w-full border-collapse text-right">
                    <thead class="sticky top-0 z-10 bg-slate-100 border-b-2 border-slate-200">
                        <tr>
                            <?php $__currentLoopData = $previewHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cell): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <th class="px-4 py-3 text-sm font-bold text-slate-800 whitespace-nowrap border-l border-slate-200"><?php echo e(e($cell)); ?></th>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php $__currentLoopData = $previewRows; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $row): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <tr class="hover:bg-slate-50/80">
                                <?php $__currentLoopData = $previewHeaders; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $header): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <td class="px-4 py-2.5 text-sm text-slate-700 whitespace-nowrap border-l border-slate-100"><?php echo e(e($row[$i] ?? '')); ?></td>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="px-6 py-4">
                <?php if($dataset->file_url): ?>
                    <p class="text-slate-500 text-sm">لا يمكن معاينة المحتوى من الرابط. استخدم «فتح رابط التحميل» أو «تحميل الملف» إن وُجد.</p>
                <?php elseif($dataset->file_path): ?>
                    <p class="text-slate-500 text-sm">تعذر قراءة معاينة الملف (يدعم: Excel و CSV). يمكنك تحميل الملف من الزر أعلاه.</p>
                <?php else: ?>
                    <p class="text-slate-500 text-sm">لا يوجد ملف مرفق. الرابط الخارجي فقط: <?php if($dataset->file_url): ?> <a href="<?php echo e($dataset->file_url); ?>" target="_blank" rel="noopener" class="text-cyan-600 hover:underline"><?php echo e($dataset->file_url); ?></a> <?php else: ?> — <?php endif; ?></p>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mindly tics\Mindlytics\resources\views/admin/community/submissions-show.blade.php ENDPATH**/ ?>
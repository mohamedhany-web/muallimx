<?php $__env->startSection('title', $assignment->title); ?>
<?php $__env->startSection('header', $assignment->title); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6 max-w-4xl mx-auto">
    <div class="flex flex-wrap items-center gap-3 text-sm">
        <a href="<?php echo e(route('student.assignments.index')); ?>" class="text-sky-600 hover:text-sky-800 font-medium">
            <i class="fas fa-arrow-right ml-1"></i> واجباتي
        </a>
        <span class="text-gray-300">|</span>
        <span class="text-gray-600"><?php echo e($assignment->course->title ?? 'كورس'); ?></span>
    </div>

    <?php if(session('success')): ?>
        <div class="rounded-xl border border-emerald-200 bg-emerald-50 text-emerald-900 px-4 py-3 text-sm font-medium"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="rounded-xl border border-red-200 bg-red-50 text-red-900 px-4 py-3 text-sm font-medium"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-4">
        <h1 class="text-2xl font-bold text-gray-900"><?php echo e($assignment->title); ?></h1>
        <?php if($assignment->due_date): ?>
            <p class="text-sm text-gray-600">
                <i class="fas fa-calendar-alt ml-1 text-sky-600"></i>
                آخر موعد: <?php echo e($assignment->due_date->timezone(config('app.timezone'))->format('Y-m-d H:i')); ?>

                <?php if($assignment->allow_late_submission): ?>
                    <span class="text-emerald-700">(يُقبل التسليم المتأخر)</span>
                <?php endif; ?>
            </p>
        <?php endif; ?>
        <?php if($assignment->description): ?>
            <div class="prose prose-sm max-w-none text-gray-700"><?php echo nl2br(e($assignment->description)); ?></div>
        <?php endif; ?>
        <?php if($assignment->instructions): ?>
            <div class="rounded-lg bg-slate-50 border border-slate-200 p-4">
                <p class="text-xs font-bold text-slate-600 mb-2">التعليمات</p>
                <div class="text-sm text-slate-800 whitespace-pre-wrap"><?php echo e($assignment->instructions); ?></div>
            </div>
        <?php endif; ?>
        <?php
            $instrFiles = is_array($assignment->resource_attachments) ? $assignment->resource_attachments : [];
        ?>
        <?php if(count($instrFiles) > 0): ?>
            <div class="rounded-xl border border-sky-200 bg-sky-50/80 p-4 space-y-3">
                <p class="text-sm font-bold text-sky-900">ملفات من المدرب</p>
                <ul class="space-y-3">
                    <?php $__currentLoopData = $instrFiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $p = is_array($att) ? ($att['path'] ?? '') : '';
                            $url = $p ? (\App\Services\AssignmentFileStorage::publicUrl($p)) : null;
                            $label = is_array($att) ? ($att['original_name'] ?? basename($p)) : '';
                            $mime = is_array($att) ? ($att['mime'] ?? '') : '';
                            $isImg = $mime && str_starts_with((string) $mime, 'image/');
                        ?>
                        <?php if($url): ?>
                            <li class="text-sm">
                                <?php if($isImg): ?>
                                    <a href="<?php echo e($url); ?>" target="_blank" rel="noopener" class="block">
                                        <img src="<?php echo e($url); ?>" alt="<?php echo e($label); ?>" class="max-h-48 rounded-lg border border-sky-200 shadow-sm">
                                    </a>
                                    <a href="<?php echo e($url); ?>" target="_blank" rel="noopener" class="text-sky-700 hover:underline text-xs mt-1 inline-block">فتح الصورة بحجم كامل</a>
                                <?php else: ?>
                                    <a href="<?php echo e($url); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-sky-700 font-semibold hover:underline">
                                        <i class="fas fa-file-download"></i> <?php echo e($label); ?>

                                    </a>
                                <?php endif; ?>
                            </li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>
        <p class="text-sm text-gray-500">الدرجة العظمى: <span class="font-semibold text-gray-800"><?php echo e($assignment->max_score); ?></span></p>
    </div>

    <?php if($submission): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm space-y-3">
            <h2 class="text-lg font-bold text-gray-900">تسليمك</h2>
            <p class="text-sm text-gray-600">
                الحالة:
                <?php if($submission->status === 'submitted'): ?>
                    <span class="font-semibold text-sky-700">قيد التصحيح</span>
                <?php elseif($submission->status === 'graded'): ?>
                    <span class="font-semibold text-emerald-700">مُقيَّم</span>
                    <?php if($submission->score !== null): ?>
                        — الدرجة: <?php echo e($submission->score); ?> / <?php echo e($assignment->max_score); ?>

                    <?php endif; ?>
                <?php elseif($submission->status === 'returned'): ?>
                    <span class="font-semibold text-violet-700">مُعاد للتعديل</span>
                <?php endif; ?>
            </p>
            <?php if($submission->submitted_at): ?>
                <p class="text-xs text-gray-500">آخر إرسال: <?php echo e($submission->submitted_at->timezone(config('app.timezone'))->format('Y-m-d H:i')); ?></p>
            <?php endif; ?>
            <?php if($submission->content): ?>
                <div class="rounded-lg border border-gray-100 bg-gray-50 p-4 text-sm text-gray-800 whitespace-pre-wrap"><?php echo e($submission->content); ?></div>
            <?php endif; ?>
            <?php if(is_array($submission->attachments) && count($submission->attachments)): ?>
                <div class="space-y-2">
                    <p class="text-sm font-semibold text-gray-700">المرفقات</p>
                    <ul class="space-y-1">
                        <?php $__currentLoopData = $submission->attachments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $att): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $p = is_array($att) ? ($att['path'] ?? null) : null;
                                $name = is_array($att) ? ($att['original_name'] ?? basename((string) $p)) : '';
                                $fileUrl = $p ? \App\Services\AssignmentFileStorage::publicUrl($p) : null;
                            ?>
                            <?php if($fileUrl): ?>
                                <li>
                                    <a href="<?php echo e($fileUrl); ?>" target="_blank" rel="noopener" class="text-sky-600 hover:underline text-sm">
                                        <i class="fas fa-paperclip ml-1"></i><?php echo e($name); ?>

                                    </a>
                                </li>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            <?php endif; ?>
            <?php if($submission->feedback): ?>
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4">
                    <p class="text-xs font-bold text-amber-800 mb-1">ملاحظات المُصحّح</p>
                    <p class="text-sm text-amber-950 whitespace-pre-wrap"><?php echo e($submission->feedback); ?></p>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if($canSubmit): ?>
        <div class="bg-white rounded-xl border border-gray-200 p-6 shadow-sm">
            <h2 class="text-lg font-bold text-gray-900 mb-4"><?php echo e($submission ? 'تحديث التسليم' : 'تسليم الواجب'); ?></h2>
            <form action="<?php echo e(route('student.assignments.submit', $assignment)); ?>" method="post" enctype="multipart/form-data" class="space-y-4">
                <?php echo csrf_field(); ?>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">النص (اختياري إن وُجدت مرفقات)</label>
                    <textarea name="content" rows="8" class="w-full rounded-lg border border-gray-300 px-3 py-2 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"><?php echo e(old('content', $submission->content ?? '')); ?></textarea>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">مرفقات (يمكن اختيار عدة ملفات؛ تُضاف للمرفقات السابقة)</label>
                    <input type="file" name="attachments[]" multiple accept=".pdf,.doc,.docx,.zip,.rar,.jpg,.jpeg,.png,.gif,.webp" class="block w-full text-sm text-gray-600">
                    <p class="text-xs text-gray-500 mt-1">PDF، Word، صور، أرشيف — حتى 40 ميجابايت لكل ملف.</p>
                </div>
                <button type="submit" class="inline-flex items-center gap-2 bg-gradient-to-l from-sky-500 to-sky-600 text-white px-6 py-3 rounded-xl font-bold text-sm shadow-lg hover:opacity-95">
                    <i class="fas fa-paper-plane"></i>
                    إرسال التسليم
                </button>
            </form>
        </div>
    <?php elseif($submitBlockReason): ?>
        <div class="rounded-xl border border-amber-200 bg-amber-50 text-amber-900 px-4 py-3 text-sm"><?php echo e($submitBlockReason); ?></div>
    <?php endif; ?>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\student\assignments\show.blade.php ENDPATH**/ ?>
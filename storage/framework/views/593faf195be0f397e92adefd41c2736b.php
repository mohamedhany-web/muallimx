

<?php $__env->startSection('title', 'تفاصيل الاجتماع'); ?>
<?php $__env->startSection('header', 'تفاصيل الاجتماع'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <?php if(session('success')): ?>
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm font-medium"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6 space-y-4">
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-900 dark:text-white"><?php echo e($meeting->title); ?></h1>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-1">الكود: <span class="font-mono font-bold"><?php echo e($meeting->code); ?></span></p>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?php echo e(route('student.classroom.edit', $meeting)); ?>" class="px-4 py-2 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold">تعديل</a>
                <?php if(!$meeting->started_at && !$meeting->ended_at): ?>
                    <form action="<?php echo e(route('student.classroom.start-meeting', $meeting)); ?>" method="POST"><?php echo csrf_field(); ?><button class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-semibold">بدء الآن</button></form>
                <?php elseif($meeting->isLive()): ?>
                    <a href="<?php echo e(route('student.classroom.room', $meeting)); ?>" class="px-4 py-2 rounded-xl bg-red-500 hover:bg-red-600 text-white text-sm font-semibold">دخول الغرفة</a>
                    <form method="POST" action="<?php echo e(route('student.classroom.end', $meeting)); ?>" onsubmit="return confirm('إنهاء الاجتماع؟');"><?php echo csrf_field(); ?><button class="px-4 py-2 rounded-xl bg-slate-700 hover:bg-slate-800 text-white text-sm font-semibold">إنهاء</button></form>
                <?php endif; ?>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الحالة</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white">
                    <?php echo e($meeting->isLive() ? 'مباشر' : (!$meeting->started_at ? 'مجدول' : 'منتهي')); ?>

                </p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الموعد المحدد</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white"><?php echo e(optional($meeting->scheduled_for)->format('Y-m-d H:i') ?? 'غير محدد'); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">مدة الاجتماع</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white"><?php echo e((int) ($meeting->planned_duration_minutes ?? $limits['classroom_max_duration_minutes'])); ?> دقيقة</p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">الحد الأقصى للمشاركين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white"><?php echo e((int) ($meeting->max_participants ?? 25)); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">أعلى ذروة مشاركين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white"><?php echo e((int) ($meeting->participants_peak ?? 0)); ?></p>
            </div>
            <div class="rounded-xl border border-slate-200 dark:border-slate-600 p-3">
                <p class="text-xs text-slate-500 dark:text-slate-400">إجمالي المشاركين المسجلين</p>
                <p class="text-sm font-semibold text-slate-800 dark:text-white"><?php echo e((int) ($meeting->participants_count ?? 0)); ?></p>
            </div>
        </div>

        <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-3 flex flex-wrap items-center justify-between gap-3">
            <div class="text-xs text-slate-600 dark:text-slate-300">رابط الانضمام للطلاب والضيوف:</div>
            <div class="flex items-center gap-2">
                <input type="text" readonly value="<?php echo e($joinUrl); ?>" class="w-[340px] max-w-[60vw] px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-xs">
                <button type="button" onclick="navigator.clipboard.writeText('<?php echo e($joinUrl); ?>')" class="px-3 py-2 rounded-lg bg-slate-100 dark:bg-slate-700 text-xs font-semibold">نسخ</button>
            </div>
        </div>

        <div class="flex items-center justify-between">
            <a href="<?php echo e(route('student.classroom.index')); ?>" class="text-sm text-sky-600 hover:underline">العودة لقائمة الاجتماعات</a>
            <form action="<?php echo e(route('student.classroom.destroy', $meeting)); ?>" method="POST" onsubmit="return confirm('حذف الاجتماع نهائياً؟');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button class="text-sm text-rose-600 hover:underline">حذف الاجتماع</button>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/student/classroom/show.blade.php ENDPATH**/ ?>
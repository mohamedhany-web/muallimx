
<?php $__env->startSection('title', 'إنشاء جلسة بث مباشر'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="<?php echo e(route('instructor.live-sessions.index')); ?>" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 dark:text-slate-400 transition-colors"><i class="fas fa-arrow-right"></i></a>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><i class="fas fa-plus-circle text-red-500 ml-2"></i>إنشاء جلسة بث</h1>
    </div>

    <form method="POST" action="<?php echo e(route('instructor.live-sessions.store')); ?>" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
        <?php echo csrf_field(); ?>
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">عنوان الجلسة <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="<?php echo e(old('title')); ?>" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="مثال: مراجعة أدوات AI — الأسبوع الثالث">
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الكورس (اختياري)</label>
                <select name="course_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <option value="">جلسة عامة (بدون كورس)</option>
                    <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($course->id); ?>" <?php echo e(old('course_id') == $course->id ? 'selected' : ''); ?>><?php echo e($course->title); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
                <p class="text-[11px] text-slate-400 mt-1">ربط الجلسة بكورس محدد سيتيح الدخول فقط للطلاب المسجلين</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">موعد البث <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="scheduled_at" value="<?php echo e(old('scheduled_at')); ?>" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <?php $__errorArgs = ['scheduled_at'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الحد الأقصى</label>
                    <input type="number" name="max_participants" value="<?php echo e(old('max_participants', 100)); ?>" min="2" max="500" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">كلمة مرور (اختياري)</label>
                <input type="text" name="password" value="<?php echo e(old('password')); ?>" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="اتركها فارغة للدخول بدون باسوورد">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">وصف الجلسة</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="محتوى الجلسة / ماذا سيتعلم المعلم..."><?php echo e(old('description')); ?></textarea>
            </div>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_recorded" value="1" <?php echo e(old('is_recorded') ? 'checked' : ''); ?> class="rounded text-red-500 focus:ring-red-500">
                    <span class="text-sm text-slate-700 dark:text-slate-300">تسجيل الجلسة</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="allow_chat" value="1" <?php echo e(old('allow_chat', true) ? 'checked' : ''); ?> class="rounded text-blue-500 focus:ring-blue-500">
                    <span class="text-sm text-slate-700 dark:text-slate-300">السماح بالشات</span>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-red-600 dark:bg-red-700 hover:bg-red-600 text-white rounded-xl font-semibold shadow-lg shadow-red-500/25 transition-all">
                <i class="fas fa-broadcast-tower ml-1"></i> إنشاء الجلسة
            </button>
            <a href="<?php echo e(route('instructor.live-sessions.index')); ?>" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-300 transition-colors">إلغاء</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\instructor\live-sessions\create.blade.php ENDPATH**/ ?>


<?php $__env->startSection('title', 'تعديل المحاضرة'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h1 class="text-2xl font-bold text-gray-900 mb-6">تعديل المحاضرة: <?php echo e($lecture->title); ?></h1>

            <form action="<?php echo e(route('admin.lectures.update', $lecture)); ?>" method="POST">
                <?php echo csrf_field(); ?>
                <?php echo method_field('PUT'); ?>
                
                <div class="space-y-6">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">الكورس *</label>
                            <select name="course_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($course->id); ?>" <?php echo e(old('course_id', $lecture->course_id) == $course->id ? 'selected' : ''); ?>>
                                        <?php echo e($course->title); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">المحاضر *</label>
                            <select name="instructor_id" required
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                <?php $__currentLoopData = $instructors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $instructor): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <option value="<?php echo e($instructor->id); ?>" <?php echo e(old('instructor_id', $lecture->instructor_id) == $instructor->id ? 'selected' : ''); ?>>
                                        <?php echo e($instructor->name); ?>

                                    </option>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">عنوان المحاضرة *</label>
                        <input type="text" name="title" value="<?php echo e(old('title', $lecture->title)); ?>" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?>
                            <p class="mt-1 text-sm text-red-600"><?php echo e($message); ?></p>
                        <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الوصف</label>
                        <textarea name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500"><?php echo e(old('description', $lecture->description)); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ ووقت المحاضرة *</label>
                            <input type="datetime-local" name="scheduled_at" 
                                   value="<?php echo e(old('scheduled_at', $lecture->scheduled_at->format('Y-m-d\TH:i'))); ?>" required
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">مدة المحاضرة (بالدقائق)</label>
                            <input type="number" name="duration_minutes" value="<?php echo e(old('duration_minutes', $lecture->duration_minutes)); ?>" min="1"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رابط تسجيل Teams</label>
                        <input type="url" name="teams_registration_link" value="<?php echo e(old('teams_registration_link', $lecture->teams_registration_link)); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رابط اجتماع Teams</label>
                        <input type="url" name="teams_meeting_link" value="<?php echo e(old('teams_meeting_link', $lecture->teams_meeting_link)); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">رابط تسجيل المحاضرة</label>
                        <input type="url" name="recording_url" value="<?php echo e(old('recording_url', $lecture->recording_url)); ?>"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">الحالة</label>
                        <select name="status"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            <option value="scheduled" <?php echo e(old('status', $lecture->status) == 'scheduled' ? 'selected' : ''); ?>>مجدولة</option>
                            <option value="in_progress" <?php echo e(old('status', $lecture->status) == 'in_progress' ? 'selected' : ''); ?>>قيد التنفيذ</option>
                            <option value="completed" <?php echo e(old('status', $lecture->status) == 'completed' ? 'selected' : ''); ?>>مكتملة</option>
                            <option value="cancelled" <?php echo e(old('status', $lecture->status) == 'cancelled' ? 'selected' : ''); ?>>ملغاة</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">ملاحظات</label>
                        <textarea name="notes" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500"><?php echo e(old('notes', $lecture->notes)); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="has_attendance_tracking" value="1" <?php echo e(old('has_attendance_tracking', $lecture->has_attendance_tracking) ? 'checked' : ''); ?>

                                   class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                            <span class="mr-2 text-sm text-gray-700">تتبع الحضور</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="has_assignment" value="1" <?php echo e(old('has_assignment', $lecture->has_assignment) ? 'checked' : ''); ?>

                                   class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                            <span class="mr-2 text-sm text-gray-700">يوجد واجب</span>
                        </label>
                        <label class="flex items-center cursor-pointer">
                            <input type="checkbox" name="has_evaluation" value="1" <?php echo e(old('has_evaluation', $lecture->has_evaluation) ? 'checked' : ''); ?>

                                   class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                            <span class="mr-2 text-sm text-gray-700">يوجد تقييم للمحاضر</span>
                        </label>
                    </div>

                    <div class="flex justify-end space-x-4 space-x-reverse">
                        <a href="<?php echo e(route('admin.lectures.index')); ?>" class="btn-secondary">
                            إلغاء
                        </a>
                        <button type="submit" class="btn-primary">
                            <i class="fas fa-save ml-2"></i>
                            حفظ التغييرات
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mindly tics\Mindlytics\resources\views/admin/lectures/edit.blade.php ENDPATH**/ ?>
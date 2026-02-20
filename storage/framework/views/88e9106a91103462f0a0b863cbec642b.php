

<?php $__env->startSection('title', 'تفاصيل المحاضرة'); ?>

<?php $__env->startSection('content'); ?>
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        <!-- معلومات المحاضرة -->
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex justify-between items-start mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-900"><?php echo e($lecture->title); ?></h1>
                    <p class="text-gray-600 mt-2"><?php echo e($lecture->course->title ?? ''); ?></p>
                </div>
                <div class="flex space-x-2 space-x-reverse">
                    <a href="<?php echo e(route('admin.lectures.edit', $lecture)); ?>" class="btn-primary">
                        <i class="fas fa-edit ml-2"></i>
                        تعديل
                    </a>
                    <a href="<?php echo e(route('admin.lectures.index')); ?>" class="btn-secondary">
                        <i class="fas fa-arrow-right ml-2"></i>
                        رجوع
                    </a>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">المحاضر</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($lecture->instructor->name ?? '-'); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">تاريخ ووقت المحاضرة</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($lecture->scheduled_at->format('Y-m-d H:i')); ?></p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">المدة</p>
                    <p class="text-lg font-semibold text-gray-900"><?php echo e($lecture->duration_minutes); ?> دقيقة</p>
                </div>
                <div class="bg-gray-50 rounded-lg p-4">
                    <p class="text-sm text-gray-600">الحالة</p>
                    <p>
                        <?php if($lecture->status == 'completed'): ?>
                            <span class="badge badge-success">مكتملة</span>
                        <?php elseif($lecture->status == 'in_progress'): ?>
                            <span class="badge badge-primary">قيد التنفيذ</span>
                        <?php elseif($lecture->status == 'cancelled'): ?>
                            <span class="badge badge-danger">ملغاة</span>
                        <?php else: ?>
                            <span class="badge badge-warning">مجدولة</span>
                        <?php endif; ?>
                    </p>
                </div>
            </div>

            <?php if($lecture->description): ?>
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-2">الوصف</h3>
                <p class="text-gray-700"><?php echo e($lecture->description); ?></p>
            </div>
            <?php endif; ?>

            <!-- روابط Teams -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <?php if($lecture->teams_registration_link): ?>
                <div class="bg-sky-50 rounded-lg p-4 border border-sky-200">
                    <h4 class="font-semibold text-gray-900 mb-2">رابط تسجيل Teams</h4>
                    <a href="<?php echo e($lecture->teams_registration_link); ?>" target="_blank" class="text-sky-600 hover:text-sky-800">
                        <?php echo e($lecture->teams_registration_link); ?> <i class="fas fa-external-link-alt mr-1"></i>
                    </a>
                </div>
                <?php endif; ?>

                <?php if($lecture->teams_meeting_link): ?>
                <div class="bg-blue-50 rounded-lg p-4 border border-blue-200">
                    <h4 class="font-semibold text-gray-900 mb-2">رابط اجتماع Teams</h4>
                    <a href="<?php echo e($lecture->teams_meeting_link); ?>" target="_blank" class="text-blue-600 hover:text-blue-800">
                        <?php echo e($lecture->teams_meeting_link); ?> <i class="fas fa-external-link-alt mr-1"></i>
                    </a>
                </div>
                <?php endif; ?>

                <?php if($lecture->recording_url): ?>
                <div class="bg-purple-50 rounded-lg p-4 border border-purple-200">
                    <h4 class="font-semibold text-gray-900 mb-2">رابط تسجيل المحاضرة</h4>
                    <a href="<?php echo e($lecture->recording_url); ?>" target="_blank" class="text-purple-600 hover:text-purple-800">
                        <?php echo e($lecture->recording_url); ?> <i class="fas fa-external-link-alt mr-1"></i>
                    </a>
                </div>
                <?php endif; ?>
            </div>

            <!-- خيارات المحاضرة -->
            <div class="flex flex-wrap gap-4 mb-6">
                <?php if($lecture->has_attendance_tracking): ?>
                    <span class="badge badge-success">تتبع الحضور</span>
                <?php endif; ?>
                <?php if($lecture->has_assignment): ?>
                    <span class="badge badge-primary">يوجد واجب</span>
                <?php endif; ?>
                <?php if($lecture->has_evaluation): ?>
                    <span class="badge badge-warning">يوجد تقييم</span>
                <?php endif; ?>
            </div>

            <?php if($lecture->notes): ?>
            <div class="bg-yellow-50 rounded-lg p-4 border border-yellow-200">
                <h4 class="font-semibold text-gray-900 mb-2">ملاحظات</h4>
                <p class="text-gray-700"><?php echo e($lecture->notes); ?></p>
            </div>
            <?php endif; ?>
        </div>

        <!-- الحضور -->
        <?php if($lecture->has_attendance_tracking): ?>
        <div class="bg-white rounded-xl shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center mb-4">
                <h2 class="text-xl font-bold text-gray-900">الحضور والانصراف</h2>
                <form action="<?php echo e(route('admin.lectures.sync-teams-attendance', $lecture)); ?>" method="POST" class="inline">
                    <?php echo csrf_field(); ?>
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-sync ml-2"></i>
                        مزامنة من Teams
                    </button>
                </form>
            </div>
            <p class="text-gray-600 mb-4">سيتم استيراد الحضور تلقائياً من ملف Teams</p>
        </div>
        <?php endif; ?>

        <!-- الواجبات -->
        <?php if($lecture->has_assignment && $lecture->assignments->count() > 0): ?>
        <div class="bg-white rounded-xl shadow-lg p-6">
            <h2 class="text-xl font-bold text-gray-900 mb-4">واجبات المحاضرة</h2>
            <div class="space-y-4">
                <?php $__currentLoopData = $lecture->assignments; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $assignment): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="font-semibold text-gray-900"><?php echo e($assignment->title); ?></h4>
                    <p class="text-sm text-gray-600 mt-1">تاريخ التسليم: <?php echo e($assignment->due_date ? $assignment->due_date->format('Y-m-d H:i') : '-'); ?></p>
                </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mindly tics\Mindlytics\resources\views/admin/lectures/show.blade.php ENDPATH**/ ?>
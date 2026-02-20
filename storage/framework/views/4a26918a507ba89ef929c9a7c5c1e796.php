

<?php $__env->startSection('title', 'مساهمو المجتمع'); ?>
<?php $__env->startSection('header', 'مساهمو مجتمع الذكاء الاصطناعي'); ?>

<?php $__env->startSection('content'); ?>
<div class="p-4 md:p-6 space-y-6">
    <?php if(session('success')): ?>
        <div class="p-4 rounded-xl bg-emerald-100 border border-emerald-300 text-emerald-800"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="p-4 rounded-xl bg-red-100 border border-red-300 text-red-800">
            <ul class="list-disc list-inside"><?php echo e($errors->first()); ?></ul>
        </div>
    <?php endif; ?>

    <!-- ترقية مستخدم حالي إلى مساهم -->
    <div class="bg-white rounded-2xl shadow border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">ترقية مستخدم حالي إلى مساهم</h2>
        <p class="text-sm text-slate-600 mb-4">أدخل بريد مستخدم مسجل في المنصة لترقيته إلى مساهم في مجتمع الذكاء الاصطناعي.</p>
        <form action="<?php echo e(route('admin.community.contributors.store')); ?>" method="POST" class="flex flex-wrap gap-4 items-end">
            <?php echo csrf_field(); ?>
            <div class="flex-1 min-w-[200px]">
                <label for="email" class="block text-sm font-semibold text-slate-700 mb-1">البريد الإلكتروني للمستخدم</label>
                <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" placeholder="user@example.com"
                       class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-cyan-600 text-white font-bold hover:bg-cyan-700 transition-colors">
                <i class="fas fa-user-plus ml-1"></i> ترقية إلى مساهم
            </button>
        </form>
    </div>

    <!-- إنشاء حساب مساهم جديد -->
    <div class="bg-white rounded-2xl shadow border border-slate-200 p-6">
        <h2 class="text-lg font-bold text-slate-800 mb-4">إنشاء حساب مساهم جديد</h2>
        <p class="text-sm text-slate-600 mb-4">إنشاء مستخدم جديد بصلاحية مساهم فقط (تسجيل الدخول من صفحة المجتمع).</p>
        <form action="<?php echo e(route('admin.community.contributors.new.store')); ?>" method="POST" class="space-y-4 max-w-2xl">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="new_name" class="block text-sm font-semibold text-slate-700 mb-1">الاسم <span class="text-red-500">*</span></label>
                    <input type="text" name="name" id="new_name" value="<?php echo e(old('name')); ?>" required
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>
                <div>
                    <label for="new_email" class="block text-sm font-semibold text-slate-700 mb-1">البريد الإلكتروني <span class="text-red-500">*</span></label>
                    <input type="email" name="email" id="new_email" value="<?php echo e(old('email')); ?>" required
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="new_password" class="block text-sm font-semibold text-slate-700 mb-1">كلمة المرور <span class="text-red-500">*</span></label>
                    <input type="password" name="password" id="new_password" required minlength="8"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                    <p class="mt-1 text-xs text-slate-500">8 أحرف على الأقل</p>
                </div>
                <div>
                    <label for="new_password_confirmation" class="block text-sm font-semibold text-slate-700 mb-1">تأكيد كلمة المرور <span class="text-red-500">*</span></label>
                    <input type="password" name="password_confirmation" id="new_password_confirmation" required minlength="8"
                           class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
                </div>
            </div>
            <div class="max-w-xs">
                <label for="new_phone" class="block text-sm font-semibold text-slate-700 mb-1">الهاتف (اختياري)</label>
                <input type="text" name="phone" id="new_phone" value="<?php echo e(old('phone')); ?>"
                       class="w-full px-4 py-2 rounded-xl border border-slate-300 focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500">
            </div>
            <button type="submit" class="px-6 py-2.5 rounded-xl bg-teal-600 text-white font-bold hover:bg-teal-700 transition-colors">
                <i class="fas fa-user-plus ml-1"></i> إنشاء حساب مساهم
            </button>
        </form>
    </div>

    <!-- قائمة المساهمين -->
    <div class="bg-white rounded-2xl shadow border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
            <h2 class="text-lg font-bold text-slate-800">قائمة المساهمين (<?php echo e($contributors->count()); ?>)</h2>
            <p class="text-sm text-slate-600 mt-1">المساهمون يمكنهم رفع مجموعات بيانات ومسابقات وتصل للإدارة للمراجعة قبل النشر.</p>
        </div>
        <div class="p-6">
            <?php if($contributors->isEmpty()): ?>
                <div class="text-center py-12 text-slate-500">
                    <i class="fas fa-users text-4xl mb-4"></i>
                    <p>لا يوجد مساهمون حتى الآن. أضف مستخدمين من خلال البريد أعلاه.</p>
                </div>
            <?php else: ?>
                <div class="overflow-x-auto">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-600 text-sm">
                                <th class="py-3 px-4">الاسم</th>
                                <th class="py-3 px-4">البريد</th>
                                <th class="py-3 px-4">الدور</th>
                                <th class="py-3 px-4">إجراء</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $__currentLoopData = $contributors; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="border-b border-slate-100 hover:bg-slate-50/50">
                                    <td class="py-3 px-4 font-medium text-slate-900"><?php echo e($user->name); ?></td>
                                    <td class="py-3 px-4 text-slate-600"><?php echo e($user->email); ?></td>
                                    <td class="py-3 px-4">
                                        <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold
                                            <?php if($user->role === 'student'): ?> bg-emerald-100 text-emerald-700
                                            <?php elseif($user->role === 'instructor'): ?> bg-indigo-100 text-indigo-700
                                            <?php else: ?> bg-slate-100 text-slate-700 <?php endif; ?>">
                                            <?php if($user->role === 'student'): ?> طالب
                                            <?php elseif($user->role === 'instructor'): ?> مدرب
                                            <?php else: ?> <?php echo e($user->role ?? '—'); ?> <?php endif; ?>
                                        </span>
                                    </td>
                                    <td class="py-3 px-4">
                                        <form action="<?php echo e(route('admin.community.contributors.destroy', $user)); ?>" method="POST" class="inline" onsubmit="return confirm('إزالة صلاحية المساهم؟');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="text-red-600 hover:text-red-800 text-sm font-semibold">
                                                <i class="fas fa-user-minus ml-1"></i> إزالة
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mindly tics\Mindlytics\resources\views/admin/community/contributors.blade.php ENDPATH**/ ?>
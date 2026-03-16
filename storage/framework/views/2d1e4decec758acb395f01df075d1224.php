

<?php $__env->startSection('title', $item ? 'تعديل عنصر المنهج' : 'إضافة عنصر منهج'); ?>
<?php $__env->startSection('header', $item ? 'تعديل عنصر المنهج' : 'إضافة عنصر منهج'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-4xl">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
        <form action="<?php echo e($item ? route('admin.curriculum-library.items.update', $item) : route('admin.curriculum-library.items.store')); ?>" method="POST" class="space-y-4">
            <?php echo csrf_field(); ?>
            <?php if($item): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">العنوان</label>
                    <input type="text" name="title" value="<?php echo e(old('title', $item?->title)); ?>" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                    <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">التصنيف</label>
                    <select name="category_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                        <option value="">— بدون تصنيف —</option>
                        <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($cat->id); ?>" <?php echo e(old('category_id', $item?->category_id) == $cat->id ? 'selected' : ''); ?>><?php echo e($cat->name); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">الرابط (slug) — اختياري</label>
                    <input type="text" name="slug" value="<?php echo e(old('slug', $item?->slug)); ?>" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-rose-600 text-xs mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">المادة / التخصص</label>
                    <input type="text" name="subject" value="<?php echo e(old('subject', $item?->subject)); ?>" placeholder="مثال: رياضيات، لغة عربية"
                           class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">المرحلة الدراسية</label>
                    <input type="text" name="grade_level" value="<?php echo e(old('grade_level', $item?->grade_level)); ?>" placeholder="مثال: ابتدائي، أول ثانوي"
                           class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" <?php echo e(old('is_active', $item?->is_active ?? true) ? 'checked' : ''); ?> class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm font-semibold text-slate-700">نشط (يظهر للمعلمين)</label>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">ترتيب العرض</label>
                    <input type="number" name="order" value="<?php echo e(old('order', $item?->order ?? 0)); ?>" min="0" class="w-24 px-3 py-2 rounded-lg border border-slate-200">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">وصف مختصر</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500" placeholder="ملخص يظهر في قائمة المكتبة"><?php echo e(old('description', $item?->description)); ?></textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">المحتوى (تفصيلي — يدعم HTML)</label>
                <textarea name="content" rows="14" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="المحتوى التفاعلي أو التعليمي للدرس/الوحدة..."><?php echo e(old('content', $item?->content)); ?></textarea>
                <p class="text-xs text-slate-500 mt-1">يمكنك استخدام HTML لعناوين، قوائم، روابط، أو تضمين أهداف الدرس وأنشطة مقترحة.</p>
            </div>

            <div class="flex gap-2 pt-4">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700"><?php echo e($item ? 'حفظ التعديلات' : 'إضافة العنصر'); ?></button>
                <a href="<?php echo e(route('admin.curriculum-library.index')); ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">إلغاء</a>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/admin/curriculum-library/items-form.blade.php ENDPATH**/ ?>
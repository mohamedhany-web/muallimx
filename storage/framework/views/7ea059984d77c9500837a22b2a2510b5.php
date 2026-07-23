

<?php $__env->startSection('title', $category ? 'تعديل تصنيف' : 'تصنيف جديد'); ?>
<?php $__env->startSection('header', $category ? 'تعديل تصنيف فيديو' : 'إنشاء تصنيف / قناة'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-2xl">
    <form method="POST" action="<?php echo e($category ? route('admin.video-library.categories.update', $category) : route('admin.video-library.categories.store')); ?>"
          class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
        <?php echo csrf_field(); ?>
        <?php if($category): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div>
            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">اسم التصنيف / القناة *</label>
            <input type="text" name="name" value="<?php echo e(old('name', $category->name ?? '')); ?>" required
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900">
            <?php $__errorArgs = ['name'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-sm text-rose-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">Slug (اختياري)</label>
            <input type="text" name="slug" value="<?php echo e(old('slug', $category->slug ?? '')); ?>" dir="ltr"
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 font-mono text-sm">
            <?php $__errorArgs = ['slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-sm text-rose-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">الوصف</label>
            <textarea name="description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900"><?php echo e(old('description', $category->description ?? '')); ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">لون الغلاف</label>
                <input type="color" name="cover_color" value="<?php echo e(old('cover_color', $category->cover_color ?? '#c62828')); ?>"
                       class="w-full h-11 rounded-xl border border-slate-200 cursor-pointer">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">أيقونة Font Awesome</label>
                <input type="text" name="icon" value="<?php echo e(old('icon', $category->icon ?? 'fa-play-circle')); ?>" placeholder="fa-graduation-cap"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 font-mono text-sm" dir="ltr">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">الترتيب</label>
                <input type="number" name="order" min="0" value="<?php echo e(old('order', $category->order ?? 0)); ?>"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900">
            </div>
        </div>

        <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-700 dark:text-slate-200">
            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-rose-600"
                   <?php if(old('is_active', $category->is_active ?? true)): echo 'checked'; endif; ?>>
            نشط ويظهر للمعلمين
        </label>

        <div class="flex gap-3 pt-2">
            <button class="px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold hover:bg-rose-700">حفظ</button>
            <a href="<?php echo e(route('admin.video-library.categories')); ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 font-semibold">إلغاء</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\admin\video-library\categories-form.blade.php ENDPATH**/ ?>
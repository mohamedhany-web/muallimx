<?php $__env->startSection('title', 'إضافة للمعرض الشخصي'); ?>
<?php $__env->startSection('header', 'إضافة للمعرض الشخصي'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full">
    <section class="relative overflow-hidden rounded-3xl mx-4 sm:mx-6 lg:mx-8 mt-6 bg-white dark:bg-slate-800/95 border border-slate-200 dark:border-slate-700 shadow-sm">
        <div class="absolute inset-0 bg-gradient-to-br from-white via-brand-50/40 to-slate-50/60 dark:from-slate-900/20 dark:via-slate-900/10 dark:to-slate-900/20"></div>
        <div class="relative z-10 px-6 sm:px-8 lg:px-10 pt-10 pb-10">
            <div class="flex items-center justify-between gap-4 flex-wrap">
                <div class="min-w-0">
                    <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full bg-brand-50 border border-brand-100 text-brand-700 text-sm font-black">
                        <i class="fas fa-layer-group text-brand-600"></i>
                        المعرض الشخصي
                    </div>
                    <h1 class="font-heading text-2xl sm:text-3xl lg:text-4xl font-black text-slate-900 dark:text-slate-100 leading-tight mt-4">
                        إضافة عنصر جديد
                    </h1>
                    <p class="text-slate-600 dark:text-slate-300 text-sm sm:text-base mt-3 max-w-2xl">
                        ارفع صور، أو أضف فيديو، أو اكتب نص، أو ضع رابط خارجي—كل شيء لعرض أعمالك بشكل احترافي.
                    </p>
                </div>
                <a href="<?php echo e(route('student.portfolio.index')); ?>" class="inline-flex items-center gap-2 px-4 py-2 rounded-2xl bg-white dark:bg-slate-900/20 border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 text-sm font-black hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                    رجوع
                </a>
            </div>
        </div>
    </section>

    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 pb-10">
        <?php if($errors->any()): ?>
            <div class="rounded-2xl bg-red-50 dark:bg-red-900/25 border border-red-200 dark:border-red-800/60 px-6 py-4 mb-6">
                <ul class="list-disc list-inside text-red-800 dark:text-red-200 text-sm font-semibold">
                    <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <li><?php echo e($e); ?></li>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?php echo e(route('student.portfolio.store')); ?>" method="POST" enctype="multipart/form-data"
              class="bg-white dark:bg-slate-800/95 rounded-3xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <?php echo csrf_field(); ?>

            <div x-data="{ type: <?php echo e(json_encode(old('content_type', 'gallery'))); ?> }">
                <div class="p-6 sm:p-8 border-b border-slate-200 dark:border-slate-700 bg-slate-50/70 dark:bg-slate-800/60">
                    <h2 class="font-heading text-lg sm:text-xl font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                        <i class="fas fa-plus-circle text-brand-600"></i>
                        بيانات العنصر
                    </h2>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mt-1">اختر نوع المحتوى ثم املأ البيانات المناسبة.</p>
                </div>

                <div class="p-6 sm:p-8 space-y-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">نوع المحتوى <span class="text-red-500">*</span></label>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <?php $__currentLoopData = $contentTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <label class="relative block cursor-pointer select-none">
                                    <input type="radio" name="content_type" value="<?php echo e($k); ?>" x-model="type"
                                           class="peer absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    <span class="flex items-center justify-center gap-2 px-4 py-3 rounded-2xl border transition-all border-slate-200 dark:border-slate-700 hover:bg-slate-50 dark:hover:bg-slate-800/50 pointer-events-none peer-checked:border-brand-500 peer-checked:bg-white dark:peer-checked:bg-slate-900/30 peer-checked:ring-2 peer-checked:ring-brand-200/30 text-sm font-black text-slate-800 dark:text-slate-100">
                                        <?php if($k === 'gallery'): ?> <i class="fas fa-images text-brand-600"></i> <?php endif; ?>
                                        <?php if($k === 'video'): ?> <i class="fas fa-video text-brand-600"></i> <?php endif; ?>
                                        <?php if($k === 'text'): ?> <i class="fas fa-align-right text-brand-600"></i> <?php endif; ?>
                                        <?php if($k === 'link'): ?> <i class="fas fa-link text-brand-600"></i> <?php endif; ?>
                                        <?php echo e($label); ?>

                                    </span>
                                </label>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">العنوان <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="<?php echo e(old('title')); ?>" required
                               placeholder="مثال: عرض أعمال / فيديو تعريفي / مقال / روابط"
                               class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/30">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">وصف مختصر (اختياري)</label>
                        <textarea name="description" rows="3" placeholder="اكتب وصفاً مختصراً للمحتوى..."
                                  class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/30"><?php echo e(old('description')); ?></textarea>
                    </div>

                    <div x-show="type === 'text'" x-cloak>
                        <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">النص / المحتوى</label>
                        <textarea name="content_text" rows="10" placeholder="اكتب محتوى واضح ومنظم..."
                                  class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/30"><?php echo e(old('content_text')); ?></textarea>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div x-show="type === 'link'" x-cloak>
                            <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">رابط خارجي (اختياري)</label>
                            <input type="url" name="project_url" value="<?php echo e(old('project_url')); ?>"
                                   placeholder="https://example.com"
                                   class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/30">
                        </div>
                        <div x-show="type === 'video'" x-cloak>
                            <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">رابط الفيديو</label>
                            <input type="url" name="video_url" value="<?php echo e(old('video_url')); ?>"
                                   placeholder="YouTube / Vimeo"
                                   class="w-full rounded-2xl border border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-brand-500 focus:ring-2 focus:ring-brand-200/30">
                        </div>
                    </div>

                    <div class="rounded-3xl border border-slate-200 dark:border-slate-700 p-5 bg-slate-50/70 dark:bg-slate-900/20">
                        <div class="flex items-center justify-between gap-3 mb-3">
                            <p class="font-heading font-black text-slate-900 dark:text-slate-100 flex items-center gap-2">
                                <i class="fas fa-images text-brand-600"></i>
                                الصور
                            </p>
                            <span class="text-xs font-bold text-slate-500 dark:text-slate-400">حتى 5 صور</span>
                        </div>
                        <div x-show="type === 'gallery'" x-cloak class="border-2 border-dashed border-slate-300 dark:border-slate-700 rounded-2xl px-4 py-3 bg-white/70 dark:bg-slate-900/20">
                            <input type="file" name="images[]" accept="image/*" multiple data-max="5" id="portfolio-images"
                                   class="w-full text-sm text-slate-700 dark:text-slate-200 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:font-black file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200 dark:file:bg-slate-800 dark:file:text-slate-200 dark:hover:file:bg-slate-700">
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2" id="images-hint">يمكنك اختيار أكثر من صورة (حد أقصى 5)</p>
                        </div>
                        <div x-show="type !== 'gallery'" x-cloak class="text-sm text-slate-600 dark:text-slate-300">
                            هذا النوع لا يحتاج صوراً.
                        </div>
                    </div>
                </div>

                <div class="px-6 sm:px-8 py-5 border-t border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800/60 flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="<?php echo e(route('student.portfolio.index')); ?>" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 font-black hover:bg-slate-50 dark:hover:bg-slate-800/60 transition-colors">
                        <i class="fas fa-arrow-right"></i>
                        إلغاء
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-2xl bg-brand-600 hover:bg-brand-700 text-white font-black shadow-sm transition-colors">
                        <i class="fas fa-save"></i>
                        حفظ
                    </button>
                </div>
            </div>
        </form>
    </section>
</div>

<?php $__env->startPush('scripts'); ?>
<script>
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('portfolio-images');
    var hint = document.getElementById('images-hint');
    if (input && hint) {
        input.addEventListener('change', function() {
            var files = this.files;
            if (files.length > 5) {
                hint.textContent = 'تم تحديد ' + files.length + ' صور. سيتم أخذ أول 5 صور فقط.';
                var dt = new DataTransfer();
                for (var i = 0; i < 5; i++) dt.items.add(files[i]);
                this.files = dt.files;
            } else if (files.length > 0) {
                hint.textContent = 'تم اختيار ' + files.length + ' صورة (حد أقصى 5).';
            } else {
                hint.textContent = 'يمكنك اختيار أكثر من صورة (حد أقصى 5)';
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\student\portfolio\create.blade.php ENDPATH**/ ?>
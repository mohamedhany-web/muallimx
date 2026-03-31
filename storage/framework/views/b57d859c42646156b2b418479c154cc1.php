<?php $__env->startSection('title', 'تعديل عنصر - البورتفوليو'); ?>
<?php $__env->startSection('header', 'تعديل عنصر'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full max-w-7xl mx-auto">
    <?php if(session('error')): ?>
        <div class="rounded-2xl bg-red-50 border-2 border-red-200 px-6 py-4 mb-6">
            <p class="text-red-800 font-bold"><?php echo e(session('error')); ?></p>
        </div>
    <?php endif; ?>

    <?php if($errors->any()): ?>
        <div class="rounded-2xl bg-red-50 border-2 border-red-200 px-6 py-4 mb-6">
            <ul class="list-disc list-inside text-red-800 text-sm">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $e): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($e); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white dark:bg-slate-800/95 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-[#2CA9BD] to-[#65DBE4] px-6 py-4">
            <h2 class="text-lg font-black text-white flex items-center gap-2">
                <i class="fas fa-edit"></i>
                تعديل مشروع
            </h2>
            <p class="text-white/90 text-sm mt-1">يمكنك تعديل العنصر وإضافة صور (حتى 5 صور إجمالاً للنوع “صور”). إذا كان العنصر مرفوضاً سيتم إعادة إرساله للمراجعة بعد الحفظ.</p>
        </div>

        <form action="<?php echo e(route('student.portfolio.update', $project)); ?>" method="POST" enctype="multipart/form-data" class="p-6 md:p-8" x-data="{ type: <?php echo e(json_encode(old('content_type', $project->content_type ?? 'gallery'))); ?> }">
            <?php echo csrf_field(); ?>
            <?php echo method_field('PUT'); ?>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-900 mb-2">نوع المحتوى <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    <?php $__currentLoopData = $contentTypeLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <label class="relative block cursor-pointer select-none">
                            <input type="radio" name="content_type" value="<?php echo e($k); ?>" x-model="type"
                                   class="peer absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <span class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 transition-all border-gray-200 hover:border-[#2CA9BD]/50 hover:bg-gray-50 pointer-events-none peer-checked:border-[#2CA9BD] peer-checked:bg-[#2CA9BD]/10 peer-checked:ring-2 peer-checked:ring-[#2CA9BD]/30 text-sm font-bold text-gray-800">
                                <?php if($k === 'gallery'): ?> <i class="fas fa-images text-[#2CA9BD]"></i> <?php endif; ?>
                                <?php if($k === 'video'): ?> <i class="fas fa-video text-[#2CA9BD]"></i> <?php endif; ?>
                                <?php if($k === 'text'): ?> <i class="fas fa-align-right text-[#2CA9BD]"></i> <?php endif; ?>
                                <?php if($k === 'link'): ?> <i class="fas fa-link text-[#2CA9BD]"></i> <?php endif; ?>
                                <?php echo e($label); ?>

                            </span>
                        </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                <div class="lg:col-span-12">
                    <label class="block text-sm font-bold text-gray-900 mb-2">العنوان <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="<?php echo e(old('title', $project->title)); ?>" required
                           class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-900 mb-2">الوصف (اختياري)</label>
                <textarea name="description" rows="3"
                          class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20"><?php echo e(old('description', $project->description)); ?></textarea>
            </div>

            <div class="mb-6" x-show="type === 'text'" x-cloak>
                <label class="block text-sm font-bold text-gray-900 mb-2">النص / المحتوى</label>
                <textarea name="content_text" rows="8"
                          class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20"><?php echo e(old('content_text', $project->content_text)); ?></textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div x-show="type === 'link'" x-cloak>
                    <label class="block text-sm font-bold text-gray-900 mb-2">رابط خارجي (اختياري)</label>
                    <input type="url" name="project_url" value="<?php echo e(old('project_url', $project->project_url)); ?>"
                           class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                </div>
                <div x-show="type === 'video'" x-cloak>
                    <label class="block text-sm font-bold text-gray-900 mb-2">رابط الفيديو</label>
                    <input type="url" name="video_url" value="<?php echo e(old('video_url', $project->video_url)); ?>"
                           class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 p-4 mb-6" x-show="type === 'gallery'" x-cloak>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="font-black text-gray-900">الصور الحالية</p>
                    <span class="text-xs text-gray-500"><?php echo e($project->images->count()); ?>/5</span>
                </div>
                <?php if($project->images->count() === 0): ?>
                    <p class="text-sm text-gray-500">لا توجد صور.</p>
                <?php else: ?>
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        <?php $__currentLoopData = $project->images; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $img): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="rounded-2xl overflow-hidden border border-gray-200 bg-gray-50">
                                <a href="<?php echo e(asset($img->image_path)); ?>" target="_blank" class="block">
                                    <img src="<?php echo e(asset($img->image_path)); ?>" alt="image" class="w-full h-28 object-cover">
                                </a>
                                <div class="p-2">
                                    <form action="<?php echo e(route('student.portfolio.images.destroy', [$project, $img])); ?>" method="POST" onsubmit="return confirm('حذف الصورة؟');">
                                        <?php echo csrf_field(); ?>
                                        <?php echo method_field('DELETE'); ?>
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-red-200 text-xs font-bold text-red-700 hover:bg-red-50 transition-colors">
                                            <i class="fas fa-trash"></i>
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end pt-4 border-t border-gray-200">
                <div class="lg:col-span-6">
                    <label class="block text-sm font-bold text-gray-900 mb-2">إضافة صور جديدة <span class="text-gray-500 font-normal">(حتى 5 صور إجمالاً)</span></label>
                    <div class="border-2 border-dashed border-[#2CA9BD]/30 rounded-xl px-4 py-3 bg-gray-50/50 hover:bg-gray-50 transition-colors" x-show="type === 'gallery'" x-cloak>
                        <input type="file" name="images[]" accept="image/*" multiple data-max="5" id="portfolio-images"
                               class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-[#2CA9BD]/10 file:text-[#2CA9BD] hover:file:bg-[#2CA9BD]/20">
                        <p class="text-xs text-gray-500 mt-2" id="images-hint">سيتم إضافة الصور حتى يكتمل الحد الأقصى (5).</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 p-4 text-sm text-gray-600" x-show="type !== 'gallery'" x-cloak>
                        لا توجد صور لهذا النوع.
                    </div>
                </div>
                <div class="lg:col-span-6 flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="<?php echo e(route('student.portfolio.show', $project)); ?>" class="inline-flex items-center justify-center gap-2 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-50 transition-all order-2 sm:order-1">
                        <i class="fas fa-arrow-right"></i>
                        إلغاء
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-[#2CA9BD] to-[#65DBE4] text-white px-6 py-3 rounded-xl font-bold hover:shadow-lg transition-all order-1 sm:order-2">
                        <i class="fas fa-save"></i>
                        حفظ التعديلات
                    </button>
                </div>
            </div>
        </form>
    </div>
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
                hint.textContent = 'تم اختيار ' + files.length + ' صورة.';
            } else {
                hint.textContent = 'سيتم إضافة الصور حتى يكتمل الحد الأقصى (5).';
            }
        });
    }
});
</script>
<?php $__env->stopPush(); ?>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\student\portfolio\edit.blade.php ENDPATH**/ ?>
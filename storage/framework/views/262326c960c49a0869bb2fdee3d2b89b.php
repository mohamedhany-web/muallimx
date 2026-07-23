

<?php $__env->startSection('title', $video ? 'تعديل فيديو' : 'إضافة فيديو'); ?>
<?php $__env->startSection('header', $video ? 'تعديل فيديو يوتيوب' : 'إضافة فيديو يوتيوب'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-3xl space-y-4">
    <div class="rounded-xl border border-sky-200 bg-sky-50 text-sky-900 text-sm px-4 py-3 leading-7">
        الصق رابط يوتيوب (watch / youtu.be / Shorts). سيُشغَّل داخل المنصة عبر Embed — لن يُحوَّل المستخدم إلى يوتيوب.
    </div>

    <form method="POST" action="<?php echo e($video ? route('admin.video-library.videos.update', $video) : route('admin.video-library.videos.store')); ?>"
          class="bg-white dark:bg-slate-800 rounded-2xl border border-slate-200 dark:border-slate-700 p-6 space-y-5"
          x-data="{
            url: <?php echo \Illuminate\Support\Js::from(old('youtube_url', $video->youtube_url ?? ''))->toHtml() ?>,
            thumb: <?php echo \Illuminate\Support\Js::from(old('thumbnail_url', $video?->displayThumbnail() ?? ''))->toHtml() ?>,
            extractId(u) {
                if (!u) return null;
                u = String(u).trim();
                if (/^[a-zA-Z0-9_-]{11}$/.test(u)) return u;
                let m = u.match(/(?:youtube\.com\/(?:[^\/]+\/.+\/|(?:v|e(?:mbed)?)\/|.*[?&]v=)|youtu\.be\/|youtube\.com\/shorts\/|youtube\.com\/live\/)([a-zA-Z0-9_-]{11})/);
                return m ? m[1] : null;
            },
            preview() {
                const id = this.extractId(this.url);
                this.thumb = id ? ('https://img.youtube.com/vi/' + id + '/hqdefault.jpg') : '';
            }
          }"
          x-init="preview()"
          @input="preview()">
        <?php echo csrf_field(); ?>
        <?php if($video): ?> <?php echo method_field('PUT'); ?> <?php endif; ?>

        <div>
            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">رابط يوتيوب *</label>
            <input type="text" name="youtube_url" x-model="url" required dir="ltr" placeholder="https://www.youtube.com/watch?v=..."
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 font-mono text-sm">
            <?php $__errorArgs = ['youtube_url'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-sm text-rose-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            <template x-if="thumb">
                <img :src="thumb" alt="معاينة" class="mt-3 w-full max-w-md aspect-video object-cover rounded-xl border border-slate-200 bg-slate-100">
            </template>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">عنوان الفيديو *</label>
            <input type="text" name="title" value="<?php echo e(old('title', $video->title ?? '')); ?>" required
                   class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900">
            <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> <p class="text-sm text-rose-600 mt-1"><?php echo e($message); ?></p> <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div>
            <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">الشرح / الوصف</label>
            <textarea name="description" rows="5" placeholder="يظهر تحت الفيديو للمعلم مثل وصف قنوات يوتيوب"
                      class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900"><?php echo e(old('description', $video->description ?? '')); ?></textarea>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">التصنيف / القناة</label>
                <select name="category_id" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900">
                    <option value="">بدون تصنيف</option>
                    <?php $__currentLoopData = $categories; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($cat->id); ?>" <?php if(old('category_id', $video->category_id ?? '') == $cat->id): echo 'selected'; endif; ?>><?php echo e($cat->name); ?></option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">Slug (اختياري)</label>
                <input type="text" name="slug" value="<?php echo e(old('slug', $video->slug ?? '')); ?>" dir="ltr"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 font-mono text-sm">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">المدة بالثواني (اختياري)</label>
                <input type="number" name="duration_seconds" min="0" value="<?php echo e(old('duration_seconds', $video->duration_seconds ?? '')); ?>"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900">
            </div>
            <div>
                <label class="block text-sm font-bold text-slate-800 dark:text-slate-200 mb-1">الترتيب</label>
                <input type="number" name="order" min="0" value="<?php echo e(old('order', $video->order ?? 0)); ?>"
                       class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900">
            </div>
        </div>

        <div class="flex flex-wrap gap-5">
            <label class="inline-flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="is_active" value="1" class="rounded text-rose-600" <?php if(old('is_active', $video->is_active ?? true)): echo 'checked'; endif; ?>>
                نشط
            </label>
            <label class="inline-flex items-center gap-2 text-sm font-semibold">
                <input type="checkbox" name="is_featured" value="1" class="rounded text-amber-600" <?php if(old('is_featured', $video->is_featured ?? false)): echo 'checked'; endif; ?>>
                مميز (يظهر في أعلى المكتبة)
            </label>
        </div>

        <div class="flex gap-3 pt-2">
            <button class="px-5 py-2.5 rounded-xl bg-rose-600 text-white font-bold hover:bg-rose-700">حفظ الفيديو</button>
            <a href="<?php echo e(route('admin.video-library.index')); ?>" class="px-5 py-2.5 rounded-xl border border-slate-200 font-semibold">إلغاء</a>
        </div>
    </form>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\admin\video-library\videos-form.blade.php ENDPATH**/ ?>
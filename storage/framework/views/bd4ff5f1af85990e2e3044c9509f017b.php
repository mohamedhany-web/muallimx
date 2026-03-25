<?php
    $depth = (int) ($depth ?? 0);
    $borderStart = match (min($depth, 3)) {
        0 => 'border-s-indigo-500',
        1 => 'border-s-violet-500',
        2 => 'border-s-cyan-500',
        default => 'border-s-slate-400 dark:border-s-slate-500',
    };
    $labelDepth = ['قسم رئيسي', 'مستوى فرعي', 'فرع ثانوي', 'فرع أعمق'][$depth] ?? 'فرع';
    $fileInputId = 'cl-file-' . $section->id;
?>
<div class="rounded-2xl border border-slate-200/90 dark:border-slate-600 bg-white dark:bg-slate-800/60 shadow-md hover:shadow-lg dark:hover:shadow-indigo-950/20 <?php echo e($borderStart); ?> border-s-4 overflow-hidden transition-all">
    
    <div class="px-4 sm:px-5 py-4 bg-gradient-to-l from-slate-50/95 to-white dark:from-slate-900/80 dark:to-slate-800/80 border-b border-slate-100 dark:border-slate-700">
        <div class="flex flex-col lg:flex-row lg:items-start gap-4 lg:justify-between">
            <div class="flex items-start gap-3 min-w-0 flex-1">
                <span class="w-11 h-11 shrink-0 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center shadow-md text-sm font-black">
                    <?php echo e($depth + 1); ?>

                </span>
                <div class="min-w-0 flex-1">
                    <p class="text-[10px] font-bold uppercase tracking-wider text-slate-400 dark:text-slate-500 mb-1"><?php echo e($labelDepth); ?></p>
                    <form action="<?php echo e(route('admin.curriculum-library.items.sections.update', [$item, $section])); ?>" method="POST" class="space-y-3">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="flex flex-col sm:flex-row flex-wrap gap-2 sm:items-center">
                            <input type="text" name="title" value="<?php echo e(old('title', $section->title)); ?>" required
                                   class="flex-1 min-w-[200px] px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm font-bold text-slate-900 dark:text-white focus:ring-2 focus:ring-indigo-500">
                            <div class="flex items-center gap-2">
                                <input type="number" name="order" value="<?php echo e($section->order); ?>" min="0" title="ترتيب"
                                       class="w-20 px-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-center">
                                <label class="inline-flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 dark:bg-slate-700/80 text-xs font-bold text-slate-700 dark:text-slate-200">
                                    <input type="hidden" name="is_active" value="0">
                                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-indigo-600" <?php echo e($section->is_active ? 'checked' : ''); ?>> نشط
                                </label>
                                <button type="submit" class="px-4 py-2.5 rounded-xl bg-slate-800 dark:bg-slate-600 text-white text-xs font-black hover:bg-slate-900 dark:hover:bg-slate-500 transition-colors">
                                    حفظ
                                </button>
                            </div>
                        </div>
                        <input type="text" name="description" value="<?php echo e(old('description', $section->description)); ?>"
                               class="w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-slate-700 dark:text-slate-300"
                               placeholder="وصف اختياري للقسم">
                        <div class="flex flex-wrap items-center gap-2 text-sm">
                            <span class="text-slate-500 dark:text-slate-400 font-semibold shrink-0">موضوع الأب:</span>
                            <select name="parent_id" class="flex-1 min-w-[12rem] max-w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-slate-800 dark:text-slate-200 text-sm">
                                <option value="">— جذر المنهج (بدون أب) —</option>
                                <?php $__currentLoopData = $flatSections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $opt): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <?php if($opt->id !== $section->id): ?>
                                        <option value="<?php echo e($opt->id); ?>" <?php echo e((int) $section->parent_id === (int) $opt->id ? 'selected' : ''); ?>>
                                            <?php echo e($opt->title); ?> · #<?php echo e($opt->id); ?>

                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </select>
                        </div>
                    </form>
                </div>
            </div>
            <form action="<?php echo e(route('admin.curriculum-library.items.sections.destroy', [$item, $section])); ?>" method="POST" class="shrink-0"
                  onsubmit="return confirm('حذف القسم وكل الفروع والمواد والملفات على R2؟');">
                <?php echo csrf_field(); ?>
                <?php echo method_field('DELETE'); ?>
                <button type="submit" class="w-full lg:w-auto inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-rose-200 dark:border-rose-900/50 bg-rose-50 dark:bg-rose-950/40 text-rose-700 dark:text-rose-300 text-sm font-bold hover:bg-rose-100 dark:hover:bg-rose-950/70 transition-colors">
                    <i class="fas fa-trash-alt text-xs"></i> حذف القسم
                </button>
            </form>
        </div>
    </div>

    
    <div class="px-4 sm:px-5 py-4 bg-slate-50/70 dark:bg-slate-900/30 border-b border-slate-100 dark:border-slate-700">
        <p class="text-xs font-black text-slate-600 dark:text-slate-400 mb-3 flex items-center gap-2">
            <i class="fas fa-code-branch text-indigo-500 text-[11px]"></i> قسم فرعي تحت «<?php echo e(Str::limit($section->title, 40)); ?>»
        </p>
        <form action="<?php echo e(route('admin.curriculum-library.items.sections.store', $item)); ?>" method="POST" class="flex flex-col sm:flex-row flex-wrap gap-3 items-stretch sm:items-end">
            <?php echo csrf_field(); ?>
            <input type="hidden" name="parent_id" value="<?php echo e($section->id); ?>">
            <input type="text" name="title" required
                   class="flex-1 min-w-[200px] px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm"
                   placeholder="عنوان الفرع">
            <input type="number" name="order" value="0" min="0" class="w-full sm:w-24 px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm">
            <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-100 dark:bg-indigo-900/50 text-indigo-800 dark:text-indigo-200 text-sm font-black border border-indigo-200/80 dark:border-indigo-800 hover:bg-indigo-200/60 dark:hover:bg-indigo-900/70 transition-colors">
                <i class="fas fa-plus ml-1"></i> إضافة فرع
            </button>
        </form>
    </div>

    
    <div class="px-4 sm:px-5 py-5 bg-gradient-to-l from-indigo-50/80 via-white to-violet-50/50 dark:from-indigo-950/20 dark:via-slate-800/40 dark:to-violet-950/20 border-b border-slate-100 dark:border-slate-700">
        <p class="text-sm font-black text-slate-800 dark:text-white mb-3 flex items-center gap-2">
            <i class="fas fa-cloud-upload-alt text-indigo-600 dark:text-indigo-400"></i>
            رفع مادة إلى Cloudflare R2
        </p>
        <form action="<?php echo e(route('admin.curriculum-library.items.materials.store', [$item, $section])); ?>" method="POST" enctype="multipart/form-data" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4">
                <div class="lg:col-span-5">
                    <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1.5">الملف</label>
                    <label for="<?php echo e($fileInputId); ?>" class="flex flex-col items-center justify-center w-full min-h-[7rem] px-4 py-6 rounded-xl border-2 border-dashed border-indigo-200 dark:border-indigo-800 bg-white/70 dark:bg-slate-900/50 cursor-pointer hover:border-indigo-400 dark:hover:border-indigo-600 transition-colors">
                        <i class="fas fa-file-import text-2xl text-indigo-400 mb-2"></i>
                        <span class="text-xs font-semibold text-slate-600 dark:text-slate-400 text-center">اضغط لاختيار ملف</span>
                        <span class="text-[10px] text-slate-400 mt-1">PPTX · PDF · HTML · أخرى</span>
                    </label>
                    <input id="<?php echo e($fileInputId); ?>" type="file" name="file" required class="sr-only">
                </div>
                <div class="lg:col-span-7 space-y-4">
                    <div>
                        <label class="block text-[11px] font-bold text-slate-600 dark:text-slate-400 mb-1.5">عنوان المادة (اختياري)</label>
                        <input type="text" name="title" class="w-full px-4 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm" placeholder="يظهر للمعلم في المنصة">
                    </div>
                    <div class="flex flex-wrap gap-6">
                        <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="view_in_platform" value="0">
                            <input type="checkbox" name="view_in_platform" value="1" class="rounded border-slate-300 text-indigo-600 w-4 h-4" checked>
                            عرض داخل المنصة
                        </label>
                        <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-300 cursor-pointer">
                            <input type="hidden" name="allow_download" value="0">
                            <input type="checkbox" name="allow_download" value="1" class="rounded border-slate-300 text-indigo-600 w-4 h-4">
                            السماح بالتحميل
                        </label>
                    </div>
                    <p class="text-[11px] text-slate-500 dark:text-slate-400 leading-relaxed">يُستنتج نوع الملف من الامتداد. HTML وعروض PowerPoint لا تُحمَّل مهما علّمت «تحميل».</p>
                    <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-black shadow-lg shadow-indigo-500/25 transition-colors">
                        <i class="fas fa-upload text-xs"></i> رفع إلى R2
                    </button>
                </div>
            </div>
        </form>
    </div>

    <?php if($section->materials->isNotEmpty()): ?>
        <div class="px-4 sm:px-5 py-3 bg-slate-100/60 dark:bg-slate-900/50 border-b border-slate-100 dark:border-slate-700">
            <p class="text-xs font-black text-slate-600 dark:text-slate-400 uppercase tracking-wide">المواد (<?php echo e($section->materials->count()); ?>)</p>
        </div>
        <div class="divide-y divide-slate-100 dark:divide-slate-700">
            <?php $__currentLoopData = $section->materials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $mat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <div class="px-4 sm:px-5 py-4 hover:bg-slate-50/80 dark:hover:bg-slate-900/40 transition-colors">
                    <form action="<?php echo e(route('admin.curriculum-library.items.materials.update', [$item, $mat])); ?>" method="POST">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('PUT'); ?>
                        <div class="flex flex-col xl:flex-row gap-4 xl:items-center">
                            <div class="flex items-center gap-3 shrink-0">
                                <span class="font-mono text-[11px] text-slate-400 w-10">#<?php echo e($mat->id); ?></span>
                                <?php
                                    $kindStyle = match($mat->file_kind) {
                                        'pdf' => 'bg-rose-100 text-rose-800 dark:bg-rose-950/60 dark:text-rose-200',
                                        'html' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-950/60 dark:text-emerald-200',
                                        'pptx' => 'bg-amber-100 text-amber-900 dark:bg-amber-950/60 dark:text-amber-200',
                                        default => 'bg-slate-200 text-slate-800 dark:bg-slate-700 dark:text-slate-200',
                                    };
                                ?>
                                <span class="text-xs px-2.5 py-1 rounded-lg font-bold <?php echo e($kindStyle); ?>"><?php echo e(strtoupper($mat->file_kind)); ?></span>
                            </div>
                            <div class="flex-1 grid grid-cols-1 md:grid-cols-12 gap-3 items-center min-w-0">
                                <input type="text" name="title" value="<?php echo e($mat->title); ?>"
                                       class="md:col-span-5 w-full px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm" placeholder="عنوان">
                                <input type="number" name="order" value="<?php echo e($mat->order); ?>" min="0"
                                       class="md:col-span-1 w-full md:w-20 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-900 text-sm text-center" title="ترتيب">
                                <div class="md:col-span-6 flex flex-wrap items-center gap-4 text-xs font-bold">
                                    <label class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                        <input type="hidden" name="view_in_platform" value="0">
                                        <input type="checkbox" name="view_in_platform" value="1" class="rounded text-indigo-600" <?php echo e($mat->view_in_platform ? 'checked' : ''); ?>> عرض
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                        <input type="hidden" name="allow_download" value="0">
                                        <input type="checkbox" name="allow_download" value="1" class="rounded text-indigo-600" <?php echo e($mat->allow_download ? 'checked' : ''); ?>> تحميل
                                    </label>
                                    <label class="inline-flex items-center gap-1.5 text-slate-700 dark:text-slate-300">
                                        <input type="hidden" name="is_active" value="0">
                                        <input type="checkbox" name="is_active" value="1" class="rounded text-indigo-600" <?php echo e($mat->is_active ? 'checked' : ''); ?>> نشط
                                    </label>
                                    <button type="submit" class="px-4 py-2 rounded-lg bg-indigo-600 text-white text-xs font-black hover:bg-indigo-700">تحديث</button>
                                </div>
                            </div>
                        </div>
                    </form>
                    <div class="mt-2 flex justify-end">
                        <form action="<?php echo e(route('admin.curriculum-library.items.materials.destroy', [$item, $mat])); ?>" method="POST" onsubmit="return confirm('حذف الملف من R2 نهائياً؟');">
                            <?php echo csrf_field(); ?>
                            <?php echo method_field('DELETE'); ?>
                            <button type="submit" class="text-xs font-bold text-rose-600 dark:text-rose-400 hover:underline">حذف الملف</button>
                        </form>
                    </div>
                </div>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>

    <?php if($section->treeChildren->isNotEmpty()): ?>
        <div class="p-4 sm:p-5 bg-slate-50/90 dark:bg-slate-900/50 border-t border-slate-100 dark:border-slate-700 space-y-4
            <?php echo e($depth > 0 ? 'ms-2 sm:ms-4 md:ms-6 ps-3 sm:ps-5 md:ps-6 border-s-2 border-indigo-100 dark:border-indigo-900/60' : ''); ?>">
            <?php $__currentLoopData = $section->treeChildren; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $child): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php echo $__env->make('admin.curriculum-library._structure-section', ['section' => $child, 'item' => $item, 'depth' => $depth + 1], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
        </div>
    <?php endif; ?>
</div>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/admin/curriculum-library/_structure-section.blade.php ENDPATH**/ ?>
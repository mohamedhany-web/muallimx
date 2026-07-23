
<?php $__env->startSection('title', 'صفحات الهبوط'); ?>
<?php $__env->startSection('header', 'صفحات الهبوط (إعلانات ممولة)'); ?>
<?php $__env->startSection('content'); ?>
<div class="w-full space-y-6">
    <div class="rounded-3xl bg-white/95 backdrop-blur border border-slate-200 shadow-lg overflow-hidden">
        <div class="px-5 py-6 sm:px-8 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">صفحات الهبوط</h1>
                <p class="text-slate-500 mt-1">أنشئ صفحات مخصّصة للإعلانات الممولة. كل صفحة لها رابط مستقل مثل <code class="text-xs bg-slate-100 px-1 rounded">/lp/اسم-الصفحة</code> ويدعم فيديوهات يوتيوب.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?php echo e(route('admin.landing-pages.create', ['template' => 1])); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-sky-500 text-sky-700 rounded-xl font-semibold hover:bg-sky-50 transition-all">
                    <i class="fas fa-magic"></i>
                    <span>من قالب إعلان</span>
                </a>
                <a href="<?php echo e(route('admin.landing-pages.create')); ?>" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white rounded-xl font-semibold shadow-lg shadow-sky-500/30 transition-all">
                    <i class="fas fa-plus"></i>
                    <span>صفحة جديدة</span>
                </a>
            </div>
        </div>
        <div class="p-5 sm:p-8">
            <?php if(session('success')): ?>
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800"><?php echo e(session('success')); ?></div>
            <?php endif; ?>

            <form method="GET" action="<?php echo e(route('admin.landing-pages.index')); ?>" class="flex flex-wrap gap-3 mb-6">
                <input type="search" name="search" value="<?php echo e(request('search')); ?>" placeholder="بحث بالعنوان أو الرابط أو الحملة..."
                       class="flex-1 min-w-[200px] px-4 py-2 border border-slate-200 rounded-xl text-sm">
                <select name="status" class="px-4 py-2 border border-slate-200 rounded-xl text-sm">
                    <option value="">كل الحالات</option>
                    <option value="active" <?php if(request('status')==='active'): echo 'selected'; endif; ?>>نشط</option>
                    <option value="inactive" <?php if(request('status')==='inactive'): echo 'selected'; endif; ?>>معطل</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold">تصفية</button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-right text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-xs font-semibold uppercase text-slate-500">
                            <th class="px-4 py-3">العنوان</th>
                            <th class="px-4 py-3">الرابط</th>
                            <th class="px-4 py-3">الحملة</th>
                            <th class="px-4 py-3">الحالة</th>
                            <th class="px-4 py-3">تحديث</th>
                            <th class="px-4 py-3">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        <?php $__empty_1 = true; $__currentLoopData = $pages; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 font-semibold text-slate-900"><?php echo e($page->title); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <code class="text-xs bg-slate-100 px-2 py-1 rounded" dir="ltr">/lp/<?php echo e($page->slug); ?></code>
                                        <button type="button"
                                                class="text-xs font-bold text-sky-600 hover:text-sky-800"
                                                data-copy-url="<?php echo e($page->publicUrl()); ?>"
                                                onclick="navigator.clipboard.writeText(this.dataset.copyUrl).then(()=>{this.textContent='تم النسخ'; setTimeout(()=>this.textContent='نسخ اللينك',1500)})">
                                            نسخ اللينك
                                        </button>
                                        <a href="<?php echo e($page->publicUrl()); ?>" target="_blank" class="text-xs text-slate-500 hover:text-sky-600">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    <?php if($page->utm_campaign || $page->utm_source): ?>
                                        <span class="text-xs"><?php echo e($page->utm_source ?: '—'); ?> / <?php echo e($page->utm_campaign ?: '—'); ?></span>
                                    <?php else: ?>
                                        <span class="text-slate-400">—</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3">
                                    <?php if($page->isPublishedNow()): ?>
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">منشورة</span>
                                    <?php elseif($page->is_active): ?>
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">مجدولة / خارج الفترة</span>
                                    <?php else: ?>
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">معطّلة</span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap"><?php echo e($page->updated_at?->format('Y-m-d H:i')); ?></td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <a href="<?php echo e(route('admin.landing-pages.edit', $page)); ?>" class="px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 text-xs font-bold hover:bg-sky-100">تعديل</a>
                                        <form method="POST" action="<?php echo e(route('admin.landing-pages.destroy', $page)); ?>" onsubmit="return confirm('حذف صفحة الهبوط؟');">
                                            <?php echo csrf_field(); ?>
                                            <?php echo method_field('DELETE'); ?>
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">لا توجد صفحات هبوط بعد. ابدأ بإنشاء صفحة من قالب الإعلان.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <div class="mt-6"><?php echo e($pages->links()); ?></div>
        </div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\admin\landing-pages\index.blade.php ENDPATH**/ ?>
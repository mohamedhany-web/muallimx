<?php $__env->startSection('title', 'إضافة كوبون جديد'); ?>
<?php $__env->startSection('header', ''); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full space-y-6">
    <div class="rounded-3xl bg-white/95 backdrop-blur border border-slate-200 shadow-lg overflow-hidden">
        <div class="px-5 py-6 sm:px-8 border-b border-slate-200">
            <div class="flex flex-wrap items-center gap-3">
                <a href="<?php echo e(route('admin.coupons.index')); ?>" class="inline-flex items-center justify-center w-9 h-9 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 transition-colors">
                    <i class="fas fa-arrow-right"></i>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-slate-900 font-heading flex items-center gap-2">
                        <i class="fas fa-ticket-alt text-violet-600"></i>
                        إضافة كوبون جديد
                    </h1>
                    <p class="text-sm text-slate-500 mt-1">أنشئ كوبون خصم واضح النطاق: للكورسات أو للباقات.</p>
                </div>
            </div>
        </div>
    </div>

    <form action="<?php echo e(route('admin.coupons.store')); ?>" method="POST" class="rounded-3xl bg-white/95 backdrop-blur border border-slate-200 shadow-lg p-5 sm:p-8 space-y-8">
        <?php echo csrf_field(); ?>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الكود <span class="text-red-500">*</span></label>
                <input type="text" name="code" required value="<?php echo e(old('code')); ?>" class="w-full rounded-xl border-slate-300 uppercase font-mono" placeholder="WELCOME10">
                <p class="text-xs text-slate-500 mt-1">يُحفظ تلقائياً بأحرف كبيرة</p>
                <?php $__errorArgs = ['code'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">العنوان <span class="text-red-500">*</span></label>
                <input type="text" name="title" required value="<?php echo e(old('title')); ?>" class="w-full rounded-xl border-slate-300">
                <?php $__errorArgs = ['title'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">نوع الخصم <span class="text-red-500">*</span></label>
                <select name="discount_type" required class="w-full rounded-xl border-slate-300">
                    <option value="percentage" <?php echo e(old('discount_type', 'percentage') === 'percentage' ? 'selected' : ''); ?>>نسبة مئوية</option>
                    <option value="fixed" <?php echo e(old('discount_type') === 'fixed' ? 'selected' : ''); ?>>مبلغ ثابت</option>
                </select>
                <?php $__errorArgs = ['discount_type'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">قيمة الخصم <span class="text-red-500">*</span></label>
                <input type="number" name="discount_value" step="0.01" min="0" required value="<?php echo e(old('discount_value')); ?>" class="w-full rounded-xl border-slate-300">
                <?php $__errorArgs = ['discount_value'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الحد الأدنى للطلب (ج.م)</label>
                <input type="number" name="minimum_amount" step="0.01" min="0" value="<?php echo e(old('minimum_amount')); ?>" class="w-full rounded-xl border-slate-300">
                <?php $__errorArgs = ['minimum_amount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الحد الأقصى للخصم (ج.م)</label>
                <input type="number" name="maximum_discount" step="0.01" min="0" value="<?php echo e(old('maximum_discount')); ?>" class="w-full rounded-xl border-slate-300">
                <p class="text-xs text-slate-500 mt-1">مفيد عند الخصم النسبي</p>
                <?php $__errorArgs = ['maximum_discount'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الحد الأقصى لعدد الاستخدامات</label>
                <input type="number" name="max_uses" min="1" value="<?php echo e(old('max_uses')); ?>" class="w-full rounded-xl border-slate-300" placeholder="اتركه فارغاً لغير محدود">
                <?php $__errorArgs = ['max_uses'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الحد لكل مستخدم</label>
                <input type="number" name="usage_limit_per_user" min="1" value="<?php echo e(old('usage_limit_per_user', 1)); ?>" class="w-full rounded-xl border-slate-300">
                <?php $__errorArgs = ['usage_limit_per_user'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">تاريخ البداية</label>
                <input type="date" name="valid_from" value="<?php echo e(old('valid_from')); ?>" class="w-full rounded-xl border-slate-300">
                <?php $__errorArgs = ['valid_from'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">تاريخ الانتهاء</label>
                <input type="date" name="valid_until" value="<?php echo e(old('valid_until')); ?>" class="w-full rounded-xl border-slate-300">
                <?php $__errorArgs = ['valid_until'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
        </div>

        <div>
            <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الوصف</label>
            <textarea name="description" rows="3" class="w-full rounded-xl border-slate-300"><?php echo e(old('description')); ?></textarea>
            <?php $__errorArgs = ['description'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
        </div>

        <div class="rounded-2xl border border-slate-200 p-5 space-y-5 bg-slate-50/70">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2">
                <i class="fas fa-bullseye text-violet-500"></i>
                نطاق الكوبون
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <label class="cursor-pointer">
                    <input type="radio" name="applicable_to" value="subscriptions" class="sr-only coupon-scope" <?php echo e(old('applicable_to') === 'subscriptions' ? 'checked' : ''); ?>>
                    <div class="rounded-2xl border border-slate-300 bg-white p-4 coupon-scope-card">
                        <p class="font-bold text-slate-900 mb-1">كوبون للباقات</p>
                        <p class="text-xs text-slate-500">يُطبَّق في صفحة دفع الاشتراك (Starter / Pro).</p>
                    </div>
                </label>
                <label class="cursor-pointer">
                    <input type="radio" name="applicable_to" value="courses" class="sr-only coupon-scope" <?php echo e(old('applicable_to', 'courses') === 'courses' ? 'checked' : ''); ?>>
                    <div class="rounded-2xl border border-slate-300 bg-white p-4 coupon-scope-card">
                        <p class="font-bold text-slate-900 mb-1">كوبون للكورسات</p>
                        <p class="text-xs text-slate-500">يُطبَّق في شراء كورسات محددة.</p>
                    </div>
                </label>
            </div>
            <?php $__errorArgs = ['applicable_to'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>

            <div>
                <div id="courseScopeWrap">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-2">الكورسات (عند اختيار «محددة»)</label>
                <div class="max-h-56 overflow-y-auto rounded-xl border border-slate-200 p-3 space-y-2 bg-white">
                    <?php $__empty_1 = true; $__currentLoopData = $courses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <label class="flex items-center gap-2 text-sm cursor-pointer">
                        <input type="checkbox" name="applicable_course_ids[]" value="<?php echo e($c->id); ?>" <?php echo e(in_array($c->id, old('applicable_course_ids', []), true) ? 'checked' : ''); ?> class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                        <span class="text-slate-800"><?php echo e($c->title); ?></span>
                    </label>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-sm text-slate-500">لا توجد كورسات في النظام.</p>
                    <?php endif; ?>
                </div>
                <?php $__errorArgs = ['applicable_course_ids'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
        </div>

        <div class="rounded-2xl border border-amber-200 p-5 space-y-4 bg-amber-50/50">
            <h2 class="font-bold text-slate-800 text-sm flex items-center gap-2"><i class="fas fa-user-tag text-amber-600"></i> كوبون تسويقي شخصي + عمولة</h2>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">معرّفات المستخدمين المسموح لهم (اختياري)</label>
                <textarea name="applicable_user_ids_text" rows="2" class="w-full rounded-xl border-slate-300 font-mono text-sm" placeholder="مثال: 12, 45 أو سطر لكل رقم"><?php echo e(old('applicable_user_ids_text')); ?></textarea>
                <p class="text-xs text-slate-500 mt-1">إن تركتها فارغة يمكن لأي مستخدم يملك الكود استخدامه (مع بقية الشروط). للتسويق المستهدف: أدخل معرّف الطالب وأزل «ظاهر للجميع».</p>
                <?php $__errorArgs = ['applicable_user_ids_text'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">مستفيد العمولة (معرّف مستخدم)</label>
                    <input type="number" name="beneficiary_user_id" min="1" value="<?php echo e(old('beneficiary_user_id')); ?>" class="w-full rounded-xl border-slate-300 font-mono" placeholder="فارغ = بدون عمولة">
                    <?php $__errorArgs = ['beneficiary_user_id'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">نسبة العمولة %</label>
                    <input type="number" name="commission_percent" step="0.01" min="0" max="100" value="<?php echo e(old('commission_percent')); ?>" class="w-full rounded-xl border-slate-300">
                    <?php $__errorArgs = ['commission_percent'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">احتساب العمولة من</label>
                    <select name="commission_on" class="w-full rounded-xl border-slate-300">
                        <option value="final_paid" <?php echo e(old('commission_on', 'final_paid') === 'final_paid' ? 'selected' : ''); ?>>المبلغ النهائي بعد الخصم</option>
                        <option value="original_price" <?php echo e(old('commission_on') === 'original_price' ? 'selected' : ''); ?>>السعر الأصلي قبل الخصم</option>
                    </select>
                    <?php $__errorArgs = ['commission_on'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-red-500 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                </div>
            </div>
            <p class="text-xs text-slate-600 dark:text-slate-400">تُسجَّل العمولة عند اعتماد الطلب من الإدارة، ثم من «عمولات كوبونات التسويق» يمكن إنشاء مصروف تسويقي؛ عند اعتماد المصروف تُحدَّث الحالة إلى مسوّى.</p>
        </div>

        <div class="flex flex-wrap gap-6">
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_active" value="1" <?php echo e(old('is_active', true) ? 'checked' : ''); ?> class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                <span class="text-sm font-medium text-slate-700">كوبون نشط</span>
            </label>
            <label class="inline-flex items-center gap-2 cursor-pointer">
                <input type="checkbox" name="is_public" value="1" <?php echo e(old('is_public', true) ? 'checked' : ''); ?> class="rounded border-slate-300 text-violet-600 focus:ring-violet-500">
                <span class="text-sm font-medium text-slate-700">ظاهر للجميع (يمكن إدخال كوده من صفحة الدفع)</span>
            </label>
        </div>

        <div class="flex flex-wrap gap-3 pt-2 border-t border-slate-200">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-semibold shadow-lg shadow-violet-500/25 transition-all">
                <i class="fas fa-save"></i> حفظ الكوبون
            </button>
            <a href="<?php echo e(route('admin.coupons.index')); ?>" class="inline-flex items-center gap-2 px-6 py-2.5 bg-slate-200 text-slate-700 rounded-xl font-medium hover:bg-slate-300 transition-colors">إلغاء</a>
        </div>
    </form>
</div>

<script>
    (function () {
        const radios = Array.from(document.querySelectorAll('.coupon-scope'));
        const wrap = document.getElementById('courseScopeWrap');

        function refresh() {
            const selected = radios.find(r => r.checked)?.value || 'courses';
            radios.forEach((r) => {
                const card = r.closest('label')?.querySelector('.coupon-scope-card');
                if (!card) return;
                if (r.checked) {
                    card.classList.add('border-violet-500', 'ring-2', 'ring-violet-200', 'bg-violet-50/60');
                    card.classList.remove('border-slate-300');
                } else {
                    card.classList.remove('border-violet-500', 'ring-2', 'ring-violet-200', 'bg-violet-50/60');
                    card.classList.add('border-slate-300');
                }
            });
            if (wrap) {
                const isCourses = selected === 'courses';
                wrap.classList.toggle('hidden', !isCourses);
                wrap.querySelectorAll('input[type="checkbox"]').forEach((c) => {
                    c.disabled = !isCourses;
                    if (!isCourses) c.checked = false;
                });
            }
        }

        radios.forEach(r => r.addEventListener('change', refresh));
        refresh();
    })();
</script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/admin/coupons/create.blade.php ENDPATH**/ ?>
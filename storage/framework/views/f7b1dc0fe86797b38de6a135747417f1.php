<?php $__env->startSection('title', 'إضافة اشتراك جديد'); ?>
<?php $__env->startSection('header', 'إضافة اشتراك جديد'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6" x-data="teacherSubscriptionForm()">
    <div class="bg-white rounded-xl shadow-lg p-6 border border-gray-200">
        <h1 class="text-2xl font-bold text-gray-900 mb-6">إضافة اشتراك جديد</h1>
        
        <form action="<?php echo e(route('admin.subscriptions.store')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-2">نمط اشتراك المعلم (اختياري)</label>
                    <select name="teacher_plan_key" x-model="selectedPlan" @change="applyPlan" class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">بدون — إدخال يدوي</option>
                        <option value="teacher_starter">باقة البداية — 200 ج.م شهريًا</option>
                        <option value="teacher_pro">باقة المعلم المحترف — 600 ج.م / 3 شهور</option>
                        <option value="teacher_premium">باقة المعلم المميز — 1500 ج.م سنويًا</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">
                        اختيار إحدى هذه الباقات يملأ الحقول تلقائيًا (النوع، اسم الخطة، السعر، دورة الفوترة) مع افتراض العملة بالجنيه المصري.
                    </p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">المستخدم *</label>
                    <select name="user_id" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="">اختر المستخدم</option>
                        <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $user): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($user->id); ?>"><?php echo e($user->name); ?> - <?php echo e($user->phone); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">نوع الاشتراك *</label>
                    <select name="subscription_type" x-model="form.subscription_type" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="monthly">شهري</option>
                        <option value="quarterly">ربع سنوي</option>
                        <option value="yearly">سنوي</option>
                        <option value="lifetime">مدى الحياة</option>
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">اسم الخطة *</label>
                    <input type="text" name="plan_name" x-model="form.plan_name" required value="<?php echo e(old('plan_name')); ?>" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">السعر *</label>
                    <div class="relative">
                        <input type="number" name="price" x-model.number="form.price" step="0.01" min="0" required value="<?php echo e(old('price')); ?>" 
                               class="w-full pl-12 pr-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <span class="absolute inset-y-0 left-4 flex items-center text-sm font-semibold text-gray-500">ج.م</span>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ البداية *</label>
                    <input type="date" name="start_date" required value="<?php echo e(old('start_date', date('Y-m-d'))); ?>" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">تاريخ الانتهاء *</label>
                    <input type="date" name="end_date" required value="<?php echo e(old('end_date', date('Y-m-d', strtotime('+1 month')))); ?>" 
                           class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">دورة الفوترة *</label>
                    <select name="billing_cycle" x-model="form.billing_cycle" required class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                        <option value="monthly">شهري</option>
                        <option value="quarterly">ربع سنوي</option>
                        <option value="yearly">سنوي</option>
                    </select>
                </div>
            </div>

            <div class="flex items-center">
                <input type="checkbox" name="auto_renew" value="1" <?php echo e(old('auto_renew', false) ? 'checked' : ''); ?> 
                       class="w-4 h-4 text-sky-600 border-gray-300 rounded focus:ring-sky-500">
                <label class="mr-2 text-sm font-medium text-gray-700">تجديد تلقائي</label>
            </div>

            <div class="bg-gray-50 border border-gray-200 rounded-lg p-4 space-y-3">
                <h2 class="text-sm font-semibold text-gray-800 mb-1">مزايا الخطة للمعلم</h2>
                <p class="text-xs text-gray-500 mb-2">
                    يمكنك تحديد ما يحصل عليه المعلم (الطالب) من مزايا ضمن هذا الاشتراك. هذه المزايا تستخدم لاحقًا لتفعيل الأدوات داخل المنصة.
                </p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[library_access]" value="1" data-sub-feature="library_access" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.library_access')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[ai_tools]" value="1" data-sub-feature="ai_tools" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.ai_tools')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[classroom_access]" value="1" data-sub-feature="classroom_access" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.classroom_access')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[support]" value="1" data-sub-feature="support" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.support')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[teacher_profile]" value="1" data-sub-feature="teacher_profile" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.teacher_profile')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[visible_to_academies]" value="1" data-sub-feature="visible_to_academies" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.visible_to_academies')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[can_apply_opportunities]" value="1" data-sub-feature="can_apply_opportunities" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.can_apply_opportunities')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[full_ai_suite]" value="1" data-sub-feature="full_ai_suite" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.full_ai_suite')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[teacher_evaluation]" value="1" data-sub-feature="teacher_evaluation" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.teacher_evaluation')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[recommended_to_academies]" value="1" data-sub-feature="recommended_to_academies" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.recommended_to_academies')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[priority_opportunities]" value="1" data-sub-feature="priority_opportunities" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.priority_opportunities')); ?></span>
                    </label>
                    <label class="inline-flex items-center">
                        <input type="checkbox" name="features[direct_support]" value="1" data-sub-feature="direct_support" class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500">
                        <span><?php echo e(__('student.subscription_feature.direct_support')); ?></span>
                    </label>
                </div>
                <p class="text-xs text-gray-400 mt-2">
                    تذكير: كل القيم المالية يتم التعامل معها بالجنيه المصري (ج.م).
                </p>
            </div>

            <div class="flex gap-4">
                <button type="submit" class="bg-gradient-to-r from-sky-600 to-sky-700 hover:from-sky-700 hover:to-sky-800 text-white px-6 py-3 rounded-lg font-medium transition-colors shadow-lg shadow-sky-500/30">
                    إنشاء الاشتراك
                </button>
                <a href="<?php echo e(route('admin.subscriptions.index')); ?>" class="bg-gray-200 hover:bg-gray-300 text-gray-800 px-6 py-3 rounded-lg font-medium transition-colors">
                    إلغاء
                </a>
            </div>
        </form>
    </div>
</div>
<script>
    function teacherSubscriptionForm() {
        var PLAN_FEATURES = {
            teacher_starter: ['library_access', 'ai_tools', 'classroom_access', 'support'],
            teacher_pro: ['library_access', 'ai_tools', 'classroom_access', 'support', 'teacher_profile', 'visible_to_academies', 'can_apply_opportunities', 'full_ai_suite'],
            teacher_premium: ['library_access', 'ai_tools', 'classroom_access', 'support', 'teacher_profile', 'visible_to_academies', 'can_apply_opportunities', 'full_ai_suite', 'teacher_evaluation', 'recommended_to_academies', 'priority_opportunities', 'direct_support'],
        };

        function syncSubscriptionFeatureCheckboxes(featureList) {
            var set = {};
            (featureList || []).forEach(function (f) { set[f] = true; });
            document.querySelectorAll('input[type=checkbox][data-sub-feature]').forEach(function (cb) {
                var fk = cb.getAttribute('data-sub-feature');
                cb.checked = !!set[fk];
            });
        }

        return {
            selectedPlan: '',
            form: {
                subscription_type: 'monthly',
                plan_name: <?php echo json_encode(old('plan_name', ''), 512) ?>,
                price: <?php echo json_encode(old('price', ''), 512) ?>,
                billing_cycle: 'monthly',
            },
            applyPlan(event) {
                var key = event.target.value;
                if (!key || !PLAN_FEATURES[key]) return;

                if (key === 'teacher_starter') {
                    this.form.subscription_type = 'monthly';
                    this.form.plan_name = 'باقة البداية للمعلمين';
                    this.form.price = 200;
                    this.form.billing_cycle = 'monthly';
                } else if (key === 'teacher_pro') {
                    this.form.subscription_type = 'quarterly';
                    this.form.plan_name = 'باقة المعلم المحترف';
                    this.form.price = 600;
                    this.form.billing_cycle = 'quarterly';
                } else if (key === 'teacher_premium') {
                    this.form.subscription_type = 'yearly';
                    this.form.plan_name = 'باقة المعلم المميز';
                    this.form.price = 1500;
                    this.form.billing_cycle = 'yearly';
                }
                this.$nextTick(function () {
                    syncSubscriptionFeatureCheckboxes(PLAN_FEATURES[key]);
                });
            },
        };
    }
</script>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\admin\subscriptions\create.blade.php ENDPATH**/ ?>
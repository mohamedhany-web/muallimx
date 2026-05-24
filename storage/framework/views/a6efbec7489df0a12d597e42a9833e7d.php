<?php $__env->startSection('title', 'مزايا اشتراك المعلمين'); ?>
<?php $__env->startSection('header', 'مزايا اشتراك المعلمين'); ?>

<?php $__env->startSection('content'); ?>
<div class="space-y-6">
    <?php if(session('success')): ?>
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>
    <?php if($errors->any()): ?>
        <div class="bg-rose-50 border border-rose-200 text-rose-800 px-4 py-3 rounded-xl shadow-sm">
            <p class="font-bold mb-2">تعذر الحفظ — راجع الحقول التالية:</p>
            <ul class="list-disc pr-5 text-sm space-y-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <li><?php echo e($error); ?></li>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">التحكم في مزايا باقات المعلمين</h1>
                <p class="text-sm text-slate-600 mt-1">
                    من هنا تتحكم في أسعار ومزايا باقات المعلمين (مجانية، أساسية، شاملة) بما فيها الحدود الشهرية لكل ميزة.
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    العملة الأساسية لكل الأسعار في هذه الصفحة هي <span class="font-semibold">الجنيه المصري (ج.م)</span>.
                </p>
                <p class="text-xs text-slate-500 mt-2 border-t border-slate-100 pt-2">
                    حقول «نص البطاقة» أدناه تُعرض في <span class="font-semibold">صفحة الأسعار العامة</span> لكل باقة معلم (نفس البيانات هنا).
                </p>
            </div>
        </div>

        <form action="<?php echo e(route('admin.teacher-features.update')); ?>" method="POST" class="space-y-6">
            <?php echo csrf_field(); ?>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <?php
                    $plansMeta = [
                        'teacher_free' => [
                            'title' => 'الباقة المجانية',
                            'subtitle' => 'مزايا محدودة — السعر دائماً صفر',
                            'badge' => 'مجانية',
                            'color' => 'from-emerald-500 to-teal-600',
                        ],
                        'teacher_starter' => [
                            'title' => 'الباقة الأساسية',
                            'subtitle' => 'كل الخدمات التعليمية بدون الميتينج',
                            'badge' => 'بدون ميتينج',
                            'color' => 'from-sky-500 to-sky-600',
                        ],
                        'teacher_pro' => [
                            'title' => 'الباقة الشاملة',
                            'subtitle' => 'تشمل الميتينج وجميع الخدمات',
                            'badge' => 'شاملة',
                            'color' => 'from-slate-900 to-slate-800',
                        ],
                    ];
                ?>

                <?php $__currentLoopData = $plansMeta; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $key => $meta): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <?php $plan = $settings[$key] ?? []; ?>
                    <div class="rounded-2xl border border-slate-200 shadow-sm overflow-hidden bg-white flex flex-col">
                        <div class="px-5 pt-5 pb-4 bg-gradient-to-br <?php echo e($meta['color']); ?> text-white relative">
                            <?php if(filled($plan['card_badge'] ?? '')): ?>
                            <div class="absolute top-3 left-3 bg-white/10 text-xs font-semibold px-3 py-1 rounded-full backdrop-blur max-w-[85%] truncate" title="<?php echo e($plan['card_badge']); ?>">
                                <?php echo e($plan['card_badge']); ?>

                            </div>
                            <?php endif; ?>
                            <h2 class="text-xl font-black mb-1"><?php echo e($plan['label'] ?? $meta['title']); ?></h2>
                            <p class="text-xs text-white/80 line-clamp-2"><?php echo e($plan['card_subtitle'] ?? $meta['subtitle']); ?></p>
                        </div>
                        <div class="p-5 space-y-4 flex-1 flex flex-col">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">عنوان الباقة (يظهر في البطاقة)</label>
                                <input type="text"
                                       name="plans[<?php echo e($key); ?>][label]"
                                       value="<?php echo e(old('plans.' . $key . '.label', $plan['label'] ?? $meta['title'])); ?>"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            </div>

                            <div class="rounded-xl border border-indigo-100 bg-indigo-50/50 p-3 space-y-3">
                                <p class="text-xs font-bold text-indigo-900">نصوص بطاقة صفحة الأسعار</p>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">سطر تحت العنوان (وصف قصير)</label>
                                    <input type="text"
                                           name="plans[<?php echo e($key); ?>][card_subtitle]"
                                           value="<?php echo e(old('plans.' . $key . '.card_subtitle', $plan['card_subtitle'] ?? '')); ?>"
                                           maxlength="500"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                           placeholder="مثال: ابدأ التدريس أونلاين بسهولة">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">شارة أعلى البطاقة (اختياري)</label>
                                    <input type="text"
                                           name="plans[<?php echo e($key); ?>][card_badge]"
                                           value="<?php echo e(old('plans.' . $key . '.card_badge', $plan['card_badge'] ?? '')); ?>"
                                           maxlength="200"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                           placeholder="اتركه فارغاً لإخفاء الشارة">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">نص تحت السعر (تلميح)</label>
                                    <input type="text"
                                           name="plans[<?php echo e($key); ?>][card_price_hint]"
                                           value="<?php echo e(old('plans.' . $key . '.card_price_hint', $plan['card_price_hint'] ?? '')); ?>"
                                           maxlength="500"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">نص زر الدعوة (CTA)</label>
                                    <input type="text"
                                           name="plans[<?php echo e($key); ?>][card_cta]"
                                           value="<?php echo e(old('plans.' . $key . '.card_cta', $plan['card_cta'] ?? '')); ?>"
                                           maxlength="120"
                                           class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                           placeholder="ابدأ الآن">
                                </div>
                                <div>
                                    <label class="block text-[11px] font-semibold text-slate-600 mb-1">صندوق ملاحظة أسفل المزايا (اختياري)</label>
                                    <textarea name="plans[<?php echo e($key); ?>][card_footer_note]"
                                              rows="2"
                                              maxlength="500"
                                              class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                              placeholder="يظهر بلون مميز فوق الزر؛ اتركه فارغاً لإخفائه"><?php echo e(old('plans.' . $key . '.card_footer_note', $plan['card_footer_note'] ?? '')); ?></textarea>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">السعر (جنيه مصري)</label>
                                <?php if($key === 'teacher_free'): ?>
                                    <input type="hidden" name="plans[<?php echo e($key); ?>][price]" value="0">
                                    <p class="text-sm font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 rounded-lg px-3 py-2">مجانية — السعر ثابت عند 0 ج.م</p>
                                    <label class="mt-3 flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                        <input type="hidden" name="plans[<?php echo e($key); ?>][is_active]" value="0">
                                        <input type="checkbox"
                                               name="plans[<?php echo e($key); ?>][is_active]"
                                               value="1"
                                               class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                               <?php echo e(old('plans.'.$key.'.is_active', $plan['is_active'] ?? true) ? 'checked' : ''); ?>>
                                        <span>إظهار الباقة المجانية في صفحة الأسعار والسماح بالتفعيل</span>
                                    </label>
                                    <div class="mt-4 space-y-3 rounded-xl border border-emerald-200 bg-emerald-50/60 p-3">
                                        <div>
                                            <label class="block text-xs font-semibold text-emerald-900 mb-1">مدة التجربة (بالأيام)</label>
                                            <input type="number"
                                                   min="1"
                                                   max="365"
                                                   name="plans[<?php echo e($key); ?>][duration_days]"
                                                   value="<?php echo e(old('plans.'.$key.'.duration_days', $plan['duration_days'] ?? 14)); ?>"
                                                   class="w-full px-3 py-2 rounded-lg border border-emerald-200 text-sm focus:ring-2 focus:ring-emerald-500">
                                            <p class="text-[11px] text-emerald-800 mt-1">ينتهي الاشتراك تلقائياً بعد هذا العدد من الأيام (أمر subscriptions:expire يومياً).</p>
                                        </div>
                                        <label class="flex items-center gap-2 text-sm text-slate-700 cursor-pointer">
                                            <input type="hidden" name="plans[<?php echo e($key); ?>][allow_repeat_activation]" value="0">
                                            <input type="checkbox"
                                                   name="plans[<?php echo e($key); ?>][allow_repeat_activation]"
                                                   value="1"
                                                   class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                                                   <?php echo e(old('plans.'.$key.'.allow_repeat_activation', $plan['allow_repeat_activation'] ?? false) ? 'checked' : ''); ?>>
                                            <span>السماح بتفعيل الباقة المجانية أكثر من مرة لنفس المستخدم</span>
                                        </label>
                                    </div>
                                    <input type="hidden" name="plans[<?php echo e($key); ?>][billing_cycle]" value="trial">
                                <?php else: ?>
                                    <div class="relative">
                                        <input type="number"
                                               step="0.01"
                                               min="0"
                                               name="plans[<?php echo e($key); ?>][price]"
                                               value="<?php echo e(old('plans.' . $key . '.price', $plan['price'] ?? 0)); ?>"
                                               class="w-full pl-12 pr-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                        <span class="absolute inset-y-0 left-3 flex items-center text-xs font-semibold text-slate-500">ج.م</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <?php if($key !== 'teacher_free'): ?>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">دورة الفوترة</label>
                                <?php $billingCycle = old('plans.' . $key . '.billing_cycle', $plan['billing_cycle'] ?? 'monthly'); ?>
                                <select name="plans[<?php echo e($key); ?>][billing_cycle]"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    <option value="monthly" <?php echo e($billingCycle === 'monthly' ? 'selected' : ''); ?>>شهري</option>
                                </select>
                            </div>
                            <?php endif; ?>

                            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50">
                                <p class="text-xs font-bold text-slate-700 mb-2">قيود الاستهلاك الدقيقة (Classroom)</p>
                                <div class="grid grid-cols-1 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">عدد الميتينج المسموح شهرياً</label>
                                        <input type="number"
                                               min="0"
                                               name="plans[<?php echo e($key); ?>][limits][classroom_meetings_per_month]"
                                               value="<?php echo e(old('plans.' . $key . '.limits.classroom_meetings_per_month', $plan['limits']['classroom_meetings_per_month'] ?? 0)); ?>"
                                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">الحد الأقصى للطلاب في الميتينج الواحد</label>
                                        <input type="number"
                                               min="1"
                                               name="plans[<?php echo e($key); ?>][limits][classroom_max_participants]"
                                               value="<?php echo e(old('plans.' . $key . '.limits.classroom_max_participants', $plan['limits']['classroom_max_participants'] ?? 25)); ?>"
                                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">المدة الافتراضية للاجتماع (بالدقائق)</label>
                                        <input type="number"
                                               min="15"
                                               name="plans[<?php echo e($key); ?>][limits][classroom_default_duration_minutes]"
                                               value="<?php echo e(old('plans.' . $key . '.limits.classroom_default_duration_minutes', $plan['limits']['classroom_default_duration_minutes'] ?? 60)); ?>"
                                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">الحد الأقصى لمدة الاجتماع (بالدقائق)</label>
                                        <input type="number"
                                               min="30"
                                               name="plans[<?php echo e($key); ?>][limits][classroom_max_duration_minutes]"
                                               value="<?php echo e(old('plans.' . $key . '.limits.classroom_max_duration_minutes', $plan['limits']['classroom_max_duration_minutes'] ?? 120)); ?>"
                                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                </div>
                            </div>

                            <div class="border border-slate-200 rounded-xl p-3 bg-slate-50">
                                <p class="text-xs font-bold text-slate-700 mb-2">إعدادات التسويق الشخصي للمعلم</p>
                                <div class="grid grid-cols-1 gap-3">
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">عدد أقسام الملف التسويقي المفعلة</label>
                                        <input type="number"
                                               min="1"
                                               max="20"
                                               name="plans[<?php echo e($key); ?>][limits][personal_marketing_profile_sections]"
                                               value="<?php echo e(old('plans.' . $key . '.limits.personal_marketing_profile_sections', $plan['limits']['personal_marketing_profile_sections'] ?? 5)); ?>"
                                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">درجة أولوية الظهور (0 - 100)</label>
                                        <input type="number"
                                               min="0"
                                               max="100"
                                               name="plans[<?php echo e($key); ?>][limits][personal_marketing_priority_score]"
                                               value="<?php echo e(old('plans.' . $key . '.limits.personal_marketing_priority_score', $plan['limits']['personal_marketing_priority_score'] ?? 0)); ?>"
                                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                    <div>
                                        <label class="block text-[11px] font-semibold text-slate-600 mb-1">أيام إبراز الملف شهرياً</label>
                                        <input type="number"
                                               min="0"
                                               max="31"
                                               name="plans[<?php echo e($key); ?>][limits][personal_marketing_monthly_featured_days]"
                                               value="<?php echo e(old('plans.' . $key . '.limits.personal_marketing_monthly_featured_days', $plan['limits']['personal_marketing_monthly_featured_days'] ?? 0)); ?>"
                                               class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    </div>
                                </div>
                            </div>

                            <div class="border-t border-slate-200 pt-3 mt-auto">
                                <p class="text-xs font-semibold text-slate-700 mb-2">المزايا المرتبطة بهذه الخطة</p>
                                <?php
                                    $planFeatures = $plan['features'] ?? [];
                                    $featureDescriptions = $plan['feature_descriptions'] ?? [];
                                    $featureLabels = [
                                        'library_access' => 'مكتبة المناهج التفاعلية الجاهزة',
                                        'ai_tools' => 'أدوات الذكاء الاصطناعي لإعداد الدروس',
                                        'classroom_access' => 'استخدام Muallimx Classroom للتدريس',
                                        'support' => 'دعم فني للمعلمين',
                                        'teacher_profile' => 'إنشاء بروفايل معلم احترافي',
                                        'visible_to_academies' => 'الظهور للأكاديميات داخل المنصة',
                                        'can_apply_opportunities' => 'التقديم على فرص التدريس',
                                        'full_ai_suite' => 'أدوات AI كاملة',
                                        'teacher_evaluation' => 'تقييم المعلم من فريق Muallimx',
                                        'recommended_to_academies' => 'ترشيح للأكاديميات المناسبة',
                                        'priority_opportunities' => 'أولوية في فرص التدريس',
                                        'direct_support' => 'دعم فني مباشر وسريع',
                                    ];
                                ?>
                                <div class="space-y-3 text-xs text-slate-700">
                                    <?php $__currentLoopData = $featureLabels; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey => $featureLabel): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <?php
                                            $isClassroomInStarter = $key === 'teacher_starter' && $featureKey === 'classroom_access';
                                            $isChecked = in_array($featureKey, $planFeatures, true) && !$isClassroomInStarter;
                                        ?>
                                        <div class="rounded-lg border border-slate-200 bg-slate-50/70 p-2.5">
                                            <label class="flex items-center">
                                                <input type="checkbox"
                                                    name="plans[<?php echo e($key); ?>][features][]"
                                                    value="<?php echo e($featureKey); ?>"
                                                    class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                                    <?php echo e($isChecked ? 'checked' : ''); ?>

                                                    <?php echo e($isClassroomInStarter ? 'disabled' : ''); ?>>
                                                <span><?php echo e($featureLabel); ?></span>
                                            </label>
                                            <?php if($isClassroomInStarter): ?>
                                                <p class="text-[11px] text-rose-600 mt-1">هذه الميزة غير متاحة في باقة البداية.</p>
                                            <?php endif; ?>
                                            <div class="mt-2">
                                                <label class="block text-[11px] font-semibold text-slate-600 mb-1">وصف الميزة في بطاقة التسعير</label>
                                                <input type="text"
                                                    name="plans[<?php echo e($key); ?>][feature_descriptions][<?php echo e($featureKey); ?>]"
                                                    value="<?php echo e(old('plans.' . $key . '.feature_descriptions.' . $featureKey, $featureDescriptions[$featureKey] ?? '')); ?>"
                                                    maxlength="500"
                                                    class="w-full px-2.5 py-1.5 rounded-md border border-slate-200 text-[11px] focus:ring-2 focus:ring-sky-500 focus:border-sky-500"
                                                    placeholder="اكتب وصفًا واضحًا وقويًا لهذه الميزة...">
                                            </div>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="<?php echo e(route('admin.subscriptions.index')); ?>" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    الرجوع للاشتراكات
                </a>
                <button type="submit" class="inline-flex items-center px-5 py-2.5 rounded-xl bg-sky-600 text-sm font-semibold text-white hover:bg-sky-700 shadow-lg shadow-sky-500/30">
                    <i class="fas fa-save ml-2"></i>
                    حفظ إعدادات المزايا
                </button>
            </div>
        </form>
    </div>
</div>
<?php $__env->stopSection(); ?>


<?php echo $__env->make('layouts.admin', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\admin\teacher-features\index.blade.php ENDPATH**/ ?>
@extends('layouts.admin')

@section('title', 'مزايا اشتراك المعلمين')
@section('header', 'مزايا اشتراك المعلمين')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl shadow-sm">
            {{ session('success') }}
        </div>
    @endif

    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
            <div>
                <h1 class="text-2xl font-black text-slate-900">التحكم في مزايا باقات المعلمين</h1>
                <p class="text-sm text-slate-600 mt-1">
                    من هنا تتحكم في أسعار ومزايا الباقات الثلاث الخاصة بالمعلمين، والتي يتم استخدامها عند إنشاء اشتراك جديد (للطلاب الذين يعملون كمعلمين).
                </p>
                <p class="text-xs text-slate-500 mt-1">
                    العملة الأساسية لكل الأسعار في هذه الصفحة هي <span class="font-semibold">الجنيه المصري (ج.م)</span>.
                </p>
            </div>
        </div>

        <form action="{{ route('admin.teacher-features.update') }}" method="POST" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                @php
                    $plansMeta = [
                        'teacher_starter' => [
                            'title' => 'باقة البداية',
                            'subtitle' => 'ابدأ التدريس أونلاين بسهولة',
                            'badge' => 'مناسبة للبداية',
                            'color' => 'from-sky-500 to-sky-600',
                        ],
                        'teacher_pro' => [
                            'title' => 'باقة المعلم المحترف',
                            'subtitle' => 'أفضل اختيار للمعلمين الذين يريدون العمل أونلاين',
                            'badge' => 'الأكثر استخدامًا',
                            'color' => 'from-slate-900 to-slate-800',
                        ],
                        'teacher_premium' => [
                            'title' => 'باقة المعلم المميز',
                            'subtitle' => 'للمعلمين الجادين في بناء مسار مهني مستقر',
                            'badge' => 'أعلى قيمة',
                            'color' => 'from-amber-500 to-amber-600',
                        ],
                    ];
                @endphp

                @foreach($plansMeta as $key => $meta)
                    @php $plan = $settings[$key] ?? []; @endphp
                    <div class="rounded-2xl border border-slate-200 shadow-sm overflow-hidden bg-white flex flex-col">
                        <div class="px-5 pt-5 pb-4 bg-gradient-to-br {{ $meta['color'] }} text-white relative">
                            <div class="absolute top-3 left-3 bg-white/10 text-xs font-semibold px-3 py-1 rounded-full backdrop-blur">
                                {{ $meta['badge'] }}
                            </div>
                            <h2 class="text-xl font-black mb-1">{{ $meta['title'] }}</h2>
                            <p class="text-xs text-white/80">{{ $meta['subtitle'] }}</p>
                        </div>
                        <div class="p-5 space-y-4 flex-1 flex flex-col">
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">وصف مختصر للبنر / العناوين</label>
                                <input type="text"
                                       name="plans[{{ $key }}][label]"
                                       value="{{ old('plans.' . $key . '.label', $plan['label'] ?? $meta['title']) }}"
                                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">السعر (جنيه مصري)</label>
                                <div class="relative">
                                    <input type="number"
                                           step="0.01"
                                           min="0"
                                           name="plans[{{ $key }}][price]"
                                           value="{{ old('plans.' . $key . '.price', $plan['price'] ?? 0) }}"
                                           class="w-full pl-12 pr-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    <span class="absolute inset-y-0 left-3 flex items-center text-xs font-semibold text-slate-500">ج.م</span>
                                </div>
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-slate-700 mb-1">دورة الفوترة</label>
                                @php $billingCycle = old('plans.' . $key . '.billing_cycle', $plan['billing_cycle'] ?? 'monthly'); @endphp
                                <select name="plans[{{ $key }}][billing_cycle]"
                                        class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                    <option value="monthly" {{ $billingCycle === 'monthly' ? 'selected' : '' }}>شهري</option>
                                    <option value="quarterly" {{ $billingCycle === 'quarterly' ? 'selected' : '' }}>ربع سنوي</option>
                                    <option value="yearly" {{ $billingCycle === 'yearly' ? 'selected' : '' }}>سنوي</option>
                                </select>
                            </div>

                            <div class="border-t border-slate-200 pt-3 mt-auto">
                                <p class="text-xs font-semibold text-slate-700 mb-2">المزايا المرتبطة بهذه الخطة</p>
                                @php $planFeatures = $plan['features'] ?? []; @endphp
                                <div class="space-y-1.5 text-xs text-slate-700">
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="library_access"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('library_access', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>مكتبة المناهج التفاعلية الجاهزة</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="ai_tools"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('ai_tools', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>أدوات الذكاء الاصطناعي لإعداد الدروس</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="classroom_access"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('classroom_access', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>استخدام MuallimX Classroom للتدريس</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="zoom_access"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('zoom_access', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>عقد حصص عبر Zoom</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="support"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('support', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>دعم فني للمعلمين</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="teacher_profile"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('teacher_profile', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>إنشاء بروفايل معلم احترافي</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="visible_to_academies"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('visible_to_academies', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>الظهور للأكاديميات داخل المنصة</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="can_apply_opportunities"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('can_apply_opportunities', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>التقديم على فرص التدريس</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="full_ai_suite"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('full_ai_suite', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>أدوات AI كاملة</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="teacher_evaluation"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('teacher_evaluation', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>تقييم المعلم من فريق MuallimX</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="recommended_to_academies"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('recommended_to_academies', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>ترشيح للأكاديميات المناسبة</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="priority_opportunities"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('priority_opportunities', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>أولوية في فرص التدريس</span>
                                    </label>
                                    <label class="flex items-center">
                                        <input type="checkbox" name="plans[{{ $key }}][features][]" value="direct_support"
                                            class="ml-2 rounded border-gray-300 text-sky-600 focus:ring-sky-500"
                                            {{ in_array('direct_support', $planFeatures, true) ? 'checked' : '' }}>
                                        <span>دعم فني مباشر وسريع</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="flex items-center justify-end gap-3 pt-4 border-t border-slate-200">
                <a href="{{ route('admin.subscriptions.index') }}" class="inline-flex items-center px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold text-slate-700 hover:bg-slate-50">
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
@endsection


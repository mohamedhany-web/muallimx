@extends('layouts.admin')

@section('title', 'صلاحيات الدور: ' . $role->display_name)
@section('header', 'إدارة صلاحيات الدور')

@section('content')
<div class="space-y-5">

    @if(session('success'))
        <div class="flex items-center gap-3 p-3 bg-green-50 border border-green-200 rounded-xl text-green-800 text-sm">
            <i class="fas fa-check-circle text-green-500 flex-shrink-0"></i>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    {{-- Header --}}
    <div class="bg-white rounded-xl border border-gray-200 p-4 flex items-center justify-between flex-wrap gap-3">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl flex items-center justify-center shadow">
                <i class="fas fa-user-shield text-white"></i>
            </div>
            <div>
                <h2 class="text-lg font-bold text-gray-900">{{ $role->display_name }}</h2>
                <p class="text-xs text-gray-400 font-mono">{{ $role->name }}</p>
            </div>
            <div class="flex items-center gap-2 mr-2">
                <span class="text-xs px-2 py-1 bg-blue-50 text-blue-600 rounded-lg border border-blue-100 font-semibold">
                    <span id="headerCount">{{ $role->permissions->count() }}</span> / {{ $permissions->flatten()->count() }} صلاحية
                </span>
                <span class="text-xs px-2 py-1 bg-emerald-50 text-emerald-600 rounded-lg border border-emerald-100 font-semibold">
                    {{ $role->users->count() }} موظف
                </span>
            </div>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.roles.edit', $role) }}"
               class="text-sm px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-edit mr-1"></i> تعديل البيانات
            </a>
            <a href="{{ route('admin.roles.index') }}"
               class="text-sm px-3 py-1.5 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-right mr-1"></i> الأدوار
            </a>
        </div>
    </div>

    {{-- دليل الأقسام --}}
    <div class="bg-indigo-50 border border-indigo-200 rounded-xl p-4">
        <p class="text-xs font-bold text-indigo-700 mb-3">
            <i class="fas fa-lightbulb mr-1"></i>
            كل صلاحية تُفعّل قسماً في سايدبار لوحة التحكم — الصلاحيات المُفعَّلة تظهر باللون الأزرق
        </p>
        @php
        $sidebarGuide = [
            // ── الطلاب والمستخدمون ──
            ['perm'=>'manage.users',              'icon'=>'fa-users',              'color'=>'text-rose-600',    'section'=>'الطلاب والتسجيلات (إدارة كاملة)'],
            ['perm'=>'manage.students-accounts',  'icon'=>'fa-user-circle',        'color'=>'text-rose-500',    'section'=>'إدارة حسابات الطلاب'],
            ['perm'=>'manage.enrollments',        'icon'=>'fa-user-graduate',      'color'=>'text-teal-600',    'section'=>'تسجيلات الطلاب'],
            ['perm'=>'manage.subscriptions',      'icon'=>'fa-calendar-check',     'color'=>'text-cyan-600',    'section'=>'الاشتراكات + العناصر المدفوعة'],
            ['perm'=>'manage.student-control',    'icon'=>'fa-eye',                'color'=>'text-rose-500',    'section'=>'رقابة الطلاب واستهلاكهم'],
            ['perm'=>'manage.support-tickets',    'icon'=>'fa-headset',            'color'=>'text-rose-400',    'section'=>'الدعم الفني (التذاكر + التصنيفات)'],
            ['perm'=>'manage.consultations',      'icon'=>'fa-comments-dollar',    'color'=>'text-teal-500',    'section'=>'استشارات المدربين'],
            ['perm'=>'manage.hiring-academies',   'icon'=>'fa-school',             'color'=>'text-teal-500',    'section'=>'الأكاديميات + فرص العمل'],
            // ── المبيعات ──
            ['perm'=>'manage.orders',             'icon'=>'fa-shopping-bag',       'color'=>'text-emerald-600', 'section'=>'قسم المبيعات (الطلبات)'],
            ['perm'=>'manage.leads',              'icon'=>'fa-user-plus',          'color'=>'text-emerald-500', 'section'=>'قسم المبيعات (Leads)'],
            ['perm'=>'view.sales-analytics',      'icon'=>'fa-chart-line',         'color'=>'text-emerald-500', 'section'=>'قسم المبيعات (التحليلات)'],
            // ── التسويق ──
            ['perm'=>'manage.coupons',            'icon'=>'fa-ticket-alt',         'color'=>'text-pink-600',    'section'=>'التسويق (الكوبونات)'],
            ['perm'=>'manage.referrals',          'icon'=>'fa-gift',               'color'=>'text-pink-600',    'section'=>'التسويق (الإحالات)'],
            ['perm'=>'manage.loyalty',            'icon'=>'fa-star',               'color'=>'text-pink-600',    'section'=>'التسويق (الولاء)'],
            ['perm'=>'manage.popup-ads',          'icon'=>'fa-bullhorn',           'color'=>'text-pink-500',    'section'=>'التسويق (الإعلانات المنبثقة)'],
            ['perm'=>'manage.personal-branding',  'icon'=>'fa-user-tie',           'color'=>'text-pink-500',    'section'=>'التسويق (العلامة الشخصية)'],
            ['perm'=>'manage.site-services',       'icon'=>'fa-concierge-bell',    'color'=>'text-sky-500',     'section'=>'صفحة الخدمات (الواجهة العامة)'],
            ['perm'=>'manage.system-settings',     'icon'=>'fa-sliders-h',         'color'=>'text-slate-600',   'section'=>'إعدادات النظام والفوتر'],
            // ── الموارد البشرية ──
            ['perm'=>'manage.leaves',             'icon'=>'fa-calendar-alt',       'color'=>'text-cyan-500',    'section'=>'الموارد البشرية (الإجازات)'],
            ['perm'=>'manage.employee-agreements','icon'=>'fa-file-contract',      'color'=>'text-cyan-600',    'section'=>'الموارد البشرية (اتفاقيات الموظفين)'],
            ['perm'=>'manage.instructor-requests','icon'=>'fa-inbox',              'color'=>'text-cyan-500',    'section'=>'الموارد البشرية (طلبات المدربين)'],
            // ── المحاسبة ──
            ['perm'=>'manage.invoices',           'icon'=>'fa-file-invoice',       'color'=>'text-amber-600',   'section'=>'المحاسبة + الاتفاقيات + المالية'],
            ['perm'=>'manage.payments',           'icon'=>'fa-credit-card',        'color'=>'text-amber-600',   'section'=>'المدفوعات'],
            ['perm'=>'manage.transactions',       'icon'=>'fa-exchange-alt',       'color'=>'text-amber-600',   'section'=>'المعاملات المالية'],
            ['perm'=>'manage.wallets',            'icon'=>'fa-wallet',             'color'=>'text-amber-600',   'section'=>'المحافظ'],
            ['perm'=>'manage.installments',       'icon'=>'fa-calendar-check',     'color'=>'text-amber-500',   'section'=>'خطط التقسيط'],
            ['perm'=>'manage.salaries',           'icon'=>'fa-money-check-alt',    'color'=>'text-amber-500',   'section'=>'رواتب المدربين'],
            ['perm'=>'manage.expenses',           'icon'=>'fa-receipt',            'color'=>'text-amber-500',   'section'=>'المصروفات'],
            ['perm'=>'manage.instructor-accounts','icon'=>'fa-user-tie',           'color'=>'text-amber-500',   'section'=>'حسابات المدربين'],
            // ── الاتفاقيات والسحب ──
            ['perm'=>'manage.agreements',         'icon'=>'fa-handshake',          'color'=>'text-orange-500',  'section'=>'اتفاقيات المدربين'],
            ['perm'=>'manage.withdrawals',        'icon'=>'fa-money-bill-wave',    'color'=>'text-orange-500',  'section'=>'طلبات السحب'],
            // ── المحتوى والتعليم ──
            ['perm'=>'manage.courses',            'icon'=>'fa-graduation-cap',     'color'=>'text-violet-600',  'section'=>'إدارة الكورسات + العناصر المدفوعة'],
            ['perm'=>'manage.lectures',           'icon'=>'fa-video',              'color'=>'text-violet-600',  'section'=>'المحاضرات'],
            ['perm'=>'manage.assignments',        'icon'=>'fa-tasks',              'color'=>'text-violet-500',  'section'=>'الواجبات والمشاريع'],
            ['perm'=>'manage.exams',              'icon'=>'fa-clipboard-check',    'color'=>'text-violet-500',  'section'=>'الامتحانات'],
            ['perm'=>'manage.question-bank',      'icon'=>'fa-database',           'color'=>'text-violet-500',  'section'=>'بنك الأسئلة'],
            ['perm'=>'manage.video-providers',    'icon'=>'fa-server',             'color'=>'text-sky-600',     'section'=>'مصادر الفيديو'],
            // ── العناصر المدفوعة ──
            ['perm'=>'manage.packages',           'icon'=>'fa-tags',               'color'=>'text-cyan-600',    'section'=>'الباقات والأسعار'],
            ['perm'=>'manage.teacher-features',   'icon'=>'fa-chalkboard-teacher', 'color'=>'text-cyan-500',    'section'=>'مزايا اشتراك المدربين'],
            ['perm'=>'manage.curriculum-library', 'icon'=>'fa-book-open',          'color'=>'text-cyan-500',    'section'=>'مكتبة المناهج'],
            // ── البث المباشر ──
            ['perm'=>'manage.live-sessions',      'icon'=>'fa-broadcast-tower',    'color'=>'text-red-500',     'section'=>'جلسات البث المباشر'],
            ['perm'=>'manage.live-servers',       'icon'=>'fa-server',             'color'=>'text-red-400',     'section'=>'سيرفرات البث (VPS)'],
            // ── الرقابة والجودة ──
            ['perm'=>'manage.quality-control',    'icon'=>'fa-shield-alt',         'color'=>'text-rose-500',    'section'=>'الرقابة والجودة'],
            ['perm'=>'view.statistics',           'icon'=>'fa-chart-bar',          'color'=>'text-purple-600',  'section'=>'الإحصائيات + التقارير'],
            // ── التقارير ──
            ['perm'=>'view.reports',              'icon'=>'fa-file-excel',         'color'=>'text-green-600',   'section'=>'التقارير الشاملة'],
            ['perm'=>'view.financial-reports',    'icon'=>'fa-file-invoice-dollar','color'=>'text-green-500',   'section'=>'التقارير المالية'],
            ['perm'=>'view.academic-reports',     'icon'=>'fa-book',               'color'=>'text-green-500',   'section'=>'التقارير الأكاديمية'],
            // ── إدارة النظام ──
            ['perm'=>'manage.notifications',      'icon'=>'fa-bell',               'color'=>'text-blue-600',    'section'=>'الإشعارات'],
            ['perm'=>'manage.email-broadcasts',   'icon'=>'fa-envelope',           'color'=>'text-blue-500',    'section'=>'البريد الجماعي (Gmail)'],
            ['perm'=>'view.activity-log',         'icon'=>'fa-history',            'color'=>'text-slate-600',   'section'=>'سجل النشاطات'],
            ['perm'=>'manage.performance',        'icon'=>'fa-tachometer-alt',     'color'=>'text-slate-500',   'section'=>'مراقبة الأداء'],
            ['perm'=>'manage.two-factor-logs',    'icon'=>'fa-lock',               'color'=>'text-slate-500',   'section'=>'سجلات المصادقة الثنائية'],
            // ── المهام والرسائل ──
            ['perm'=>'manage.tasks',              'icon'=>'fa-list-check',         'color'=>'text-sky-600',     'section'=>'إدارة المهام + الفريق'],
            ['perm'=>'manage.messages',           'icon'=>'fa-envelope-open-text', 'color'=>'text-blue-600',    'section'=>'الرسائل'],
            // ── متقدم ──
            ['perm'=>'manage.certificates',       'icon'=>'fa-certificate',        'color'=>'text-yellow-600',  'section'=>'الشهادات'],
            ['perm'=>'manage.roles',              'icon'=>'fa-user-tag',           'color'=>'text-indigo-600',  'section'=>'الأدوار والصلاحيات'],
            ['perm'=>'manage.permissions',        'icon'=>'fa-key',                'color'=>'text-indigo-500',  'section'=>'إدارة الصلاحيات'],
            ['perm'=>'manage.user-permissions',   'icon'=>'fa-user-shield',        'color'=>'text-indigo-500',  'section'=>'صلاحيات المستخدمين'],
        ];
        $activePermNames = $role->permissions->pluck('name')->toArray();
        @endphp
        <div class="flex flex-wrap gap-1">
            @foreach($sidebarGuide as $g)
            @php $active = in_array($g['perm'], $activePermNames); @endphp
            <span title="{{ $g['section'] }}" class="inline-flex items-center gap-1 text-[10px] px-1.5 py-0.5 rounded border font-medium cursor-help
                         {{ $active ? 'bg-indigo-600 text-white border-indigo-600' : 'bg-white text-gray-400 border-gray-200' }}">
                <i class="fas {{ $g['icon'] }}"></i>
                {{ $g['perm'] }}
            </span>
            @endforeach
        </div>
    </div>

    {{-- نموذج الصلاحيات --}}
    <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}" id="permissionsForm">
        @csrf
        <div class="bg-white rounded-xl border border-gray-200">

            <div class="px-5 py-3 border-b border-gray-100 flex items-center justify-between flex-wrap gap-2">
                <h3 class="text-sm font-bold text-gray-800">
                    <i class="fas fa-key text-amber-500 mr-1"></i>
                    جميع صلاحيات النظام — اختر ما تريد منحه لهذا الدور
                </h3>
                <div class="flex items-center gap-2">
                    <button type="button" onclick="toggleAll(true)"
                            class="text-xs px-3 py-1.5 bg-blue-100 text-blue-700 rounded-lg hover:bg-blue-200 border border-blue-200 font-medium">
                        <i class="fas fa-check-double mr-1"></i> تحديد الكل
                    </button>
                    <button type="button" onclick="toggleAll(false)"
                            class="text-xs px-3 py-1.5 bg-gray-100 text-gray-500 rounded-lg hover:bg-gray-200 border border-gray-200 font-medium">
                        <i class="fas fa-times mr-1"></i> إلغاء الكل
                    </button>
                    <span class="text-xs text-gray-500 bg-gray-50 px-2 py-1.5 rounded-lg border border-gray-200">
                        <span id="checkedCount">{{ $role->permissions->count() }}</span>/{{ $permissions->flatten()->count() }}
                    </span>
                </div>
            </div>

            <div class="p-4">
                @php $rolePermIds = $role->permissions->pluck('id')->toArray(); @endphp
                <div class="space-y-5">
                    @foreach($permissions as $group => $groupPermissions)
                    <div>
                        <div class="flex items-center gap-2 mb-2">
                            <span class="w-1.5 h-4 bg-indigo-400 rounded-full flex-shrink-0"></span>
                            <h4 class="text-xs font-bold text-gray-600 uppercase tracking-wide">{{ $group ?? 'عام' }}</h4>
                            <div class="flex-1 h-px bg-gray-100"></div>
                            <button type="button" data-group="g{{ $loop->index }}" onclick="toggleGroup(this)"
                                    class="text-xs text-indigo-500 hover:text-indigo-700 font-medium flex-shrink-0">
                                تحديد
                            </button>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-1.5" id="g{{ $loop->index }}">
                            @foreach($groupPermissions as $permission)
                            @php $isChecked = in_array($permission->id, $rolePermIds); @endphp
                            <label class="perm-card flex items-center gap-2 px-2.5 py-2 rounded-lg border cursor-pointer transition-all select-none text-xs
                                          {{ $isChecked ? 'bg-indigo-50 border-indigo-400' : 'bg-gray-50 border-gray-200 hover:border-indigo-300' }}">
                                <input type="checkbox" name="permissions[]" value="{{ $permission->id }}"
                                       {{ $isChecked ? 'checked' : '' }}
                                       onchange="onPermChange(this)"
                                       class="w-3.5 h-3.5 text-indigo-600 border-gray-300 rounded flex-shrink-0">
                                <div class="min-w-0">
                                    <span class="font-semibold text-gray-800 block truncate leading-tight">{{ $permission->display_name }}</span>
                                    <code class="text-[10px] text-gray-400 font-mono truncate block">{{ $permission->name }}</code>
                                </div>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>

            <div class="px-5 py-3 bg-gray-50 rounded-b-xl border-t border-gray-100 flex items-center justify-between">
                <p class="text-xs text-gray-400">
                    <i class="fas fa-info-circle mr-1"></i>
                    التغييرات تُطبَّق فوراً على الموظفين الذين يحملون هذا الدور
                </p>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-white rounded-xl font-bold text-sm shadow-sm"
                        style="background-color:#16a34a;">
                    <i class="fas fa-save"></i> حفظ
                </button>
            </div>
        </div>
    </form>

    {{-- المستخدمون --}}
    @if($role->users->count() > 0)
    <div class="bg-white rounded-xl border border-gray-200 p-4">
        <h3 class="text-sm font-bold text-gray-700 mb-3">
            <i class="fas fa-users text-indigo-500 mr-1"></i>
            الموظفون بهذا الدور ({{ $role->users->count() }})
        </h3>
        <div class="flex flex-wrap gap-2">
            @foreach($role->users as $user)
            <div class="flex items-center gap-2 px-3 py-2 bg-gray-50 border border-gray-200 rounded-xl">
                <div class="w-7 h-7 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-full flex items-center justify-center text-white font-bold text-xs flex-shrink-0">
                    {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-gray-900 truncate max-w-[120px]">{{ $user->name }}</p>
                    <p class="text-[10px] text-gray-400 truncate max-w-[120px]">{{ $user->email ?? '—' }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

</div>

<script>
function onPermChange(cb) {
    const label = cb.closest('label');
    label.classList.toggle('bg-indigo-50', cb.checked);
    label.classList.toggle('border-indigo-400', cb.checked);
    label.classList.toggle('bg-gray-50', !cb.checked);
    label.classList.toggle('border-gray-200', !cb.checked);
    updateCount();
}
function toggleAll(state) {
    document.querySelectorAll('#permissionsForm input[type=checkbox]').forEach(cb => {
        if (cb.checked !== state) { cb.checked = state; onPermChange(cb); }
    });
}
function toggleGroup(btn) {
    const boxes = document.getElementById(btn.dataset.group).querySelectorAll('input[type=checkbox]');
    const allOn = [...boxes].every(c => c.checked);
    boxes.forEach(c => { if (c.checked !== !allOn) { c.checked = !allOn; onPermChange(c); } });
    btn.textContent = allOn ? 'تحديد' : 'إلغاء';
}
function updateCount() {
    const n = document.querySelectorAll('#permissionsForm input[type=checkbox]:checked').length;
    document.getElementById('checkedCount').textContent = n;
    document.getElementById('headerCount').textContent = n;
}
</script>
@endsection

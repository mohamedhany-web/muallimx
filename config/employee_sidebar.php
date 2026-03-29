<?php

/**
 * عناصر القائمة: المفتاح = نفس مفتاح permissions في employee_jobs.
 * route = اسم مسار Laravel.
 */
return [
    'items' => [
        'dashboard' => [
            'label' => 'لوحة التحكم',
            'icon' => 'fas fa-home',
            'route' => 'employee.dashboard',
            'route_patterns' => ['employee.dashboard'],
            'active_class' => 'bg-blue-600 shadow-lg',
        ],
        'desk_accountant' => [
            'label' => 'لوحة المحاسب',
            'icon' => 'fas fa-calculator',
            'route' => 'employee.accountant-desk.index',
            'route_patterns' => ['employee.accountant-desk.*'],
            'active_class' => 'bg-amber-600 shadow-lg',
        ],
        'sales_desk' => [
            'label' => 'لوحة المبيعات',
            'icon' => 'fas fa-chart-line',
            'route' => 'employee.sales.desk',
            'route_patterns' => ['employee.sales.desk'],
            'active_class' => 'bg-emerald-600 shadow-lg',
        ],
        'sales_orders' => [
            'permission' => 'sales_desk',
            'label' => 'طلبات المبيعات',
            'icon' => 'fas fa-shopping-bag',
            'route' => 'employee.sales.orders.index',
            'route_patterns' => ['employee.sales.orders.*'],
            'active_class' => 'bg-emerald-700 shadow-lg',
        ],
        'hr_desk' => [
            'label' => 'لوحة الموارد البشرية',
            'icon' => 'fas fa-users',
            'route' => 'employee.hr-desk.index',
            'route_patterns' => ['employee.hr-desk.*'],
            'active_class' => 'bg-rose-600 shadow-lg',
        ],
        'hr_leave_requests' => [
            'permission' => 'hr_desk',
            'label' => 'مراجعة الإجازات',
            'icon' => 'fas fa-calendar-check',
            'route' => 'employee.hr.leaves.index',
            'route_patterns' => ['employee.hr.leaves.*'],
            'active_class' => 'bg-rose-700 shadow-lg',
        ],
        'hr_directory' => [
            'permission' => 'hr_desk',
            'label' => 'دليل الموظفين',
            'icon' => 'fas fa-address-book',
            'route' => 'employee.hr.employees.index',
            'route_patterns' => ['employee.hr.employees.*'],
            'active_class' => 'bg-indigo-700 shadow-lg',
        ],
        'hr_recruitment' => [
            'permission' => 'hr_desk',
            'label' => 'التوظيف والمقابلات',
            'icon' => 'fas fa-user-tie',
            'route' => 'employee.hr.recruitment.index',
            'route_patterns' => ['employee.hr.recruitment.*'],
            'active_class' => 'bg-violet-700 shadow-lg',
        ],
        'supervision_desk' => [
            'label' => 'لوحة الإشراف',
            'icon' => 'fas fa-clipboard-check',
            'route' => 'employee.supervision-desk.index',
            'route_patterns' => ['employee.supervision-desk.*'],
            'active_class' => 'bg-indigo-600 shadow-lg',
        ],
        'public_catalog' => [
            'label' => 'تصفح الكورسات (العامة)',
            'icon' => 'fas fa-graduation-cap',
            'route' => 'public.courses',
            'route_patterns' => ['public.courses', 'public.course.*'],
            'active_class' => 'bg-teal-600 shadow-lg',
        ],
        'tasks' => [
            'label' => 'مهامي',
            'icon' => 'fas fa-tasks',
            'route' => 'employee.tasks.index',
            'route_patterns' => ['employee.tasks.*'],
            'active_class' => 'bg-sky-600 shadow-lg',
        ],
        'leaves' => [
            'label' => 'إجازاتي',
            'icon' => 'fas fa-umbrella-beach',
            'route' => 'employee.leaves.index',
            'route_patterns' => ['employee.leaves.*'],
            'active_class' => 'bg-cyan-600 shadow-lg',
        ],
        'accounting' => [
            'label' => 'محاسبتي الشخصية',
            'icon' => 'fas fa-wallet',
            'route' => 'employee.accounting.index',
            'route_patterns' => ['employee.accounting.*'],
            'active_class' => 'bg-slate-500 shadow-lg',
        ],
        'agreements' => [
            'label' => 'اتفاقيات العمل',
            'icon' => 'fas fa-file-contract',
            'route' => 'employee.agreements.index',
            'route_patterns' => ['employee.agreements.*'],
            'active_class' => 'bg-violet-600 shadow-lg',
        ],
        'reports' => [
            'label' => 'تقاريري',
            'icon' => 'fas fa-chart-line',
            'route' => 'employee.reports',
            'route_patterns' => ['employee.reports'],
            'active_class' => 'bg-purple-600 shadow-lg',
        ],
        'calendar' => [
            'label' => 'التقويم',
            'icon' => 'fas fa-calendar-alt',
            'route' => 'employee.calendar',
            'route_patterns' => ['employee.calendar*'],
            'active_class' => 'bg-orange-600 shadow-lg',
        ],
        'profile' => [
            'label' => 'الملف الشخصي',
            'icon' => 'fas fa-user',
            'route' => 'employee.profile',
            'route_patterns' => ['employee.profile*'],
            'active_class' => 'bg-blue-600 shadow-lg',
        ],
        'notifications' => [
            'label' => 'الإشعارات',
            'icon' => 'fas fa-bell',
            'route' => 'employee.notifications',
            'route_patterns' => ['employee.notifications*'],
            'active_class' => 'bg-blue-600 shadow-lg',
        ],
        'settings' => [
            'label' => 'الإعدادات',
            'icon' => 'fas fa-cog',
            'route' => 'employee.settings',
            'route_patterns' => ['employee.settings*'],
            'active_class' => 'bg-slate-600 shadow-lg',
        ],
    ],

    /*
     * قائمة كل وظيفة: أقسام عربية + ترتيب العناصر حسب ما يلزم الوظيفة.
     * يُعرض فقط ما وُجد في permissions للموظف (employeeCan).
     */
    'menus_by_job' => [
        'accountant' => [
            ['title' => 'القيادة', 'keys' => ['dashboard']],
            ['title' => 'عمل المحاسبة والمالية', 'keys' => ['desk_accountant', 'agreements', 'accounting']],
            ['title' => 'المهام والإجازات', 'keys' => ['tasks', 'leaves']],
            ['title' => 'التخطيط والتقارير', 'keys' => ['calendar', 'reports']],
            ['title' => 'حسابي والتنبيهات', 'keys' => ['profile', 'notifications', 'settings']],
        ],
        'sales' => [
            ['title' => 'القيادة والمبيعات', 'keys' => ['dashboard', 'sales_desk', 'sales_orders', 'sales_leads']],
            ['title' => 'الكتالوج والعروض', 'keys' => ['public_catalog']],
            ['title' => 'المهام والمتابعة', 'keys' => ['tasks', 'leaves']],
            ['title' => 'التخطيط والتقارير', 'keys' => ['calendar', 'reports']],
            ['title' => 'حسابي والتنبيهات', 'keys' => ['profile', 'notifications', 'settings']],
        ],
        'hr' => [
            ['title' => 'القيادة والموارد البشرية', 'keys' => ['dashboard', 'hr_desk', 'hr_leave_requests', 'hr_directory', 'hr_recruitment']],
            ['title' => 'مهامي وإجازاتي', 'keys' => ['tasks', 'leaves']],
            ['title' => 'التخطيط والتقارير', 'keys' => ['calendar', 'reports']],
            ['title' => 'حسابي والتنبيهات', 'keys' => ['profile', 'notifications', 'settings']],
        ],
        'general_supervision' => [
            ['title' => 'القيادة والإشراف', 'keys' => ['dashboard', 'supervision_desk']],
            ['title' => 'المهام والإجازات', 'keys' => ['tasks', 'leaves']],
            ['title' => 'التخطيط والتقارير', 'keys' => ['calendar', 'reports']],
            ['title' => 'حسابي والتنبيهات', 'keys' => ['profile', 'notifications', 'settings']],
        ],
        'supervisor' => [
            ['title' => 'القيادة والإشراف', 'keys' => ['dashboard', 'supervision_desk']],
            ['title' => 'المهام والإجازات', 'keys' => ['tasks', 'leaves']],
            ['title' => 'التخطيط والتقارير', 'keys' => ['calendar', 'reports']],
            ['title' => 'حسابي والتنبيهات', 'keys' => ['profile', 'notifications', 'settings']],
        ],
    ],

    /** موظف بلا وظيفة محددة أو كود غير معروف: كل المفاتيح المعروفة بالترتيب العام */
    'fallback_sections' => [
        [
            'title' => null,
            'keys' => [
                'dashboard',
                'desk_accountant', 'sales_desk', 'sales_orders', 'sales_leads', 'hr_desk', 'hr_leave_requests', 'hr_directory', 'hr_recruitment', 'supervision_desk',
                'public_catalog',
                'tasks', 'leaves', 'accounting', 'agreements',
                'reports', 'calendar',
                'profile', 'notifications', 'settings',
            ],
        ],
    ],
];

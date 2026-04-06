<?php

namespace Database\Seeders;

use App\Models\EmployeeJob;
use Illuminate\Database\Seeder;

class EmployeeJobSeeder extends Seeder
{
    /**
     * الوظائف الثابتة للموظفين مع صلاحيات السايدبار لكل وظيفة
     */
    public function run(): void
    {
        $jobs = [
            [
                'name' => 'محاسب',
                'code' => 'accountant',
                'description' => 'مسؤول عن الحسابات والرواتب والاتفاقيات',
                'permissions' => [
                    'dashboard', 'tasks', 'leaves', 'accounting', 'agreements', 'reports', 'calendar',
                    'profile', 'notifications', 'settings',
                    'desk_accountant',
                ],
            ],
            [
                'name' => 'اشراف عام',
                'code' => 'general_supervision',
                'description' => 'متابعة عامة وتقارير وإحصائيات وجودة التنفيذ',
                'permissions' => [
                    'dashboard', 'tasks', 'leaves', 'reports', 'calendar',
                    'profile', 'notifications', 'settings',
                    'supervision_desk',
                ],
            ],
            [
                'name' => 'HR',
                'code' => 'hr',
                'description' => 'الموارد البشرية ومتابعة الموظفين والإجازات',
                'permissions' => [
                    'dashboard', 'tasks', 'leaves', 'reports', 'calendar',
                    'profile', 'notifications', 'settings',
                    'hr_desk',
                ],
            ],
            [
                'name' => 'مشرفه',
                'code' => 'supervisor',
                'description' => 'مشرف/ة ومتابعة المهام والتقارير',
                'permissions' => [
                    'dashboard', 'tasks', 'leaves', 'reports', 'calendar',
                    'profile', 'notifications', 'settings',
                    'supervision_desk',
                ],
            ],
            [
                'name' => 'مشرف أكاديمي',
                'code' => 'academic_supervisor',
                'description' => 'متابعة الطلاب المعيّنين: الكورسات، الاشتراك، الميتينج، والنشاط',
                'permissions' => [
                    'dashboard', 'tasks', 'leaves', 'calendar',
                    'profile', 'notifications', 'settings',
                    'academic_supervision_desk',
                ],
            ],
            [
                'name' => 'سيلز',
                'code' => 'sales',
                'description' => 'المبيعات ومتابعة الطلبات والعملاء',
                'permissions' => [
                    'dashboard', 'tasks', 'leaves', 'reports', 'calendar',
                    'profile', 'notifications', 'settings',
                    'sales_desk',
                    'public_catalog',
                ],
            ],
            [
                'name' => 'مخصص',
                'code' => 'custom',
                'description' => 'وظيفة مخصصة يتم ضبط صلاحياتها من هنا',
                'permissions' => [
                    // تُترك فارغة افتراضياً؛ يتم تعديلها من لوحة إدارة الوظائف لتحديد الأقسام المسموح بها
                ],
            ],
        ];

        foreach ($jobs as $job) {
            EmployeeJob::updateOrCreate(
                ['code' => $job['code']],
                [
                    'name' => $job['name'],
                    'description' => $job['description'] ?? null,
                    'responsibilities' => null,
                    'permissions' => $job['permissions'],
                    'min_salary' => null,
                    'max_salary' => null,
                    'is_active' => true,
                ]
            );
        }
    }
}

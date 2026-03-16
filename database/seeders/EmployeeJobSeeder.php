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
                'permissions' => ['dashboard', 'tasks', 'leaves', 'accounting', 'agreements', 'reports', 'calendar', 'profile', 'notifications', 'settings'],
            ],
            [
                'name' => 'اشراف عام',
                'code' => 'general_supervision',
                'description' => 'متابعة عامة وتقارير وإحصائيات',
                'permissions' => ['dashboard', 'tasks', 'leaves', 'reports', 'calendar', 'profile', 'notifications', 'settings'],
            ],
            [
                'name' => 'HR',
                'code' => 'hr',
                'description' => 'الموارد البشرية ومتابعة الموظفين والإجازات',
                'permissions' => ['dashboard', 'tasks', 'leaves', 'reports', 'calendar', 'profile', 'notifications', 'settings'],
            ],
            [
                'name' => 'مشرفه',
                'code' => 'supervisor',
                'description' => 'مشرف/ة ومتابعة المهام والتقارير',
                'permissions' => ['dashboard', 'tasks', 'leaves', 'reports', 'calendar', 'profile', 'notifications', 'settings'],
            ],
            [
                'name' => 'سيلز',
                'code' => 'sales',
                'description' => 'المبيعات ومتابعة الطلبات والعملاء',
                'permissions' => ['dashboard', 'tasks', 'leaves', 'profile', 'notifications', 'settings'],
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

<?php

/**
 * أنواع مهام الموظفين: مرتبطة بالوظيفة (employee_jobs.code) عند التعيين.
 * job_codes = null يعني متاحة لأي موظف (بما فيهم من بلا وظيفة محددة).
 */
return [
    'types' => [
        'general' => [
            'label' => 'مهمة عامة',
            'admin_description' => 'مهمة تشغيلية عامة لا تتبع نموذجاً محدداً لوظيفة معيّنة.',
            'deliverable_expectation' => 'الموظف يرفع ملفاً أو رابطاً أو صورة توضح إنجاز العمل، مع عنوان ووصف في التسليم.',
            'employee_hint' => 'استخدم التسليمات لإرفاق المخرجات (ملف، صورة، أو رابط) مع عنوان واضح.',
            'job_codes' => null,
            'uses_video_deliverable_fields' => false,
        ],
        'video_editing' => [
            'label' => 'مونتاج فيديو',
            'admin_description' => 'مهام تحرير فيديو؛ يُفضّل أن يذكر الموظف في الوصف/التسليم المصدر والمدة إن وُجدت.',
            'deliverable_expectation' => 'ملف فيديو أو رابط، مع ذكر «ممن استُلم المصدر» ومدة قبل/بعد المونتاج في الوصف أو حقول التسليم عند توفرها.',
            'employee_hint' => 'أرفق الملف أو الرابط، واذكر في الوصف مصدر الفيديو والمدة إن طُلب منك ذلك.',
            'job_codes' => null,
            'uses_video_deliverable_fields' => true,
        ],
        'accountant_financial' => [
            'label' => 'مهمة محاسبية / مالية',
            'admin_description' => 'مراجعة طلبات دفع، مطابقة مستندات، متابعة رواتب أو اتفاقيات من ناحية التحقق.',
            'deliverable_expectation' => 'تقارير مختصرة، أرقام مرجعية (رقم طلب، فاتورة)، روابط مستندات، أو ملفات PDF موقّعة حسب المطلوب.',
            'employee_hint' => 'في كل تسليم اذكر المرجع (مثلاً رقم الطلب أو الموظف) وأرفق المستند أو الملخص المطلوب.',
            'job_codes' => ['accountant'],
            'uses_video_deliverable_fields' => false,
        ],
        'sales_customer' => [
            'label' => 'مبيعات وعملاء',
            'admin_description' => 'متابعة عملاء محتملين أو حاليين، طلبات كورسات، استكمال بيانات التواصل.',
            'deliverable_expectation' => 'ملخص اتصال، حالة الطلب، بريد أو اسم العميل، روابط محادثات أو ملفات عروض.',
            'employee_hint' => 'وثّق في التسليم: من تواصلت معه، ماذا تم الاتفاق عليه، ورقم الطلب إن وُجد.',
            'job_codes' => ['sales'],
            'uses_video_deliverable_fields' => false,
        ],
        'hr_people' => [
            'label' => 'موارد بشرية',
            'admin_description' => 'متابعة ملفات موظفين، إجازات، استقطاب، أو توثيق إجراءات HR.',
            'deliverable_expectation' => 'نماذج، موافقات، جداول، أو ملخص قرار مع ذكر هوية الموظف المعني دون مخالفة الخصوصية.',
            'employee_hint' => 'أرفق المستندات المطلوبة واذكر نوع الإجراء (إجازة، تعيين، تحديث بيانات، …).',
            'job_codes' => ['hr'],
            'uses_video_deliverable_fields' => false,
        ],
        'supervision_quality' => [
            'label' => 'إشراف وجودة',
            'admin_description' => 'مراجعة جودة تنفيذ، تقارير تدقيق، متابعة التزام الفريق بالسياسات.',
            'deliverable_expectation' => 'تقارير نقاط الملاحظة، قوائم تحقق، توصيات، مع إمكانية إرفاق أدلة.',
            'employee_hint' => 'قدّم تقريراً واضحاً بالملاحظات والتوصيات، مع مرفقات إن لزم.',
            'job_codes' => ['general_supervision', 'supervisor'],
            'uses_video_deliverable_fields' => false,
        ],
    ],
];

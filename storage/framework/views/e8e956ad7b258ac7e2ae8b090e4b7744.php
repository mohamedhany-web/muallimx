

<?php $__env->startSection('title', 'Muallimx Classroom — إدارة الاجتماعات'); ?>
<?php $__env->startSection('header', 'إدارة اجتماعات Classroom'); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    <?php if(session('success')): ?>
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium"><?php echo e(session('success')); ?></div>
    <?php endif; ?>
    <?php if(session('error')): ?>
        <div class="rounded-xl bg-rose-50 border border-rose-200 text-rose-700 px-4 py-3 text-sm font-medium"><?php echo e(session('error')); ?></div>
    <?php endif; ?>

    <?php if(session('info')): ?>
        <div class="rounded-xl bg-sky-50 border border-sky-200 text-sky-800 px-4 py-3 text-sm font-medium"><?php echo e(session('info')); ?></div>
    <?php endif; ?>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-lg p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-black text-slate-800 dark:text-white">Muallimx Classroom</h1>
                <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">شارك رابطاً ثابتاً واحداً مع طلابك — يدخلون فقط عندما تبدأ اللايف، وكل جلسة تُحسب من باقتك.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <?php if(!empty($activeLiveMeeting)): ?>
                    <a href="<?php echo e(route('student.classroom.room', $activeLiveMeeting)); ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold shadow-lg">
                        <i class="fas fa-broadcast-tower"></i>
                        العودة للجلسة المباشرة
                    </a>
                <?php elseif(!empty($quotaExhausted)): ?>
                    <a href="<?php echo e(route('public.pricing')); ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white text-sm font-bold shadow-lg">
                        <i class="fas fa-tags"></i>
                        الرصيد خلص — ترقية الباقة
                    </a>
                <?php else: ?>
                    <form action="<?php echo e(route('student.classroom.start')); ?>" method="POST" class="inline">
                        <?php echo csrf_field(); ?>
                        <button type="submit" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-rose-500 hover:bg-rose-600 text-white text-sm font-bold shadow-lg shadow-rose-500/30">
                            <i class="fas fa-play"></i>
                            بدء لايف الآن
                        </button>
                    </form>
                <?php endif; ?>
                <a href="<?php echo e(route('student.classroom.create')); ?>" class="inline-flex items-center justify-center gap-2 px-5 py-2.5 rounded-xl bg-slate-800 hover:bg-slate-900 dark:bg-slate-700 text-white text-sm font-bold">
                    <i class="fas fa-plus"></i>
                    إنشاء / جدولة
                </a>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-gradient-to-l from-[#283593] to-[#1F2A7A] text-white shadow-lg p-5 sm:p-6">
        <div class="flex flex-col lg:flex-row lg:items-start gap-4">
            <div class="flex-1 min-w-0">
                <p class="text-xs font-bold text-white/70 uppercase tracking-wider mb-1">رابطك الثابت للطلاب</p>
                <p class="text-sm text-white/85 mb-3">انسخه مرة واحدة وشاركه دائماً. الطلاب ينتظرون هنا حتى تبدأ اللايف — ثم يدخلون تلقائياً.</p>
                <div class="flex flex-col sm:flex-row gap-2">
                    <input type="text" readonly value="<?php echo e($fixedJoinUrl); ?>" id="fixed-join-url"
                           class="flex-1 min-w-0 rounded-xl bg-white/10 border border-white/20 px-3 py-2.5 text-sm font-mono text-white" dir="ltr">
                    <button type="button"
                            onclick="navigator.clipboard.writeText(document.getElementById('fixed-join-url').value); this.textContent='تم النسخ'; setTimeout(()=>this.textContent='نسخ الرابط',1500)"
                            class="px-4 py-2.5 rounded-xl bg-[#FB5607] hover:bg-[#e84d00] text-white text-sm font-bold shrink-0">
                        نسخ الرابط
                    </button>
                    <a href="<?php echo e($fixedJoinUrl); ?>" target="_blank" class="px-4 py-2.5 rounded-xl bg-white/10 hover:bg-white/15 text-white text-sm font-bold shrink-0 text-center">فتح</a>
                </div>
                <form method="POST" action="<?php echo e(route('student.classroom.fixed-link')); ?>" class="mt-4 flex flex-col sm:flex-row gap-2 items-stretch sm:items-end">
                    <?php echo csrf_field(); ?>
                    <?php echo method_field('PUT'); ?>
                    <div class="flex-1">
                        <label class="block text-[11px] font-semibold text-white/70 mb-1">تخصيص الجزء الأخير من الرابط</label>
                        <div class="flex items-center gap-1 rounded-xl bg-white/10 border border-white/20 px-3 py-2" dir="ltr">
                            <span class="text-xs text-white/50 whitespace-nowrap">/classroom/join/t/</span>
                            <input type="text" name="classroom_slug" value="<?php echo e(auth()->user()->classroom_slug); ?>"
                                   pattern="[a-z0-9]+(?:-[a-z0-9]+)*" required maxlength="80"
                                   class="flex-1 bg-transparent border-0 text-sm text-white focus:ring-0 p-0">
                        </div>
                        <?php $__errorArgs = ['classroom_slug'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="text-rose-200 text-xs mt-1"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>
                    <button type="submit" class="px-4 py-2.5 rounded-xl bg-white text-[#283593] text-sm font-bold shrink-0">حفظ</button>
                </form>
            </div>
            <div class="lg:w-56 rounded-xl bg-white/10 border border-white/15 p-4 text-center">
                <p class="text-[11px] text-white/70 mb-1">استهلاك هذا الشهر</p>
                <p class="text-2xl font-black"><?php echo e(number_format($usedMeetingsThisMonth)); ?> <span class="text-base font-bold text-white/70">/ <?php echo e(number_format($limits['classroom_meetings_per_month'])); ?></span></p>
                <p class="text-xs mt-2 <?php echo e($remainingMeetingsThisMonth > 0 ? 'text-emerald-300' : 'text-rose-300'); ?>">متبقي: <?php echo e(number_format($remainingMeetingsThisMonth)); ?> جلسة</p>
            </div>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5">
        <h2 class="text-sm font-bold text-slate-800 dark:text-white mb-3">أدوات الاجتماع</h2>
        <div class="flex flex-wrap items-center gap-3">
            <a href="<?php echo e(route('student.classroom.whiteboard')); ?>" target="_blank" rel="noopener noreferrer"
               class="inline-flex items-center justify-center px-4 py-2.5 rounded-xl bg-amber-500/15 hover:bg-amber-500/25 text-amber-800 dark:text-amber-200 text-sm font-semibold border border-amber-400/40 dark:border-amber-500/35 transition-colors">
                وايت بورد
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">إجمالي الاجتماعات</p>
            <p class="text-xl font-bold text-slate-800 dark:text-white"><?php echo e(number_format($stats['total'])); ?></p>
        </div>
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">اجتماعات مباشرة</p>
            <p class="text-xl font-bold text-rose-600 dark:text-rose-400"><?php echo e(number_format($stats['live'])); ?></p>
        </div>
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">الحد الشهري / المستخدم</p>
            <p class="text-xl font-bold text-slate-800 dark:text-white"><?php echo e(number_format($usedMeetingsThisMonth)); ?> / <?php echo e(number_format($limits['classroom_meetings_per_month'])); ?></p>
        </div>
        <div class="rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-800 p-4">
            <p class="text-xs text-slate-500 dark:text-slate-400">المتبقي هذا الشهر</p>
            <p class="text-xl font-bold <?php echo e($remainingMeetingsThisMonth > 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-600 dark:text-rose-400'); ?>"><?php echo e(number_format($remainingMeetingsThisMonth)); ?></p>
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
            <form method="GET" action="<?php echo e(route('student.classroom.index')); ?>" class="flex flex-wrap items-center gap-2">
                <span class="text-xs text-slate-500 dark:text-slate-400">فلتر الحالة:</span>
                <?php $__currentLoopData = ['all' => 'الكل', 'live' => 'مباشر', 'scheduled' => 'مجدول', 'ended' => 'منتهي']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $k => $label): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <button type="submit" name="status" value="<?php echo e($k); ?>" class="px-3 py-1.5 rounded-lg text-xs font-semibold <?php echo e($status === $k ? 'bg-sky-600 text-white' : 'bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300'); ?>">
                        <?php echo e($label); ?>

                    </button>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                <thead class="bg-slate-50 dark:bg-slate-800/70">
                    <tr class="text-xs text-slate-600 dark:text-slate-300 uppercase">
                        <th class="px-4 py-3 text-right">الاجتماع</th>
                        <th class="px-4 py-3 text-right">الكود</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">الحد/الذروة</th>
                        <th class="px-4 py-3 text-right">الرابط</th>
                        <th class="px-4 py-3 text-right">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                    <?php $__empty_1 = true; $__currentLoopData = $meetings; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $m): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php $joinUrl = $joinBaseUrl . '/' . $m->code; ?>
                        <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/20">
                            <td class="px-4 py-3">
                                <p class="text-sm font-semibold text-slate-900 dark:text-white"><?php echo e($m->title ?: 'اجتماع بدون عنوان'); ?></p>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    الإنشاء: <?php echo e($m->created_at->format('Y-m-d H:i')); ?>

                                    <?php if($m->scheduled_for): ?>
                                        · الموعد: <?php echo e($m->scheduled_for->format('Y-m-d H:i')); ?>

                                    <?php endif; ?>
                                </p>
                            </td>
                            <td class="px-4 py-3 text-sm font-mono text-slate-700 dark:text-slate-300"><?php echo e($m->code); ?></td>
                            <td class="px-4 py-3">
                                <?php if($m->isLive()): ?>
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold bg-rose-100 text-rose-700">مباشر</span>
                                <?php elseif(!$m->started_at): ?>
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold bg-amber-100 text-amber-700">مجدول</span>
                                <?php else: ?>
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold bg-slate-100 text-slate-700">منتهي</span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-700 dark:text-slate-300">
                                <?php echo e((int) ($m->max_participants ?? 25)); ?> / <?php echo e((int) ($m->participants_peak ?? 0)); ?>

                            </td>
                            <td class="px-4 py-3 text-sm">
                                <button type="button" onclick="navigator.clipboard.writeText('<?php echo e($joinUrl); ?>'); this.textContent='تم النسخ'; setTimeout(()=>this.textContent='نسخ', 1000)" class="px-2.5 py-1 rounded-md bg-slate-100 dark:bg-slate-700 text-slate-700 dark:text-slate-300 text-xs font-semibold">نسخ</button>
                            </td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="<?php echo e(route('student.classroom.show', $m)); ?>" class="text-sky-600 hover:underline">عرض</a>
                                    <a href="<?php echo e(route('student.classroom.edit', $m)); ?>" class="text-amber-600 hover:underline">تعديل</a>
                                    <?php if(!$m->started_at && !$m->ended_at): ?>
                                        <form action="<?php echo e(route('student.classroom.start-meeting', $m)); ?>" method="POST" class="inline"><?php echo csrf_field(); ?><button class="text-emerald-600 hover:underline">بدء</button></form>
                                    <?php elseif($m->isLive()): ?>
                                        <a href="<?php echo e(route('student.classroom.room', $m)); ?>" class="text-rose-600 hover:underline">دخول</a>
                                    <?php elseif($m->ended_at && $m->recording_download_url): ?>
                                        <a href="<?php echo e($m->recording_download_url); ?>" target="_blank" class="text-indigo-600 hover:underline">تحميل التسجيل</a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500 dark:text-slate-400">لا توجد اجتماعات حتى الآن.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="px-6 py-4 border-t border-slate-200 dark:border-slate-700"><?php echo e($meetings->links()); ?></div>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\student\classroom\index.blade.php ENDPATH**/ ?>
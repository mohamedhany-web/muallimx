<div class="flex flex-col h-full">
    
    <div class="ins-sidebar-brand flex items-center gap-3 px-4 py-4 flex-shrink-0 relative">
        <button @click="if (window.innerWidth < 1024) sidebarOpen = false"
                class="lg:hidden absolute top-3 left-3 w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center justify-center transition-colors z-10">
            <i class="fas fa-times text-xs"></i>
        </button>
        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-[#283593] to-[#FB5607] text-white flex items-center justify-center flex-shrink-0 shadow-lg shadow-[#283593]/25">
            <i class="fas fa-chalkboard-teacher text-lg"></i>
        </div>
        <div class="flex-1 min-w-0 relative z-10">
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100 leading-tight">Muallimx</h2>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5"><?php echo e(__('student.learning_center')); ?></p>
        </div>
    </div>

    <?php
        $user = auth()->user();
        $isStudent = $user->role === 'student' || strtolower((string) $user->role) === 'student';
        $activeSub = $user->activeSubscription();
        $featureConfig = config('student_subscription_features', []);
        $pricingUrl = Route::has('public.pricing') ? route('public.pricing') : url('/pricing');
        $unlockedFeatures = 0;
        foreach ($featureConfig as $fk => $_) {
            if ($user->hasSubscriptionFeature($fk)) {
                $unlockedFeatures++;
            }
        }
        $featuresTotal = count($featureConfig);
        $isFreeTrial = $activeSub
            && is_string($activeSub->teacher_plan_key ?? null)
            && \App\Support\TeacherPlanKeys::isFree($activeSub->teacher_plan_key);
    ?>

    
    <div class="px-3 py-3 flex-shrink-0">
        <div class="grid grid-cols-2 gap-2.5">
            <a href="<?php echo e($pricingUrl); ?>"
               class="ins-stat-card bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80 block group cursor-pointer no-underline text-inherit rounded-xl">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-[#FB5607] dark:text-amber-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-crown text-sm"></i>
                    </span>
                    <span class="text-[10px] font-bold text-[#FB5607] dark:text-amber-400 uppercase tracking-wider"><?php echo e(__('student.sidebar_plan')); ?></span>
                </div>
                <div class="text-sm font-black text-gray-900 dark:text-gray-100 leading-tight truncate">
                    <?php echo e($activeSub?->plan_name ?? __('student.no_plan_yet')); ?>

                </div>
            </a>
            <a href="<?php echo e(route('dashboard')); ?>" class="ins-stat-card bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80 block group no-underline text-inherit rounded-xl">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-8 h-8 rounded-lg bg-[#FFE5F7] dark:bg-violet-900/40 text-[#283593] dark:text-violet-400 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <i class="fas fa-puzzle-piece text-sm"></i>
                    </span>
                    <span class="text-[10px] font-bold text-[#283593] dark:text-violet-400 uppercase tracking-wider"><?php echo e(__('student.sidebar_features')); ?></span>
                </div>
                <div class="text-xl font-black text-gray-900 dark:text-gray-100 leading-none tabular-nums"><?php echo e($unlockedFeatures); ?>/<?php echo e($featuresTotal); ?></div>
            </a>
        </div>
    </div>

    
    <nav class="flex-1 overflow-y-auto sidebar-scroll px-0 py-2 space-y-0.5 min-h-0">
        <?php if($isStudent || $user->hasAnyPermission('student.view.courses', 'student.view.my-courses', 'student.view.orders', 'student.view.invoices', 'student.view.wallet', 'student.view.certificates', 'student.view.achievements', 'student.view.exams', 'student.view.calendar', 'student.view.notifications', 'student.view.profile', 'student.view.settings')): ?>

            <div class="ins-nav-group">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-home text-[9px] opacity-50"></i>
                    <?php echo e(__('student.sidebar_home')); ?>

                </span>
            </div>

            <a href="<?php echo e(route('dashboard')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('dashboard') ? 'active' : ''); ?>">
                <span class="ins-icon bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                    <i class="fas fa-th-large text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.dashboard')); ?></span>
            </a>

            <a href="<?php echo e($pricingUrl); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('public.pricing', 'public.subscription.checkout*') ? 'active' : ''); ?>">
                <span class="ins-icon bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900/40 dark:to-orange-900/40 text-[#FB5607] dark:text-amber-400">
                    <i class="fas fa-<?php echo e($activeSub ? 'sync-alt' : 'crown'); ?> text-sm"></i>
                </span>
                <span class="flex-1 truncate font-semibold">
                    <?php echo e($activeSub ? __('student.renew_or_upgrade') : __('student.sidebar_packages')); ?>

                </span>
                <?php if(!$activeSub || $isFreeTrial): ?>
                    <span class="ins-nav-badge bg-[#FB5607] text-white text-[9px]"><?php echo e($isFreeTrial ? __('student.trial_badge') : __('student.new_badge')); ?></span>
                <?php endif; ?>
            </a>

            <?php if($activeSub && Route::has('student.my-subscription')): ?>
            <a href="<?php echo e(route('student.my-subscription')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('student.my-subscription') ? 'active' : ''); ?>">
                <span class="ins-icon bg-[#FFE5F7] dark:bg-sky-900/40 text-[#283593] dark:text-sky-400">
                    <i class="fas fa-gem text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.my_subscription_short')); ?></span>
                <span class="text-[9px] font-medium text-slate-400 dark:text-slate-500 bg-slate-100 dark:bg-slate-800 px-1.5 py-0.5 rounded"><?php echo e($activeSub->end_date?->format('m/d')); ?></span>
            </a>
            <?php endif; ?>

            
            <div class="ins-nav-group mt-3">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-puzzle-piece text-[9px] text-amber-500 opacity-80"></i>
                    <span><?php echo e(__('student.package_features_title')); ?></span>
                </span>
            </div>

            <?php $__currentLoopData = $featureConfig; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey => $cfg): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <?php
                    $unlocked = $user->hasSubscriptionFeature($featureKey);
                    $routeName = $cfg['route'] ?? 'student.features.show';
                    $params = $cfg['route_params'] ?? [];
                    if ($routeName === 'student.features.show') {
                        $params = array_merge($params, ['feature' => $featureKey]);
                    }
                    if ($unlocked) {
                        $url = ($routeName === 'student.features.show')
                            ? (Route::has('student.features.show') ? route('student.features.show', $params) : $pricingUrl)
                            : (Route::has($routeName) ? route($routeName, $params) : $pricingUrl);
                    } else {
                        $url = $pricingUrl;
                    }
                    if ($routeName === 'student.portfolio.index') $isActive = request()->routeIs('student.portfolio.*');
                    elseif ($routeName === 'curriculum-library.index') $isActive = request()->routeIs('curriculum-library.*');
                    elseif ($routeName === 'video-library.index') $isActive = request()->routeIs('video-library.*');
                    elseif ($routeName === 'student.classroom.index') $isActive = request()->routeIs('student.classroom.*');
                    elseif ($routeName === 'student.support.index') $isActive = request()->routeIs('student.support.*');
                    elseif ($routeName === 'student.academies.visibility') $isActive = request()->routeIs('student.academies.*');
                    elseif ($routeName === 'student.opportunities.index') $isActive = request()->routeIs('student.opportunities.*');
                    else $isActive = request()->routeIs('student.features.show') && request()->route('feature') === $featureKey;
                    $isActive = $unlocked && $isActive;
                ?>
                <a href="<?php echo e($url); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
                   class="ins-nav <?php echo e($isActive ? 'active' : ''); ?> <?php echo e($unlocked ? '' : 'opacity-90'); ?>">
                    <span class="ins-icon <?php echo e($cfg['icon_bg'] ?? 'bg-slate-100 dark:bg-slate-700/70'); ?> <?php echo e($cfg['icon_text'] ?? 'text-slate-600 dark:text-slate-300'); ?>">
                        <i class="fas <?php echo e($unlocked ? ($cfg['icon'] ?? 'fa-star') : 'fa-lock'); ?> text-sm"></i>
                    </span>
                    <span class="flex-1 truncate"><?php echo e(__("student.subscription_feature.{$featureKey}")); ?></span>
                    <?php if (! ($unlocked)): ?>
                        <span class="text-[9px] font-bold text-[#FB5607] bg-orange-50 dark:bg-orange-900/30 px-1.5 py-0.5 rounded"><?php echo e(__('student.feature_locked')); ?></span>
                    <?php endif; ?>
                </a>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <div class="ins-nav-group mt-3">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-toolbox text-[9px] opacity-50"></i>
                    <?php echo e(__('student.sidebar_tools')); ?>

                </span>
            </div>

            <?php if($isStudent || $user->hasPermission('student.view.calendar')): ?>
            <a href="<?php echo e(route('calendar')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('calendar') ? 'active' : ''); ?>">
                <span class="ins-icon bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400">
                    <i class="fas fa-calendar-alt text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.calendar')); ?></span>
            </a>
            <?php endif; ?>

            <?php if($isStudent || $user->hasPermission('student.view.wallet')): ?>
            <a href="<?php echo e(route('student.wallet.index')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('student.wallet.*') ? 'active' : ''); ?>">
                <span class="ins-icon bg-teal-100 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400">
                    <i class="fas fa-wallet text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.wallet')); ?></span>
            </a>
            <?php endif; ?>

            <?php if($isStudent || $user->hasPermission('student.view.orders')): ?>
            <a href="<?php echo e(route('orders.index')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('orders.*') ? 'active' : ''); ?>">
                <span class="ins-icon bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400">
                    <i class="fas fa-receipt text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.orders')); ?></span>
            </a>
            <?php endif; ?>

            <?php if($isStudent || $user->hasPermission('student.view.notifications')): ?>
            <a href="<?php echo e(route('notifications')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('notifications') ? 'active' : ''); ?>">
                <span class="ins-icon bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400">
                    <i class="fas fa-bell text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.notifications')); ?></span>
            </a>
            <?php endif; ?>

            <?php if($isStudent && $user->canAccessStudentAiUsages() && Route::has('student.ai-usages.index')): ?>
            <a href="<?php echo e(route('student.ai-usages.index')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('student.ai-usages.*') ? 'active' : ''); ?>">
                <span class="ins-icon bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400">
                    <i class="fas fa-flask text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.ai_usages.nav')); ?></span>
            </a>
            <?php endif; ?>

            <div class="ins-nav-group mt-3">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-user-cog text-[9px] opacity-50"></i>
                    <?php echo e(__('student.sidebar_account')); ?>

                </span>
            </div>

            <?php if($isStudent || $user->hasPermission('student.view.profile')): ?>
            <a href="<?php echo e(route('profile')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('profile') ? 'active' : ''); ?>">
                <span class="ins-icon bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400">
                    <i class="fas fa-user text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.profile')); ?></span>
            </a>
            <?php endif; ?>

            <?php if($isStudent || $user->hasPermission('student.view.settings')): ?>
            <a href="<?php echo e(route('settings')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
               class="ins-nav <?php echo e(request()->routeIs('settings') ? 'active' : ''); ?>">
                <span class="ins-icon bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400">
                    <i class="fas fa-cog text-sm"></i>
                </span>
                <span class="flex-1 truncate"><?php echo e(__('student.settings')); ?></span>
            </a>
            <?php endif; ?>
        <?php endif; ?>

        <?php if(auth()->user()->isAdmin() || auth()->user()->isInstructor()): ?>
            <div class="ins-nav-group mt-3">
                <span class="inline-flex items-center gap-1.5">
                    <i class="fas fa-exchange-alt text-[9px] opacity-50"></i>
                    لوحة أخرى
                </span>
            </div>
            <?php if(auth()->user()->isAdmin()): ?>
                <a href="<?php echo e(route('admin.dashboard')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
                   class="ins-nav <?php echo e(request()->routeIs('admin.dashboard') ? 'active' : ''); ?>">
                    <span class="ins-icon bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400">
                        <i class="fas fa-shield-alt text-sm"></i>
                    </span>
                    <span class="flex-1 truncate"><?php echo e(__('student.admin_panel')); ?></span>
                </a>
            <?php endif; ?>
            <?php if(auth()->user()->isInstructor()): ?>
                <a href="<?php echo e(route('dashboard')); ?>" @click="if (window.innerWidth < 1024) setTimeout(() => { sidebarOpen = false }, 50)"
                   class="ins-nav">
                    <span class="ins-icon bg-[#FFE5F7] dark:bg-sky-900/40 text-[#283593] dark:text-sky-400">
                        <i class="fas fa-chalkboard-teacher text-sm"></i>
                    </span>
                    <span class="flex-1 truncate">لوحة المعلم</span>
                </a>
            <?php endif; ?>
        <?php endif; ?>
    </nav>

    <div class="px-3 py-3 flex-shrink-0 border-t border-gray-200/80 dark:border-gray-700/80">
        <div class="ins-user-card flex items-center gap-3">
            <div class="u-avatar flex-shrink-0 w-10 h-10 rounded-xl">
                <?php if(auth()->user()->profile_image): ?>
                    <img src="<?php echo e(auth()->user()->profile_image_url); ?>" alt="" class="w-full h-full object-cover rounded-xl">
                <?php else: ?>
                    <?php echo e(mb_substr(auth()->user()->name, 0, 1)); ?>

                <?php endif; ?>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate leading-tight"><?php echo e(auth()->user()->name); ?></p>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                    <?php if(auth()->user()->isAdmin()): ?> <?php echo e(__('student.admin_role')); ?>

                    <?php elseif(auth()->user()->isInstructor()): ?> <?php echo e(__('student.instructor_role')); ?>

                    <?php else: ?> <?php echo e(__('student.student_role')); ?>

                    <?php endif; ?>
                </p>
            </div>
            <form method="POST" action="<?php echo e(route('logout')); ?>" class="flex-shrink-0">
                <?php echo csrf_field(); ?>
                <button type="submit" class="w-8 h-8 rounded-lg bg-red-50 dark:bg-red-900/20 hover:bg-red-100 dark:hover:bg-red-900/40 text-red-500 dark:text-red-400 flex items-center justify-center transition-colors" title="تسجيل الخروج">
                    <i class="fas fa-sign-out-alt text-xs"></i>
                </button>
            </form>
        </div>
    </div>
</div>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/layouts/student-sidebar.blade.php ENDPATH**/ ?>
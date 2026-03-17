<div class="flex flex-col h-full">
    {{-- Brand: icon only, no logo --}}
    <div class="ins-sidebar-brand flex items-center gap-3 px-4 py-4 flex-shrink-0 relative">
        <button @click="if (window.innerWidth < 1024) sidebarOpen = false"
                class="lg:hidden absolute top-3 left-3 w-8 h-8 rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-600 dark:text-gray-300 hover:bg-gray-300 dark:hover:bg-gray-600 flex items-center justify-center transition-colors z-10">
            <i class="fas fa-times text-xs"></i>
        </button>
        <div class="w-11 h-11 rounded-xl bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400 flex items-center justify-center flex-shrink-0">
            <i class="fas fa-user-graduate text-xl"></i>
        </div>
        <div class="flex-1 min-w-0 relative z-10">
            <h2 class="text-base font-bold text-gray-900 dark:text-gray-100 leading-tight">MuallimX</h2>
            <p class="text-[11px] text-gray-500 dark:text-gray-400 font-medium mt-0.5">{{ __('student.learning_center') }}</p>
        </div>
    </div>

    {{-- Stats cards --}}
    @php
        $coursesCount = auth()->user()->activeCourses()->count();
        $enrollments = auth()->user()->courseEnrollments()->whereIn('status', ['active', 'completed'])->get();
        $totalProgress = $enrollments->isEmpty() ? 0 : round($enrollments->avg('progress') ?? 0, 0);
    @endphp
    <div class="px-3 py-4 flex-shrink-0">
        <div class="grid grid-cols-2 gap-2.5">
            <a href="{{ route('my-courses.index') }}" class="ins-stat-card bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80 block">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-8 h-8 rounded-lg bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400 flex items-center justify-center">
                        <i class="fas fa-book-open text-xs"></i>
                    </span>
                    <span class="text-[10px] font-bold text-violet-600 dark:text-violet-400 uppercase tracking-wider">{{ __('student.courses') }}</span>
                </div>
                <div class="text-xl font-black text-gray-900 dark:text-gray-100 leading-none tabular-nums">{{ $coursesCount }}</div>
            </a>
            <a href="{{ route('my-courses.index') }}" class="ins-stat-card bg-white dark:bg-gray-800/80 border border-gray-200 dark:border-gray-700/80 block">
                <div class="flex items-center gap-2 mb-1.5">
                    <span class="w-8 h-8 rounded-lg bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400 flex items-center justify-center">
                        <i class="fas fa-chart-line text-xs"></i>
                    </span>
                    <span class="text-[10px] font-bold text-amber-600 dark:text-amber-400 uppercase tracking-wider">{{ __('student.progress') }}</span>
                </div>
                <div class="text-xl font-black text-gray-900 dark:text-gray-100 leading-none tabular-nums">{{ $totalProgress }}%</div>
            </a>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto sidebar-scroll px-0 py-2 space-y-0 min-h-0">
        @php
            $user = auth()->user();
            $isStudent = $user->role === 'student' || strtolower($user->role) === 'student';
        @endphp
        @if($isStudent || $user->hasAnyPermission('student.view.courses', 'student.view.my-courses', 'student.view.orders', 'student.view.invoices', 'student.view.wallet', 'student.view.certificates', 'student.view.achievements', 'student.view.exams', 'student.view.calendar', 'student.view.notifications', 'student.view.profile', 'student.view.settings'))

            <div class="ins-nav-group">الرئيسية</div>

            <a href="{{ route('dashboard') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                <span class="ins-icon bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400"><i class="fas fa-grid-2"></i></span>
                <span class="flex-1 truncate">{{ __('student.dashboard') }}</span>
            </a>

            @hasPermission('student.view.courses')
            @php $catalogActive = request()->routeIs('academic-years*') || request()->routeIs('subjects.*') || request()->routeIs('courses.*'); @endphp
            <a href="{{ route('academic-years') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ $catalogActive ? 'active' : '' }}">
                <span class="ins-icon bg-indigo-100 dark:bg-indigo-900/40 text-indigo-600 dark:text-indigo-400"><i class="fas fa-search"></i></span>
                <span class="flex-1 truncate">{{ __('student.browse_courses') }}</span>
            </a>
            @endif

            @if($isStudent || $user->hasPermission('student.view.my-courses'))
            <a href="{{ route('my-courses.index') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('my-courses.*') ? 'active' : '' }}">
                <span class="ins-icon bg-violet-100 dark:bg-violet-900/40 text-violet-600 dark:text-violet-400"><i class="fas fa-book-open"></i></span>
                <span class="flex-1 truncate">{{ __('student.my_courses') }}</span>
                @if($coursesCount > 0)
                    <span class="ins-nav-badge bg-violet-100 dark:bg-violet-900/50 text-violet-700 dark:text-violet-300">{{ $coursesCount }}</span>
                @endif
            </a>
            @endif

            @if(Route::has('student.live-sessions.index'))
            <a href="{{ route('student.live-sessions.index') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('student.live-sessions.*') ? 'active' : '' }}">
                <span class="ins-icon bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400"><i class="fas fa-broadcast-tower"></i></span>
                <span class="flex-1 truncate">البث المباشر</span>
                @php $studentLiveCount = \App\Models\LiveSession::where('status', 'live')->count(); @endphp
                @if($studentLiveCount > 0)
                    <span class="ins-nav-badge bg-red-100 dark:bg-red-900/50 text-red-600 dark:text-red-400">
                        <span class="w-1.5 h-1.5 bg-red-500 rounded-full animate-pulse inline-block ml-1"></span>{{ $studentLiveCount }}
                    </span>
                @endif
            </a>
            @endif

            @if(Route::has('student.live-recordings.index'))
            <a href="{{ route('student.live-recordings.index') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('student.live-recordings.*') ? 'active' : '' }}">
                <span class="ins-icon bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400"><i class="fas fa-play-circle"></i></span>
                <span class="flex-1 truncate">تسجيلات البث</span>
            </a>
            @endif

            <div class="ins-nav-group mt-2">التعلم والإنجازات</div>

            @if($isStudent || $user->hasPermission('student.view.orders'))
            <a href="{{ route('orders.index') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('orders.*') ? 'active' : '' }}">
                <span class="ins-icon bg-amber-100 dark:bg-amber-900/40 text-amber-600 dark:text-amber-400"><i class="fas fa-shopping-cart"></i></span>
                <span class="flex-1 truncate">{{ __('student.orders') }}</span>
            </a>
            @endif

            @if($isStudent || $user->hasPermission('student.view.exams'))
            <a href="{{ route('student.exams.index') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('student.exams.*') ? 'active' : '' }}">
                <span class="ins-icon bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400"><i class="fas fa-clipboard-check"></i></span>
                <span class="flex-1 truncate">{{ __('student.exams') }}</span>
            </a>
            @endif

            @if($isStudent || $user->hasPermission('student.view.certificates'))
            <a href="{{ route('student.certificates.index') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('student.certificates.*') ? 'active' : '' }}">
                <span class="ins-icon bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 dark:text-emerald-400"><i class="fas fa-certificate"></i></span>
                <span class="flex-1 truncate">{{ __('student.certificates') }}</span>
            </a>
            @endif

            @if($isStudent || $user->hasPermission('student.view.wallet'))
            <a href="{{ route('student.wallet.index') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('student.wallet.*') ? 'active' : '' }}">
                <span class="ins-icon bg-teal-100 dark:bg-teal-900/40 text-teal-600 dark:text-teal-400"><i class="fas fa-wallet"></i></span>
                <span class="flex-1 truncate">{{ __('student.wallet') }}</span>
            </a>
            @endif

            @if($isStudent || $user->hasPermission('student.view.calendar'))
            @php
                $upcomingEventsCount = 0;
                try {
                    $user = auth()->user();
                    $upcomingEventsCount += \App\Models\Lecture::whereHas('course', function($q) use ($user) {
                        $q->whereHas('enrollments', function($q2) use ($user) { $q2->where('user_id', $user->id)->where('status', 'active'); });
                    })->where('status', 'scheduled')->where('scheduled_at', '>=', now())->count();
                    $upcomingEventsCount += \App\Models\Exam::whereHas('course', function($q) use ($user) {
                        $q->whereHas('enrollments', function($q2) use ($user) { $q2->where('user_id', $user->id)->where('status', 'active'); });
                    })->where('is_active', true)->where('is_published', true)->where(function($q) { $q->where('start_time', '>=', now())->orWhere('start_date', '>=', now()); })->count();
                    $upcomingEventsCount += \App\Models\Assignment::whereHas('course', function($q) use ($user) {
                        $q->whereHas('enrollments', function($q2) use ($user) { $q2->where('user_id', $user->id)->where('status', 'active'); });
                    })->where('status', 'published')->where('due_date', '>=', now())->count();
                    $upcomingEventsCount += \App\Models\LectureAssignment::whereHas('lecture.course', function($q) use ($user) {
                        $q->whereHas('enrollments', function($q2) use ($user) { $q2->where('user_id', $user->id)->where('status', 'active'); });
                    })->where('status', 'published')->where('due_date', '>=', now())->count();
                } catch (\Exception $e) {}
            @endphp
            <a href="{{ route('calendar') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('calendar') ? 'active' : '' }}">
                <span class="ins-icon bg-blue-100 dark:bg-blue-900/40 text-blue-600 dark:text-blue-400 relative">
                    <i class="fas fa-calendar-alt"></i>
                    @if($upcomingEventsCount > 0)
                        <span class="absolute -top-0.5 -right-0.5 w-3.5 h-3.5 bg-red-500 rounded-full border-2 border-white dark:border-gray-900 flex items-center justify-center text-[7px] font-bold text-white">{{ $upcomingEventsCount > 9 ? '9+' : $upcomingEventsCount }}</span>
                    @endif
                </span>
                <span class="flex-1 truncate">{{ __('student.calendar') }}</span>
                @if($upcomingEventsCount > 0)
                    <span class="ins-nav-badge bg-blue-100 dark:bg-blue-900/50 text-blue-700 dark:text-blue-300">{{ $upcomingEventsCount }}</span>
                @endif
            </a>
            @endif

            @if($isStudent || $user->hasPermission('student.view.notifications'))
            <a href="{{ route('notifications') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('notifications') ? 'active' : '' }}">
                <span class="ins-icon bg-rose-100 dark:bg-rose-900/40 text-rose-600 dark:text-rose-400 relative">
                    <i class="fas fa-bell"></i>
                    <span class="absolute -top-0.5 -right-0.5 w-2 h-2 bg-red-500 rounded-full border border-white dark:border-gray-900"></span>
                </span>
                <span class="flex-1 truncate">{{ __('student.notifications') }}</span>
            </a>
            @endif

            {{-- القسم المدفوع: يظهر عند وجود اشتراك نشط، مع روابط كل المزايا المتاحة حسب الباقة --}}
            @php
                $activeSub = $user->activeSubscription();
                $featureConfig = config('student_subscription_features', []);
            @endphp
            @if($activeSub)
            <div class="ins-nav-group mt-2">القسم المدفوع</div>

            @if(Route::has('student.my-subscription'))
            <a href="{{ route('student.my-subscription') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('student.my-subscription') ? 'active' : '' }}">
                <span class="ins-icon bg-sky-100 dark:bg-sky-900/40 text-sky-600 dark:text-sky-400">
                    <i class="fas fa-layer-group"></i>
                </span>
                <span class="flex-1 truncate">اشتراكي</span>
                <span class="text-[10px] text-slate-500" title="ينتهي في {{ $activeSub->end_date?->format('Y-m-d') }}">ينتهي {{ $activeSub->end_date?->format('m/d') }}</span>
            </a>
            @endif

            @foreach($featureConfig as $featureKey => $cfg)
                @if(in_array($featureKey, ['zoom_access', 'ai_tools']))
                    @continue
                @endif
                @if(!$user->hasSubscriptionFeature($featureKey) && !($featureKey === 'teacher_profile' && $user->hasPermission('student.view.profile')))
                    @continue
                @endif
                @php
                    $routeName = $cfg['route'] ?? 'student.features.show';
                    $params = $cfg['route_params'] ?? [];
                    $url = $routeName === 'student.features.show' ? route('student.features.show', $params) : route($routeName, $params);
                    if ($routeName === 'student.portfolio.index') $isActive = request()->routeIs('student.portfolio.*');
                    elseif ($routeName === 'curriculum-library.index') $isActive = request()->routeIs('curriculum-library.*');
                    else $isActive = request()->routeIs('student.features.show') && request()->route('feature') === $featureKey;
                @endphp
                @if(($routeName === 'student.features.show' && Route::has('student.features.show')) || ($routeName !== 'student.features.show' && Route::has($routeName)))
                <a href="{{ $url }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
                   class="ins-nav {{ $isActive ? 'active' : '' }}">
                    <span class="ins-icon {{ $cfg['icon_bg'] ?? 'bg-slate-100' }} {{ $cfg['icon_text'] ?? 'text-slate-600' }}">
                        <i class="fas {{ $cfg['icon'] ?? 'fa-star' }}"></i>
                    </span>
                    <span class="flex-1 truncate">{{ __("student.subscription_feature.{$featureKey}") }}</span>
                    <span class="ins-nav-badge bg-amber-100 dark:bg-amber-900/40 text-amber-700 dark:text-amber-300 text-[10px] font-semibold px-2">مدفوع</span>
                </a>
                @endif
            @endforeach
            @endif

            <div class="ins-nav-group mt-2">الحساب</div>

            @if($isStudent || $user->hasPermission('student.view.profile'))
            <a href="{{ route('profile') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('profile') ? 'active' : '' }}">
                <span class="ins-icon bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400"><i class="fas fa-user"></i></span>
                <span class="flex-1 truncate">{{ __('student.profile') }}</span>
            </a>
            @endif

            @if($isStudent || $user->hasPermission('student.view.settings'))
            <a href="{{ route('settings') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
               class="ins-nav {{ request()->routeIs('settings') ? 'active' : '' }}">
                <span class="ins-icon bg-gray-100 dark:bg-gray-700/60 text-gray-600 dark:text-gray-400"><i class="fas fa-cog"></i></span>
                <span class="flex-1 truncate">{{ __('student.settings') }}</span>
            </a>
            @endif
        @endif

        @if(auth()->user()->isAdmin() || auth()->user()->isInstructor())
            <div class="ins-nav-group mt-2">لوحة أخرى</div>
            @if(auth()->user()->isAdmin())
                <a href="{{ route('admin.dashboard') }}" @click="if(window.innerWidth<1024) sidebarOpen=false"
                   class="ins-nav {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                    <span class="ins-icon bg-red-100 dark:bg-red-900/40 text-red-600 dark:text-red-400"><i class="fas fa-shield-alt"></i></span>
                    <span class="flex-1 truncate">{{ __('student.admin_panel') }}</span>
                </a>
            @endif
        @endif
    </nav>

    {{-- User card --}}
    <div class="px-3 py-3 flex-shrink-0 border-t border-gray-100 dark:border-gray-800">
        <div class="ins-user-card flex items-center gap-3">
            <div class="u-avatar flex-shrink-0 w-10 h-10 rounded-xl">
                @if(auth()->user()->profile_image)
                    <img src="{{ auth()->user()->profile_image_url }}" alt="">
                @else
                    {{ mb_substr(auth()->user()->name, 0, 1) }}
                @endif
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-sm font-bold text-gray-900 dark:text-gray-100 truncate leading-tight">{{ auth()->user()->name }}</p>
                <p class="text-[10px] text-gray-500 dark:text-gray-400 truncate mt-0.5">
                    @if(auth()->user()->isAdmin()) {{ __('student.admin_role') }}
                    @elseif(auth()->user()->isInstructor()) {{ __('student.instructor_role') }}
                    @else {{ __('student.student_role') }}
                    @endif
                </p>
            </div>
        </div>
    </div>
</div>

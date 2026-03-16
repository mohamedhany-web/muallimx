@php
    $appLocale = app()->getLocale();
    $appRtl = $appLocale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $appLocale }}" dir="{{ $appRtl ? 'rtl' : 'ltr' }}" class="light">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MuallimX') }} - @yield('title', __('auth.dashboard'))</title>

    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $platformLogoUrl ?? asset('logo-removebg-preview.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ $platformLogoUrl ?? asset('logo-removebg-preview.png') }}">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        navy: { 50:'#f0f4ff',100:'#dbe4ff',200:'#bac8ff',300:'#91a7ff',400:'#748ffc',500:'#5c7cfa',600:'#4c6ef5',700:'#4263eb',800:'#3b5bdb',900:'#364fc7',950:'#0c1222' },
                        brand: { 50:'#ecfeff',100:'#cffafe',200:'#a5f3fc',300:'#67e8f9',400:'#22d3ee',500:'#06b6d4',600:'#0891b2',700:'#0e7490',800:'#155e75',900:'#164e63' },
                        surface: { 50:'#fafbfc', 100:'#f4f5f7', 200:'#e8eaed', 300:'#dadce0' }
                    }
                }
            }
        };
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    @php
        $showContentProtection = !empty(trim((string) ($__env->yieldContent('enable-content-protection') ?? '')));
    @endphp
    @if($showContentProtection)
    <script>
        window.Laravel = { user: { name: '{{ auth()->check() ? auth()->user()->name : "زائر" }}' } };
    </script>
    <script src="{{ asset('js/platform-protection.js') }}"></script>
    @endif

    <script>
        (function() {
            var s = localStorage.getItem('theme');
            var d = window.matchMedia('(prefers-color-scheme: dark)').matches;
            if (s === 'dark' || (!s && d)) {
                document.documentElement.classList.add('dark');
                document.documentElement.classList.remove('light');
            } else {
                document.documentElement.classList.remove('dark');
                document.documentElement.classList.add('light');
            }
        })();
    </script>

    <style>
        * { font-family: 'IBM Plex Sans Arabic', system-ui, -apple-system, sans-serif; }
        [x-cloak] { display: none !important; }
        html { scroll-behavior: smooth; }
        body { background: #f8f9fb; overflow-x: hidden; }
        html.dark body { background: #0c1222; }

        /* ── Sidebar ── */
        .app-sidebar {
            width: 260px;
            background: #fff;
            border-left: 1px solid #e5e7eb;
        }
        html.dark .app-sidebar {
            background: #111827;
            border-left-color: #1f2937;
        }
        @media (max-width: 1023px) {
            .app-sidebar { width: 280px; }
        }

        .app-sidebar::-webkit-scrollbar,
        .sidebar-scroll::-webkit-scrollbar { width: 4px; }
        .app-sidebar::-webkit-scrollbar-thumb,
        .sidebar-scroll::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 4px; }
        html.dark .app-sidebar::-webkit-scrollbar-thumb,
        html.dark .sidebar-scroll::-webkit-scrollbar-thumb { background: #374151; }

        /* Sidebar nav items */
        .s-nav {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            color: #4b5563; transition: all .15s;
            border: 1px solid transparent;
        }
        .s-nav:hover { background: #f3f4f6; color: #111827; }
        .s-nav.active { background: #eff6ff; color: #1d4ed8; border-color: #bfdbfe; }
        html.dark .s-nav { color: #9ca3af; }
        html.dark .s-nav:hover { background: #1f2937; color: #e5e7eb; }
        html.dark .s-nav.active { background: #172554; color: #60a5fa; border-color: #1e3a5f; }

        .s-nav .s-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; flex-shrink: 0; transition: all .15s;
        }

        /* ── Header ── */
        .app-header {
            height: 56px; background: #fff;
            border-bottom: 1px solid #e5e7eb;
        }
        html.dark .app-header {
            background: #111827; border-bottom-color: #1f2937;
        }

        /* Header buttons */
        .h-btn {
            width: 36px; height: 36px; border-radius: 8px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #6b7280; border: 1px solid #e5e7eb;
            transition: all .15s; background: transparent;
        }
        .h-btn:hover { background: #f3f4f6; color: #111827; border-color: #d1d5db; }
        html.dark .h-btn { color: #9ca3af; border-color: #374151; }
        html.dark .h-btn:hover { background: #1f2937; color: #e5e7eb; border-color: #4b5563; }

        /* Search input */
        .search-box {
            background: #f3f4f6; border: 1px solid transparent;
            border-radius: 8px; padding: 7px 12px;
            transition: all .2s;
        }
        .search-box:focus-within { background: #fff; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.1); }
        html.dark .search-box { background: #1f2937; }
        html.dark .search-box:focus-within { background: #111827; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59,130,246,.15); }
        html.dark .search-box input { color: #e5e7eb; }

        /* Dropdown */
        .dd-menu {
            background: #fff; border: 1px solid #e5e7eb;
            border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,.08);
            overflow: hidden;
        }
        html.dark .dd-menu { background: #1f2937; border-color: #374151; box-shadow: 0 10px 40px rgba(0,0,0,.3); }
        .dd-item { display: flex; align-items: center; transition: background .1s; }
        .dd-item:hover { background: #f3f4f6; }
        html.dark .dd-item:hover { background: #374151; }

        /* User avatar */
        .u-avatar {
            width: 32px; height: 32px; border-radius: 8px;
            background: linear-gradient(135deg, #06b6d4, #3b82f6);
            display: flex; align-items: center; justify-content: center;
            color: #fff; font-weight: 600; font-size: 13px;
        }
        .u-avatar img { width: 100%; height: 100%; object-fit: cover; border-radius: 8px; }

        /* Logo fix */
        .logo-section img,
        .app-sidebar img[alt*="Logo"] {
            transform: none !important; rotate: 0deg !important;
            object-fit: contain !important; object-position: center !important;
        }

        /* Notification badge */
        .n-badge {
            position: absolute; top: -3px; right: -3px;
            min-width: 16px; height: 16px; padding: 0 4px;
            background: #ef4444; border-radius: 99px;
            font-size: 9px; color: #fff; font-weight: 700;
            display: flex; align-items: center; justify-content: center;
            border: 2px solid #fff;
        }
        html.dark .n-badge { border-color: #111827; }

        /* Stat mini cards (student sidebar) */
        .stat-mini { border-radius: 8px; padding: 8px 10px; }

        /* Student sidebar nav-item compat */
        .nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 8px 12px; border-radius: 8px;
            font-size: 13px; font-weight: 500;
            color: #4b5563; transition: all .15s;
        }
        .nav-item:hover { background: #f3f4f6; color: #111827; }
        .nav-item.active { background: #eff6ff; color: #1d4ed8; }
        html.dark .nav-item { color: #9ca3af; }
        html.dark .nav-item:hover { background: #1f2937; color: #e5e7eb; }
        html.dark .nav-item.active { background: #172554; color: #60a5fa; }
        .nav-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; flex-shrink: 0;
        }

        /* Student sidebar bottom card */
        .user-card-bottom { border-top: 1px solid #e5e7eb; }
        html.dark .user-card-bottom { border-top-color: #1f2937; }
        .logo-area { border-bottom: 1px solid #e5e7eb; }
        html.dark .logo-area { border-bottom-color: #1f2937; }

        /* ── Instructor sidebar: clean light header ── */
        .ins-sidebar-brand {
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            position: relative;
        }
        html.dark .ins-sidebar-brand {
            background: #1e293b;
            border-bottom-color: #334155;
        }
        .ins-stat-card {
            border-radius: 12px; padding: 12px 14px;
            transition: transform .2s, box-shadow .2s;
        }
        .ins-stat-card:hover { transform: translateY(-2px); box-shadow: 0 8px 20px -8px rgba(0,0,0,.12); }
        html.dark .ins-stat-card:hover { box-shadow: 0 8px 20px -8px rgba(0,0,0,.35); }
        .ins-nav-group { font-size: 10px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; color: #6b7280; padding: 12px 14px 6px; }
        html.dark .ins-nav-group { color: #6b7280; }
        .ins-nav {
            display: flex; align-items: center; gap: 12px;
            padding: 10px 14px; border-radius: 10px; margin: 0 6px;
            font-size: 13px; font-weight: 500; color: #374151;
            transition: all .2s cubic-bezier(0.4,0,0.2,1);
            border: 1px solid transparent;
            position: relative;
        }
        .ins-nav::before {
            content: ''; position: absolute; right: 0; top: 50%; transform: translateY(-50%);
            width: 3px; height: 0; border-radius: 3px 0 0 3px;
            background: #0ea5e9;
            transition: height .2s ease;
        }
        .ins-nav:hover { background: #f8fafc; color: #0f172a; }
        .ins-nav.active { background: #f0f9ff; color: #0284c7; border-color: #bae6fd; }
        .ins-nav.active::before { height: 24px; }
        html.dark .ins-nav { color: #9ca3af; }
        html.dark .ins-nav:hover { background: #1f2937; color: #f1f5f9; }
        html.dark .ins-nav.active { background: #0c4a6e; color: #7dd3fc; border-color: #164e63; }
        html.dark .ins-nav.active::before { background: #38bdf8; }
        .ins-nav .ins-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
            transition: transform .2s;
        }
        .ins-nav:hover .ins-icon { transform: scale(1.05); }
        .ins-nav-badge {
            min-width: 20px; height: 20px; padding: 0 6px;
            border-radius: 10px; font-size: 11px; font-weight: 700;
            display: inline-flex; align-items: center; justify-content: center;
        }
        .ins-user-card {
            background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
            border: 1px solid #e2e8f0; border-radius: 12px;
            padding: 12px 14px; transition: all .2s;
        }
        .ins-user-card:hover { border-color: #cbd5e1; box-shadow: 0 4px 12px -4px rgba(0,0,0,.08); }
        html.dark .ins-user-card { background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border-color: #334155; }
        html.dark .ins-user-card:hover { border-color: #475569; box-shadow: 0 4px 12px -4px rgba(0,0,0,.25); }
    </style>

    @stack('styles')
</head>
<body x-data="{ sidebarOpen: window.innerWidth >= 1024 }"
      x-init="window.addEventListener('resize', () => { sidebarOpen = window.innerWidth >= 1024; })">

<script>
function themeManager() {
    return {
        dark: false,
        init() {
            this.dark = document.documentElement.classList.contains('dark');
        },
        toggle() {
            this.dark = !this.dark;
            document.documentElement.classList.toggle('dark', this.dark);
            document.documentElement.classList.toggle('light', !this.dark);
            localStorage.setItem('theme', this.dark ? 'dark' : 'light');
        }
    };
}
</script>

    <div class="flex h-screen overflow-hidden">
        @auth
            <aside x-show="sidebarOpen || window.innerWidth >= 1024"
                   x-transition:enter="transition ease-out duration-200"
                   x-transition:enter-start="opacity-0 translate-x-full"
                   x-transition:enter-end="opacity-100 translate-x-0"
                   x-transition:leave="transition ease-in duration-150"
                   x-transition:leave-start="opacity-100 translate-x-0"
                   x-transition:leave-end="opacity-0 translate-x-full"
                   class="app-sidebar flex-shrink-0 fixed lg:static inset-y-0 right-0 z-50 lg:z-auto overflow-y-auto">
                @if(auth()->user()->isInstructor() || auth()->user()->isTeacher())
                    @include('layouts.instructor-sidebar')
                @else
                    @include('layouts.student-sidebar')
                @endif
            </aside>

            <div x-show="sidebarOpen && window.innerWidth < 1024"
                 @click="sidebarOpen = false"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-black/40 backdrop-blur-sm z-40 lg:hidden"></div>
        @endauth

        <div class="flex flex-col flex-1 min-w-0">
            @auth
                <header class="app-header flex items-center justify-between px-4 md:px-6 flex-shrink-0 sticky top-0 z-30">
                    <div class="flex items-center gap-3 flex-1 min-w-0">
                        <button @click="sidebarOpen = !sidebarOpen"
                                class="lg:hidden h-btn flex-shrink-0">
                            <i class="fas fa-bars text-sm"></i>
                        </button>

                        <div class="hidden md:flex items-center flex-1 max-w-md">
                            <div class="search-box flex items-center gap-2 w-full">
                                <i class="fas fa-search text-gray-400 dark:text-gray-500 text-xs"></i>
                                <input type="text" placeholder="{{ __('common.nav_search_placeholder_long') }}" class="flex-1 bg-transparent border-none outline-none text-sm text-gray-700 dark:text-gray-300 placeholder-gray-400 dark:placeholder-gray-500 min-w-0">
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-2">
                        {{-- Theme toggle --}}
                        <div x-data="themeManager()" x-init="init()">
                            <button @click="toggle()" type="button" class="h-btn"
                                    :title="dark ? '{{ $appRtl ? 'الوضع النهاري' : 'Light mode' }}' : '{{ $appRtl ? 'الوضع الليلي' : 'Dark mode' }}'">
                                <i class="text-sm" :class="dark ? 'fas fa-sun text-amber-400' : 'fas fa-moon text-gray-400'"></i>
                            </button>
                        </div>

                        {{-- Notifications --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="h-btn relative">
                                <i class="fas fa-bell text-sm"></i>
                                <span class="n-badge">3</span>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute left-0 mt-2 w-80 dd-menu z-50">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100">{{ $appRtl ? 'الإشعارات' : 'Notifications' }}</h3>
                                </div>
                                <div class="p-6 text-center text-gray-400 dark:text-gray-500 text-sm">
                                    <i class="fas fa-bell-slash text-xl mb-2 block"></i>
                                    <p>{{ $appRtl ? 'لا توجد إشعارات جديدة' : 'No new notifications' }}</p>
                                </div>
                            </div>
                        </div>

                        {{-- User menu --}}
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-2 p-1.5 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-800 transition-colors">
                                <div class="u-avatar flex-shrink-0">
                                    @if(auth()->user()->profile_image)
                                        <img src="{{ auth()->user()->profile_image_url }}" alt="">
                                    @else
                                        {{ mb_substr(auth()->user()->name, 0, 1) }}
                                    @endif
                                </div>
                                <span class="hidden lg:block text-sm font-medium text-gray-700 dark:text-gray-300 max-w-[120px] truncate">{{ auth()->user()->name }}</span>
                                <i class="fas fa-chevron-down text-[10px] text-gray-400 hidden lg:block transition-transform" :class="{ 'rotate-180': open }"></i>
                            </button>
                            <div x-show="open" @click.away="open = false" x-transition
                                 class="absolute left-0 mt-2 w-56 dd-menu z-50">
                                <div class="px-4 py-3 border-b border-gray-100 dark:border-gray-700">
                                    <p class="text-sm font-semibold text-gray-900 dark:text-gray-100 truncate">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 truncate mt-0.5">{{ auth()->user()->email ?? '—' }}</p>
                                </div>
                                <div class="p-1.5">
                                    @php
                                        $profileRoute = (auth()->user()->isInstructor() || auth()->user()->isTeacher() || in_array(strtolower(auth()->user()->role ?? ''), ['instructor', 'teacher'])) ? route('instructor.profile') : route('profile');
                                    @endphp
                                    <a href="{{ $profileRoute }}" class="dd-item px-3 py-2 rounded-lg text-sm text-gray-700 dark:text-gray-300 gap-2.5">
                                        <i class="fas fa-user text-gray-400 text-xs w-4"></i>
                                        {{ $appRtl ? 'الملف الشخصي' : 'Profile' }}
                                    </a>
                                    <a href="{{ route('settings') }}" class="dd-item px-3 py-2 rounded-lg text-sm text-gray-700 dark:text-gray-300 gap-2.5">
                                        <i class="fas fa-cog text-gray-400 text-xs w-4"></i>
                                        {{ $appRtl ? 'الإعدادات' : 'Settings' }}
                                    </a>
                                    <hr class="my-1.5 border-gray-100 dark:border-gray-700">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="w-full dd-item px-3 py-2 rounded-lg text-sm text-red-600 dark:text-red-400 gap-2.5 text-right">
                                            <i class="fas fa-sign-out-alt text-xs w-4"></i>
                                            {{ $appRtl ? 'تسجيل الخروج' : 'Sign out' }}
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </header>
            @endauth

            <main class="flex-1 overflow-auto bg-surface-50 dark:bg-navy-950">
                <div class="p-4 md:p-6 lg:p-8 w-full max-w-full">
                    @if(session('success'))
                        <div class="mb-5 flex items-center gap-3 bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 text-emerald-700 dark:text-emerald-300 px-4 py-3 rounded-xl text-sm">
                            <i class="fas fa-check-circle flex-shrink-0"></i>
                            {{ session('success') }}
                        </div>
                    @endif
                    @if(session('error'))
                        <div class="mb-5 flex items-center gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 text-red-700 dark:text-red-300 px-4 py-3 rounded-xl text-sm">
                            <i class="fas fa-exclamation-circle flex-shrink-0"></i>
                            {{ session('error') }}
                        </div>
                    @endif
                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>

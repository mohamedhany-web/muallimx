@php $locale = app()->getLocale(); $rtl = $locale === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('public.community_heading')) - {{ config('app.name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Tajawal', 'Cairo', sans-serif; }
        [x-cloak] { display: none !important; }
    </style>
    @stack('styles')
</head>
<body class="bg-slate-50 text-gray-900 min-h-screen" x-data="{ sidebarOpen: false, isLg: false }" x-init="
    isLg = window.matchMedia('(min-width: 1024px)').matches;
    window.matchMedia('(min-width: 1024px)').addEventListener('change', e => { isLg = e.matches });
">
    <div class="flex min-h-screen">
        <!-- Backdrop للموبايل عند فتح السايدبار -->
        <div x-show="sidebarOpen && !isLg" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 bg-black/50 z-40 lg:hidden"
             @click="sidebarOpen = false"
             x-cloak></div>

        <!-- Sidebar: على الموبايل درج من الجانب، على الديسكتوب ثابت -->
        <aside class="flex flex-col w-64 fixed inset-y-0 z-50 bg-slate-900 border-{{ $rtl ? 'l' : 'r' }} border-slate-700/50 transition-transform duration-300 ease-out {{ $rtl ? 'right-0' : 'left-0' }}"
               :class="(sidebarOpen || isLg) ? 'translate-x-0' : '{{ $rtl ? 'translate-x-full' : '-translate-x-full' }}'"
               x-show="sidebarOpen || isLg"
               x-cloak>
            <div class="p-4 border-b border-slate-700/50 flex items-center justify-between">
                <a href="{{ route('community.dashboard') }}" class="flex items-center gap-2 text-white font-bold" @click="if(!isLg) sidebarOpen = false">
                    <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                        <i class="fas fa-database text-white"></i>
                    </span>
                    <span>{{ __('public.community_heading') }}</span>
                </a>
                <button type="button" @click="sidebarOpen = false" class="lg:hidden p-2 rounded-lg text-slate-400 hover:text-white hover:bg-slate-800" aria-label="إغلاق القائمة">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            <nav class="flex-1 p-3 space-y-0.5 overflow-y-auto">
                <a href="{{ route('community.dashboard') }}" @click="if(!isLg) sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('community.dashboard') ? 'bg-slate-700/80 text-white' : '' }}">
                    <i class="fas fa-home w-5"></i>
                    <span>{{ __('auth.home') }}</span>
                </a>
                <a href="{{ route('community.competitions.index') }}" @click="if(!isLg) sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('community.competitions.*') ? 'bg-slate-700/80 text-white' : '' }}">
                    <i class="fas fa-trophy w-5"></i>
                    <span>{{ __('admin.community_competitions') }}</span>
                </a>
                <a href="{{ route('community.datasets.index') }}" @click="if(!isLg) sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('community.datasets.*') ? 'bg-slate-700/80 text-white' : '' }}">
                    <i class="fas fa-database w-5"></i>
                    <span>{{ __('admin.community_datasets') }}</span>
                </a>
                <a href="{{ route('community.discussions.index') }}" @click="if(!isLg) sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('community.discussions.*') ? 'bg-slate-700/80 text-white' : '' }}">
                    <i class="fas fa-comments w-5"></i>
                    <span>{{ __('admin.community_discussions') }}</span>
                </a>
                @if(auth()->user()->is_community_contributor ?? false)
                <a href="{{ route('community.contributor.dashboard') }}" @click="if(!isLg) sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-cyan-300 hover:bg-slate-800 hover:text-white {{ request()->routeIs('community.contributor.*') ? 'bg-cyan-600/30 text-white' : '' }}">
                    <i class="fas fa-user-edit w-5"></i>
                    <span>لوحة المساهم</span>
                </a>
                @endif
                <a href="{{ route('public.courses') }}" @click="if(!isLg) sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-300 hover:bg-slate-800 hover:text-white">
                    <i class="fas fa-book w-5"></i>
                    <span>{{ __('landing.nav.courses') }}</span>
                </a>
            </nav>
            <div class="p-3 border-t border-slate-700/50">
                <a href="{{ route('dashboard') }}" @click="if(!isLg) sidebarOpen = false" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-white text-sm">
                    <i class="fas fa-external-link-alt w-4"></i>
                    <span>لوحة المنصة الرئيسية</span>
                </a>
                <form method="POST" action="{{ route('logout') }}" class="mt-1" @submit="if(!isLg) sidebarOpen = false">
                    @csrf
                    <button type="submit" class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-slate-400 hover:bg-slate-800 hover:text-red-300 w-full text-sm">
                        <i class="fas fa-sign-out-alt w-4"></i>
                        <span>{{ __('auth.logout') }}</span>
                    </button>
                </form>
            </div>
        </aside>

        <!-- منطقة المحتوى الرئيسية ـ نفس مساحات لوحة الطالب مع هامش للشريط الثابت -->
        <div class="flex flex-col flex-1 min-w-0 {{ $rtl ? 'lg:mr-64' : 'lg:ml-64' }}">
            <!-- Top bar ـ نفس هامش الهيدر في لوحة الطالب -->
            <header class="flex-shrink-0 sticky top-0 z-20 bg-white border-b border-slate-200 px-4 sm:px-6 lg:px-8 py-3 flex items-center justify-between gap-4">
                <div class="flex items-center gap-3 flex-1 min-w-0">
                    <button type="button" @click="sidebarOpen = true" class="lg:hidden flex-shrink-0 p-2.5 rounded-xl text-slate-600 hover:bg-slate-100 hover:text-slate-900" aria-label="فتح القائمة">
                        <i class="fas fa-bars text-xl"></i>
                    </button>
                    <div class="flex-1 max-w-xl">
                        <div class="flex items-center gap-2 px-3 py-2 rounded-xl bg-slate-100 text-slate-500 text-sm">
                            <i class="fas fa-search"></i>
                            <span>بحث (قريباً)</span>
                        </div>
                    </div>
                </div>
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="flex items-center gap-2 p-2 rounded-xl hover:bg-slate-100">
                        @if(auth()->user()->profile_image ?? null)
                            <img src="{{ auth()->user()->profile_image_url ?? '#' }}" alt="" class="w-8 h-8 rounded-full object-cover">
                        @else
                            <span class="w-8 h-8 rounded-full bg-cyan-600 text-white flex items-center justify-center font-bold text-sm">{{ mb_substr(auth()->user()->name ?? 'م', 0, 1) }}</span>
                        @endif
                        <span class="hidden sm:inline font-semibold text-slate-700">{{ auth()->user()->name }}</span>
                        <i class="fas fa-chevron-down text-slate-500 text-xs"></i>
                    </button>
                    <div x-show="open" @click.outside="open = false" x-transition class="absolute top-full mt-1 {{ $rtl ? 'right-0' : 'left-0' }} w-48 py-1 bg-white rounded-xl shadow-xl border border-slate-200">
                        <a href="{{ route('community.dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">{{ __('auth.dashboard') }}</a>
                        <a href="{{ route('dashboard') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-50">المنصة الرئيسية</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full text-right px-4 py-2 text-sm text-red-600 hover:bg-slate-50">{{ __('auth.logout') }}</button>
                        </form>
                    </div>
                </div>
            </header>

            <!-- Main ـ نفس بنية لوحة الطالب: خلفية وحشو -->
            <main class="flex-1 overflow-auto bg-gray-50 min-w-0 w-full">
                <div class="w-full max-w-full p-4 sm:p-6 lg:p-8">
                    @yield('content')
                </div>
            </main>
        </div>
    </div>
    @stack('scripts')
</body>
</html>

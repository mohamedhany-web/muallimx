@php
    $user = auth()->user();
@endphp
<div class="flex flex-col h-full bg-gradient-to-b from-slate-900 via-slate-800 to-slate-900 text-white dark:from-slate-950 dark:via-slate-900 dark:to-slate-950">
    <!-- Logo/Brand -->
    <div class="flex items-center justify-center h-16 px-4 border-b border-slate-700/50 dark:border-slate-600/50">
        <a href="{{ route('employee.dashboard') }}" class="flex items-center gap-2">
            <div class="w-10 h-10 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl flex items-center justify-center shadow-lg">
                <i class="fas fa-briefcase text-white text-lg"></i>
            </div>
            <span class="text-lg font-bold text-white">MuallimX</span>
        </a>
    </div>

    <!-- التنقل: الموظف يستقبل مهام فقط -->
    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-4 py-6 space-y-2 employee-sidebar-nav">
        <a href="{{ route('employee.dashboard') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('employee.dashboard') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white dark:text-slate-400 dark:hover:bg-slate-700/50' }}"
           @click="if (window.innerWidth < 1024) { $dispatch('close-sidebar'); }">
            <i class="fas fa-home text-base"></i>
            <span>لوحة التحكم</span>
        </a>

        <a href="{{ route('employee.tasks.index') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('employee.tasks.*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white dark:text-slate-400 dark:hover:bg-slate-700/50' }}"
           @click="if (window.innerWidth < 1024) { $dispatch('close-sidebar'); }">
            <i class="fas fa-tasks text-base"></i>
            <span>مهامي</span>
        </a>

        <div class="border-t border-slate-700/50 dark:border-slate-600/50 my-4"></div>

        <a href="{{ route('employee.profile') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('employee.profile*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white dark:text-slate-400 dark:hover:bg-slate-700/50' }}"
           @click="if (window.innerWidth < 1024) { $dispatch('close-sidebar'); }">
            <i class="fas fa-user text-base"></i>
            <span>الملف الشخصي</span>
        </a>

        <a href="{{ route('employee.notifications') }}"
           class="flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-semibold transition-all duration-200 {{ request()->routeIs('employee.notifications*') ? 'bg-blue-600 text-white shadow-lg' : 'text-slate-300 hover:bg-slate-700/50 hover:text-white dark:text-slate-400 dark:hover:bg-slate-700/50' }}"
           @click="if (window.innerWidth < 1024) { $dispatch('close-sidebar'); }">
            <i class="fas fa-bell text-base"></i>
            <span>الإشعارات</span>
            @php
                try {
                    $unreadCount = $user->notifications()->whereNull('read_at')->count();
                } catch (\Exception $e) {
                    $unreadCount = 0;
                }
            @endphp
            @if($unreadCount > 0)
                <span class="mr-auto bg-red-500 text-white text-xs font-bold rounded-full px-2 py-0.5 min-w-[20px] text-center">{{ $unreadCount > 99 ? '99+' : $unreadCount }}</span>
            @endif
        </a>
    </nav>

    <!-- User Info -->
    <div class="border-t border-slate-700/50 dark:border-slate-600/50 p-4">
        <div class="flex items-center gap-3 mb-3">
            @php $profileImage = $user->profile_image_url ?? null; @endphp
            @if($profileImage)
                <img src="{{ $profileImage }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full object-cover border-2 border-blue-400 flex-shrink-0">
            @else
                <div class="w-10 h-10 bg-blue-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                    {{ mb_substr($user->name, 0, 1, 'UTF-8') }}
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-white truncate">{{ $user->name }}</p>
                <p class="text-xs text-slate-400 truncate">{{ optional($user->employeeJob)->name ?? 'موظف' }}</p>
            </div>
        </div>
        <form method="POST" action="{{ route('logout') }}" class="w-full">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-2 bg-slate-700/50 hover:bg-slate-700 dark:bg-slate-600/50 dark:hover:bg-slate-600 text-white text-sm font-semibold rounded-xl transition-colors">
                <i class="fas fa-sign-out-alt"></i>
                <span>تسجيل الخروج</span>
            </button>
        </form>
    </div>
</div>

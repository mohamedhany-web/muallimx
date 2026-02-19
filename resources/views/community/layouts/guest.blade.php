@php $locale = app()->getLocale(); $rtl = $locale === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $rtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', __('auth.login')) - {{ __('public.community_heading') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { font-family: 'Tajawal', 'Cairo', sans-serif; }
        body { background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #0f172a 100%); min-height: 100vh; }
    </style>
</head>
<body class="min-h-screen flex flex-col text-gray-100">
    <header class="flex-shrink-0 py-4 px-4 sm:px-6">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <a href="{{ route('public.community.index') }}" class="flex items-center gap-2 text-white font-bold text-lg hover:opacity-90">
                <span class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-500 to-blue-600 flex items-center justify-center">
                    <i class="fas fa-database text-white"></i>
                </span>
                <span>{{ __('public.community_heading') }}</span>
            </a>
            <a href="{{ route('home') }}" class="text-sm text-slate-300 hover:text-white">{{ __('auth.home') }}</a>
        </div>
    </header>
    <main class="flex-1 flex items-center justify-center p-4 sm:p-6">
        @yield('content')
    </main>
    <footer class="flex-shrink-0 py-3 text-center text-slate-400 text-sm">
        @yield('footer', '')
    </footer>
</body>
</html>

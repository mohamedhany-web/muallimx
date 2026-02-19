@extends('community.layouts.guest')

@section('title', __('auth.login'))

@section('content')
<div class="w-full max-w-md">
    <div class="bg-slate-800/90 backdrop-blur border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8">
        <h1 class="text-2xl font-black text-white mb-1">{{ __('auth.login') }}</h1>
        <p class="text-slate-400 text-sm mb-6">{{ __('auth.enter_credentials') }}</p>

        @if($errors->any())
            <div class="mb-4 p-3 rounded-xl bg-red-500/20 border border-red-500/50 text-red-200 text-sm">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('community.login.post') }}" class="space-y-4">
            @csrf
            <input type="text" name="website" value="" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-300 mb-1">{{ __('auth.email') }}</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                       class="w-full px-4 py-3 rounded-xl bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-300 mb-1">{{ __('auth.password') }}</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30">
            </div>
            <div class="flex items-center gap-2">
                <input type="checkbox" name="remember" id="remember" class="rounded border-slate-600 bg-slate-700 text-cyan-500 focus:ring-cyan-500">
                <label for="remember" class="text-sm text-slate-400">{{ __('auth.remember') }}</label>
            </div>
            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold hover:from-cyan-600 hover:to-blue-700 transition-all shadow-lg">
                {{ __('auth.login') }}
            </button>
        </form>

        <p class="mt-6 text-center text-slate-400 text-sm">
            {{ __('auth.no_account_question') }}
            <a href="{{ route('community.register') }}" class="text-cyan-400 font-bold hover:text-cyan-300">{{ __('auth.no_account_register_now') }}</a>
        </p>
        <p class="mt-2 text-center text-slate-500 text-xs">
            <a href="{{ route('login') }}" class="hover:text-slate-400">تسجيل الدخول من المنصة الرئيسية</a>
        </p>
    </div>
</div>
@endsection

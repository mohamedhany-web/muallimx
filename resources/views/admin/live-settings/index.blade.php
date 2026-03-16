@extends('layouts.admin')
@section('title', 'إعدادات نظام البث المباشر')

@section('content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><i class="fas fa-sliders-h text-violet-500 ml-2"></i>إعدادات نظام البث المباشر</h1>
        <p class="text-sm text-slate-500 mt-1">تكوين إعدادات Jitsi والبث العامة</p>
    </div>

    @if(session('success'))
    <div class="bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800 rounded-xl p-4 text-emerald-700 dark:text-emerald-400 text-sm">
        <i class="fas fa-check-circle ml-1"></i> {{ session('success') }}
    </div>
    @endif

    <form method="POST" action="{{ route('admin.live-settings.update') }}" class="space-y-6">
        @csrf

        @php $index = 0; @endphp
        @foreach($settings as $group => $items)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6">
            <h2 class="font-bold text-slate-800 dark:text-white mb-4 flex items-center gap-2">
                @if($group === 'general')
                    <i class="fas fa-cog text-slate-400"></i> إعدادات عامة
                @elseif($group === 'jitsi')
                    <i class="fas fa-video text-blue-400"></i> إعدادات Jitsi
                @elseif($group === 'access')
                    <i class="fas fa-lock text-amber-400"></i> صلاحيات الدخول
                @elseif($group === 'room')
                    <i class="fas fa-door-open text-emerald-400"></i> إعدادات الغرفة
                @else
                    <i class="fas fa-cog text-slate-400"></i> {{ $group }}
                @endif
            </h2>
            <div class="space-y-4">
                @foreach($items as $setting)
                <div class="flex items-center justify-between gap-4">
                    <input type="hidden" name="settings[{{ $index }}][key]" value="{{ $setting->key }}">
                    <label class="text-sm font-medium text-slate-700 dark:text-slate-300 flex-1">{{ $setting->label ?? $setting->key }}</label>
                    @if($setting->type === 'boolean')
                        <select name="settings[{{ $index }}][value]" class="w-28 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm">
                            <option value="1" {{ $setting->value ? 'selected' : '' }}>نعم</option>
                            <option value="0" {{ !$setting->value ? 'selected' : '' }}>لا</option>
                        </select>
                    @elseif($setting->type === 'integer')
                        <input type="number" name="settings[{{ $index }}][value]" value="{{ $setting->value }}" class="w-32 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm">
                    @else
                        <input type="text" name="settings[{{ $index }}][value]" value="{{ $setting->value }}" class="w-64 rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white text-sm" placeholder="—">
                    @endif
                </div>
                @php $index++; @endphp
                @endforeach
            </div>
        </div>
        @endforeach

        <button type="submit" class="px-6 py-2.5 bg-violet-600 hover:bg-violet-700 text-white rounded-xl font-semibold shadow-lg shadow-violet-500/25 transition-all">
            <i class="fas fa-save ml-1"></i> حفظ الإعدادات
        </button>
    </form>
</div>
@endsection

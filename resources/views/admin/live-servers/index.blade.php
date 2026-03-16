@extends('layouts.admin')
@section('title', 'سيرفرات البث')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><i class="fas fa-server text-cyan-500 ml-2"></i>سيرفرات البث (VPS)</h1>
            <p class="text-sm text-slate-500 mt-1">إدارة سيرفرات البث المباشر المتاحة</p>
        </div>
        <a href="{{ route('admin.live-servers.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-cyan-500 hover:bg-cyan-600 text-white rounded-xl font-semibold shadow-lg shadow-cyan-500/25 transition-all">
            <i class="fas fa-plus"></i> إضافة سيرفر
        </a>
    </div>

    <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($servers as $server)
        <div class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-5 space-y-4">
            <div class="flex items-start justify-between">
                <div>
                    <h3 class="font-bold text-slate-800 dark:text-white">{{ $server->name }}</h3>
                    <p class="text-sm text-slate-400 font-mono mt-0.5">{{ $server->domain }}</p>
                </div>
                @if($server->status === 'active')
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 dark:bg-emerald-900/40 text-emerald-600 text-xs font-medium">نشط</span>
                @elseif($server->status === 'maintenance')
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 dark:bg-amber-900/40 text-amber-600 text-xs font-medium">صيانة</span>
                @else
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 dark:bg-slate-600 text-slate-500 text-xs font-medium">معطل</span>
                @endif
            </div>

            <div class="space-y-2 text-sm">
                <div class="flex justify-between"><span class="text-slate-500">النوع:</span><span class="font-medium text-slate-700 dark:text-slate-300">{{ ucfirst($server->provider) }}</span></div>
                @if($server->ip_address)
                <div class="flex justify-between"><span class="text-slate-500">IP:</span><span class="font-mono text-xs text-slate-600 dark:text-slate-400">{{ $server->ip_address }}</span></div>
                @endif
                <div class="flex justify-between"><span class="text-slate-500">الجلسات النشطة:</span><span class="font-bold text-slate-700 dark:text-white">{{ $server->active_sessions_count }}</span></div>
                <div class="flex justify-between"><span class="text-slate-500">الحمل:</span><span>{{ $server->current_load }}/{{ $server->max_participants }}</span></div>
            </div>

            <div class="w-full bg-slate-200 dark:bg-slate-700 rounded-full h-2">
                <div class="h-2 rounded-full transition-all {{ $server->load_percentage > 80 ? 'bg-red-500' : ($server->load_percentage > 50 ? 'bg-amber-500' : 'bg-emerald-500') }}" style="width: {{ $server->load_percentage }}%"></div>
            </div>

            <div class="flex items-center gap-2 pt-2 border-t border-slate-100 dark:border-slate-700">
                <a href="{{ route('admin.live-servers.edit', $server) }}" class="flex-1 text-center px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-medium hover:bg-slate-200 transition-colors">تعديل</a>
                <form method="POST" action="{{ route('admin.live-servers.toggle-status', $server) }}" class="flex-1">
                    @csrf
                    <button class="w-full px-3 py-1.5 bg-slate-100 dark:bg-slate-700 text-slate-600 dark:text-slate-300 rounded-lg text-xs font-medium hover:bg-slate-200 transition-colors">
                        {{ $server->status === 'active' ? 'إيقاف' : 'تفعيل' }}
                    </button>
                </form>
                <form method="POST" action="{{ route('admin.live-servers.destroy', $server) }}" onsubmit="return confirm('حذف السيرفر؟')">
                    @csrf @method('DELETE')
                    <button class="px-3 py-1.5 bg-red-50 dark:bg-red-900/20 text-red-500 rounded-lg text-xs font-medium hover:bg-red-100 transition-colors">حذف</button>
                </form>
            </div>
        </div>
        @empty
        <div class="md:col-span-3 text-center py-12">
            <i class="fas fa-server text-4xl text-slate-300 dark:text-slate-600 mb-3"></i>
            <p class="text-slate-500">لا توجد سيرفرات بث بعد</p>
            <a href="{{ route('admin.live-servers.create') }}" class="inline-flex items-center gap-2 mt-3 px-4 py-2 bg-cyan-500 text-white rounded-lg text-sm font-medium hover:bg-cyan-600 transition-colors"><i class="fas fa-plus"></i> أضف أول سيرفر</a>
        </div>
        @endforelse
    </div>
</div>
@endsection

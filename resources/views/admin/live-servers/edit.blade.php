@extends('layouts.admin')
@section('title', 'تعديل سيرفر: ' . $liveServer->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('admin.live-servers.index') }}" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors"><i class="fas fa-arrow-right"></i></a>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><i class="fas fa-edit text-amber-500 ml-2"></i>تعديل سيرفر البث</h1>
    </div>

    <form method="POST" action="{{ route('admin.live-servers.update', $liveServer) }}" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
        @csrf @method('PUT')
        <div class="grid md:grid-cols-2 gap-5">
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">اسم السيرفر</label>
                <input type="text" name="name" value="{{ old('name', $liveServer->name) }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">النطاق</label>
                <input type="text" name="domain" value="{{ old('domain', $liveServer->domain) }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">النوع</label>
                <select name="provider" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <option value="jitsi" {{ $liveServer->provider === 'jitsi' ? 'selected' : '' }}>Jitsi Meet</option>
                    <option value="custom" {{ $liveServer->provider === 'custom' ? 'selected' : '' }}>مخصص</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الحالة</label>
                <select name="status" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <option value="active" {{ $liveServer->status === 'active' ? 'selected' : '' }}>نشط</option>
                    <option value="inactive" {{ $liveServer->status === 'inactive' ? 'selected' : '' }}>معطل</option>
                    <option value="maintenance" {{ $liveServer->status === 'maintenance' ? 'selected' : '' }}>صيانة</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">IP</label>
                <input type="text" name="ip_address" value="{{ old('ip_address', $liveServer->ip_address) }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الحد الأقصى</label>
                <input type="number" name="max_participants" value="{{ old('max_participants', $liveServer->max_participants) }}" min="2" max="10000" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
            </div>
            <div class="md:col-span-2">
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">ملاحظات</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">{{ old('notes', $liveServer->notes) }}</textarea>
            </div>
        </div>
        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-blue-600 hover:bg-blue-700 text-white rounded-xl font-semibold transition-colors"><i class="fas fa-save ml-1"></i> حفظ</button>
            <a href="{{ route('admin.live-servers.index') }}" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-300 transition-colors">إلغاء</a>
        </div>
    </form>
</div>
@endsection

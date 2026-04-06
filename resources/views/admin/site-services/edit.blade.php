@extends('layouts.admin')
@section('title', 'تعديل خدمة')
@section('header', 'تعديل خدمة')
@section('content')
<div class="w-full">
    <div class="rounded-3xl bg-white/95 backdrop-blur border border-slate-200 shadow-lg overflow-hidden">
        <div class="px-5 py-6 sm:px-8 border-b border-slate-200 flex flex-wrap items-center justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-slate-900">تعديل: {{ $siteService->name }}</h1>
                <p class="text-slate-500 mt-1 text-sm">معاينة: <a href="{{ route('public.services.show', $siteService) }}" target="_blank" rel="noopener" class="text-sky-600 hover:underline">/services/{{ $siteService->slug }}</a></p>
            </div>
        </div>
        <form action="{{ route('admin.site-services.update', $siteService) }}" method="POST" class="p-5 sm:p-8 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">اسم الخدمة <span class="text-rose-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $siteService->name) }}" required maxlength="255"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">
                @error('name')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">الرابط في المتصفح (اختياري)</label>
                <input type="text" name="slug" value="{{ old('slug', $siteService->slug) }}" dir="ltr"
                       class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500 font-mono text-sm">
                <p class="mt-1 text-xs text-slate-500">اتركه فارغاً لإعادة توليد الرابط من الاسم.</p>
                @error('slug')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">مقدمة قصيرة</label>
                <textarea name="summary" rows="3" maxlength="2000"
                          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">{{ old('summary', $siteService->summary) }}</textarea>
                @error('summary')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-2">تفاصيل الخدمة <span class="text-rose-500">*</span></label>
                <textarea name="body" rows="12" required
                          class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/30 focus:border-sky-500">{{ old('body', $siteService->body) }}</textarea>
                @error('body')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">ترتيب العرض</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $siteService->sort_order) }}" min="0"
                           class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/30">
                    @error('sort_order')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
                <div class="flex items-end pb-1">
                    <input type="hidden" name="is_active" value="0">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked((string) old('is_active', $siteService->is_active ? '1' : '0') === '1')
                               class="rounded border-slate-300 text-sky-500 focus:ring-sky-500">
                        <span class="text-sm font-semibold text-slate-700">نشط ويظهر في الموقع</span>
                    </label>
                </div>
            </div>
            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold">
                    <i class="fas fa-save"></i> حفظ التعديلات
                </button>
                <a href="{{ route('admin.site-services.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection

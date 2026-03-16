@extends('layouts.admin')

@section('title', $category ? 'تعديل التصنيف' : 'إضافة تصنيف')
@section('header', $category ? 'تعديل التصنيف' : 'إضافة تصنيف')

@section('content')
<div class="max-w-2xl">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
        <form action="{{ $category ? route('admin.curriculum-library.categories.update', $category) : route('admin.curriculum-library.categories.store') }}" method="POST" class="space-y-4">
            @csrf
            @if($category) @method('PUT') @endif

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">اسم التصنيف</label>
                <input type="text" name="name" value="{{ old('name', $category?->name) }}" required
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
                @error('name') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">الرابط (slug) — اختياري</label>
                <input type="text" name="slug" value="{{ old('slug', $category?->slug) }}"
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                @error('slug') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">الوصف</label>
                <textarea name="description" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">{{ old('description', $category?->description) }}</textarea>
            </div>
            <div class="flex gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">ترتيب العرض</label>
                    <input type="number" name="order" value="{{ old('order', $category?->order ?? 0) }}" min="0" class="w-24 px-3 py-2 rounded-lg border border-slate-200">
                </div>
                <div class="flex items-center gap-2 pt-6">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $category?->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm font-semibold text-slate-700">نشط</label>
                </div>
            </div>
            <div class="flex gap-2 pt-4">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">{{ $category ? 'حفظ التعديلات' : 'إضافة' }}</button>
                <a href="{{ route('admin.curriculum-library.categories') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

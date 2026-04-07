@extends('layouts.admin')
@section('title', 'تعديل رأي')
@section('header', 'تعديل رأي')
@section('content')
@php
    $t = $siteTestimonial;
    $oldType = old('content_type', $t->content_type);
@endphp
<div class="w-full" x-data="{ type: '{{ $oldType }}' }">
    <div class="rounded-3xl bg-white/95 backdrop-blur border border-slate-200 shadow-lg overflow-hidden">
        <div class="px-5 py-6 sm:px-8 border-b border-slate-200">
            <h1 class="text-xl font-bold text-slate-900">تعديل رأي</h1>
        </div>
        <form action="{{ route('admin.site-testimonials.update', $t) }}" method="POST" enctype="multipart/form-data" class="p-5 sm:p-8 space-y-6">
            @csrf
            @method('PUT')
            <div>
                <span class="block text-sm font-semibold text-slate-700 mb-2">نوع العرض <span class="text-rose-500">*</span></span>
                <div class="flex flex-wrap gap-4">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="content_type" value="text" x-model="type" class="text-sky-600 focus:ring-sky-500">
                        <span>نص</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="radio" name="content_type" value="image" x-model="type" class="text-sky-600 focus:ring-sky-500">
                        <span>صورة</span>
                    </label>
                </div>
                @error('content_type')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
            </div>

            <template x-if="type === 'text'">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">نص الرأي <span class="text-rose-500">*</span></label>
                    <textarea name="body" rows="6" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl focus:ring-2 focus:ring-sky-500/30">{{ old('body', $t->body) }}</textarea>
                    @error('body')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                </div>
            </template>

            <template x-if="type === 'image'">
                <div class="space-y-4">
                    @if($t->publicImageUrl())
                        <div class="rounded-xl border border-slate-200 overflow-hidden max-w-md">
                            <img src="{{ $t->publicImageUrl() }}" alt="" class="w-full h-auto max-h-56 object-contain bg-slate-50">
                        </div>
                    @endif
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">استبدال الصورة (اختياري)</label>
                        <input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif"
                               class="block w-full text-sm text-slate-600 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-violet-50 file:text-violet-700">
                        @error('image')<p class="mt-1 text-sm text-rose-600">{{ $message }}</p>@enderror
                        @if($t->image_path)
                            <input type="hidden" name="remove_image" value="0">
                            <label class="mt-3 inline-flex items-center gap-2 cursor-pointer text-sm text-rose-700">
                                <input type="checkbox" name="remove_image" value="1" class="rounded border-slate-300 text-rose-600">
                                <span>حذف الصورة الحالية (يلزمك رفع صورة جديدة)</span>
                            </label>
                        @endif
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-2">وصف تحت الصورة (اختياري)</label>
                        <textarea name="body" rows="2" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl">{{ old('body', $t->body) }}</textarea>
                    </div>
                </div>
            </template>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">اسم صاحب الرأي</label>
                    <input type="text" name="author_name" value="{{ old('author_name', $t->author_name) }}" maxlength="190" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">المسمى (اختياري)</label>
                    <input type="text" name="role_label" value="{{ old('role_label', $t->role_label) }}" maxlength="190" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl">
                </div>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">ترتيب العرض</label>
                    <input type="number" name="sort_order" value="{{ old('sort_order', $t->sort_order) }}" min="0" class="w-full px-4 py-2.5 border border-slate-200 rounded-xl">
                </div>
                <div class="flex flex-col gap-3 justify-end pb-1">
                    <input type="hidden" name="is_active" value="0">
                    <input type="hidden" name="is_featured" value="0">
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $t->is_active ? '1' : '0') === '1') class="rounded border-slate-300 text-sky-600">
                        <span class="text-sm font-semibold text-slate-700">نشط</span>
                    </label>
                    <label class="inline-flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $t->is_featured ? '1' : '0') === '1') class="rounded border-slate-300 text-amber-600">
                        <span class="text-sm font-semibold text-slate-700">بطاقة مميزة</span>
                    </label>
                </div>
            </div>

            <div class="flex flex-wrap gap-3 pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-semibold">
                    <i class="fas fa-save"></i> حفظ
                </button>
                <a href="{{ route('admin.site-testimonials.index') }}" class="inline-flex items-center gap-2 px-6 py-2.5 rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 font-semibold">رجوع</a>
            </div>
        </form>
    </div>
</div>
@endsection

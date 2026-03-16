@extends('layouts.admin')

@section('title', $item ? 'تعديل عنصر المنهج' : 'إضافة عنصر منهج')
@section('header', $item ? 'تعديل عنصر المنهج' : 'إضافة عنصر منهج')

@section('content')
<div class="max-w-4xl">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
        <form action="{{ $item ? route('admin.curriculum-library.items.update', $item) : route('admin.curriculum-library.items.store') }}" method="POST" class="space-y-4">
            @csrf
            @if($item) @method('PUT') @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1">العنوان</label>
                    <input type="text" name="title" value="{{ old('title', $item?->title) }}" required
                           class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                    @error('title') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">التصنيف</label>
                    <select name="category_id" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                        <option value="">— بدون تصنيف —</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ old('category_id', $item?->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">الرابط (slug) — اختياري</label>
                    <input type="text" name="slug" value="{{ old('slug', $item?->slug) }}" class="w-full px-3 py-2 rounded-lg border border-slate-200">
                    @error('slug') <p class="text-rose-600 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">المادة / التخصص</label>
                    <input type="text" name="subject" value="{{ old('subject', $item?->subject) }}" placeholder="مثال: رياضيات، لغة عربية"
                           class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">المرحلة الدراسية</label>
                    <input type="text" name="grade_level" value="{{ old('grade_level', $item?->grade_level) }}" placeholder="مثال: ابتدائي، أول ثانوي"
                           class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $item?->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm font-semibold text-slate-700">نشط (يظهر للمعلمين)</label>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">ترتيب العرض</label>
                    <input type="number" name="order" value="{{ old('order', $item?->order ?? 0) }}" min="0" class="w-24 px-3 py-2 rounded-lg border border-slate-200">
                </div>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">وصف مختصر</label>
                <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500" placeholder="ملخص يظهر في قائمة المكتبة">{{ old('description', $item?->description) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">المحتوى (تفصيلي — يدعم HTML)</label>
                <textarea name="content" rows="14" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="المحتوى التفاعلي أو التعليمي للدرس/الوحدة...">{{ old('content', $item?->content) }}</textarea>
                <p class="text-xs text-slate-500 mt-1">يمكنك استخدام HTML لعناوين، قوائم، روابط، أو تضمين أهداف الدرس وأنشطة مقترحة.</p>
            </div>

            <div class="flex gap-2 pt-4">
                <button type="submit" class="px-5 py-2.5 rounded-xl bg-indigo-600 text-white font-semibold hover:bg-indigo-700">{{ $item ? 'حفظ التعديلات' : 'إضافة العنصر' }}</button>
                <a href="{{ route('admin.curriculum-library.index') }}" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-700 font-semibold hover:bg-slate-50">إلغاء</a>
            </div>
        </form>
    </div>
</div>
@endsection

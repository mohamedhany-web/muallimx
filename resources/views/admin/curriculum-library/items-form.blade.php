@extends('layouts.admin')

@section('title', $item ? 'تعديل عنصر المنهج' : 'إضافة عنصر منهج')
@section('header', $item ? 'تعديل عنصر المنهج' : 'إضافة عنصر منهج')

@section('content')
<div class="w-full max-w-none">
    <div class="bg-white rounded-2xl shadow-lg border border-slate-200 p-6">
        <form action="{{ $item ? route('admin.curriculum-library.items.update', $item) : route('admin.curriculum-library.items.store') }}" method="POST" class="space-y-4" enctype="multipart/form-data">
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
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">اللغة (مناهج أكس)</label>
                    <select name="language" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                        <option value="ar" {{ old('language', $item?->language ?? 'ar') === 'ar' ? 'selected' : '' }}>العربية</option>
                        <option value="en" {{ old('language', $item?->language ?? 'ar') === 'en' ? 'selected' : '' }}>English</option>
                        <option value="fr" {{ old('language', $item?->language ?? 'ar') === 'fr' ? 'selected' : '' }}>Français</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1">نوع المحتوى</label>
                    <select name="item_type" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500">
                        <option value="presentation" {{ old('item_type', $item?->item_type ?? 'presentation') === 'presentation' ? 'selected' : '' }}>بوربوينت تفاعلي</option>
                        <option value="assignment" {{ old('item_type', $item?->item_type ?? 'presentation') === 'assignment' ? 'selected' : '' }}>وجبة (تحميل/إرسال للطالب)</option>
                    </select>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $item?->is_active ?? true) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_active" class="text-sm font-semibold text-slate-700">نشط (يظهر للمعلمين)</label>
                </div>
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_free_preview" id="is_free_preview" value="1" {{ old('is_free_preview', $item?->is_free_preview ?? false) ? 'checked' : '' }} class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    <label for="is_free_preview" class="text-sm font-semibold text-slate-700">معاينة مجانية (يُعرض كعينة للتجربة)</label>
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

            @if($item)
            <div class="border border-slate-200 rounded-xl p-4 bg-slate-50/50">
                <h3 class="text-sm font-bold text-slate-800 mb-2">الملفات المرفقة (بوربوينت / وجبات)</h3>
                @if($item->files && $item->files->isNotEmpty())
                    <ul class="space-y-2 mb-3">
                        @foreach($item->files as $f)
                            <li class="flex items-center justify-between py-2 px-3 rounded-lg bg-white border border-slate-100">
                                <span class="text-sm text-slate-700">{{ $f->label ?: ($f->file_type === 'presentation' ? 'عرض شرائح' : 'وجبة') }}</span>
                                <form action="{{ route('admin.curriculum-library.items.files.destroy', [$item, $f]) }}" method="POST" class="inline" onsubmit="return confirm('حذف هذا الملف؟');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-rose-600 hover:text-rose-800 text-xs font-semibold">حذف</button>
                                </form>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <p class="text-xs text-slate-600 mb-2">إضافة ملف جديد:</p>
                <div class="flex flex-wrap gap-3 items-end">
                    <div class="flex-1 min-w-[200px]">
                        <input type="file" name="new_files[]" class="block w-full text-sm text-slate-600 file:mr-2 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:text-indigo-700" accept=".ppt,.pptx,.pdf,.zip,.doc,.docx">
                        <select name="new_files_type[]" class="mt-1 w-full px-2 py-1 rounded border border-slate-200 text-sm">
                            <option value="presentation">بوربوينت</option>
                            <option value="assignment">وجبة</option>
                        </select>
                    </div>
                    <div class="min-w-[180px]">
                        <input type="text" name="new_files_label[]" class="w-full px-2 py-1.5 rounded border border-slate-200 text-sm" placeholder="اسم الملف (اختياري)">
                    </div>
                </div>
            </div>
            @else
            <p class="text-sm text-slate-500">بعد إضافة العنصر يمكنك تعديله وإرفاق ملفات (بوربوينت / وجبات).</p>
            @endif

            <div>
                <label class="block text-sm font-semibold text-slate-700 mb-1">المحتوى (تفصيلي — يدعم HTML)</label>
                <textarea name="content" rows="10" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:ring-2 focus:ring-indigo-500 font-mono text-sm" placeholder="المحتوى التفاعلي أو التعليمي للدرس/الوحدة...">{{ old('content', $item?->content) }}</textarea>
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

@extends('layouts.admin')

@section('title', 'تصنيفات استفسار الدعم الفني')
@section('header', 'تصنيفات استفسار الدعم الفني')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
        <h1 class="text-xl font-bold text-slate-900">تصنيفات الاستفسار</h1>
        <p class="text-sm text-slate-600 mt-1">يختار الطالب أحد هذه التصنيفات عند إنشاء تذكرة دعم. التصنيفات المعطّلة لا تظهر في نموذج الطالب.</p>
        <a href="{{ route('admin.support-tickets.index') }}" class="inline-flex items-center gap-2 mt-4 text-sm font-semibold text-sky-600 hover:text-sky-800">
            <i class="fas fa-arrow-right"></i> العودة لتذاكر الدعم
        </a>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6">
        <h2 class="font-bold text-slate-900 mb-4">إضافة تصنيف جديد</h2>
        <form method="POST" action="{{ route('admin.support-inquiry-categories.store') }}" class="flex flex-col sm:flex-row flex-wrap items-end gap-3">
            @csrf
            <div class="flex-1 min-w-[200px]">
                <label class="block text-xs font-semibold text-slate-600 mb-1">اسم التصنيف</label>
                <input type="text" name="name" value="{{ old('name') }}" required maxlength="120"
                       class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm" placeholder="مثال: مشكلة في البث المباشر">
                @error('name')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div class="w-28">
                <label class="block text-xs font-semibold text-slate-600 mb-1">الترتيب</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
            </div>
            <label class="inline-flex items-center gap-2 pb-2 text-sm text-slate-700">
                <input type="hidden" name="is_active" value="0">
                <input type="checkbox" name="is_active" value="1" checked class="rounded border-slate-300 text-sky-600"> نشط
            </label>
            <button type="submit" class="px-5 py-2 rounded-xl bg-sky-600 text-white text-sm font-semibold hover:bg-sky-700">إضافة</button>
        </form>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-5 py-4 bg-slate-50 border-b border-slate-200">
            <h2 class="font-bold text-slate-900">التصنيفات الحالية</h2>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse($categories as $cat)
                <div class="p-4 sm:p-5 space-y-3">
                    <form method="POST" action="{{ route('admin.support-inquiry-categories.update', $cat) }}" class="flex flex-col lg:flex-row flex-wrap items-end gap-3">
                        @csrf
                        @method('PUT')
                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">الاسم</label>
                            <input type="text" name="name" value="{{ old('name', $cat->name) }}" required maxlength="120" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
                        </div>
                        <div class="w-24">
                            <label class="block text-xs font-semibold text-slate-500 mb-1">ترتيب</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $cat->sort_order) }}" min="0" class="w-full px-3 py-2 rounded-lg border border-slate-200 text-sm">
                        </div>
                        <label class="inline-flex items-center gap-2 pb-2 text-sm text-slate-700">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-sky-600" {{ $cat->is_active ? 'checked' : '' }}> نشط
                        </label>
                        <button type="submit" class="px-4 py-2 rounded-lg bg-slate-800 text-white text-sm font-semibold">حفظ التعديل</button>
                    </form>
                    <form method="POST" action="{{ route('admin.support-inquiry-categories.destroy', $cat) }}" class="inline" onsubmit="return confirm('حذف هذا التصنيف؟ التذاكر المرتبطة ستبقى دون تصنيف.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">حذف التصنيف</button>
                    </form>
                </div>
            @empty
                <p class="px-5 py-10 text-center text-sm text-slate-500">لا توجد تصنيفات بعد.</p>
            @endforelse
        </div>
    </div>
</div>
@endsection

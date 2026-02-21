@extends('community.layouts.app')

@section('title', 'تقديم مجموعة بيانات')
@section('content')
<div class="w-full">
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl bg-red-100 border border-red-300 text-red-800">
            <ul class="list-disc list-inside text-sm">{{ $errors->first() }}</ul>
        </div>
    @endif

    <div class="mb-6">
        <h1 class="text-2xl font-black text-slate-900">تقديم مجموعة بيانات جديدة</h1>
        <p class="text-slate-600 mt-1">ستتم مراجعة التقديم من الإدارة قبل النشر في المجتمع.</p>
    </div>

    <form action="{{ route('community.contributor.datasets.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6 bg-white rounded-2xl border border-slate-200 shadow-sm p-6">
        @csrf

        <div>
            <label for="title" class="block text-sm font-bold text-slate-700 mb-2">عنوان مجموعة البيانات <span class="text-red-500">*</span></label>
            <input type="text" name="title" id="title" value="{{ old('title') }}" required
                   placeholder="مثال: مجموعة بيانات المبيعات 2024"
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
        </div>

        <div>
            <label for="description" class="block text-sm font-bold text-slate-700 mb-2">الوصف</label>
            <textarea name="description" id="description" rows="4" placeholder="وصف المجموعة، المصدر، طريقة الاستخدام..."
                      class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 resize-y">{{ old('description') }}</textarea>
        </div>

        <div>
            <label for="category" class="block text-sm font-bold text-slate-700 mb-2">التصنيف</label>
            <select name="category" id="category" class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
                <option value="">— اختر تصنيفاً —</option>
                @foreach(\App\Models\CommunityDataset::CATEGORIES as $key => $label)
                    <option value="{{ $key }}" {{ old('category') === $key ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="file" class="block text-sm font-bold text-slate-700 mb-2">ملف البيانات (اختياري)</label>
            <input type="file" name="file" id="file" accept=".xlsx,.xls,.csv"
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-cyan-50 file:text-cyan-700 file:font-bold">
            <p class="mt-1.5 text-xs text-slate-500">الحد الأقصى 10 MB. الامتدادات: xlsx, xls, csv</p>
        </div>

        <div>
            <label for="file_url" class="block text-sm font-bold text-slate-700 mb-2">رابط التحميل (اختياري)</label>
            <input type="url" name="file_url" id="file_url" value="{{ old('file_url') }}"
                   placeholder="https://example.com/dataset.csv"
                   class="w-full px-4 py-3 rounded-xl border border-slate-200 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/20">
        </div>

        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-cyan-600 text-white font-bold hover:bg-cyan-700 transition-colors shadow-md">
                <i class="fas fa-paper-plane"></i>
                <span>إرسال للمراجعة</span>
            </button>
            <a href="{{ route('community.contributor.datasets.index') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition-colors">إلغاء</a>
        </div>
    </form>
</div>
@endsection

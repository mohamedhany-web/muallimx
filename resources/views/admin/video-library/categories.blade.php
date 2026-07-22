@extends('layouts.admin')

@section('title', 'تصنيفات مكتبة الفيديو')
@section('header', 'تصنيفات / قنوات مكتبة الفيديو')

@section('content')
<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-xl font-black text-slate-900 dark:text-slate-100">التصنيفات (القنوات)</h1>
            <p class="text-sm text-slate-500 mt-1">كل تصنيف يظهر كقناة في واجهة المعلم مع شبكة فيديوهات.</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('admin.video-library.index') }}" class="px-4 py-2 rounded-xl border border-slate-200 text-sm font-semibold">الفيديوهات</a>
            <a href="{{ route('admin.video-library.categories.create') }}" class="px-4 py-2 rounded-xl bg-rose-600 text-white text-sm font-bold"><i class="fas fa-plus ml-1"></i> تصنيف جديد</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
        @forelse($categories as $category)
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-800 overflow-hidden">
                <div class="h-2" style="background: {{ $category->cover_color ?: '#c62828' }}"></div>
                <div class="p-5">
                    <div class="flex items-start gap-3">
                        <span class="w-11 h-11 rounded-xl flex items-center justify-center text-white" style="background: {{ $category->cover_color ?: '#c62828' }}">
                            <i class="fas {{ $category->icon ?: 'fa-play-circle' }}"></i>
                        </span>
                        <div class="min-w-0 flex-1">
                            <h3 class="font-black text-slate-900 dark:text-slate-100">{{ $category->name }}</h3>
                            <p class="text-xs text-slate-500 mt-1 line-clamp-2">{{ $category->description ?: 'بدون وصف' }}</p>
                            <p class="text-xs font-semibold text-slate-600 mt-2">{{ $category->videos_count }} فيديو · ترتيب {{ $category->order }}</p>
                        </div>
                    </div>
                    <div class="mt-4 flex items-center gap-3 text-sm">
                        @if($category->is_active)
                            <span class="text-emerald-600 font-bold text-xs">نشط</span>
                        @else
                            <span class="text-slate-400 font-bold text-xs">موقوف</span>
                        @endif
                        <a href="{{ route('admin.video-library.categories.edit', $category) }}" class="text-sky-600 font-semibold hover:underline">تعديل</a>
                        <form method="POST" action="{{ route('admin.video-library.categories.destroy', $category) }}" onsubmit="return confirm('حذف التصنيف؟ الفيديوهات ستبقى بدون تصنيف.')">
                            @csrf @method('DELETE')
                            <button class="text-rose-600 font-semibold hover:underline">حذف</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-16 text-slate-500">لا توجد تصنيفات. أنشئ أول قناة.</div>
        @endforelse
    </div>
</div>
@endsection

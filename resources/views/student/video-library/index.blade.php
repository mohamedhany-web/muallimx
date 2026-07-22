@extends('layouts.app')

@section('title', 'مكتبة الفيديو')
@section('header', 'مكتبة الفيديو')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-8">
    {{-- Hero --}}
    <div class="rounded-3xl overflow-hidden border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 shadow-sm">
        <div class="relative px-6 sm:px-8 py-8 sm:py-10"
             style="background: radial-gradient(circle at 15% 20%, rgba(198,40,40,.18), transparent 40%), radial-gradient(circle at 90% 10%, rgba(40,53,147,.12), transparent 35%), linear-gradient(135deg, #0f172a 0%, #1e293b 55%, #7f1d1d 100%);">
            <div class="relative z-10 max-w-3xl text-white">
                <p class="text-xs font-bold tracking-wide text-rose-200/90 mb-2">Muallimx Video Channels</p>
                <h1 class="text-2xl sm:text-3xl font-black leading-tight">مكتبة الفيديو التعليمية</h1>
                <p class="mt-2 text-sm sm:text-base text-slate-200 leading-relaxed">
                    قنوات تعليمية منظمة بتصنيفات — الفيديوهات تُشغَّل داخل المنصة مثل درس الكورس، مع عنوان وشرح تحت كل فيديو.
                    @if(empty($hasFullAccess) || !$hasFullAccess)
                        <span class="block mt-2 text-amber-200 text-xs font-semibold">معاينة مجانية: فيديو واحد فقط. اشترك للوصول الكامل.</span>
                    @else
                        <span class="block mt-2 text-emerald-200 text-xs font-semibold">لديك وصول كامل لمكتبة الفيديو ضمن اشتراكك.</span>
                    @endif
                </p>
            </div>
        </div>

        <form method="GET" action="{{ route('video-library.index') }}" class="p-4 sm:p-5 flex flex-wrap gap-3 items-center border-t border-slate-100 dark:border-slate-700">
            <div class="relative flex-1 min-w-[220px]">
                <i class="fas fa-search absolute start-3 top-1/2 -translate-y-1/2 text-slate-400 text-sm"></i>
                <input type="text" name="q" value="{{ request('q') }}" placeholder="ابحث في العناوين والشروحات..."
                       class="w-full ps-9 pe-3 py-2.5 rounded-xl border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-950 text-sm focus:ring-2 focus:ring-rose-500/40 focus:border-rose-500">
            </div>
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 dark:text-slate-300 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-600 cursor-pointer">
                <input type="checkbox" name="featured" value="1" @checked(request()->boolean('featured')) class="rounded text-rose-600">
                المميزة فقط
            </label>
            <button class="px-5 py-2.5 rounded-xl bg-rose-600 hover:bg-rose-700 text-white text-sm font-bold">بحث</button>
            @if(request()->filled('q') || request()->boolean('featured') || request()->filled('category'))
                <a href="{{ route('video-library.index') }}" class="text-sm font-semibold text-slate-500 hover:text-rose-600">مسح</a>
            @endif
        </form>
    </div>

    {{-- Channel chips --}}
    @if($categories->isNotEmpty())
        <section>
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-black text-slate-900 dark:text-slate-100">القنوات والتصنيفات</h2>
                <a href="{{ route('video-library.index') }}" class="text-sm font-semibold {{ !request('category') ? 'text-rose-600' : 'text-slate-500 hover:text-rose-600' }}">الكل</a>
            </div>
            <div class="flex gap-3 overflow-x-auto pb-2 -mx-1 px-1 snap-x">
                @foreach($categories as $cat)
                    @php $isOn = request('category') === $cat->slug || (isset($activeCategory) && $activeCategory && $activeCategory->id === $cat->id); @endphp
                    <a href="{{ route('video-library.category', $cat) }}"
                       class="snap-start shrink-0 min-w-[200px] max-w-[240px] rounded-2xl border p-4 transition-all {{ $isOn ? 'border-rose-400 bg-rose-50 dark:bg-rose-950/30 shadow-md' : 'border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 hover:border-rose-300 hover:shadow' }}">
                        <div class="flex items-center gap-3">
                            <span class="w-12 h-12 rounded-full flex items-center justify-center text-white shadow-inner text-lg"
                                  style="background: {{ $cat->cover_color ?: '#c62828' }}">
                                <i class="fas {{ $cat->icon ?: 'fa-play-circle' }}"></i>
                            </span>
                            <div class="min-w-0">
                                <p class="font-bold text-slate-900 dark:text-slate-100 truncate">{{ $cat->name }}</p>
                                <p class="text-xs text-slate-500 mt-0.5">{{ $cat->videos_count }} فيديو</p>
                            </div>
                        </div>
                        @if($cat->description)
                            <p class="text-xs text-slate-500 mt-3 line-clamp-2 leading-relaxed">{{ $cat->description }}</p>
                        @endif
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    @if(isset($activeCategory) && $activeCategory)
        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 bg-white dark:bg-slate-900 p-5 flex items-start gap-4">
            <span class="w-14 h-14 rounded-full flex items-center justify-center text-white text-xl shrink-0"
                  style="background: {{ $activeCategory->cover_color ?: '#c62828' }}">
                <i class="fas {{ $activeCategory->icon ?: 'fa-play-circle' }}"></i>
            </span>
            <div>
                <h2 class="text-xl font-black text-slate-900 dark:text-slate-100">{{ $activeCategory->name }}</h2>
                @if($activeCategory->description)
                    <p class="text-sm text-slate-600 dark:text-slate-300 mt-1 leading-relaxed">{{ $activeCategory->description }}</p>
                @endif
            </div>
        </div>
    @endif

    {{-- Featured strip --}}
    @if(!request()->filled('category') && !request()->boolean('featured') && $featured->isNotEmpty())
        <section>
            <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 mb-4">مميزة لك</h2>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @foreach($featured as $item)
                    @include('student.video-library._video-card', [
                        'item' => $item,
                        'hasFullAccess' => $hasFullAccess,
                        'usedFreePreview' => $usedFreePreview,
                        'previewVideoId' => $previewVideoId,
                    ])
                @endforeach
            </div>
        </section>
    @endif

    {{-- Main grid --}}
    <section>
        <h2 class="text-lg font-black text-slate-900 dark:text-slate-100 mb-4">
            @if(isset($activeCategory) && $activeCategory)
                فيديوهات القناة
            @else
                أحدث الفيديوهات
            @endif
        </h2>

        @if($videos->isEmpty())
            <div class="rounded-2xl border border-dashed border-slate-300 dark:border-slate-600 py-16 text-center">
                <div class="w-16 h-16 mx-auto rounded-2xl bg-slate-100 dark:bg-slate-800 text-slate-400 flex items-center justify-center mb-4">
                    <i class="fas fa-video text-2xl"></i>
                </div>
                <h3 class="font-bold text-slate-800 dark:text-slate-100">لا توجد فيديوهات هنا بعد</h3>
                <p class="text-sm text-slate-500 mt-1">جرّب تصنيفاً آخر أو ابحث بكلمات مختلفة.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-5">
                @foreach($videos as $item)
                    @include('student.video-library._video-card', [
                        'item' => $item,
                        'hasFullAccess' => $hasFullAccess,
                        'usedFreePreview' => $usedFreePreview,
                        'previewVideoId' => $previewVideoId,
                    ])
                @endforeach
            </div>
            <div class="mt-8">{{ $videos->links() }}</div>
        @endif
    </section>
</div>
@endsection

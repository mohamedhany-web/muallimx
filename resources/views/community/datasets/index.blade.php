@extends('community.layouts.app')

@section('title', __('admin.community_datasets'))

@section('content')
<div class="w-full">
    <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2">
        مجموعات البيانات
    </h1>
    <p class="text-slate-600 mb-8">
        استكشف مجموعات بيانات مفتوحة للتدريب والتجربة في مشاريعك.
    </p>

    @if($datasets->isNotEmpty())
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($datasets as $dataset)
                <a href="{{ route('community.datasets.show', $dataset) }}" class="block bg-white rounded-2xl border border-slate-200 p-6 shadow-sm hover:shadow-md transition-shadow">
                    <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center mb-4">
                        <i class="fas fa-database text-xl"></i>
                    </div>
                    <h3 class="text-lg font-black text-slate-900 mb-2">{{ $dataset->title }}</h3>
                    @if($dataset->description)
                        <p class="text-slate-600 text-sm mb-4 line-clamp-3">{{ Str::limit($dataset->description, 120) }}</p>
                    @endif
                    <div class="flex flex-wrap items-center gap-2">
                        @if($dataset->file_size)
                            <span class="text-xs text-slate-500">{{ $dataset->file_size }}</span>
                        @endif
                        <span class="inline-flex items-center gap-1 text-sm font-bold text-blue-600">
                            <i class="fas fa-eye"></i>
                            <span>عرض ومعاينة</span>
                        </span>
                    </div>
                </a>
            @endforeach
        </div>
        @if($datasets->hasPages())
            <div class="mt-8">{{ $datasets->links() }}</div>
        @endif
    @else
        <div class="bg-white rounded-2xl border border-slate-200 p-8 shadow-sm text-center">
            <div class="w-20 h-20 rounded-2xl bg-blue-100 text-blue-600 flex items-center justify-center mx-auto mb-6">
                <i class="fas fa-database text-4xl"></i>
            </div>
            <h2 class="text-xl font-black text-slate-900 mb-3">مجموعات البيانات قادمة قريباً</h2>
            <p class="text-slate-600 max-w-xl mx-auto mb-6">
                سنضيف مجموعات بيانات جاهزة للتحميل والاستخدام في المسابقات والمشاريع.
            </p>
            <div class="flex flex-wrap justify-center gap-3">
                <span class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">تحميل وتصدير</span>
                <span class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">رفع ومشاركة</span>
                <span class="px-4 py-2 rounded-xl bg-slate-100 text-slate-600 text-sm font-semibold">تصنيف وبحث</span>
            </div>
        </div>
    @endif
</div>
@endsection

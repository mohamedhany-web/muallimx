@extends('layouts.admin')

@section('title', $project->title . ' - البورتفوليو')
@section('header', 'عرض المشروع')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl bg-green-50 border-2 border-green-200 px-6 py-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
            <span class="font-bold text-green-800">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl bg-red-50 border-2 border-red-200 px-6 py-4 flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
            <span class="font-bold text-red-800">{{ session('error') }}</span>
        </div>
    @endif

    <a href="{{ route('admin.portfolio.index') }}" class="inline-flex items-center gap-2 text-blue-600 hover:underline font-bold">
        <i class="fas fa-arrow-right"></i>
        العودة للقائمة
    </a>

    <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-lg">
        @php $adminThumb = \App\Services\PortfolioImageStorage::publicUrl($project->preview_image_path); @endphp
        @if($adminThumb)
            <div class="aspect-video bg-gray-100">
                <img src="{{ $adminThumb }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
            </div>
        @endif
        <div class="p-8">
            <h1 class="text-2xl font-black text-gray-900 mb-4">{{ $project->title }}</h1>
            @if($project->description)
                <div class="prose text-gray-600 mb-6">{!! nl2br(e($project->description)) !!}</div>
            @endif
            @if($project->project_url)
                <p class="mb-4"><a href="{{ $project->project_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:underline font-bold">{{ $project->project_url }}</a></p>
            @endif
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6 text-sm">
                <p><strong>المعلم:</strong> {{ $project->user->name ?? '—' }}</p>
                <p><strong>المسار:</strong> {{ $project->academicYear->name ?? '—' }}</p>
                @php
                    $statusLabels = ['pending_review' => 'قيد المراجعة', 'approved' => 'معتمد', 'rejected' => 'مرفوض', 'published' => 'منشور'];
                @endphp
                <p><strong>الحالة:</strong> {{ $statusLabels[$project->status] ?? $project->status }}</p>
                <p><strong>ظاهر في المعرض:</strong> {{ $project->is_visible ? 'نعم' : 'لا' }}</p>
                @if($project->reviewer)
                    <p><strong>راجع من:</strong> {{ $project->reviewer->name }}</p>
                @endif
            </div>

            {{-- مراجعة البروفايل من الأدمن فقط عند الإرسال --}}
            @if($project->status === 'pending_review')
                <div class="flex flex-wrap gap-4 pt-6 border-t border-gray-200 mb-6">
                    <form action="{{ route('admin.portfolio.approve', $project) }}" method="POST" class="inline">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">ملاحظات (اختياري)</label>
                            <textarea name="instructor_notes" rows="2" class="w-full rounded-xl border-2 border-gray-200 px-3 py-2 text-sm"></textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 bg-green-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-green-700">اعتماد</button>
                    </form>
                    <form action="{{ route('admin.portfolio.reject', $project) }}" method="POST" class="inline">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">سبب الرفض (اختياري)</label>
                            <input type="text" name="rejected_reason" class="w-full rounded-xl border-2 border-gray-200 px-3 py-2 text-sm" placeholder="سبب الرفض">
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-red-700">رفض</button>
                    </form>
                </div>
            @endif

            @if($project->status === 'approved')
                <form action="{{ route('admin.portfolio.publish', $project) }}" method="POST" class="inline mb-6">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-blue-600 hover:bg-blue-700 text-white px-6 py-2.5 rounded-xl font-bold">نشر في البورتفوليو</button>
                </form>
            @endif

            @if(in_array($project->status, ['approved', 'published']))
                <form action="{{ route('admin.portfolio.toggle-visibility', $project) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 {{ $project->is_visible ? 'bg-amber-600 hover:bg-amber-700' : 'bg-green-600 hover:bg-green-700' }} text-white px-6 py-2.5 rounded-xl font-bold">
                        {{ $project->is_visible ? 'إخفاء من المعرض' : 'إظهار في المعرض' }}
                    </button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

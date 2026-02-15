@extends('layouts.app')

@section('title', __('instructor.review_title') . ': ' . $project->title)
@section('header', __('instructor.review_project'))

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

    <a href="{{ route('instructor.portfolio.index') }}" class="inline-flex items-center gap-2 text-[#2CA9BD] hover:underline font-bold">
        <i class="fas fa-arrow-right"></i>
        {{ __('instructor.back_to_projects') }}
    </a>

    <div class="bg-white rounded-2xl border-2 border-gray-200 overflow-hidden shadow-lg">
        @if($project->image_path)
            <div class="aspect-video bg-gray-100">
                <img src="{{ asset($project->image_path) }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
            </div>
        @endif
        <div class="p-8">
            <h1 class="text-2xl font-black text-gray-900 mb-4">{{ $project->title }}</h1>
            @if($project->description)
                <div class="prose text-gray-600 mb-6">{!! nl2br(e($project->description)) !!}</div>
            @endif
            @if($project->project_url)
                <p class="mb-4"><a href="{{ $project->project_url }}" target="_blank" rel="noopener" class="text-[#2CA9BD] hover:underline font-bold">{{ $project->project_url }}</a></p>
            @endif
            <p class="text-sm text-gray-500 mb-6"><strong>{{ __('instructor.student') }}:</strong> {{ $project->user->name ?? '—' }} | <strong>{{ __('instructor.path_name') }}:</strong> {{ $project->academicYear->name ?? '—' }}</p>

            @if($project->status === 'pending_review')
                <div class="flex flex-wrap gap-4 pt-6 border-t border-gray-200">
                    <form action="{{ route('instructor.portfolio.approve', $project) }}" method="POST" class="inline">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('instructor.notes_optional') }}</label>
                            <textarea name="instructor_notes" rows="2" class="w-full rounded-xl border-2 border-gray-200 px-3 py-2 text-sm"></textarea>
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 bg-green-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-green-700">{{ __('instructor.approve') }}</button>
                    </form>
                    <form action="{{ route('instructor.portfolio.reject', $project) }}" method="POST" class="inline">
                        @csrf
                        <div class="mb-2">
                            <label class="block text-sm font-bold text-gray-700 mb-1">{{ __('instructor.rejection_reason_optional') }}</label>
                            <input type="text" name="rejected_reason" class="w-full rounded-xl border-2 border-gray-200 px-3 py-2 text-sm" placeholder="{{ __('instructor.reject_reason_placeholder') }}">
                        </div>
                        <button type="submit" class="inline-flex items-center gap-2 bg-red-600 text-white px-6 py-2.5 rounded-xl font-bold hover:bg-red-700">{{ __('instructor.reject') }}</button>
                    </form>
                </div>
            @endif

            @if($project->status === 'approved')
                <form action="{{ route('instructor.portfolio.publish', $project) }}" method="POST" class="inline mt-4">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 bg-[#2CA9BD] text-white px-6 py-2.5 rounded-xl font-bold hover:bg-[#1F3A56]">{{ __('instructor.publish_to_portfolio_btn') }}</button>
                </form>
            @endif
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', __('student.portfolio_marketing.edit_title'))
@section('header', __('student.portfolio_marketing.edit_header'))

@section('content')
@php
    $maxMb = max(1, round((int) config('upload_limits.max_upload_kb') / 1024, 1));
@endphp
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
    @if(session('error'))
        <div class="rounded-2xl bg-red-50 border-2 border-red-200 px-6 py-4 mb-6">
            <p class="text-red-800 font-bold">{{ session('error') }}</p>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-2xl bg-red-50 border-2 border-red-200 px-6 py-4 mb-6">
            <ul class="list-disc list-inside text-red-800 text-sm">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800/95 rounded-2xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="bg-gradient-to-r from-[#2CA9BD] to-[#65DBE4] px-6 py-4">
            <h2 class="text-lg font-black text-white flex items-center gap-2">
                <i class="fas fa-edit"></i>
                {{ __('student.portfolio_marketing.edit_header') }}
            </h2>
            <p class="text-white/90 text-sm mt-1">{{ __('student.portfolio_marketing.edit_sub') }}</p>
        </div>

        <form action="{{ route('student.portfolio.update', $project) }}" method="POST" enctype="multipart/form-data" class="p-6 md:p-8" x-data="{ type: {{ json_encode(old('content_type', $project->content_type ?? 'gallery')) }} }">
            @csrf
            @method('PUT')

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.content_type_label') }} <span class="text-red-500">*</span></label>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach($contentTypeLabels as $k => $label)
                        <label class="relative block cursor-pointer select-none">
                            <input type="radio" name="content_type" value="{{ $k }}" x-model="type"
                                   class="peer absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                            <span class="flex items-center justify-center gap-2 px-4 py-3 rounded-xl border-2 transition-all border-gray-200 hover:border-[#2CA9BD]/50 hover:bg-gray-50 pointer-events-none peer-checked:border-[#2CA9BD] peer-checked:bg-[#2CA9BD]/10 peer-checked:ring-2 peer-checked:ring-[#2CA9BD]/30 text-sm font-bold text-gray-800">
                                @if($k === 'gallery') <i class="fas fa-images text-[#2CA9BD]"></i> @endif
                                @if($k === 'video') <i class="fas fa-video text-[#2CA9BD]"></i> @endif
                                @if($k === 'text') <i class="fas fa-align-right text-[#2CA9BD]"></i> @endif
                                @if($k === 'link') <i class="fas fa-link text-[#2CA9BD]"></i> @endif
                                {{ $label }}
                            </span>
                        </label>
                    @endforeach
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
                <div class="lg:col-span-12">
                    <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_title') }} <span class="text-red-500">*</span></label>
                    <input type="text" name="title" value="{{ old('title', $project->title) }}" required
                           class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                </div>
            </div>

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_description') }}</label>
                <textarea name="description" rows="3"
                          class="w-full rounded-xl border-2 border-[#2CA9BD]/20 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-900/40 text-gray-900 dark:text-slate-100 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">{{ old('description', $project->description) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                <div>
                    <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_category') }}</label>
                    <select name="project_type" class="w-full rounded-xl border-2 border-[#2CA9BD]/20 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-900/40 text-gray-900 dark:text-slate-100 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                        <option value="">— بدون —</option>
                        @foreach($projectTypeLabels as $val => $plabel)
                            <option value="{{ $val }}" @selected(old('project_type', $project->project_type) === $val)>{{ $plabel }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_extra_link') }}</label>
                    <input type="url" name="github_url" value="{{ old('github_url', $project->github_url) }}" placeholder="{{ __('student.portfolio_marketing.placeholder_extra_link') }}"
                           class="w-full rounded-xl border-2 border-[#2CA9BD]/20 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-900/40 text-gray-900 dark:text-slate-100 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                    <p class="text-xs text-gray-500 dark:text-slate-400 mt-1">{{ __('student.portfolio_marketing.field_extra_link_hint') }}</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_learning_path') }}</label>
                    <select name="academic_year_id" class="w-full rounded-xl border-2 border-[#2CA9BD]/20 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-900/40 text-gray-900 dark:text-slate-100 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                        <option value="">— بدون —</option>
                        @foreach($academicYears as $y)
                            <option value="{{ $y->id }}" @selected((string) old('academic_year_id', $project->academic_year_id) === (string) $y->id)>{{ $y->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_course') }}</label>
                    <select name="advanced_course_id" class="w-full rounded-xl border-2 border-[#2CA9BD]/20 dark:border-slate-600 px-4 py-3 bg-white dark:bg-slate-900/40 text-gray-900 dark:text-slate-100 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                        <option value="">— بدون —</option>
                        @foreach($advancedCourses as $c)
                            <option value="{{ $c->id }}" @selected((string) old('advanced_course_id', $project->advanced_course_id) === (string) $c->id)>{{ $c->title }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="mb-6" x-show="type === 'text'" x-cloak>
                <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_content_text') }}</label>
                <textarea name="content_text" rows="8"
                          class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">{{ old('content_text', $project->content_text) }}</textarea>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
                <div x-show="type === 'link'" x-cloak>
                    <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_external_link') }}</label>
                    <input type="url" name="project_url" value="{{ old('project_url', $project->project_url) }}"
                           class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                </div>
                <div x-show="type === 'video'" x-cloak>
                    <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.field_video') }}</label>
                    <input type="url" name="video_url" value="{{ old('video_url', $project->video_url) }}"
                           class="w-full rounded-xl border-2 border-[#2CA9BD]/20 px-4 py-3 focus:border-[#2CA9BD] focus:ring-2 focus:ring-[#2CA9BD]/20">
                </div>
            </div>

            <div class="rounded-2xl border border-gray-200 p-4 mb-6" x-show="type === 'gallery'" x-cloak>
                <div class="flex items-center justify-between gap-3 mb-3">
                    <p class="font-black text-gray-900 dark:text-slate-100">{{ __('student.portfolio_marketing.edit_current_images') }}</p>
                    <span class="text-xs text-gray-500">{{ $project->images->count() }}/5</span>
                </div>
                @if($project->images->count() === 0)
                    <p class="text-sm text-gray-500 dark:text-slate-400">{{ __('student.portfolio_marketing.edit_no_images') }}</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
                        @foreach($project->images as $img)
                            @php $imgUrl = \App\Services\PortfolioImageStorage::publicUrl($img->image_path); @endphp
                            <div class="rounded-2xl overflow-hidden border border-gray-200 dark:border-slate-600 bg-gray-50 dark:bg-slate-800/40">
                                @if($imgUrl)
                                <a href="{{ $imgUrl }}" target="_blank" rel="noopener noreferrer" class="block">
                                    <img src="{{ $imgUrl }}" alt="" class="w-full h-28 object-cover">
                                </a>
                                @else
                                <div class="w-full h-28 flex items-center justify-center text-slate-400 text-xs">{{ __('student.portfolio_marketing.image_unavailable') }}</div>
                                @endif
                                <div class="p-2">
                                    <form action="{{ route('student.portfolio.images.destroy', [$project, $img]) }}" method="POST" onsubmit="return confirm(@json(__('student.portfolio_marketing.confirm_delete_image')));">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 rounded-xl border border-red-200 text-xs font-bold text-red-700 hover:bg-red-50 transition-colors">
                                            <i class="fas fa-trash"></i>
                                            حذف
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 items-end pt-4 border-t border-gray-200">
                <div class="lg:col-span-6">
                    <label class="block text-sm font-bold text-gray-900 dark:text-slate-100 mb-2">{{ __('student.portfolio_marketing.edit_add_images') }}</label>
                    <div class="border-2 border-dashed border-[#2CA9BD]/30 rounded-xl px-4 py-3 bg-gray-50/50 hover:bg-gray-50 transition-colors" x-show="type === 'gallery'" x-cloak>
                        <input type="file" name="images[]" accept="image/*" multiple data-max="5" id="portfolio-images"
                               class="w-full text-sm text-gray-600 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:font-semibold file:bg-[#2CA9BD]/10 file:text-[#2CA9BD] hover:file:bg-[#2CA9BD]/20">
                        <p class="text-xs text-gray-500 dark:text-slate-400 mt-2" id="images-hint">{{ __('student.portfolio_marketing.edit_images_hint', ['max' => $maxMb]) }}</p>
                    </div>
                    <div class="rounded-xl border border-gray-200 dark:border-slate-600 p-4 text-sm text-gray-600 dark:text-slate-300" x-show="type !== 'gallery'" x-cloak>
                        {{ __('student.portfolio_marketing.edit_no_images_for_type') }}
                    </div>
                </div>
                <div class="lg:col-span-6 flex flex-col sm:flex-row gap-3 justify-end">
                    <a href="{{ route('student.portfolio.show', $project) }}" class="inline-flex items-center justify-center gap-2 border-2 border-gray-300 text-gray-700 px-6 py-3 rounded-xl font-bold hover:bg-gray-50 transition-all order-2 sm:order-1">
                        <i class="fas fa-arrow-right"></i>
                        {{ __('student.portfolio_marketing.btn_cancel') }}
                    </a>
                    <button type="submit" class="inline-flex items-center justify-center gap-2 bg-gradient-to-r from-[#2CA9BD] to-[#65DBE4] text-white px-6 py-3 rounded-xl font-bold hover:shadow-lg transition-all order-1 sm:order-2">
                        <i class="fas fa-save"></i>
                        {{ __('student.portfolio_marketing.btn_save') }}
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var input = document.getElementById('portfolio-images');
    var hint = document.getElementById('images-hint');
    if (input && hint) {
        input.addEventListener('change', function() {
            var files = this.files;
            if (files.length > 5) {
                hint.textContent = 'تم تحديد ' + files.length + ' صور. سيتم أخذ أول 5 صور فقط.';
                var dt = new DataTransfer();
                for (var i = 0; i < 5; i++) dt.items.add(files[i]);
                this.files = dt.files;
            } else if (files.length > 0) {
                hint.textContent = 'تم اختيار ' + files.length + ' صورة.';
            } else {
                hint.textContent = 'سيتم إضافة الصور حتى يكتمل الحد الأقصى (5).';
            }
        });
    }
});
</script>
@endpush
@endsection


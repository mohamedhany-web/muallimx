@extends('layouts.app')

@section('title', 'التسويق الشخصي - ملفك التعريفي')
@section('header', 'التسويق الشخصي')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
    <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/25 border border-emerald-200 dark:border-emerald-800/60 px-4 py-3 flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
        <span class="font-semibold text-emerald-800 dark:text-emerald-200">{{ session('success') }}</span>
    </div>
    @endif
    @if(session('error'))
    <div class="rounded-xl bg-red-50 dark:bg-red-900/25 border border-red-200 dark:border-red-800/60 px-4 py-3 flex items-center gap-3">
        <i class="fas fa-exclamation-triangle text-red-600 dark:text-red-400"></i>
        <span class="font-semibold text-red-800 dark:text-red-200">{{ session('error') }}</span>
    </div>
    @endif

    @php $u = auth()->user(); @endphp
    @if($u->portfolio_profile_status === \App\Models\User::PORTFOLIO_PROFILE_PENDING)
    <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 px-4 py-3 flex items-start gap-3">
        <i class="fas fa-hourglass-half text-amber-600 dark:text-amber-400 mt-0.5"></i>
        <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ __('student.portfolio_marketing.profile_status_pending_banner') }}</p>
    </div>
    @elseif($u->portfolio_profile_status === \App\Models\User::PORTFOLIO_PROFILE_REJECTED)
    <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 px-4 py-3 space-y-2">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 mt-0.5"></i>
            <p class="text-sm font-semibold text-red-900 dark:text-red-100">{{ __('student.portfolio_marketing.profile_status_rejected_banner') }}</p>
        </div>
        @if($u->portfolio_profile_rejected_reason)
            <p class="text-sm text-red-800 dark:text-red-200/90 mr-7 whitespace-pre-line"><span class="font-bold">{{ __('student.portfolio_marketing.profile_rejected_reason_label') }}:</span> {{ $u->portfolio_profile_rejected_reason }}</p>
        @endif
    </div>
    @endif

    <!-- الهيدر -->
    <div class="bg-white dark:bg-slate-800/95 rounded-xl p-5 border border-gray-200 dark:border-slate-700 shadow-sm">
        <div class="flex flex-col gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 dark:text-slate-100 mb-1">التسويق الشخصي للمعلم</h1>
                <p class="text-sm text-gray-500 dark:text-slate-400">
                    {{ __('student.portfolio_marketing.index_intro') }}
                </p>
            </div>
        </div>
    </div>

    <!-- الملف التعريفي -->
    <div class="bg-white dark:bg-slate-800/95 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 flex items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-slate-100">ملفك التعريفي</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">اجعل الملف قوياً: عنوان تعريفي + نبذة + مهارات + فيديو تعريفي اختياري.</p>
            </div>
            <a href="{{ route('student.portfolio.profile.edit') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-bold shadow-sm transition-colors">
                <i class="fas fa-user-edit"></i>
                تعديل الملف
            </a>
        </div>
        <div class="p-5">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <div class="md:col-span-2 rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-2">Headline</p>
                    <p class="font-black text-slate-900 dark:text-slate-100">{{ $u->portfolio_headline ?: '—' }}</p>
                    @if($u->portfolio_about)
                        <p class="text-sm text-slate-600 dark:text-slate-200/90 mt-3 whitespace-pre-line">{{ $u->portfolio_about }}</p>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400 mt-3">أضف نبذة قصيرة تعرّف بك وتوضح ماذا تقدم.</p>
                    @endif
                </div>
                <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-4">
                    <p class="text-xs text-slate-500 dark:text-slate-400 font-semibold mb-2">Skills</p>
                    @if($u->portfolio_skills)
                        <p class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $u->portfolio_skills }}</p>
                    @else
                        <p class="text-sm text-slate-500 dark:text-slate-400">—</p>
                    @endif
                    @if($u->portfolio_intro_video_url)
                        <a href="{{ $u->portfolio_intro_video_url }}" target="_blank" class="inline-flex items-center gap-2 mt-4 text-sm font-bold text-sky-700 dark:text-sky-300 hover:underline">
                            <i class="fas fa-play-circle"></i>
                            فيديو تعريفي
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- مشاريعي -->
    <div class="bg-white dark:bg-slate-800/95 rounded-xl border border-gray-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-5 py-4 border-b border-gray-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/70 flex items-center justify-between gap-3">
            <div>
                <h3 class="font-bold text-slate-900 dark:text-slate-100">{{ __('student.portfolio_marketing.index_section_title') }}</h3>
                <p class="text-xs text-slate-500 dark:text-slate-400">{{ __('student.portfolio_marketing.index_section_hint') }}</p>
            </div>
            <a href="{{ route('student.portfolio.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold shadow-sm transition-colors">
                <i class="fas fa-plus"></i>
                {{ __('student.portfolio_marketing.index_add_btn') }}
            </a>
        </div>

        <div class="p-5">
            @if($projects->count() === 0)
                <div class="rounded-xl border border-dashed border-slate-300 dark:border-slate-600 p-8 text-center">
                    <div class="w-14 h-14 bg-emerald-100 dark:bg-emerald-900/40 rounded-2xl flex items-center justify-center mx-auto mb-3 text-emerald-600 dark:text-emerald-400">
                        <i class="fas fa-folder-open text-xl"></i>
                    </div>
                    <p class="font-bold text-slate-900 dark:text-slate-100 mb-1">{{ __('student.portfolio_marketing.index_empty_title') }}</p>
                    <p class="text-sm text-slate-500 dark:text-slate-400 mb-4">{{ __('student.portfolio_marketing.index_empty_hint') }}</p>
                    <a href="{{ route('student.portfolio.create') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold transition-colors">
                        <i class="fas fa-upload"></i>
                        {{ __('student.portfolio_marketing.index_empty_cta') }}
                    </a>
                </div>
            @else
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
                    @foreach($projects as $project)
                        @php
                            $statusMap = [
                                \App\Models\PortfolioProject::STATUS_PENDING_REVIEW => [__('student.portfolio_marketing.status_pending_review'), 'bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-200', 'fa-hourglass-half'],
                                \App\Models\PortfolioProject::STATUS_APPROVED => [__('student.portfolio_marketing.status_approved'), 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/30 dark:text-emerald-200', 'fa-check-circle'],
                                \App\Models\PortfolioProject::STATUS_REJECTED => [__('student.portfolio_marketing.status_rejected'), 'bg-red-100 text-red-800 dark:bg-red-900/30 dark:text-red-200', 'fa-times-circle'],
                                \App\Models\PortfolioProject::STATUS_PUBLISHED => [__('student.portfolio_marketing.status_published'), 'bg-sky-100 text-sky-800 dark:bg-sky-900/30 dark:text-sky-200', 'fa-globe'],
                            ];
                            $meta = $statusMap[$project->status] ?? [__('student.portfolio_marketing.status_unknown'), 'bg-slate-100 text-slate-700 dark:bg-slate-800 dark:text-slate-200', 'fa-question-circle'];
                            $preview = \App\Services\PortfolioImageStorage::publicUrl($project->preview_image_path);
                        @endphp
                        <div class="rounded-2xl border border-slate-200 dark:border-slate-700 overflow-hidden shadow-sm bg-white dark:bg-slate-900/20">
                            <div class="h-40 bg-slate-100 dark:bg-slate-800/60 relative">
                                @if($preview)
                                    <img src="{{ $preview }}" alt="{{ $project->title }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-slate-400">
                                        <i class="fas fa-image text-3xl"></i>
                                    </div>
                                @endif
                                <div class="absolute top-3 right-3">
                                    <span class="inline-flex items-center gap-2 text-[11px] font-bold px-3 py-1 rounded-full {{ $meta[1] }}">
                                        <i class="fas {{ $meta[2] }}"></i>
                                        {{ $meta[0] }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-4 space-y-2">
                                <div class="flex items-start justify-between gap-3">
                                    <a href="{{ route('student.portfolio.show', $project) }}" class="font-black text-slate-900 dark:text-slate-100 hover:underline line-clamp-2">{{ $project->title }}</a>
                                </div>
                                <p class="text-xs text-slate-500 dark:text-slate-400">
                                    @if($project->academicYear)
                                        <span class="font-semibold">المسار:</span> {{ $project->academicYear->name }}
                                    @endif
                                    @if($project->academicYear && $project->advancedCourse) | @endif
                                    @if($project->advancedCourse)
                                        <span class="font-semibold">الكورس:</span> {{ $project->advancedCourse->title }}
                                    @endif
                                </p>
                                <div class="flex flex-wrap gap-2 pt-2">
                                    <a href="{{ route('student.portfolio.show', $project) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-xs font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                                        <i class="fas fa-eye"></i> عرض
                                    </a>
                                    @if(!in_array($project->status, [\App\Models\PortfolioProject::STATUS_APPROVED, \App\Models\PortfolioProject::STATUS_PUBLISHED], true))
                                        <a href="{{ route('student.portfolio.edit', $project) }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-emerald-200 dark:border-emerald-800/60 text-xs font-bold text-emerald-700 dark:text-emerald-200 hover:bg-emerald-50 dark:hover:bg-emerald-900/20 transition-colors">
                                            <i class="fas fa-edit"></i> تعديل
                                        </a>
                                    @endif
                                    @if($project->status !== \App\Models\PortfolioProject::STATUS_PUBLISHED)
                                        <form action="{{ route('student.portfolio.destroy', $project) }}" method="POST" onsubmit="return confirm(@json(__('student.portfolio_marketing.confirm_delete_item')));" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-red-200 dark:border-red-800/60 text-xs font-bold text-red-700 dark:text-red-200 hover:bg-red-50 dark:hover:bg-red-900/20 transition-colors">
                                                <i class="fas fa-trash"></i> حذف
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    {{ $projects->links() }}
                </div>
            @endif
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="bg-white dark:bg-slate-800/95 rounded-xl p-4 border border-gray-200 dark:border-slate-700 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">الخطة الحالية</p>
            <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ $subscription?->plan_name ?? 'بدون باقة مفعلة' }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800/95 rounded-xl p-4 border border-gray-200 dark:border-slate-700 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">أقسام الملف المتاحة</p>
            <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ (int) ($limits['personal_marketing_profile_sections'] ?? 5) }}</p>
        </div>
        <div class="bg-white dark:bg-slate-800/95 rounded-xl p-4 border border-gray-200 dark:border-slate-700 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">درجة أولوية الظهور</p>
            <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ (int) ($limits['personal_marketing_priority_score'] ?? 0) }}/100</p>
        </div>
        <div class="bg-white dark:bg-slate-800/95 rounded-xl p-4 border border-gray-200 dark:border-slate-700 shadow-sm">
            <p class="text-xs text-slate-500 dark:text-slate-400">أيام إبراز الملف شهرياً</p>
            <p class="text-lg font-bold text-slate-900 dark:text-slate-100">{{ (int) ($limits['personal_marketing_monthly_featured_days'] ?? 0) }}</p>
        </div>
    </div>

    <div class="bg-white dark:bg-slate-800/95 rounded-xl border border-dashed border-gray-200 dark:border-slate-600 p-8 text-center">
        <div class="w-16 h-16 bg-emerald-100 dark:bg-emerald-900/40 rounded-2xl flex items-center justify-center mx-auto mb-4 text-emerald-600 dark:text-emerald-400">
            <i class="fas fa-bullhorn text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 dark:text-slate-100 mb-2">نظام التسويق الشخصي متصل باشتراكك</h3>
        <p class="text-sm text-gray-500 dark:text-slate-400 mb-4 max-w-2xl mx-auto">
            كل باقة تمنحك مستوى مختلف من الظهور التسويقي والمزايا الإضافية. يمكنك ترقية الاشتراك لزيادة أولوية الظهور وتفعيل مزايا تسويقية أقوى.
        </p>
        <a href="{{ route('student.my-subscription') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-sky-600 dark:bg-sky-700 text-white text-sm font-semibold hover:bg-sky-700 dark:hover:bg-sky-600 transition-colors">
            <i class="fas fa-layer-group"></i>
            عرض ومقارنة مزايا اشتراكي
        </a>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', __('student.portfolio_marketing.portfolio_profile_page_title'))
@section('header', __('student.portfolio_marketing.portfolio_profile_page_header'))

@section('content')
<div class="w-full max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/25 border border-emerald-200 dark:border-emerald-800/60 px-4 py-3 flex items-center gap-3">
            <i class="fas fa-check-circle text-emerald-600 dark:text-emerald-400"></i>
            <span class="font-semibold text-emerald-800 dark:text-emerald-200">{{ session('success') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="rounded-xl bg-red-50 dark:bg-red-900/25 border border-red-200 dark:border-red-800/60 px-4 py-3">
            <ul class="list-disc list-inside text-red-800 dark:text-red-200 text-sm">
                @foreach($errors->all() as $e)
                    <li>{{ $e }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @if($user->portfolio_profile_status === \App\Models\User::PORTFOLIO_PROFILE_PENDING)
        <div class="rounded-xl bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800/50 px-4 py-3 flex items-start gap-3">
            <i class="fas fa-hourglass-half text-amber-600 dark:text-amber-400 mt-0.5"></i>
            <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ __('student.portfolio_marketing.profile_status_pending_banner') }}</p>
        </div>
    @elseif($user->portfolio_profile_status === \App\Models\User::PORTFOLIO_PROFILE_REJECTED)
        <div class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800/50 px-4 py-3 space-y-2">
            <div class="flex items-start gap-3">
                <i class="fas fa-exclamation-circle text-red-600 dark:text-red-400 mt-0.5"></i>
                <p class="text-sm font-semibold text-red-900 dark:text-red-100">{{ __('student.portfolio_marketing.profile_status_rejected_banner') }}</p>
            </div>
            @if($user->portfolio_profile_rejected_reason)
                <p class="text-sm text-red-800 dark:text-red-200/90 mr-7 whitespace-pre-line"><span class="font-bold">{{ __('student.portfolio_marketing.profile_rejected_reason_label') }}:</span> {{ $user->portfolio_profile_rejected_reason }}</p>
            @endif
        </div>
    @elseif($user->portfolio_profile_status === \App\Models\User::PORTFOLIO_PROFILE_APPROVED)
        <div class="rounded-xl bg-emerald-50 dark:bg-emerald-900/20 border border-emerald-200 dark:border-emerald-800/50 px-4 py-3 text-sm font-semibold text-emerald-900 dark:text-emerald-100 flex items-center gap-2">
            <i class="fas fa-check-circle"></i>
            {{ __('student.portfolio_marketing.profile_status_approved') }}
        </div>
    @endif

    <div class="bg-white dark:bg-slate-800/95 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100">{{ __('student.portfolio_marketing.portfolio_profile_page_header') }}</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">{{ __('student.portfolio_marketing.portfolio_profile_intro_short') }}</p>
            </div>
            <a href="{{ route('student.portfolio.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors shrink-0">
                <i class="fas fa-arrow-right"></i>
                {{ __('student.portfolio_marketing.back_to_list') }}
            </a>
        </div>

        <form action="{{ route('student.portfolio.profile.update') }}" method="POST" enctype="multipart/form-data" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <div class="rounded-2xl border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50/80 dark:bg-slate-900/30 p-5 sm:p-6">
                <p class="font-black text-slate-900 dark:text-slate-100 mb-1">{{ __('student.portfolio_marketing.profile_headshot_title') }} <span class="text-red-500">*</span></p>
                <p class="text-xs text-slate-500 dark:text-slate-400 mb-4">{{ __('student.portfolio_marketing.profile_headshot_hint') }}</p>
                <div class="flex flex-col sm:flex-row sm:items-center gap-5"
                     x-data="{
                        serverUrl: @js($user->profile_image_url),
                        previewUrl: @js($user->profile_image_url),
                        blobUrl: null,
                        onFilePick(event) {
                            const file = event.target.files?.[0];
                            if (this.blobUrl) {
                                URL.revokeObjectURL(this.blobUrl);
                                this.blobUrl = null;
                            }
                            if (file && file.type.startsWith('image/')) {
                                this.blobUrl = URL.createObjectURL(file);
                                this.previewUrl = this.blobUrl;
                                const rm = this.$refs.removeProfile;
                                if (rm) rm.checked = false;
                            } else if (!file) {
                                this.previewUrl = this.serverUrl;
                            }
                        },
                        onRemoveChange(checked) {
                            if (checked) {
                                if (this.blobUrl) {
                                    URL.revokeObjectURL(this.blobUrl);
                                    this.blobUrl = null;
                                }
                                const input = this.$refs.profileFileInput;
                                if (input) input.value = '';
                                this.previewUrl = null;
                            } else {
                                this.previewUrl = this.blobUrl || this.serverUrl;
                            }
                        }
                     }"
                     x-init="$nextTick(() => { if ($refs.removeProfile && $refs.removeProfile.checked) onRemoveChange(true) })">
                    <div class="shrink-0 w-28 h-28 rounded-2xl overflow-hidden bg-white dark:bg-slate-800 shadow-sm border-2 border-slate-200 dark:border-slate-600">
                        <template x-if="previewUrl">
                            <img :src="previewUrl" alt="" class="w-full h-full object-cover" width="112" height="112">
                        </template>
                        <template x-if="!previewUrl">
                            <div class="w-full h-full min-h-[7rem] border-2 border-dashed border-slate-300 dark:border-slate-600 bg-slate-50 dark:bg-slate-800/50 flex items-center justify-center text-slate-400">
                                <i class="fas fa-user text-4xl"></i>
                            </div>
                        </template>
                    </div>
                    <div class="flex-1 min-w-0 space-y-3">
                        <div>
                            <label class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-bold cursor-pointer transition-colors">
                                <i class="fas fa-camera"></i>
                                {{ __('student.portfolio_marketing.profile_headshot_choose') }}
                                <input type="file" name="profile_image" accept="image/*" class="sr-only" x-ref="profileFileInput" @change="onFilePick($event)">
                            </label>
                            <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">{{ __('student.portfolio_marketing.profile_photo_size_hint', ['max' => max(1, round((int) config('upload_limits.max_upload_kb') / 1024, 1))]) }}</p>
                        </div>
                        @if($user->profile_image)
                            <label class="inline-flex items-center gap-2 text-sm font-bold text-slate-700 dark:text-slate-200 cursor-pointer">
                                <input type="checkbox" name="remove_profile_image" value="1" class="rounded border-slate-300 text-red-600 focus:ring-red-500" x-ref="removeProfile" @checked(old('remove_profile_image')) @change="onRemoveChange($event.target.checked)">
                                {{ __('student.portfolio_marketing.profile_headshot_remove') }}
                            </label>
                        @endif
                        @error('profile_image')
                            <p class="text-sm text-red-600 dark:text-red-400 font-semibold">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">عنوان تعريفي (Headline)</label>
                    <input type="text" name="portfolio_headline" value="{{ old('portfolio_headline', $user->portfolio_headline) }}"
                           placeholder="مثال: معلّم لغة عربية | تدريب أونلاين | تطوير مناهج"
                           class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200/30">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">يفضل 6–12 كلمة تُعرّفك بوضوح.</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">فيديو تعريفي (اختياري)</label>
                    <input type="url" name="portfolio_intro_video_url" value="{{ old('portfolio_intro_video_url', $user->portfolio_intro_video_url) }}"
                           placeholder="YouTube / Vimeo link"
                           class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200/30">
                    <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">ضع رابط فيديو قصير 30–90 ثانية.</p>
                </div>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">نبذة / About</label>
                <textarea name="portfolio_about" rows="6"
                          placeholder="من أنت؟ ماذا تقدّم؟ لمن؟ ولماذا أنت مناسب؟ اكتب بشكل نقاط أو فقرات قصيرة."
                          class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200/30">{{ old('portfolio_about', $user->portfolio_about) }}</textarea>
            </div>

            <div>
                <label class="block text-sm font-bold text-slate-900 dark:text-slate-100 mb-2">المهارات (Skills)</label>
                <textarea name="portfolio_skills" rows="3"
                          placeholder="تخطيط حصص، تقييم صفي، أدوات Zoom، إدارة صفية ..."
                          class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-200/30">{{ old('portfolio_skills', $user->portfolio_skills) }}</textarea>
                <p class="text-xs text-slate-500 dark:text-slate-400 mt-2">افصل المهارات بفاصلة أو سطر جديد.</p>
            </div>

            <div class="flex flex-col sm:flex-row gap-3 justify-end pt-2">
                <button type="submit" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-black transition-colors">
                    <i class="fas fa-save"></i>
                    حفظ
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

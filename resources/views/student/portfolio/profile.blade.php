@extends('layouts.app')

@section('title', 'الملف التعريفي - my portfolio')
@section('header', 'الملف التعريفي')

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

    <div class="bg-white dark:bg-slate-800/95 rounded-2xl border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-5 border-b border-slate-200 dark:border-slate-700 bg-slate-50 dark:bg-slate-800/60 flex items-center justify-between gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-black text-slate-900 dark:text-slate-100">ملف تعريفي احترافي</h1>
                <p class="text-sm text-slate-500 dark:text-slate-400">اكتب نبذة قوية وروابطك ومهاراتك — هذا هو الأساس، والمشاريع مجرد جزء منه.</p>
            </div>
            <a href="{{ route('student.portfolio.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-sm font-bold text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-800/40 transition-colors">
                <i class="fas fa-arrow-right"></i>
                رجوع
            </a>
        </div>

        <form action="{{ route('student.portfolio.profile.update') }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

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

            @php
                $social = is_array($user->portfolio_social_links) ? $user->portfolio_social_links : [];
            @endphp
            <div class="rounded-2xl border border-slate-200 dark:border-slate-700 p-5">
                <p class="font-black text-slate-900 dark:text-slate-100 mb-4">روابط التواصل</p>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2"><i class="fab fa-linkedin ml-2"></i>LinkedIn</label>
                        <input type="url" name="linkedin" value="{{ old('linkedin', $social['linkedin'] ?? '') }}"
                               class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2"><i class="fab fa-twitter ml-2"></i>Twitter/X</label>
                        <input type="url" name="twitter" value="{{ old('twitter', $social['twitter'] ?? '') }}"
                               class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2"><i class="fab fa-youtube ml-2"></i>YouTube</label>
                        <input type="url" name="youtube" value="{{ old('youtube', $social['youtube'] ?? '') }}"
                               class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2"><i class="fab fa-facebook ml-2"></i>Facebook</label>
                        <input type="url" name="facebook" value="{{ old('facebook', $social['facebook'] ?? '') }}"
                               class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-bold text-slate-700 dark:text-slate-200 mb-2"><i class="fas fa-globe ml-2"></i>Website</label>
                        <input type="url" name="website" value="{{ old('website', $social['website'] ?? '') }}"
                               class="w-full rounded-xl border-2 border-slate-200 dark:border-slate-700 px-4 py-3 bg-white dark:bg-slate-900/30">
                    </div>
                </div>
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


@extends('layouts.app')
@section('title', 'إنشاء جلسة بث مباشر')

@section('content')
<div class="max-w-2xl mx-auto space-y-6">
    <div class="flex items-center gap-3">
        <a href="{{ route('instructor.live-sessions.index') }}" class="p-2 rounded-lg hover:bg-slate-100 dark:hover:bg-slate-700 text-slate-500 transition-colors"><i class="fas fa-arrow-right"></i></a>
        <h1 class="text-2xl font-bold text-slate-800 dark:text-white"><i class="fas fa-plus-circle text-red-500 ml-2"></i>إنشاء جلسة بث</h1>
    </div>

    <form method="POST" action="{{ route('instructor.live-sessions.store') }}" class="bg-white dark:bg-slate-800 rounded-xl border border-slate-200 dark:border-slate-700 p-6 space-y-5">
        @csrf
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">عنوان الجلسة <span class="text-red-500">*</span></label>
                <input type="text" name="title" value="{{ old('title') }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="مثال: مراجعة أدوات AI — الأسبوع الثالث">
                @error('title')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الكورس (اختياري)</label>
                <select name="course_id" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    <option value="">جلسة عامة (بدون كورس)</option>
                    @foreach($courses as $course)
                        <option value="{{ $course->id }}" {{ old('course_id') == $course->id ? 'selected' : '' }}>{{ $course->title }}</option>
                    @endforeach
                </select>
                <p class="text-[11px] text-slate-400 mt-1">ربط الجلسة بكورس محدد سيتيح الدخول فقط للطلاب المسجلين</p>
            </div>
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">موعد البث <span class="text-red-500">*</span></label>
                    <input type="datetime-local" name="scheduled_at" value="{{ old('scheduled_at') }}" required class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                    @error('scheduled_at')<p class="text-red-500 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">الحد الأقصى</label>
                    <input type="number" name="max_participants" value="{{ old('max_participants', 100) }}" min="2" max="500" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white">
                </div>
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">كلمة مرور (اختياري)</label>
                <input type="text" name="password" value="{{ old('password') }}" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="اتركها فارغة للدخول بدون باسوورد">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-700 dark:text-slate-200 mb-1">وصف الجلسة</label>
                <textarea name="description" rows="3" class="w-full rounded-lg border-slate-300 dark:border-slate-600 dark:bg-slate-700 dark:text-white" placeholder="محتوى الجلسة / ماذا سيتعلم المعلم...">{{ old('description') }}</textarea>
            </div>
        </div>

        <div class="border-t border-slate-200 dark:border-slate-700 pt-4">
            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="is_recorded" value="1" {{ old('is_recorded') ? 'checked' : '' }} class="rounded text-red-500 focus:ring-red-500">
                    <span class="text-sm text-slate-700 dark:text-slate-300">تسجيل الجلسة</span>
                </label>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="allow_chat" value="1" {{ old('allow_chat', true) ? 'checked' : '' }} class="rounded text-blue-500 focus:ring-blue-500">
                    <span class="text-sm text-slate-700 dark:text-slate-300">السماح بالشات</span>
                </label>
            </div>
        </div>

        <div class="flex items-center gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl font-semibold shadow-lg shadow-red-500/25 transition-all">
                <i class="fas fa-broadcast-tower ml-1"></i> إنشاء الجلسة
            </button>
            <a href="{{ route('instructor.live-sessions.index') }}" class="px-6 py-2.5 bg-slate-200 dark:bg-slate-700 text-slate-700 dark:text-slate-300 rounded-xl font-medium hover:bg-slate-300 transition-colors">إلغاء</a>
        </div>
    </form>
</div>
@endsection

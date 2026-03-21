@extends('layouts.app')

@section('title', 'الدعم الفني')
@section('header', 'الدعم الفني')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <h1 class="text-2xl font-black text-slate-900 dark:text-white">خدمة الدعم الفني 24/7</h1>
        <p class="text-sm text-slate-600 dark:text-slate-400 mt-1">أنشئ تذكرة دعم وسيقوم الفريق بمتابعتها حتى الحل.</p>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <form action="{{ route('student.support.store') }}" method="POST" class="lg:col-span-1 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-5 space-y-4">
            @csrf
            <h2 class="font-bold text-slate-900 dark:text-white">إنشاء تذكرة جديدة</h2>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">عنوان المشكلة</label>
                <input type="text" name="subject" value="{{ old('subject') }}" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                @error('subject')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">الأولوية</label>
                <select name="priority" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">
                    <option value="normal">عادية</option>
                    <option value="low">منخفضة</option>
                    <option value="high">عالية</option>
                    <option value="urgent">عاجلة</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 dark:text-slate-300 mb-1">تفاصيل المشكلة</label>
                <textarea name="message" rows="6" class="w-full px-3 py-2 rounded-lg border border-slate-200 dark:border-slate-600 bg-white dark:bg-slate-700 text-slate-800 dark:text-white">{{ old('message') }}</textarea>
                @error('message')<p class="text-xs text-rose-600 mt-1">{{ $message }}</p>@enderror
            </div>
            <button type="submit" class="w-full px-4 py-2.5 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">إرسال التذكرة</button>
        </form>

        <div class="lg:col-span-2 rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
            <div class="px-5 py-4 bg-slate-50 dark:bg-slate-800/60 border-b border-slate-200 dark:border-slate-700">
                <h2 class="font-bold text-slate-900 dark:text-white">تذاكري</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 dark:divide-slate-700">
                    <thead class="bg-slate-50 dark:bg-slate-800/60">
                        <tr class="text-xs text-slate-600 dark:text-slate-300 uppercase">
                            <th class="px-4 py-3 text-right">العنوان</th>
                            <th class="px-4 py-3 text-right">الحالة</th>
                            <th class="px-4 py-3 text-right">الأولوية</th>
                            <th class="px-4 py-3 text-right">آخر تحديث</th>
                            <th class="px-4 py-3 text-right">تفاصيل</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 dark:divide-slate-700/60">
                        @forelse($tickets as $ticket)
                            <tr class="hover:bg-slate-50/60 dark:hover:bg-slate-700/20">
                                <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $ticket->subject }}</td>
                                <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300">{{ $ticket->status }}</td>
                                <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300">{{ $ticket->priority }}</td>
                                <td class="px-4 py-3 text-xs text-slate-500 dark:text-slate-400">{{ optional($ticket->last_reply_at ?? $ticket->updated_at)->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3 text-sm"><a href="{{ route('student.support.show', $ticket) }}" class="text-sky-600 hover:underline">فتح</a></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">لا توجد تذاكر بعد.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="px-5 py-3 border-t border-slate-200 dark:border-slate-700">{{ $tickets->links() }}</div>
        </div>
    </div>
</div>
@endsection


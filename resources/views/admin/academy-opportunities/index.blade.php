@extends('layouts.admin')

@section('title', 'فرص الأكاديميات')
@section('header', 'فرص الأكاديميات')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6 flex flex-col lg:flex-row lg:items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold text-slate-900 dark:text-white">{{ __('admin.academy_opportunities_title') }}</h1>
            <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">تظهر للمعلمين للتقديم الذاتي؛ ومن «مكتب التوظيف» تتحكم المنصة في الملفات المعتمدة للأكاديميات.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            @if(Route::has('admin.hiring-academies.index'))
            <a href="{{ route('admin.hiring-academies.index') }}" class="px-4 py-2 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-semibold">أكاديميات التوظيف</a>
            @endif
            <a href="{{ route('admin.academy-opportunities.create') }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">إضافة فرصة</a>
        </div>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-xs uppercase text-slate-600 dark:text-slate-400">
                        <th class="px-4 py-3 text-right">الجهة</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.academy_opportunity_hiring_academy') }}</th>
                        <th class="px-4 py-3 text-right">العنوان</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">مميزة</th>
                        <th class="px-4 py-3 text-right">تقديمات</th>
                        <th class="px-4 py-3 text-right">{{ __('admin.academy_opportunity_presentations_count') }}</th>
                        <th class="px-4 py-3 text-right">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opportunities as $op)
                        <tr class="dark:border-slate-700">
                            <td class="px-4 py-3 text-sm text-slate-800 dark:text-slate-200">{{ $op->organization_name }}</td>
                            <td class="px-4 py-3 text-xs">
                                @if($op->hiringAcademy)
                                    <a href="{{ route('admin.hiring-academies.show', $op->hiringAcademy) }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">{{ $op->hiringAcademy->name }}</a>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900 dark:text-white">{{ $op->title }}</td>
                            <td class="px-4 py-3 text-xs text-slate-700 dark:text-slate-300">{{ $op->status }}</td>
                            <td class="px-4 py-3 text-xs">{{ $op->is_featured ? 'نعم' : 'لا' }}</td>
                            <td class="px-4 py-3 text-xs">{{ number_format($op->applications_count) }}</td>
                            <td class="px-4 py-3 text-xs font-semibold text-violet-600 dark:text-violet-400">{{ number_format($op->teacher_presentations_count) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                                    <a href="{{ route('admin.academy-opportunities.recruitment', $op) }}" class="text-indigo-600 dark:text-indigo-400 font-semibold hover:underline">مكتب التوظيف</a>
                                    <span class="text-slate-300">|</span>
                                    <a href="{{ route('admin.academy-opportunities.applications', $op) }}" class="text-violet-600 hover:underline">الطلبات</a>
                                    <a href="{{ route('admin.academy-opportunities.edit', $op) }}" class="text-sky-600 hover:underline">تعديل</a>
                                    <form action="{{ route('admin.academy-opportunities.destroy', $op) }}" method="POST" onsubmit="return confirm('حذف الفرصة؟');" class="inline">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 hover:underline">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="px-4 py-8 text-center text-sm text-slate-500">لا توجد فرص مضافة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-200">{{ $opportunities->links() }}</div>
    </div>
</div>
@endsection


@extends('layouts.admin')

@section('title', 'فرص الأكاديميات')
@section('header', 'فرص الأكاديميات')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6 flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-900">إدارة فرص الأكاديميات</h1>
            <p class="text-sm text-slate-600 mt-1">الفرص التي تظهر للمعلمين ضمن ميزة "الظهور للأكاديميات".</p>
        </div>
        <a href="{{ route('admin.academy-opportunities.create') }}" class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">إضافة فرصة</a>
    </div>

    <div class="rounded-2xl bg-white border border-slate-200 shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr class="text-xs uppercase text-slate-600">
                        <th class="px-4 py-3 text-right">الجهة</th>
                        <th class="px-4 py-3 text-right">العنوان</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">مميزة</th>
                        <th class="px-4 py-3 text-right">طلبات التقديم</th>
                        <th class="px-4 py-3 text-right">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($opportunities as $op)
                        <tr>
                            <td class="px-4 py-3 text-sm text-slate-800">{{ $op->organization_name }}</td>
                            <td class="px-4 py-3 text-sm font-semibold text-slate-900">{{ $op->title }}</td>
                            <td class="px-4 py-3 text-xs text-slate-700">{{ $op->status }}</td>
                            <td class="px-4 py-3 text-xs">{{ $op->is_featured ? 'نعم' : 'لا' }}</td>
                            <td class="px-4 py-3 text-xs">{{ number_format($op->applications_count) }}</td>
                            <td class="px-4 py-3 text-sm">
                                <div class="flex items-center gap-2">
                                    <a href="{{ route('admin.academy-opportunities.applications', $op) }}" class="text-violet-600 hover:underline">الطلبات</a>
                                    <a href="{{ route('admin.academy-opportunities.edit', $op) }}" class="text-sky-600 hover:underline">تعديل</a>
                                    <form action="{{ route('admin.academy-opportunities.destroy', $op) }}" method="POST" onsubmit="return confirm('حذف الفرصة؟');">
                                        @csrf @method('DELETE')
                                        <button class="text-rose-600 hover:underline">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">لا توجد فرص مضافة.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-5 py-3 border-t border-slate-200">{{ $opportunities->links() }}</div>
    </div>
</div>
@endsection


@extends('layouts.admin')

@section('title', $academy->name)
@section('header', $academy->name)

@section('content')
<div class="space-y-8">
    @if(session('success'))
        <div class="rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 px-4 py-3 text-sm font-medium">{{ session('success') }}</div>
    @endif

    <div class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <span class="inline-flex px-3 py-1 rounded-full text-xs font-bold mb-2
                @if($academy->status === 'active') bg-emerald-100 text-emerald-800
                @elseif($academy->status === 'lead') bg-amber-100 text-amber-900
                @else bg-slate-200 text-slate-700 @endif">{{ $academy->statusLabel() }}</span>
            <h1 class="text-2xl font-black text-slate-900 dark:text-white font-heading">{{ $academy->name }}</h1>
            @if($academy->legal_name)<p class="text-sm text-slate-500 mt-1">{{ $academy->legal_name }}</p>@endif
            <p class="text-xs text-slate-400 mt-2 font-mono">slug: {{ $academy->slug }}</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('admin.hiring-academies.edit', $academy) }}" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-bold">تعديل</a>
            <a href="{{ route('admin.academy-opportunities.create') }}" class="px-4 py-2 rounded-xl bg-sky-600 text-white text-sm font-bold">فرصة جديدة</a>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h2 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2"><i class="fas fa-address-card text-indigo-500"></i> التواصل</h2>
            <dl class="space-y-2 text-sm">
                @if($academy->contact_name)<div><dt class="text-slate-500">الاسم</dt><dd class="font-semibold text-slate-800 dark:text-slate-100">{{ $academy->contact_name }}</dd></div>@endif
                @if($academy->contact_email)<div><dt class="text-slate-500">البريد</dt><dd class="font-semibold">{{ $academy->contact_email }}</dd></div>@endif
                @if($academy->contact_phone)<div><dt class="text-slate-500">الهاتف</dt><dd class="font-semibold font-mono">{{ $academy->contact_phone }}</dd></div>@endif
                @if($academy->website)<div><dt class="text-slate-500">الموقع</dt><dd><a href="{{ $academy->website }}" class="text-sky-600 hover:underline" target="_blank" rel="noopener">{{ $academy->website }}</a></dd></div>@endif
                @if($academy->city)<div><dt class="text-slate-500">المدينة</dt><dd>{{ $academy->city }}</dd></div>@endif
                @if($academy->address)<div><dt class="text-slate-500">العنوان</dt><dd>{{ $academy->address }}</dd></div>@endif
            </dl>
        </div>
        <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 p-6 shadow-sm">
            <h2 class="font-bold text-slate-900 dark:text-white mb-4 flex items-center gap-2"><i class="fas fa-file-contract text-amber-500"></i> داخلي</h2>
            @if($academy->commercial_notes)
                <p class="text-xs font-bold text-slate-500 mb-1">تجاري / تعاقد</p>
                <div class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line mb-4">{{ $academy->commercial_notes }}</div>
            @endif
            @if($academy->internal_notes)
                <p class="text-xs font-bold text-slate-500 mb-1">ملاحظات الفريق</p>
                <div class="text-sm text-slate-700 dark:text-slate-200 whitespace-pre-line">{{ $academy->internal_notes }}</div>
            @endif
            @if(!$academy->commercial_notes && !$academy->internal_notes)
                <p class="text-sm text-slate-400">لا توجد ملاحظات داخلية.</p>
            @endif
        </div>
    </div>

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 dark:border-slate-700 flex items-center justify-between">
            <h2 class="font-bold text-slate-900 dark:text-white">فرص التوظيف المرتبطة</h2>
            <span class="text-sm font-bold text-indigo-600">{{ number_format($academy->opportunities_count) }}</span>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="bg-slate-50 dark:bg-slate-900/50 text-xs text-slate-500 uppercase">
                    <tr>
                        <th class="px-4 py-3 text-right">العنوان</th>
                        <th class="px-4 py-3 text-right">الحالة</th>
                        <th class="px-4 py-3 text-right">تقديمات</th>
                        <th class="px-4 py-3 text-right">عروض</th>
                        <th class="px-4 py-3 text-right"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100 dark:divide-slate-700">
                    @forelse($academy->opportunities as $op)
                        <tr>
                            <td class="px-4 py-3 font-semibold text-slate-900 dark:text-white">{{ $op->title }}</td>
                            <td class="px-4 py-3 text-xs">{{ $op->status }}</td>
                            <td class="px-4 py-3">{{ number_format($op->applications_count) }}</td>
                            <td class="px-4 py-3">{{ number_format($op->teacher_presentations_count) }}</td>
                            <td class="px-4 py-3">
                                <a href="{{ route('admin.academy-opportunities.recruitment', $op) }}" class="text-violet-600 font-semibold hover:underline">مكتب التوظيف</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="px-4 py-10 text-center text-slate-500">لا توجد فرص بعد. أنشئ فرصة من قائمة «فرص الأكاديميات» واربطها بهذه الأكاديمية.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <form action="{{ route('admin.hiring-academies.destroy', $academy) }}" method="POST" onsubmit="return confirm('حذف الأكاديمية؟');" class="inline">
        @csrf @method('DELETE')
        <button type="submit" class="text-rose-600 text-sm font-semibold hover:underline">حذف الأكاديمية (إن لم تكن مرتبطة بفرص)</button>
    </form>
</div>
@endsection

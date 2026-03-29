@extends('layouts.admin')

@section('title', 'Lead #' . $salesLead->id)
@section('header', 'عميل محتمل #' . $salesLead->id)

@section('content')
<div class="space-y-6 max-w-4xl">
    <a href="{{ route('admin.sales.leads.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-slate-600 hover:text-slate-900">
        <i class="fas fa-arrow-right"></i> القائمة
    </a>

    <div class="bg-white rounded-xl border border-slate-200 shadow-sm p-6 space-y-4">
        <div class="flex flex-wrap justify-between gap-3">
            <h2 class="text-xl font-black text-slate-900">{{ $salesLead->name }}</h2>
            <span class="rounded-full bg-slate-100 text-slate-800 px-3 py-1 text-sm font-bold">{{ $salesLead->status_label }}</span>
        </div>
        <dl class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
            <div><dt class="text-slate-500 font-semibold">البريد</dt><dd>{{ $salesLead->email ?: '—' }}</dd></div>
            <div><dt class="text-slate-500 font-semibold">الهاتف</dt><dd>{{ $salesLead->phone ?: '—' }}</dd></div>
            <div><dt class="text-slate-500 font-semibold">الشركة</dt><dd>{{ $salesLead->company ?: '—' }}</dd></div>
            <div><dt class="text-slate-500 font-semibold">المصدر</dt><dd>{{ $salesLead->source_label }}</dd></div>
            <div><dt class="text-slate-500 font-semibold">كورس الاهتمام</dt><dd>{{ $salesLead->interestedCourse?->title ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 font-semibold">أنشأه</dt><dd>{{ $salesLead->creator?->name ?? '—' }}</dd></div>
            <div><dt class="text-slate-500 font-semibold">المسؤول</dt><dd>{{ $salesLead->assignedTo?->name ?? '—' }}</dd></div>
        </dl>
        @if($salesLead->notes)
            <div class="rounded-lg bg-slate-50 border border-slate-100 p-3 text-sm whitespace-pre-wrap">{{ $salesLead->notes }}</div>
        @endif
        @if($salesLead->isConverted())
            <div class="rounded-lg border border-emerald-200 bg-emerald-50/50 p-4 text-sm space-y-2">
                <p class="font-bold text-emerald-900">تحويل</p>
                @if($salesLead->converted_at)<p>{{ $salesLead->converted_at->format('Y-m-d H:i') }}</p>@endif
                @if($salesLead->linkedUser)
                    <p>مستخدم: <a href="{{ route('admin.users.show', $salesLead->linkedUser->id) }}" class="font-bold text-emerald-700 hover:underline">{{ $salesLead->linkedUser->name }}</a></p>
                @endif
                @if($salesLead->convertedOrder)
                    <p>طلب: <a href="{{ route('admin.orders.show', $salesLead->convertedOrder) }}" class="font-bold text-emerald-700 hover:underline">#{{ $salesLead->convertedOrder->id }}</a>
                        — {{ $salesLead->convertedOrder->course?->title ?? '—' }}</p>
                @endif
            </div>
        @endif
        @if($salesLead->isLost() && $salesLead->lost_reason)
            <div class="rounded-lg border border-rose-200 bg-rose-50/50 p-4 text-sm">
                <p class="font-bold text-rose-900 mb-1">سبب الخسارة</p>
                <p class="text-rose-800 whitespace-pre-wrap">{{ $salesLead->lost_reason }}</p>
            </div>
        @endif
    </div>
</div>
@endsection

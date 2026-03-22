@extends('layouts.admin')

@section('title', 'إضافة فرصة أكاديمية')
@section('header', 'إضافة فرصة أكاديمية')

@section('content')
<div class="space-y-6">
    <form action="{{ route('admin.academy-opportunities.store') }}" method="POST" class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6 space-y-4">
        @csrf
        @include('admin.academy-opportunities.form', ['opportunity' => null])
        <div class="text-left">
            <button class="px-4 py-2 rounded-xl bg-sky-600 hover:bg-sky-700 text-white text-sm font-semibold">حفظ الفرصة</button>
        </div>
    </form>
</div>
@endsection


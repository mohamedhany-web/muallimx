@extends('layouts.admin')

@section('title', __('admin.edit').' — '.$academy->name)
@section('header', __('admin.edit').' — '.$academy->name)

@section('content')
<div class="max-w-4xl space-y-6">
    <form action="{{ route('admin.hiring-academies.update', $academy) }}" method="POST" class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6 md:p-8 space-y-6">
        @csrf @method('PUT')
        @include('admin.hiring-academies._form', ['academy' => $academy])
        <div class="flex flex-wrap gap-3 pt-2">
            <button type="submit" class="px-6 py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-bold shadow-lg">تحديث</button>
            <a href="{{ route('admin.hiring-academies.show', $academy) }}" class="px-6 py-3 rounded-xl border border-slate-200 dark:border-slate-600 text-slate-700 dark:text-slate-200 text-sm font-semibold">عرض الملف</a>
        </div>
    </form>
</div>
@endsection

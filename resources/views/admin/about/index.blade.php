@extends('layouts.admin')

@section('title', __('admin.about_page'))
@section('header', __('admin.about_page'))

@section('content')
<div class="max-w-3xl mx-auto py-12">
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 md:p-12">
        <p class="text-gray-600 mb-6">إدارة محتوى صفحة «من نحن» المعروضة للزوار. يمكنك معاينة الصفحة أو تعديل النصوص لاحقاً عند تفعيل التحرير.</p>
        <div class="flex flex-wrap gap-4">
            <a href="{{ route('public.about') }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-colors">
                <i class="fas fa-external-link-alt"></i>
                <span>معاينة صفحة من نحن</span>
            </a>
            <a href="{{ route('admin.dashboard') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-gray-100 text-gray-800 font-bold hover:bg-gray-200 transition-colors">
                <i class="fas fa-arrow-right"></i>
                <span>لوحة التحكم</span>
            </a>
        </div>
    </div>
</div>
@endsection

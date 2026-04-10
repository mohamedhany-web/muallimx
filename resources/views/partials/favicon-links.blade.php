{{-- أيقونة التبويب ونتائج البحث: نفس شعار لوحة التحكم من إعدادات النظام عند رفعه --}}
@php
    $brandIcon = \App\Services\AdminPanelBranding::logoPublicUrl();
@endphp
@if($brandIcon)
    <link rel="icon" href="{{ $brandIcon }}" sizes="any">
    <link rel="shortcut icon" href="{{ $brandIcon }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ $brandIcon }}">
    <link rel="icon" href="{{ $brandIcon }}" sizes="32x32">
    <link rel="icon" href="{{ $brandIcon }}" sizes="16x16">
@else
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo-removebg-preview.png') }}">
    <link rel="icon" type="image/png" sizes="32x32" href="{{ asset('logo-removebg-preview.png') }}">
    <link rel="icon" type="image/png" sizes="16x16" href="{{ asset('logo-removebg-preview.png') }}">
@endif

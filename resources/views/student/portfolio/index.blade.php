@extends('layouts.app')

@section('title', 'التسويق الشخصي - ملفك التعريفي')
@section('header', 'التسويق الشخصي')

@section('content')
<div class="w-full px-4 sm:px-6 lg:px-8 py-6 space-y-6">
    @if(session('success'))
    <div class="rounded-xl bg-emerald-50 border border-emerald-200 px-4 py-3 flex items-center gap-3">
        <i class="fas fa-check-circle text-emerald-600"></i>
        <span class="font-semibold text-emerald-800">{{ session('success') }}</span>
    </div>
    @endif

    <!-- الهيدر -->
    <div class="bg-white rounded-xl p-5 border border-gray-200 shadow-sm">
        <div class="flex flex-col gap-3">
            <div>
                <h1 class="text-xl sm:text-2xl font-bold text-gray-900 mb-1">التسويق الشخصي للمعلم</h1>
                <p class="text-sm text-gray-500">
                    هذا القسم مخصص لعرض ملفك التعريفي وتسويقك الشخصي كمعلم أونلاين.
                    سيتم ربطه لاحقًا بنظام التسويق الشخصي في المنصة، ولا يتطلب منك رفع مشاريع يدوياً في الوقت الحالي.
                </p>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl border border-dashed border-gray-200 p-10 sm:p-12 text-center">
        <div class="w-16 h-16 bg-emerald-100 rounded-2xl flex items-center justify-center mx-auto mb-4 text-emerald-600">
            <i class="fas fa-user-tie text-2xl"></i>
        </div>
        <h3 class="text-lg font-bold text-gray-900 mb-2">قسم التسويق الشخصي قيد التطوير</h3>
        <p class="text-sm text-gray-500 mb-4 max-w-md mx-auto">
            سيتم قريبًا ربط هذه الصفحة بملفك التعريفي في المنصة، ليظهر للجهات التي تبحث عن معلمين أونلاين.
            لا تحتاج للقيام بأي إجراء هنا في الوقت الحالي.
        </p>
    </div>
</div>
@endsection

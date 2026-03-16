@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $itemTitle = isset($course) ? ($course->title ?? 'الكورس') : (isset($learningPath) ? ($learningPath->name ?? 'الطلب') : 'الطلب');
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>إتمام الطلب - {{ $itemTitle }} - {{ config('app.name') }}</title>
    <meta name="theme-color" content="#0F172A">

    <link rel="icon" href="{{ asset('favicon.ico') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: { 50:'#f0f4ff',100:'#dbe4ff',200:'#bac8ff',300:'#91a7ff',400:'#748ffc',500:'#5c7cfa',600:'#4c6ef5',700:'#4263eb',800:'#3b5bdb',900:'#364fc7',950:'#0F172A' },
                    brand: { 50:'#ecfeff',100:'#cffafe',200:'#a5f3fc',300:'#67e8f9',400:'#22d3ee',500:'#06b6d4',600:'#0891b2',700:'#0e7490',800:'#155e75',900:'#164e63' }
                },
                fontFamily: {
                    heading: ['Tajawal','IBM Plex Sans Arabic','sans-serif'],
                    body: ['IBM Plex Sans Arabic','Tajawal','sans-serif'],
                }
            }
        }
    }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        [x-cloak]{display:none !important}
        *{font-family:'IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif}
        h1,h2,h3,.font-heading{font-family:'Tajawal','IBM Plex Sans Arabic',sans-serif}
        html{scroll-behavior:smooth}
        body{min-height:100vh;display:flex;flex-direction:column;background:#fff}
        body>*{flex-shrink:0}
        .reveal{opacity:0;transform:translateY(30px);transition:opacity .6s ease,transform .6s ease}
        .reveal.revealed{opacity:1;transform:translateY(0)}
        .btn-primary{transition:all .3s ease}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px -8px rgba(6,182,212,.4)}
        .card-hover{transition:all .3s ease}
        .card-hover:hover{box-shadow:0 20px 40px -15px rgba(0,0,0,.08)}
        .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
    </style>
</head>
<body class="bg-white text-navy-950 antialiased font-body" x-data="{ isSubmitting: false }">
    @include('components.unified-navbar')
    <style>.navbar-spacer{display:none}</style>
    <script>(function(){var n=document.getElementById('navbar');if(n){n.classList.add('nav-transparent');n.classList.remove('nav-solid');}})();</script>

    <main class="flex-1 pt-20">
        {{-- Hero --}}
        <section class="relative py-12 lg:py-16 overflow-hidden bg-navy-950">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-[#0c1833] to-navy-950"></div>
            <div class="absolute top-0 {{ $isRtl?'left-0':'right-0' }} w-96 h-96 rounded-full bg-brand-500/10 blur-[100px]"></div>
            <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <nav class="text-sm text-slate-400 mb-6 flex items-center gap-2">
                    <a href="{{ url('/') }}" class="hover:text-white transition-colors">الرئيسية</a>
                    <span>/</span>
                    <a href="{{ route('public.courses') }}" class="hover:text-white transition-colors">الكورسات</a>
                    <span>/</span>
                    @if(isset($course))
                        <a href="{{ route('public.course.show', $course->id) }}" class="hover:text-white transition-colors">{{ Str::limit($course->title ?? 'الكورس', 30) }}</a>
                    @endif
                    <span>/</span>
                    <span class="text-white font-medium">إتمام الطلب</span>
                </nav>
                <div class="text-center max-w-2xl mx-auto">
                    <h1 class="font-heading text-3xl sm:text-4xl font-black text-white mb-3 reveal">إتمام الطلب</h1>
                    <p class="text-slate-300 text-lg reveal">خطوة أخيرة للحصول على {{ isset($course) ? 'الكورس' : 'المسار التعليمي' }}</p>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-16 bg-gradient-to-t from-white to-transparent"></div>
        </section>

        {{-- Checkout content --}}
        <section class="py-12 lg:py-16 bg-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                    {{-- ملخص الطلب --}}
                    <div class="lg:col-span-1 order-2 lg:order-1">
                        <div class="reveal card-hover sticky top-24 rounded-3xl bg-white border border-slate-100 p-6 shadow-lg">
                            <h3 class="font-heading text-xl font-black text-navy-950 mb-6 flex items-center gap-2">
                                <i class="fas fa-shopping-bag text-brand-500"></i>
                                ملخص الطلب
                            </h3>
                            <div class="flex items-start gap-4 mb-6 pb-6 border-b border-slate-100">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-brand-500 to-navy-600 flex items-center justify-center flex-shrink-0 shadow-lg">
                                    @if(isset($course))
                                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                                    @else
                                        <i class="fas fa-route text-white text-xl"></i>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-navy-950 text-base line-clamp-2">
                                        @if(isset($course)){{ $course->title }}@elseif(isset($learningPath)){{ $learningPath->name }}@else الطلب @endif
                                    </h4>
                                    <p class="text-sm text-slate-500 mt-0.5">
                                        @if(isset($course)){{ $course->academicSubject->name ?? 'غير محدد' }}@else مسار تعليمي شامل @endif
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-600">السعر</span>
                                    <span class="font-bold text-brand-600 text-lg">
                                        {{ number_format(isset($course) ? $course->price : (isset($learningPath) ? ($learningPath->price ?? 0) : 0), 0) }}
                                        <span class="text-slate-500 text-sm font-medium">ج.م</span>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center pt-4 border-t-2 border-slate-100">
                                    <span class="font-bold text-navy-950">الإجمالي</span>
                                    <span class="text-2xl font-black text-brand-600">
                                        {{ number_format(isset($course) ? $course->price : (isset($learningPath) ? ($learningPath->price ?? 0) : 0), 0) }}
                                        <span class="text-slate-500 text-base font-medium">ج.م</span>
                                    </span>
                                </div>
                            </div>
                            <ul class="space-y-2 text-sm text-slate-600">
                                <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> وصول مدى الحياة</li>
                                <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> شهادة إتمام</li>
                                <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> دعم فني</li>
                            </ul>
                        </div>
                    </div>

                    {{-- طرق الدفع --}}
                    <div class="lg:col-span-2 order-1 lg:order-2">
                        <div class="reveal card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-lg">
                            <h2 class="font-heading text-2xl font-black text-navy-950 mb-6 flex items-center gap-3">
                                <i class="fas fa-credit-card text-brand-500"></i>
                                طرق الدفع المتاحة
                            </h2>

                            @if(session('error'))
                                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                                    <p class="text-red-700 text-sm flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> {{ session('error') }}</p>
                                </div>
                            @endif
                            @if($errors->any())
                                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                                    <ul class="list-disc list-inside space-y-1 text-red-700 text-sm">
                                        @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                                    </ul>
                                </div>
                            @endif
                            @if(session('success'))
                                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                                    <p class="text-emerald-700 text-sm flex items-center gap-2"><i class="fas fa-check-circle"></i> {{ session('success') }}</p>
                                </div>
                            @endif

                            <div class="mb-6 p-5 bg-slate-50 rounded-2xl border border-slate-100">
                                <p class="text-sm font-bold text-navy-950 mb-4">يمكنك الدفع بإحدى الطرق التالية:</p>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                                    <div class="flex items-center gap-3 p-4 bg-white rounded-xl border border-slate-100">
                                        <div class="w-12 h-12 rounded-xl bg-brand-50 flex items-center justify-center"><i class="fas fa-credit-card text-xl text-brand-600"></i></div>
                                        <div><p class="font-bold text-navy-950 text-sm">البطاقات</p><p class="text-xs text-slate-500">فيزا، ماستركارد، ميزة</p></div>
                                    </div>
                                    <div class="flex items-center gap-3 p-4 bg-white rounded-xl border border-slate-100">
                                        <div class="w-12 h-12 rounded-xl bg-emerald-50 flex items-center justify-center"><i class="fas fa-wallet text-xl text-emerald-600"></i></div>
                                        <div><p class="font-bold text-navy-950 text-sm">المحفظة</p><p class="text-xs text-slate-500">فودافون كاش وغيرها</p></div>
                                    </div>
                                    <div class="flex items-center gap-3 p-4 bg-white rounded-xl border border-slate-100">
                                        <div class="w-12 h-12 rounded-xl bg-amber-50 flex items-center justify-center"><i class="fas fa-calendar-alt text-xl text-amber-600"></i></div>
                                        <div><p class="font-bold text-navy-950 text-sm">التقسيط</p><p class="text-xs text-slate-500">تقسيط عبر البنوك</p></div>
                                    </div>
                                </div>
                                <p class="mt-4 text-xs text-slate-500 flex items-center gap-1.5">
                                    <i class="fas fa-info-circle text-brand-500"></i>
                                    عند الضغط على «متابعة للدفع» ستُنقل لصفحة دفع آمنة.
                                </p>
                            </div>

                            <form action="{{ isset($course) ? route('public.course.checkout.kashier', $course->id) : (isset($learningPath) ? route('public.learning-path.checkout.kashier', Str::slug($learningPath->name)) : '#') }}" method="POST" @submit="isSubmitting = true">
                                @csrf
                                <div class="flex flex-col sm:flex-row gap-4">
                                    <button type="submit" :disabled="isSubmitting"
                                            class="btn-primary flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-l from-brand-500 to-brand-600 text-white px-6 py-4 rounded-2xl font-bold shadow-lg disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                                        <i class="fas fa-lock" x-show="!isSubmitting"></i>
                                        <i class="fas fa-spinner fa-spin" x-show="isSubmitting" x-cloak></i>
                                        <span x-text="isSubmitting ? 'جاري التوجيه...' : 'متابعة للدفع'"></span>
                                    </button>
                                    <a href="{{ isset($course) ? route('public.course.show', $course->id) : (isset($learningPath) ? route('public.learning-path.show', Str::slug($learningPath->name)) : url('/courses')) }}"
                                       :class="{ 'pointer-events-none opacity-50': isSubmitting }"
                                       class="inline-flex items-center justify-center gap-2 bg-white border-2 border-slate-200 text-navy-950 px-6 py-4 rounded-2xl font-bold hover:border-slate-300 hover:bg-slate-50 transition-all">
                                        <i class="fas fa-arrow-{{ $isRtl ? 'right' : 'left' }}"></i>
                                        إلغاء
                                    </a>
                                </div>
                                <p class="mt-4 text-xs text-slate-500 text-center flex items-center justify-center gap-1.5">
                                    <i class="fas fa-shield-alt text-brand-500"></i>
                                    تفعيل فوري بعد إتمام الدفع
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    @include('components.unified-footer')

    <script>
    (function(){
        function initReveal(){var t=document.querySelectorAll('.reveal');if(!t.length)return;var o=new IntersectionObserver(function(e){e.forEach(function(en){if(en.isIntersecting){en.target.classList.add('revealed');o.unobserve(en.target);}});},{threshold:.1});t.forEach(function(el){o.observe(el);});}
        if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initReveal);else initReveal();
    })();
    </script>
</body>
</html>

@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp

<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ __('public.pricing_page_title') }} - {{ __('public.site_suffix') }}</title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: { 950:'#020617' },
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *{font-family:'IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif}
        h1,h2,h3,h4,h5,h6,.font-heading{font-family:'Tajawal','IBM Plex Sans Arabic',sans-serif}
        html{scroll-behavior:smooth;overflow-x:hidden}
        body{background:#fff;overflow-x:hidden}
        .card-hover{transition:all .4s cubic-bezier(.16,1,.3,1)}
        .card-hover:hover{transform:translateY(-6px);box-shadow:0 20px 40px -18px rgba(15,23,42,.35)}
        .btn-primary{position:relative;overflow:hidden;transition:all .3s cubic-bezier(.16,1,.3,1)}
        .btn-primary::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);transition:left .5s}
        .btn-primary:hover::before{left:100%}
        .btn-primary:hover{transform:translateY(-1px)}
    </style>
</head>
<body class="bg-white text-slate-900 antialiased">
    @include('components.unified-navbar')
    <style>.navbar-spacer{display:none}</style>
    <script>(function(){var n=document.getElementById('navbar');if(n){n.classList.add('nav-transparent');n.classList.remove('nav-solid');}})();</script>

    <main class="flex-1">
        <!-- Hero Section (aligned with landing) -->
        <section class="relative min-h-[60vh] flex items-center overflow-hidden bg-navy-950"
                 style="background: radial-gradient(circle at top, rgba(34,211,238,0.12), transparent 60%), linear-gradient(135deg,#020617 0%,#020617 40%,#0f172a 100%);">
            <div class="absolute inset-0 opacity-[0.03]"
                 style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.4) 1px,transparent 0);background-size:32px 32px"></div>
            <div class="absolute top-[-20%] {{ $isRtl ? 'left-[-10%]' : 'right-[-10%]' }} w-[480px] h-[480px] rounded-full bg-cyan-500/15 blur-[110px]"></div>
            <div class="absolute bottom-[-15%] {{ $isRtl ? 'right-[-10%]' : 'left-[-10%]' }} w-[520px] h-[520px] rounded-full bg-blue-600/10 blur-[120px]"></div>

            <div class="relative z-10 max-w-6xl mx-auto px-5 sm:px-8 lg:px-12 pt-28 pb-20 w-full text-center">
                <div class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-white/[0.06] border border-white/[0.12] text-brand-300 text-sm font-medium backdrop-blur-sm mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    خطط تسعير مصممة خصيصاً للمعلمين
                </div>

                <h1 class="font-heading text-4xl sm:text-5xl md:text-6xl font-black leading-tight text-white mb-4">
                    الأسعار والباقات
                    <br>
                    <span class="bg-clip-text text-transparent"
                          style="background-image:linear-gradient(135deg,#22d3ee 0%,#3b82f6 40%,#8b5cf6 100%);">
                        للمعلمين أونلاين
                    </span>
                </h1>

                <p class="text-lg sm:text-xl text-slate-200/90 max-w-3xl mx-auto leading-relaxed mb-6">
                    ابدأ مسيرتك كمعلم أونلاين باستخدام أدوات احترافية ومناهج جاهزة وبروفايل مهني يفتح لك فرص عمل مع أكاديميات.
                </p>

                <p class="text-sm text-slate-400 max-w-2xl mx-auto">
                    جميع الأسعار بالجنيه المصري (ج.م) وتشمل أدوات AI، مكتبة مناهج، ودعم فني للمعلمين.
                </p>
            </div>
        </section>

        <!-- Teacher Plans Section (بيانات من إعدادات مزايا اشتراك المعلمين /admin/teacher-features) -->
        <section class="py-16 md:py-20 bg-white">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="text-center mb-12">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-4">
                        باقات المعلمين
                    </span>
                    <h2 class="font-heading text-3xl md:text-4xl font-extrabold text-slate-900 mb-4">
                        اختر الباقة المناسبة لطموحك كمعلم أونلاين
                    </h2>
                    <p class="text-lg text-slate-600 max-w-2xl mx-auto leading-relaxed">
                        من بداية مشوارك وحتى بناء مسار مهني مستقر، كل باقة مصممة لتزيد دخلك وتوفّر وقتك في التحضير والمتابعة.
                    </p>
                </div>

                @php
                    $planKeys = ['teacher_starter', 'teacher_pro', 'teacher_premium'];
                    $planMeta = [
                        'teacher_starter' => ['subtitle' => 'ابدأ التدريس أونلاين بسهولة', 'badge' => null, 'priceHint' => 'أقل من 7 جنيه يوميًا.', 'cta' => 'ابدأ الآن', 'card' => 'white', 'accent' => 'sky'],
                        'teacher_pro'     => ['subtitle' => 'أفضل اختيار للمعلمين الذين يريدون العمل أونلاين', 'badge' => 'الأفضل للبدء أونلاين', 'priceHint' => 'استثمار ربع سنوي يمنحك حضورًا مهنيًا وفرص عمل حقيقية.', 'cta' => 'ابدأ العمل الآن', 'card' => 'dark', 'accent' => 'sky'],
                        'teacher_premium' => ['subtitle' => 'للمعلمين الجادين في بناء مسار مهني مستقر', 'badge' => null, 'priceHint' => 'اشتراك سنوي يمنحك استقرارًا وفرص تدريس مستمرة طوال العام.', 'cta' => 'ابدأ رحلتك الآن', 'card' => 'white', 'accent' => 'amber'],
                    ];
                    $billingPhrases = ['monthly' => 'جنيه شهريًا', 'quarterly' => 'جنيه / 3 شهور', 'yearly' => 'جنيه سنويًا'];
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                    @foreach($planKeys as $planKey)
                        @php
                            $plan = $teacherPlans[$planKey] ?? null;
                            if (!$plan) continue;
                            $meta = $planMeta[$planKey] ?? [];
                            $label = $plan['label'] ?? $planKey;
                            $price = (float) ($plan['price'] ?? 0);
                            $cycle = $plan['billing_cycle'] ?? 'monthly';
                            $cyclePhrase = $billingPhrases[$cycle] ?? 'جنيه';
                            $features = $plan['features'] ?? [];
                            $isPro = $planKey === 'teacher_pro';
                        @endphp
                        <div class="rounded-3xl shadow-lg border p-8 flex flex-col relative overflow-hidden card-hover
                            @if($isPro) bg-navy-950 border-brand-500/60
                            @elseif($planKey === 'teacher_premium') bg-white border-amber-300/80
                            @else bg-white border-slate-100
                            @endif">
                            @if(!empty($meta['badge']))
                                <div class="absolute top-3 left-3 bg-sky-500 text-white text-xs font-bold px-3 py-1 rounded-full">{{ $meta['badge'] }}</div>
                            @endif
                            <div class="mb-4">
                                <h3 class="text-2xl font-bold {{ $isPro ? 'text-white' : 'text-slate-900' }} mb-1">{{ $label }}</h3>
                                <p class="text-sm font-semibold {{ $isPro ? 'text-sky-300' : ($planKey === 'teacher_premium' ? 'text-amber-600' : 'text-sky-600') }}">
                                    {{ $meta['subtitle'] ?? '' }}
                                </p>
                            </div>
                            <div class="mb-6">
                                <div class="text-3xl font-extrabold {{ $isPro ? 'text-white' : 'text-slate-900' }} mb-1">
                                    {{ number_format($price, 0) }} <span class="text-lg font-bold">{{ $cyclePhrase }}</span>
                                </div>
                                @if(!empty($meta['priceHint']))
                                    <p class="text-sm {{ $isPro ? 'text-slate-300' : 'text-slate-500' }}">{{ $meta['priceHint'] }}</p>
                                @endif
                            </div>
                            <ul class="space-y-3 {{ $isPro ? 'text-slate-100' : 'text-slate-700' }} mb-8 flex-1 text-sm">
                                @foreach($features as $featureKey)
                                    <li class="flex items-start">
                                        <i class="fas fa-check-circle {{ $isPro ? 'text-sky-300' : ($planKey === 'teacher_premium' ? 'text-amber-500' : 'text-sky-500') }} ml-2 mt-1"></i>
                                        <span>{{ __("student.subscription_feature.{$featureKey}") }}</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if($isPro)
                                <div class="mb-4 text-sky-200 text-sm font-semibold">فرص حقيقية للعمل مع أكاديميات، وليس مجرد أدوات.</div>
                            @endif
                            @if($planKey === 'teacher_premium')
                                <div class="mb-4 text-amber-700 text-sm font-semibold">نساعدك في الوصول إلى فرص تدريس حقيقية وبناء اسمك كمعلم أونلاين.</div>
                            @endif
                            <a href="{{ route('public.subscription.checkout', $planKey) }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl font-bold text-sm transition-colors
                                @if($isPro) bg-sky-400 hover:bg-sky-300 text-slate-900
                                @elseif($planKey === 'teacher_premium') bg-amber-500 hover:bg-amber-600 text-white
                                @else btn-primary bg-sky-600 hover:bg-sky-700 text-white
                                @endif">
                                {{ $meta['cta'] ?? 'ابدأ الآن' }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <!-- Existing Platform Packages -->
        <section class="py-16 md:py-20 bg-slate-50/60 border-t border-slate-100">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 lg:px-12">
        @if(isset($packages) && $packages->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8 max-w-7xl mx-auto">
            @foreach($packages as $index => $package)
            <!-- Package Card -->
            <div class="bg-white rounded-xl shadow-lg p-8 border-2 {{ $package->is_popular ? 'border-sky-400 transform scale-105' : 'border-gray-200' }} card-hover relative {{ $package->is_popular ? 'bg-gradient-to-br from-sky-500 to-blue-600' : '' }}">
                @if($package->is_popular)
                <div class="absolute top-0 left-0 right-0 bg-yellow-400 text-yellow-900 text-xs font-bold px-3 py-2 rounded-t-xl text-center">الأكثر شعبية</div>
                @endif
                
                <div class="text-center mb-6 {{ $package->is_popular ? 'mt-4' : '' }}">
                    @if($package->thumbnail)
                    <div class="w-20 h-20 rounded-full overflow-hidden mx-auto mb-4 feature-icon-hover">
                        <img src="{{ asset('storage/' . $package->thumbnail) }}" alt="{{ $package->name }}" class="w-full h-full object-cover" loading="lazy" decoding="async">
                    </div>
                    @else
                    <div class="w-20 h-20 {{ $package->is_popular ? 'bg-white/20' : 'bg-gradient-to-br from-sky-400 to-sky-600' }} rounded-full flex items-center justify-center mx-auto mb-4 feature-icon-hover">
                        @if($package->is_featured)
                            <i class="fas fa-crown {{ $package->is_popular ? 'text-white' : 'text-white' }} text-2xl"></i>
                        @elseif($package->is_popular)
                            <i class="fas fa-star text-white text-2xl"></i>
                        @else
                            <i class="fas fa-box text-white text-2xl"></i>
                        @endif
                    </div>
                    @endif
                    
                    <h3 class="text-2xl font-bold {{ $package->is_popular ? 'text-white' : 'text-gray-900' }} mb-2">{{ $package->name }}</h3>
                    
                    @if($package->original_price && $package->original_price > $package->price)
                    <div class="mb-2">
                        <span class="text-lg {{ $package->is_popular ? 'text-blue-200' : 'text-gray-400' }} line-through">{{ number_format($package->original_price, 2) }} ج.م</span>
                    </div>
                    @endif
                    
                    <div class="text-5xl font-bold {{ $package->is_popular ? 'text-white' : 'text-sky-600' }} mb-2">
                        @if($package->price > 0)
                            {{ number_format($package->price, 2) }} <span class="text-2xl">ج.م</span>
                        @else
                            <span class="text-2xl">مجاني</span>
                        @endif
                    </div>
                    
                    @if($package->description)
                    <p class="{{ $package->is_popular ? 'text-blue-100' : 'text-gray-600' }}">{{ Str::limit($package->description, 50) }}</p>
                    @endif
                    
                    @if($package->courses_count > 0)
                    <p class="text-sm {{ $package->is_popular ? 'text-blue-200' : 'text-gray-500' }} mt-2">
                        <i class="fas fa-graduation-cap ml-1"></i>
                        {{ $package->courses_count }} كورس
                    </p>
                    @endif
                </div>
                
                <!-- Features -->
                @if($package->features && count($package->features) > 0)
                <ul class="space-y-4 mb-8">
                    @foreach($package->features as $feature)
                    <li class="flex items-center {{ $package->is_popular ? 'text-white' : 'text-gray-700' }}">
                        <i class="fas fa-check-circle {{ $package->is_popular ? 'text-yellow-300' : 'text-sky-500' }} ml-3"></i>
                        {{ $feature }}
                    </li>
                    @endforeach
                </ul>
                @else
                <ul class="space-y-4 mb-8">
                    <li class="flex items-center {{ $package->is_popular ? 'text-white' : 'text-gray-700' }}">
                        <i class="fas fa-check-circle {{ $package->is_popular ? 'text-yellow-300' : 'text-sky-500' }} ml-3"></i>
                        وصول لجميع الكورسات في الباقة
                    </li>
                    @if($package->courses_count > 0)
                    <li class="flex items-center {{ $package->is_popular ? 'text-white' : 'text-gray-700' }}">
                        <i class="fas fa-check-circle {{ $package->is_popular ? 'text-yellow-300' : 'text-sky-500' }} ml-3"></i>
                        {{ $package->courses_count }} كورس برمجي شامل
                    </li>
                    @endif
                    <li class="flex items-center {{ $package->is_popular ? 'text-white' : 'text-gray-700' }}">
                        <i class="fas fa-check-circle {{ $package->is_popular ? 'text-yellow-300' : 'text-sky-500' }} ml-3"></i>
                        دعم فني متواصل
                    </li>
                </ul>
                @endif
                
                <!-- CTA Button -->
                @if($package->price > 0)
                <a href="{{ route('public.package.show', $package->slug) }}" class="{{ $package->is_popular ? 'bg-white text-sky-600 hover:bg-gray-100' : 'btn-primary' }} font-bold py-3 px-6 rounded-lg transition-colors w-full text-center block">
                    <i class="fas fa-shopping-cart ml-2"></i>
                    اشتر الآن
                </a>
                @else
                <a href="{{ route('public.package.show', $package->slug) }}" class="{{ $package->is_popular ? 'bg-white text-sky-600 hover:bg-gray-100' : 'btn-primary' }} font-bold py-3 px-6 rounded-lg transition-colors w-full text-center block">
                    <i class="fas fa-eye ml-2"></i>
                    عرض التفاصيل
                </a>
                @endif
            </div>
            @endforeach
        </div>
        @else
        <!-- Empty State -->
        <div class="text-center py-16">
            <div class="max-w-md mx-auto">
                <div class="w-24 h-24 bg-gray-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <i class="fas fa-box text-gray-400 text-4xl"></i>
                </div>
                <h3 class="text-2xl font-bold text-gray-900 mb-4">لا توجد باقات متاحة حالياً</h3>
                <p class="text-gray-600 mb-6">نعمل على إضافة باقات جديدة قريباً</p>
                <a href="{{ route('public.courses') }}" class="btn-primary inline-block">
                    <i class="fas fa-arrow-left ml-2"></i>
                    تصفح الكورسات
                </a>
            </div>
        </div>
        @endif
    </div>
</section>
    </main>

    @include('components.unified-footer')
</body>
</html>

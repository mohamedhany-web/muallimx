@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}" itemscope itemtype="https://schema.org/EducationalOrganization">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ __('landing.meta.title') }}</title>
    <meta name="title"       content="{{ __('landing.meta.title') }}">
    <meta name="description" content="{{ __('landing.meta.description') }}">
    <meta name="keywords"    content="تأهيل المعلمين, تدريب المعلمين أونلاين, أدوات AI للمعلم, دروس أونلاين, منصة تعليم, Muallimx, بناء بروفايل المعلم">
    <meta name="author"      content="Muallimx">
    <meta name="robots"      content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="language"    content="{{ $locale === 'ar' ? 'Arabic' : 'English' }}">
    <meta name="theme-color" content="#283593">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <link rel="canonical"    href="{{ url('/') }}">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <!-- hreflang -->
    <link rel="alternate" hreflang="ar"        href="{{ url('/') }}?lang=ar">
    <link rel="alternate" hreflang="en"        href="{{ url('/') }}?lang=en">
    <link rel="alternate" hreflang="x-default" href="{{ url('/') }}">
    <!-- Open Graph -->
    <meta property="og:type"             content="website">
    <meta property="og:url"              content="{{ url('/') }}">
    <meta property="og:title"            content="{{ __('landing.meta.og_title') }}">
    <meta property="og:description"      content="{{ __('landing.meta.og_description') }}">
    <meta property="og:image"            content="{{ asset('images/og-image.jpg') }}">
    <meta property="og:image:alt"        content="{{ __('landing.meta.og_title') }}">
    <meta property="og:image:width"      content="1200">
    <meta property="og:image:height"     content="630">
    <meta property="og:locale"           content="{{ $locale === 'ar' ? 'ar_AR' : 'en_US' }}">
    <meta property="og:locale:alternate" content="{{ $locale === 'ar' ? 'en_US' : 'ar_AR' }}">
    <meta property="og:site_name"        content="Muallimx">
    <!-- Twitter / X Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@Muallimx">
    <meta name="twitter:creator"     content="@Muallimx">
    <meta name="twitter:url"         content="{{ url('/') }}">
    <meta name="twitter:title"       content="{{ __('landing.meta.og_title') }}">
    <meta name="twitter:description" content="{{ __('landing.meta.og_description') }}">
    <meta name="twitter:image"       content="{{ asset('images/og-image.jpg') }}">
    <meta name="twitter:image:alt"   content="{{ __('landing.meta.og_title') }}">
    @include('partials.favicon-links')
    @include('partials.seo-jsonld', ['jsonldType' => 'website'])

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    mx: {
                        navy: '#283593',
                        indigo: '#1F2A7A',
                        orange: '#FB5607',
                        cream: '#FFF7ED',
                        rose: '#FFE5F7',
                        gold: '#FFE569',
                        soft: '#F7F8FF'
                    }
                },
                fontFamily: {
                    heading: ['Cairo','Tajawal','IBM Plex Sans Arabic','sans-serif'],
                    body: ['Cairo','IBM Plex Sans Arabic','Tajawal','sans-serif'],
                }
            }
        }
    }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

    <style>
        [x-cloak]{display:none !important}
        *{font-family:'Cairo','IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif}
        h1,h2,h3,h4,h5,h6,.font-heading{font-family:'Cairo','Tajawal','IBM Plex Sans Arabic',sans-serif}
        html{scroll-behavior:smooth;overflow-x:hidden}
        body{overflow-x:hidden;background:#fff;min-height:100vh;display:flex;flex-direction:column}

        .container-1200{max-width:1200px;margin-inline:auto;padding-inline:24px}
        @media (max-width: 768px){.container-1200{padding-inline:16px}}

        .reveal{opacity:0;transform:translateY(26px);transition:opacity .6s ease,transform .6s ease}
        .reveal.revealed{opacity:1;transform:translateY(0)}
        .s1{transition-delay:.06s}.s2{transition-delay:.12s}.s3{transition-delay:.18s}.s4{transition-delay:.24s}

        .btn-primary{padding:12px 24px;border-radius:16px;font-weight:700;color:#fff;background:#FB5607;transition:transform .2s ease,box-shadow .2s ease}
        .btn-primary:hover{transform:scale(1.02);box-shadow:0 12px 28px -10px rgba(251,86,7,.45)}
        .btn-secondary{padding:12px 24px;border-radius:16px;border:1px solid #d6daea;color:#1F2A7A;background:#fff;transition:background .2s ease}
        .btn-secondary:hover{background:#f8f9ff}

        .card-base{border-radius:18px;padding:20px;box-shadow:0 8px 24px -18px rgba(31,42,122,.25);border:1px solid #eceef8;background:#fff}
        .hover-lift{transition:transform .25s ease,box-shadow .25s ease}
        .hover-lift:hover{transform:translateY(-4px) scale(1.01);box-shadow:0 20px 35px -20px rgba(31,42,122,.35)}

        #scroll-progress{position:fixed;top:0;left:0;height:3px;width:0;background:linear-gradient(90deg,#FB5607,#FFE569);z-index:9999}

        .arrow-link::after{content:'\f177';font-family:'Font Awesome 6 Free';font-weight:900;margin-inline-start:8px}
        [dir='ltr'] .arrow-link::after{content:'\f178'}
        .home-testimonials-wrap{overflow:hidden;-webkit-mask-image:linear-gradient(to right,transparent,black 4%,black 96%,transparent);mask-image:linear-gradient(to right,transparent,black 4%,black 96%,transparent)}
        .home-testimonials-scroller{display:flex;flex-direction:row;gap:1rem;overflow-x:auto;overscroll-behavior-x:contain;scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none;-ms-overflow-style:none;touch-action:pan-x;padding-block:4px}
        .home-testimonials-scroller::-webkit-scrollbar{display:none}
        .home-testimonials-slide{scroll-snap-align:center;flex-shrink:0}

        /* separated sticky header */
        .navbar-spacer{display:block!important}
        #navbar,#navbar.nav-transparent,#navbar.nav-solid{
            background:rgba(31,42,122,.92)!important;
            backdrop-filter:blur(12px)!important;
            -webkit-backdrop-filter:blur(12px)!important;
            border-bottom:1px solid rgba(255,255,255,.08)!important;
        }
        .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .home-instructor-carousel::-webkit-scrollbar{display:none}
        .home-instructor-carousel{-ms-overflow-style:none;scrollbar-width:none}
    </style>
</head>
<body class="font-body text-slate-800">
<div id="scroll-progress"></div>

@include('components.unified-navbar')

<main class="flex-1">
    {{-- 1) Hero --}}
    <section class="pt-10 sm:pt-14 lg:pt-16 pb-10 sm:pb-12 overflow-hidden relative" style="background:radial-gradient(circle at 12% 80%,rgba(255,229,247,.65),transparent 28%),radial-gradient(circle at 88% 20%,rgba(40,53,147,.10),transparent 30%),linear-gradient(180deg,#f4f6ff 0%,#fbfbff 55%,#ffffff 100%)">
        <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image:radial-gradient(circle at 1px 1px,rgba(40,53,147,.08) 1px,transparent 0);background-size:30px 30px"></div>
        <div class="container-1200 relative z-10">
            <div class="max-w-4xl mx-auto text-center reveal">
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs sm:text-sm font-bold mb-6" style="background:#FFE5F7;color:#283593;border:1px solid #f5c7e8">
                    <i class="fas fa-chalkboard-teacher"></i> منصة للمعلمين
                </span>
                <h1 class="font-heading text-[2rem] sm:text-[2.8rem] lg:text-[3.35rem] leading-[1.22] font-black text-mx-indigo mb-5">
                    كل أدوات المعلم المحترف
                    <span class="block" style="color:#FB5607">في باقة واحدة واضحة</span>
                </h1>
                <p class="text-slate-600 text-base sm:text-lg leading-8 mb-7 max-w-3xl mx-auto">
                    Muallimx تساعد المعلم على التحضير والتدريس والنمو المهني: مناهج جاهزة، فيديوهات تعليمية، أدوات ذكاء اصطناعي، فصل افتراضي، وتسويق شخصي.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                    <a class="btn-primary inline-flex items-center justify-center gap-2 !bg-[#FB5607] hover:!bg-[#e84d00]" href="{{ route('register') }}">ابدأ مجاناً <i class="fas fa-user-plus text-xs"></i></a>
                    <a class="btn-secondary inline-flex items-center justify-center gap-2 !bg-[#283593] !text-white !border-[#283593] hover:!bg-[#1f2a7a]" href="{{ route('public.pricing') }}#plans">عرض الباقات <i class="fas fa-tags text-xs"></i></a>
                    <button type="button" id="pwa-install-quick-btn" class="btn-secondary hidden inline-flex items-center justify-center gap-2 !bg-white !text-[#283593] !border-[#283593] hover:!bg-[#f8f9ff]">
                        تثبيت التطبيق <i class="fas fa-mobile-screen-button text-xs"></i>
                    </button>
                </div>
            </div>

            @php
                $hs = $homeStats ?? ['learners' => 0, 'features' => 0, 'plans' => 0, 'services' => 0];
                $fmt = fn (int $n) => number_format($n, 0, '.', ',');
            @endphp
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-10 reveal s2">
                <article class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-white text-center shadow-[0_10px_24px_-18px_rgba(31,42,122,.25)]">
                    <p class="text-3xl sm:text-4xl font-black text-mx-indigo" dir="ltr">{{ $fmt((int) ($hs['learners'] ?? 0)) }}</p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">معلم على المنصة</p>
                </article>
                <article class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-white text-center shadow-[0_10px_24px_-18px_rgba(31,42,122,.25)]">
                    <p class="text-3xl sm:text-4xl font-black text-[#FB5607]" dir="ltr">{{ $fmt((int) ($hs['features'] ?? 0)) }}+</p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">ميزة للمعلم</p>
                </article>
                <article class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-[#FFE5F7] text-center shadow-[0_10px_24px_-18px_rgba(31,42,122,.25)]">
                    <p class="text-3xl sm:text-4xl font-black text-mx-indigo" dir="ltr">{{ $fmt((int) ($hs['plans'] ?? 3)) }}</p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">باقات اشتراك</p>
                </article>
                <article class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-[#fffbea] text-center shadow-[0_10px_24px_-18px_rgba(31,42,122,.25)]">
                    <p class="text-3xl sm:text-4xl font-black text-mx-indigo" dir="ltr">{{ $fmt((int) ($hs['services'] ?? 0)) }}</p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">خدمات مساندة</p>
                </article>
            </div>
        </div>
    </section>

    {{-- ماذا نقدّم؟ شرح واضح للعميل --}}
    <section id="what-we-offer" class="py-14 sm:py-16 bg-white border-t border-slate-100">
        <div class="container-1200">
            <div class="max-w-3xl mx-auto text-center reveal mb-10">
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold mb-4" style="background:#FFE5F7;color:#283593">ماذا نقدّم؟</span>
                <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-4">بجمل بسيطة: منصة أدوات للمعلم</h2>
                <p class="text-slate-600 text-base sm:text-lg leading-8">
                    كثير من العملاء يسألوننا على واتساب: «أنتم بتقدّموا إيه؟»
                    الإجابة المختصرة: <strong class="text-mx-indigo">Muallimx منصة اشتراك للمعلمين</strong> تمنحك أدوات جاهزة للتحضير والتدريس والنمو المهني داخل حساب واحد.
                </p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 reveal s2">
                @php
                    $offers = [
                        ['icon' => 'fa-book-open', 'title' => 'مكتبة مناهج تفاعلية', 'desc' => 'محتوى جاهز للتحضير والتدريس بدل البدء من الصفر كل مرة.'],
                        ['icon' => 'fa-play-circle', 'title' => 'قنوات فيديو تعليمية', 'desc' => 'فيديوهات منظمة تُشغَّل داخل المنصة مع عنوان وشرح لكل درس.'],
                        ['icon' => 'fa-robot', 'title' => 'أدوات ذكاء اصطناعي', 'desc' => 'مساعدة في التحضير والنصائح والألعاب التعليمية بسرعة.'],
                        ['icon' => 'fa-chalkboard-teacher', 'title' => 'فصل افتراضي (Classroom)', 'desc' => 'عقد حصص أونلاين ضمن الباقات التي تشمل الميزة.'],
                        ['icon' => 'fa-user-tie', 'title' => 'تسويق شخصي للمعلم', 'desc' => 'بناء ملفك وظهورك للأكاديميات وفرص التدريس.'],
                        ['icon' => 'fa-headset', 'title' => 'دعم ومتابعة', 'desc' => 'قنوات دعم فني ومتابعة حتى تستفيد من أدواتك فعلياً.'],
                    ];
                @endphp
                @foreach($offers as $offer)
                    <article class="card-base hover-lift h-full">
                        <div class="w-11 h-11 rounded-xl mb-3 flex items-center justify-center text-[#FB5607]" style="background:#fff3ec">
                            <i class="fas {{ $offer['icon'] }}"></i>
                        </div>
                        <h3 class="font-heading font-bold text-mx-indigo mb-2">{{ $offer['title'] }}</h3>
                        <p class="text-sm text-slate-600 leading-7">{{ $offer['desc'] }}</p>
                    </article>
                @endforeach
            </div>
            <p class="text-center text-sm text-slate-500 mt-8 reveal">
                تريد التفاصيل والأسعار؟
                <a href="{{ route('public.pricing') }}#plans" class="font-bold text-[#FB5607] underline underline-offset-2">اطلع على الباقات من هنا</a>
            </p>
        </div>
    </section>

    {{-- مجالات الخدمة --}}
    <section class="py-12 sm:py-14 bg-mx-soft">
        <div class="container-1200">
            <div class="max-w-3xl mb-7 reveal">
                <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-3">{{ __('public.home_categories_title') }}</h2>
                <p class="text-slate-600 text-sm sm:text-base leading-7">{{ __('public.home_categories_subtitle') }}</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach(($homeCategories ?? collect()) as $cat)
                    <a href="{{ $cat['url'] ?? route('public.services.index') }}" class="reveal card-base hover-lift block no-underline text-inherit text-start p-5 flex flex-col h-full">
                        <div class="w-11 h-11 rounded-xl mb-3 flex items-center justify-center text-mx-orange shrink-0" style="background:#fff3ec"><i class="fas {{ $cat['icon'] }}"></i></div>
                        <h3 class="font-heading font-bold text-mx-indigo leading-snug mb-2">{{ $cat['name'] }}</h3>
                        <p class="text-xs sm:text-sm text-slate-600 leading-relaxed grow">{{ $cat['description'] ?? '' }}</p>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- كيف تعمل؟ --}}
    <section class="py-12 sm:py-16 bg-white">
        <div class="container-1200">
            <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-8 reveal text-center">كيف تبدأ خلال دقائق؟</h2>
            <div class="grid lg:grid-cols-3 gap-4 relative max-w-5xl mx-auto">
                <div class="hidden lg:block absolute top-11 right-[16%] left-[16%] h-px" style="background:linear-gradient(to left,#cdd6ff,#f0f3ff,#cdd6ff)"></div>
                @php $steps=[['أنشئ حسابك','تسجيل سريع ثم تدخل لوحة المعلم مباشرة.','fa-user-plus'],['اختر باقتك','مجانية للتجربة أو أساسية/شاملة حسب احتياجك.','fa-tags'],['فعّل أدواتك','مناهج، AI، فيديو، كلاس رووم — من داخل حسابك.','fa-rocket']]; @endphp
                @foreach($steps as $i=>$st)
                <article class="reveal s{{ $i+1 }} card-base relative text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-4 text-white" style="background:{{ $i===1 ? '#FB5607':'#283593' }}"><i class="fas {{ $st[2] }}"></i></div>
                    <h3 class="font-heading text-xl font-extrabold text-mx-indigo mb-2">{{ $st[0] }}</h3>
                    <p class="text-sm text-slate-600 leading-7">{{ $st[1] }}</p>
                </article>
                @endforeach
            </div>
        </div>
    </section>

    {{-- الباقات على الصفحة الرئيسية --}}
    <section id="home-plans" class="py-14 sm:py-16 bg-mx-soft scroll-mt-24">
        <div class="container-1200">
            <div class="text-center mb-10 reveal max-w-3xl mx-auto">
                <span class="inline-block px-4 py-1.5 rounded-full text-sm font-semibold mb-4" style="background:#FFE5F7;color:#283593">باقات المعلمين</span>
                <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-3">اختر باقتك من هنا مباشرة</h2>
                <p class="text-slate-600 leading-7">الأسعار واضحة والمزايا ظاهرة — بدون الحاجة تسأل «أنتم بتقدّموا إيه؟» على واتساب.</p>
            </div>

            @php
                $planKeys = ['teacher_free', 'teacher_starter', 'teacher_pro'];
                $billingPhrases = ['monthly' => 'جنيه شهريًا', 'quarterly' => 'جنيه / 3 شهور', 'yearly' => 'جنيه سنويًا'];
                $teacherPlans = $teacherPlans ?? [];
            @endphp

            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 max-w-6xl mx-auto">
                @foreach($planKeys as $planKey)
                    @php
                        $plan = $teacherPlans[$planKey] ?? null;
                        if (!$plan) continue;
                        if ($planKey === 'teacher_free' && !filter_var($plan['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN)) continue;
                        $label = $plan['label'] ?? $planKey;
                        $price = (float) ($plan['price'] ?? 0);
                        $cycle = $plan['billing_cycle'] ?? 'monthly';
                        $cyclePhrase = $billingPhrases[$cycle] ?? 'جنيه';
                        $features = array_slice($plan['features'] ?? [], 0, 6);
                        $isPro = $planKey === 'teacher_pro';
                        $isFree = $planKey === 'teacher_free';
                        $badge = trim((string) ($plan['card_badge'] ?? ''));
                        $subtitle = $plan['card_subtitle'] ?? '';
                        $cta = $plan['card_cta'] ?? 'ابدأ الآن';
                    @endphp
                    <article class="reveal card-base hover-lift !p-7 flex flex-col relative
                        {{ $isPro ? 'border-[#283593] ring-2 ring-[#283593]/10' : ($isFree ? 'border-emerald-400 ring-2 ring-emerald-500/15' : 'border-slate-200') }}">
                        @if($badge !== '')
                            <div class="absolute top-3 left-4 bg-[#FB5607] text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg z-10">{{ $badge }}</div>
                        @endif
                        <h3 class="text-2xl font-black text-mx-indigo mb-1">{{ $label }}</h3>
                        @if($subtitle !== '')
                            <p class="text-sm font-semibold text-[#283593] mb-4">{{ $subtitle }}</p>
                        @endif
                        <div class="mb-5">
                            @if($isFree)
                                <p class="text-3xl font-black text-mx-indigo">مجاناً</p>
                                <p class="text-sm font-bold text-emerald-700 mt-1">لمدة {{ (int) ($plan['duration_days'] ?? 14) }} يوماً</p>
                            @else
                                <p class="text-3xl font-black text-mx-indigo">{{ number_format($price, 0) }} <span class="text-base font-bold text-slate-600">{{ $cyclePhrase }}</span></p>
                            @endif
                        </div>
                        <ul class="space-y-2.5 text-sm text-slate-700 mb-7 flex-1">
                            @foreach($features as $featureKey)
                                <li class="flex items-start gap-2">
                                    <i class="fas fa-check-circle text-[#283593] mt-0.5"></i>
                                    <span class="font-semibold">{{ __("student.subscription_feature.{$featureKey}") }}</span>
                                </li>
                            @endforeach
                        </ul>
                        @if($isFree)
                            @auth
                                <form action="{{ route('public.subscription.activate-free') }}" method="POST" class="w-full">
                                    @csrf
                                    <input type="hidden" name="plan" value="teacher_free">
                                    <button type="submit" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white">{{ $cta }}</button>
                                </form>
                            @else
                                <a href="{{ route('login', ['intended' => route('public.pricing')]) }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl font-bold text-sm bg-emerald-600 hover:bg-emerald-700 text-white">سجّل الدخول للتفعيل المجاني</a>
                            @endauth
                        @else
                            <a href="{{ route('public.subscription.checkout', $planKey) }}" class="w-full inline-flex items-center justify-center px-6 py-3 rounded-xl font-bold text-sm {{ $isPro ? 'bg-[#283593] hover:bg-[#1f2a7a] text-white' : 'bg-[#FB5607] hover:bg-[#e84d00] text-white' }}">{{ $cta }}</a>
                        @endif
                    </article>
                @endforeach
            </div>

            <div class="text-center mt-8">
                <a href="{{ route('public.pricing') }}#plans" class="btn-secondary inline-flex items-center gap-2">تفاصيل أكثر عن الباقات <i class="fas fa-arrow-{{ $isRtl ? 'left' : 'right' }} text-xs"></i></a>
            </div>
        </div>
    </section>

    {{-- آراء المعلمين --}}
    @if(isset($homeTestimonials) && $homeTestimonials->isNotEmpty())
    @php
        $tCount = $homeTestimonials->count();
    @endphp
    <section class="py-14 sm:py-20 bg-white border-t border-slate-100">
        <div class="container-1200 mb-8 flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 reveal">
            <div>
                <span class="inline-flex items-center gap-2 rounded-full px-3 py-1 text-xs font-bold mb-3" style="background:#FFE5F7;color:#283593">{{ __('public.testimonials_page_title') }}</span>
                <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo">{{ __('public.home_testimonials_heading') }}</h2>
                <p class="text-slate-600 text-sm sm:text-base mt-2 max-w-xl leading-7">{{ __('public.home_testimonials_sub') }}</p>
            </div>
            <a href="{{ route('public.testimonials') }}" class="btn-secondary inline-flex items-center justify-center gap-2 shrink-0 text-sm">{{ __('public.home_testimonials_all_link') }} <i class="fas fa-arrow-{{ $isRtl ? 'left' : 'right' }} text-xs"></i></a>
        </div>
        <div class="home-testimonials-wrap reveal">
            @if($tCount === 1)
                <div class="flex justify-center px-1">
                    @include('partials.home-testimonial-card', ['t' => $homeTestimonials->first()])
                </div>
            @else
                <div id="home-t-scroll" class="home-testimonials-scroller" dir="ltr" role="region" aria-label="{{ __('public.home_testimonials_heading') }}">
                    @foreach($homeTestimonials as $tItem)
                        <div class="home-testimonials-slide">
                            @include('partials.home-testimonial-card', ['t' => $tItem])
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </section>
    @endif

    {{-- CTA نهائي --}}
    <section class="pt-14 sm:pt-18 pb-10 sm:pb-12" style="background:linear-gradient(180deg,#f4f7ff 0%,#ffffff 100%)">
        <div class="container-1200">
            <div class="reveal rounded-[28px] border border-slate-200 bg-white shadow-[0_20px_44px_-26px_rgba(31,42,122,.28)] px-6 sm:px-10 py-10 sm:py-12 text-center">
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs sm:text-sm font-bold mb-5" style="background:#FFE5F7;color:#283593">
                    <i class="fas fa-rocket"></i> خطوتك التالية واضحة
                </span>
                <h2 class="font-heading text-3xl sm:text-5xl font-black text-mx-indigo mb-4">جاهز تفعّل أدواتك كمعلم؟</h2>
                <p class="text-slate-600 text-base sm:text-lg max-w-3xl mx-auto leading-8 mb-7">أنشئ حساباً، اختر باقتك، وابدأ استخدام المناهج والذكاء الاصطناعي وباقي المزايا من لوحة واحدة بسيطة.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    <a href="{{ route('register') }}" class="btn-primary inline-flex items-center justify-center gap-2">إنشاء حساب مجاني <i class="fas fa-arrow-{{ $isRtl ? 'left' : 'right' }} text-xs"></i></a>
                    <a href="{{ route('public.pricing') }}#plans" class="btn-secondary inline-flex items-center justify-center gap-2">عرض الباقات</a>
                </div>
            </div>
        </div>
    </section>
</main>

{{-- 9) Footer — موحّد مع باقي الصفحات العامة (إعدادات النظام) --}}
@include('components.unified-footer')

@if(isset($popupAd) && $popupAd)
    @include('partials.popup-ad', ['ad' => $popupAd])
@endif

<button type="button"
        id="pwa-install-floating-btn"
        class="hidden fixed z-[9998] items-center justify-center rounded-full shadow-lg text-white"
        style="
            width: 56px;
            height: 56px;
            {{ $isRtl ? 'left' : 'right' }}: 18px;
            bottom: 86px;
            background-color: #283593;
            box-shadow: 0 10px 25px -10px rgba(0,0,0,.45);
        "
        aria-label="تثبيت التطبيق">
    <i class="fas fa-download text-xl"></i>
</button>

<script>
(function(){
    'use strict';
    function progress(){var s=window.pageYOffset||document.documentElement.scrollTop,h=document.documentElement.scrollHeight-window.innerHeight,p=h>0?(s/h)*100:0,b=document.getElementById('scroll-progress');if(b)b.style.width=p+'%';}
    window.addEventListener('scroll',progress,{passive:true});

    function reveal(){var els=document.querySelectorAll('.reveal');if(!els.length)return;var io=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('revealed');io.unobserve(e.target);}});},{threshold:.12,rootMargin:'0px 0px -50px 0px'});els.forEach(function(el){io.observe(el)});}
    if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',reveal);}else{reveal();}
})();

(function () {
    var quickBtn = document.getElementById('pwa-install-quick-btn');
    var floatingBtn = document.getElementById('pwa-install-floating-btn');
    if (!floatingBtn) return;

    var deferredPrompt = null;
    var isStandalone = window.matchMedia('(display-mode: standalone)').matches || window.navigator.standalone === true;
    if (isStandalone) return;

    if ('serviceWorker' in navigator) {
        window.addEventListener('load', function () {
            navigator.serviceWorker.register('/sw.js?v=3').catch(function () {});
        });
    }

    function showInstallButtons() {
        floatingBtn.classList.remove('hidden');
        floatingBtn.classList.add('inline-flex');
        if (quickBtn) {
            quickBtn.classList.remove('hidden');
            quickBtn.classList.add('inline-flex');
        }
    }

    function hideInstallButtons() {
        floatingBtn.classList.add('hidden');
        floatingBtn.classList.remove('inline-flex');
        if (quickBtn) {
            quickBtn.classList.add('hidden');
            quickBtn.classList.remove('inline-flex');
        }
    }

    var isIos = /iphone|ipad|ipod/i.test(window.navigator.userAgent);

    async function triggerInstall() {
        if (isIos) {
            alert('في iPhone/iPad: اضغط زر المشاركة ثم اختر "إضافة إلى الشاشة الرئيسية".');
            return;
        }
        if (!deferredPrompt) {
            alert('التثبيت المباشر غير متاح الآن. من قائمة المتصفح اختر "Install app" أو "تثبيت التطبيق".');
            return;
        }
        deferredPrompt.prompt();
        try { await deferredPrompt.userChoice; } catch (e) {}
        deferredPrompt = null;
    }

    window.addEventListener('beforeinstallprompt', function (e) {
        e.preventDefault();
        deferredPrompt = e;
        showInstallButtons();
    });

    window.addEventListener('appinstalled', function () {
        hideInstallButtons();
    });

    floatingBtn.addEventListener('click', triggerInstall);
    if (quickBtn) quickBtn.addEventListener('click', triggerInstall);

    if (isIos) {
        showInstallButtons();
    }
})();
</script>
</body>
</html>

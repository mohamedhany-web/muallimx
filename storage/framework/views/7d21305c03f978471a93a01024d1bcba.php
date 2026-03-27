<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>" itemscope itemtype="https://schema.org/EducationalOrganization">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">

    <title><?php echo e(__('landing.meta.title')); ?></title>
    <meta name="title" content="<?php echo e(__('landing.meta.title')); ?>">
    <meta name="description" content="<?php echo e(__('landing.meta.description')); ?>">
    <meta name="keywords" content="تأهيل المعلمين, تدريب المعلمين أونلاين, أدوات المعلم الرقمية, ذكاء اصطناعي للمعلم, مولد خطة الدرس, بناء بروفايل المعلم, توظيف المعلمين, دبلومات تعليمية, مناهج تفاعلية, MuallimX, معلم أونلاين, تطوير مهني للمعلمين">
    <meta name="author" content="MuallimX">
    <meta name="robots" content="index, follow, max-image-preview:large, max-snippet:-1, max-video-preview:-1">
    <meta name="language" content="Arabic">
    <meta name="revisit-after" content="7 days">
    <meta name="rating" content="general">
    <meta name="distribution" content="global">

    <link rel="canonical" href="<?php echo e(url('/')); ?>">
    <link rel="alternate" hreflang="ar" href="<?php echo e(url('/')); ?>?lang=ar">
    <link rel="alternate" hreflang="en" href="<?php echo e(url('/')); ?>?lang=en">
    <link rel="alternate" hreflang="x-default" href="<?php echo e(url('/')); ?>?lang=ar">

    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url('/')); ?>">
    <meta property="og:title" content="<?php echo e(__('landing.meta.og_title')); ?>">
    <meta property="og:description" content="<?php echo e(__('landing.meta.og_description')); ?>">
    <meta property="og:image" content="<?php echo e(asset('images/og-image.jpg')); ?>">
    <meta property="og:image:width" content="1200">
    <meta property="og:image:height" content="630">
    <meta property="og:image:alt" content="<?php echo e(__('landing.meta.og_title')); ?>">
    <meta property="og:locale" content="<?php echo e($locale === 'ar' ? 'ar_AR' : 'en_US'); ?>">
    <meta property="og:site_name" content="MuallimX">

    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:url" content="<?php echo e(url('/')); ?>">
    <meta name="twitter:title" content="<?php echo e(__('landing.meta.og_title')); ?>">
    <meta name="twitter:description" content="<?php echo e(__('landing.meta.og_description')); ?>">
    <meta name="twitter:image" content="<?php echo e(asset('images/og-image.jpg')); ?>">
    <meta name="twitter:image:alt" content="MuallimX — منصة تأهيل المعلمين">

    <meta name="theme-color" content="#0F172A">
    <meta name="msapplication-TileColor" content="#0F172A">
    <meta name="application-name" content="MuallimX">

    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('logo-removebg-preview.png')); ?>">
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo e(asset('logo-removebg-preview.png')); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <?php
    $schema1 = [
        "@context" => "https://schema.org",
        "@type" => "EducationalOrganization",
        "name" => "MuallimX",
        "alternateName" => "معلّمكس",
        "url" => url('/'),
        "logo" => asset('images/logo.png'),
        "description" => "منصّة عربية متخصصة في تأهيل وتطوير المعلمين للعمل أونلاين باحتراف من خلال تدريب تطبيقي ومناهج تفاعلية وأدوات ذكاء اصطناعي وبناء بروفايل مهني.",
        "contactPoint" => ["@type" => "ContactPoint", "contactType" => "Customer Service", "email" => "info@mualimx.com", "availableLanguage" => "Arabic"],
        "sameAs" => [],
    ];
    $schema2 = [
        "@context" => "https://schema.org",
        "@type" => "WebSite",
        "name" => "MuallimX",
        "url" => url('/'),
        "potentialAction" => [
            "@type" => "SearchAction",
            "target" => ["@type" => "EntryPoint", "urlTemplate" => url('/courses?search={search_term_string}')],
            "query-input" => "required name=search_term_string"
        ]
    ];
    ?>
    <script type="application/ld+json"><?php echo json_encode($schema1, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>
    <script type="application/ld+json"><?php echo json_encode($schema2, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?></script>

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
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

    <style>
        [x-cloak]{display:none !important}
        *{font-family:'IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif}
        h1,h2,h3,h4,h5,h6,.font-heading{font-family:'Tajawal','IBM Plex Sans Arabic',sans-serif}
        html{scroll-behavior:smooth;overflow-x:hidden !important;overflow-y:auto !important}
        body{overflow-x:hidden !important;overflow-y:auto !important;background:#fff;min-height:100vh;display:flex;flex-direction:column}
        body.overflow-hidden{overflow:hidden !important;position:fixed !important;width:100% !important}
        body>*{flex-shrink:0}

        .reveal{opacity:0;transform:translateY(40px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}
        .reveal.revealed{opacity:1;transform:translateY(0)}
        .reveal-left{opacity:0;transform:translateX(-50px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}
        .reveal-left.revealed{opacity:1;transform:translateX(0)}
        .reveal-right{opacity:0;transform:translateX(50px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}
        .reveal-right.revealed{opacity:1;transform:translateX(0)}
        .reveal-scale{opacity:0;transform:scale(.9);transition:opacity .7s cubic-bezier(.16,1,.3,1),transform .7s cubic-bezier(.16,1,.3,1)}
        .reveal-scale.revealed{opacity:1;transform:scale(1)}
        .stagger-1{transition-delay:.05s}.stagger-2{transition-delay:.1s}.stagger-3{transition-delay:.15s}.stagger-4{transition-delay:.2s}.stagger-5{transition-delay:.25s}.stagger-6{transition-delay:.3s}

        .glass{background:rgba(255,255,255,.7);backdrop-filter:blur(20px) saturate(180%);-webkit-backdrop-filter:blur(20px) saturate(180%);border:1px solid rgba(255,255,255,.4)}
        .glass-dark{background:rgba(15,23,42,.6);backdrop-filter:blur(24px) saturate(200%);-webkit-backdrop-filter:blur(24px) saturate(200%);border:1px solid rgba(255,255,255,.08)}

        @keyframes float-slow{0%,100%{transform:translateY(0) rotate(0)}50%{transform:translateY(-20px) rotate(3deg)}}
        @keyframes float-delayed{0%,100%{transform:translateY(0)}50%{transform:translateY(-15px) rotate(-2deg)}}
        .float-slow{animation:float-slow 8s ease-in-out infinite}
        .float-delayed{animation:float-delayed 10s ease-in-out infinite 2s}
        @keyframes pulse-ring{0%{transform:scale(.9);opacity:.8}50%{transform:scale(1.05);opacity:.4}100%{transform:scale(.9);opacity:.8}}
        .pulse-ring{animation:pulse-ring 3s ease-in-out infinite}

        .text-gradient{background:linear-gradient(135deg,#06b6d4 0%,#3b82f6 50%,#8b5cf6 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

        .btn-primary{position:relative;overflow:hidden;transition:all .4s cubic-bezier(.16,1,.3,1)}
        .btn-primary::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .6s}
        .btn-primary:hover::before{left:100%}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 20px 40px -12px rgba(6,182,212,.4)}
        .btn-outline{transition:all .3s cubic-bezier(.16,1,.3,1)}
        .btn-outline:hover{transform:translateY(-2px);box-shadow:0 10px 30px -10px rgba(15,23,42,.3)}
        .card-hover{transition:all .4s cubic-bezier(.16,1,.3,1)}
        .card-hover:hover{transform:translateY(-8px);box-shadow:0 25px 60px -15px rgba(0,0,0,.12)}

        details summary::-webkit-details-marker{display:none}
        details summary{list-style:none}
        details[open] .faq-icon{transform:rotate(45deg)}
        details[open] .faq-answer{animation:slideDown .3s ease-out}
        @keyframes slideDown{from{opacity:0;max-height:0;transform:translateY(-8px)}to{opacity:1;max-height:300px;transform:translateY(0)}}

        .noise::after{content:'';position:absolute;inset:0;opacity:.02;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");pointer-events:none}

        #scroll-progress{position:fixed;top:0;left:0;width:0%;height:3px;background:linear-gradient(90deg,#06b6d4,#3b82f6,#8b5cf6);z-index:9999;transition:width .1s linear}

        .popular-glow{box-shadow:0 0 0 1px rgba(6,182,212,.3),0 25px 60px -15px rgba(6,182,212,.25)}
        .testimonial-track{display:flex;transition:transform .6s cubic-bezier(.16,1,.3,1)}

        @media(max-width:768px){
            .reveal,.reveal-left,.reveal-right,.reveal-scale{transition-duration:.5s}
            .stagger-1,.stagger-2,.stagger-3,.stagger-4,.stagger-5,.stagger-6{transition-delay:0s}
        }
    </style>
</head>

<body class="bg-white text-navy-950 antialiased font-body">
    <div id="scroll-progress"></div>

    <?php echo $__env->make('components.unified-navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>.navbar-spacer{display:none}</style>
    <script>(function(){var n=document.getElementById('navbar');if(n){n.classList.add('nav-transparent');n.classList.remove('nav-solid');}})();</script>

    <main class="flex-1">

        
        <section id="hero" class="relative min-h-[92vh] flex items-center overflow-hidden bg-navy-950 noise">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-[#0c1833] to-navy-950"></div>
            <div class="absolute top-[-20%] <?php echo e($isRtl?'left-[-10%]':'right-[-10%]'); ?> w-[600px] h-[600px] rounded-full bg-brand-500/10 blur-[120px] float-slow"></div>
            <div class="absolute bottom-[-10%] <?php echo e($isRtl?'right-[-5%]':'left-[-5%]'); ?> w-[500px] h-[500px] rounded-full bg-blue-600/8 blur-[100px] float-delayed"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] rounded-full border border-white/[0.03]"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] rounded-full border border-white/[0.04]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.3) 1px,transparent 0);background-size:40px 40px"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 pt-28 pb-20 md:pt-36 md:pb-28 w-full">
                <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                    <div class="space-y-8">
                        <div class="reveal">
                            <span class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-white/[0.06] border border-white/[0.1] text-brand-300 text-sm font-medium backdrop-blur-sm">
                                <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                                المنصة العربية الأولى لتأهيل المعلمين للعمل أونلاين
                            </span>
                        </div>

                        <h1 class="reveal stagger-1 font-heading text-4xl sm:text-5xl md:text-6xl lg:text-[4.2rem] font-black leading-[1.15] text-white">
                            علّم أونلاين
                            <br>
                            <span class="text-gradient">باحتراف وثقة</span>
                        </h1>

                        <p class="reveal stagger-2 text-lg sm:text-xl text-slate-300/90 max-w-xl leading-relaxed font-light">
                            تدريب تطبيقي متقدم، أدوات ذكاء اصطناعي تختصر وقت تحضيرك، مناهج وأنشطة جاهزة، وبروفايل مهني يفتح لك فرص عمل حقيقية — أيًا كان تخصصك.
                        </p>

                        <div class="reveal stagger-3 flex flex-col sm:flex-row gap-4">
                            <a href="<?php echo e(route('register')); ?>" class="btn-primary inline-flex items-center justify-center gap-3 bg-gradient-to-l from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white font-bold text-base sm:text-lg px-8 py-4 rounded-2xl shadow-xl shadow-brand-600/25">
                                ابدأ رحلتك الآن
                                <i class="fas fa-arrow-left text-sm"></i>
                            </a>
                            <a href="<?php echo e(route('public.courses')); ?>" class="btn-outline inline-flex items-center justify-center gap-3 bg-white/[0.05] hover:bg-white/[0.1] border border-white/[0.15] text-white font-semibold text-base sm:text-lg px-8 py-4 rounded-2xl backdrop-blur-sm">
                                <i class="fas fa-play-circle text-brand-400"></i>
                                استعرض البرامج
                            </a>
                        </div>

                        <div class="reveal stagger-4 flex items-center gap-6 pt-4 flex-wrap">
                            <div class="flex -space-x-3 <?php echo e($isRtl?'-space-x-reverse':''); ?>">
                                <?php $__currentLoopData = ['ن','أ','م','س']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="w-10 h-10 rounded-full border-2 border-navy-950 bg-gradient-to-br from-brand-400 to-blue-500 flex items-center justify-center text-[11px] font-bold text-white"><?php echo e($i); ?></div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <div class="text-sm text-slate-400">
                                <span class="text-white font-bold text-lg">+3,000</span>
                                <br>معلّم يطوّرون مهاراتهم معنا
                            </div>
                            <div class="hidden sm:block h-8 w-px bg-white/10"></div>
                            <div class="text-sm text-slate-400">
                                <div class="flex items-center gap-1 text-amber-400 text-xs mb-0.5">
                                    <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star-half-alt"></i>
                                </div>
                                4.9 من 5 — تقييم المعلّمين
                            </div>
                        </div>
                    </div>

                    
                    <div class="reveal-right stagger-2 hidden lg:block">
                        <div class="relative">
                            <div class="absolute -inset-4 bg-gradient-to-br from-brand-500/20 via-blue-500/10 to-purple-500/10 rounded-3xl blur-2xl"></div>
                            <div class="relative glass-dark rounded-3xl p-6 space-y-5">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-xs text-slate-400">مساعد المعلّم الذكي</p>
                                        <p class="text-sm font-bold text-white mt-0.5">AI Teacher Assistant</p>
                                    </div>
                                    <span class="px-3 py-1 rounded-full bg-emerald-500/15 border border-emerald-500/30 text-emerald-300 text-xs font-semibold flex items-center gap-1.5">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                                        يعمل الآن
                                    </span>
                                </div>

                                <div class="rounded-2xl bg-white/[0.04] border border-white/[0.06] p-4 space-y-3">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-xl bg-brand-500/20 flex items-center justify-center text-brand-300"><i class="fas fa-wand-magic-sparkles text-sm"></i></div>
                                        <p class="text-sm text-white font-semibold">ولّد خطة درس كاملة في 30 ثانية</p>
                                    </div>
                                    <div class="bg-white/[0.03] rounded-xl p-3 text-xs text-slate-400 leading-relaxed border border-white/[0.04]">
                                        <p class="text-brand-300 font-semibold mb-1">📋 خطة درس: أساسيات القراءة — الصف الثاني</p>
                                        <p>• الأهداف: تمييز الحروف المتشابهة...</p>
                                        <p>• النشاط: لعبة المطابقة التفاعلية...</p>
                                        <p>• التقييم: ورقة عمل + تقييم شفوي...</p>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    <div class="rounded-2xl bg-white/[0.04] border border-white/[0.06] p-3 flex items-center gap-3">
                                        <span class="w-9 h-9 rounded-xl bg-purple-500/15 flex items-center justify-center text-purple-300"><i class="fas fa-file-alt text-sm"></i></span>
                                        <div><p class="text-[11px] text-slate-500">مناهج جاهزة</p><p class="text-sm font-bold text-white">+500</p></div>
                                    </div>
                                    <div class="rounded-2xl bg-white/[0.04] border border-white/[0.06] p-3 flex items-center gap-3">
                                        <span class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center text-amber-300"><i class="fas fa-puzzle-piece text-sm"></i></span>
                                        <div><p class="text-[11px] text-slate-500">أنشطة تعليمية</p><p class="text-sm font-bold text-white">+200</p></div>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-2 border-t border-white/[0.06]">
                                    <span class="text-xs text-slate-500">وفّر +5 ساعات تحضير أسبوعياً</span>
                                    <span class="text-xs text-brand-400 font-medium flex items-center gap-1">
                                        جرّب المساعد <i class="fas fa-arrow-left text-[9px]"></i>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
        </section>


        
        <section id="stats" class="relative py-6 -mt-12 z-20">
            <div class="max-w-6xl mx-auto px-5 sm:px-8">
                <div class="reveal glass rounded-3xl shadow-xl shadow-slate-200/50 p-6 sm:p-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6 md:gap-8">
                        <?php
                        $stats = [
                            ['icon'=>'fa-chalkboard-teacher','number'=>'3000','suffix'=>'+','label'=>'معلّم على المنصة','color'=>'brand'],
                            ['icon'=>'fa-graduation-cap','number'=>'120','suffix'=>'+','label'=>'دبلوم وكورس تدريبي','color'=>'blue'],
                            ['icon'=>'fa-robot','number'=>'10','suffix'=>'+','label'=>'أداة AI للمعلّم','color'=>'purple'],
                            ['icon'=>'fa-briefcase','number'=>'500','suffix'=>'+','label'=>'فرصة عمل تم تحقيقها','color'=>'emerald'],
                        ];
                        ?>
                        <?php $__currentLoopData = $stats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $stat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <div class="reveal stagger-<?php echo e($idx+1); ?> text-center group">
                            <div class="w-14 h-14 mx-auto mb-3 rounded-2xl bg-<?php echo e($stat['color']); ?>-50 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <i class="fas <?php echo e($stat['icon']); ?> text-<?php echo e($stat['color']); ?>-500 text-xl"></i>
                            </div>
                            <p class="font-heading text-3xl sm:text-4xl font-black text-navy-950" data-count="<?php echo e($stat['number']); ?>" data-suffix="<?php echo e($stat['suffix']); ?>">
                                <span class="counter-value">0</span><?php echo e($stat['suffix']); ?>

                            </p>
                            <p class="text-sm text-slate-500 mt-1 font-medium"><?php echo e($stat['label']); ?></p>
                        </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </div>
        </section>


        
        <section id="services" class="py-20 md:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-4">خدمات المنصة</span>
                    <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                        كل ما يحتاجه المعلّم
                        <span class="text-gradient">في مكان واحد</span>
                    </h2>
                    <p class="text-lg text-slate-500 leading-relaxed">
                        من التدريب والتأهيل إلى أدوات الذكاء الاصطناعي وبناء البروفايل — نجهّزك لتعمل أونلاين باحتراف.
                    </p>
                </div>

                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                    <?php
                    $services = [
                        [
                            'icon'=>'fa-university','title'=>'أكاديمية التدريب',
                            'desc'=>'دبلومات احترافية وكورسات قصيرة ومسارات تعلّم مصممة بحسب هدفك: عمل أونلاين، صناعة محتوى، تسويق للمعلمين، أو العمل بالدولار.',
                            'tags'=>['دبلومات','كورسات','مسارات'],
                            'color'=>'brand','count'=>'120+ برنامج',
                        ],
                        [
                            'icon'=>'fa-robot','title'=>'أدوات AI للمعلّم',
                            'desc'=>'مساعد ذكاء اصطناعي يولّد خطط الدروس والأنشطة والتقييمات ويساعدك في إعداد المحتوى وكتابة البروفايل والسيرة الذاتية.',
                            'tags'=>['مولّد الدروس','أنشطة','تقييمات'],
                            'color'=>'purple','count'=>'10+ أداة',
                        ],
                        [
                            'icon'=>'fa-user-tie','title'=>'البروفايل والتوظيف',
                            'desc'=>'ابنِ بروفايل احترافي (CV + مهارات + شهادات + فيديو تعريفي) وأتح ملفك للأكاديميات وجهات التوظيف للحصول على فرص حقيقية.',
                            'tags'=>['بروفايل','توظيف','مقابلات'],
                            'color'=>'emerald','count'=>'500+ فرصة',
                        ],
                        [
                            'icon'=>'fa-hands-helping','title'=>'الاستشارات والورش',
                            'desc'=>'جلسات إرشاد فردية (Mentoring)، تقييم أداء، وورش مباشرة مع خبراء — مع نظام حجز ودفع إلكتروني متكامل.',
                            'tags'=>['جلسات فردية','ورش','تقييم أداء'],
                            'color'=>'blue','count'=>'جلسات حيّة',
                        ],
                    ];
                    ?>

                    <?php $__currentLoopData = $services; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $srv): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="reveal stagger-<?php echo e($idx+1); ?> group">
                        <div class="card-hover h-full rounded-3xl bg-white border border-slate-100 p-6 sm:p-7 flex flex-col shadow-sm hover:shadow-xl hover:border-<?php echo e($srv['color']); ?>-200/50">
                            <div class="w-14 h-14 rounded-2xl bg-<?php echo e($srv['color']); ?>-50 group-hover:bg-<?php echo e($srv['color']); ?>-100 flex items-center justify-center mb-5 transition-colors duration-300">
                                <i class="fas <?php echo e($srv['icon']); ?> text-<?php echo e($srv['color']); ?>-500 text-2xl group-hover:scale-110 transition-transform duration-300"></i>
                            </div>
                            <h3 class="font-heading text-xl font-bold text-navy-950 mb-3"><?php echo e($srv['title']); ?></h3>
                            <p class="text-sm text-slate-500 leading-relaxed mb-5 flex-1"><?php echo e($srv['desc']); ?></p>
                            <div class="flex flex-wrap gap-2 mb-5">
                                <?php $__currentLoopData = $srv['tags']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $tag): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="px-3 py-1 rounded-full bg-slate-50 text-slate-600 text-xs font-medium"><?php echo e($tag); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <div class="flex items-center justify-between pt-4 border-t border-slate-50">
                                <span class="text-xs text-slate-400 flex items-center gap-1.5"><i class="fas fa-check-circle text-<?php echo e($srv['color']); ?>-400"></i><?php echo e($srv['count']); ?></span>
                                <a href="<?php echo e(route('public.courses')); ?>" class="text-<?php echo e($srv['color']); ?>-500 font-semibold text-sm flex items-center gap-1.5 hover:gap-3 transition-all duration-300">
                                    اكتشف المزيد <i class="fas fa-arrow-left text-xs"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>


        
        <section id="why-us" class="py-20 md:py-28 bg-slate-50/50">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="grid lg:grid-cols-2 gap-16 items-center">
                    <div>
                        <div class="reveal">
                            <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-4">لماذا MuallimX؟</span>
                            <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-6 leading-tight">
                                ليست مجرّد كورسات
                                <br><span class="text-gradient">بل منظومة متكاملة</span>
                            </h2>
                            <p class="text-lg text-slate-500 leading-relaxed mb-10">
                                نجمع بين التدريب العملي، أدوات الذكاء الاصطناعي، المناهج الجاهزة، والدعم المهني — لنساعدك تبدأ أو تطوّر مسيرتك كمعلّم أونلاين محترف.
                            </p>
                        </div>
                        <div class="grid sm:grid-cols-2 gap-5">
                            <?php
                            $features = [
                                ['icon'=>'fa-award','title'=>'مدرّبون خبراء','desc'=>'معلمون ناجحون أونلاين يشاركونك تجاربهم الحقيقية.','color'=>'brand'],
                                ['icon'=>'fa-wand-magic-sparkles','title'=>'أدوات AI ذكية','desc'=>'مساعد يختصر ساعات التحضير إلى دقائق.','color'=>'purple'],
                                ['icon'=>'fa-briefcase','title'=>'فرص عمل حقيقية','desc'=>'بروفايل احترافي يصلك بأكاديميات وجهات توظيف.','color'=>'emerald'],
                                ['icon'=>'fa-video','title'=>'ورش وجلسات حيّة','desc'=>'تفاعل مباشر مع خبراء التعليم والتوظيف.','color'=>'blue'],
                                ['icon'=>'fa-book-open','title'=>'مناهج وأنشطة جاهزة','desc'=>'مكتبة ضخمة من خطط الدروس والمواد التعليمية.','color'=>'amber'],
                                ['icon'=>'fa-certificate','title'=>'شهادات وإجازات','desc'=>'شهادات إتمام معتمدة وبرامج إجازات متخصصة.','color'=>'rose'],
                            ];
                            ?>
                            <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="reveal stagger-<?php echo e($idx+1); ?> flex gap-4 p-4 rounded-2xl hover:bg-white hover:shadow-md transition-all duration-300 group">
                                <div class="w-11 h-11 rounded-xl bg-<?php echo e($feat['color']); ?>-50 group-hover:bg-<?php echo e($feat['color']); ?>-100 flex items-center justify-center flex-shrink-0 transition-colors">
                                    <i class="fas <?php echo e($feat['icon']); ?> text-<?php echo e($feat['color']); ?>-500 text-lg"></i>
                                </div>
                                <div>
                                    <h4 class="font-heading font-bold text-navy-950 mb-1"><?php echo e($feat['title']); ?></h4>
                                    <p class="text-sm text-slate-500 leading-relaxed"><?php echo e($feat['desc']); ?></p>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>

                    <div class="reveal-right hidden lg:block">
                        <div class="relative">
                            <div class="absolute -inset-8 bg-gradient-to-br from-brand-100/40 to-blue-100/30 rounded-[2rem] blur-xl"></div>
                            <div class="relative glass rounded-3xl p-8 shadow-lg space-y-6">
                                <div class="flex items-center gap-4 p-4 rounded-2xl bg-white shadow-sm border border-slate-100">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center text-white"><i class="fas fa-wand-magic-sparkles text-lg"></i></div>
                                    <div class="flex-1">
                                        <p class="font-heading font-bold text-navy-950">AI يحضّر لك الحصة</p>
                                        <p class="text-sm text-slate-500">خطة درس + أنشطة + تقييم في دقيقة</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 p-4 rounded-2xl bg-white shadow-sm border border-slate-100">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-400 to-emerald-600 flex items-center justify-center text-white"><i class="fas fa-id-badge text-lg"></i></div>
                                    <div class="flex-1">
                                        <p class="font-heading font-bold text-navy-950">بروفايل يبيع نفسه</p>
                                        <p class="text-sm text-slate-500">CV + فيديو تعريفي + تقييمات</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 p-4 rounded-2xl bg-white shadow-sm border border-slate-100">
                                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-400 to-purple-600 flex items-center justify-center text-white"><i class="fas fa-handshake text-lg"></i></div>
                                    <div class="flex-1">
                                        <p class="font-heading font-bold text-navy-950">ربط مباشر بالأكاديميات</p>
                                        <p class="text-sm text-slate-500">فرص عمل ومقابلات حقيقية</p>
                                    </div>
                                </div>
                                <div class="p-4 rounded-2xl bg-gradient-to-l from-brand-500 to-blue-600 text-white">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <p class="font-bold text-lg">+5 ساعات</p>
                                            <p class="text-sm text-white/80">يوفرها AI أسبوعياً من وقت تحضيرك</p>
                                        </div>
                                        <i class="fas fa-clock text-3xl text-white/30"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        
        <section id="testimonials" class="py-20 md:py-28 bg-white overflow-hidden">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-amber-50 text-amber-600 text-sm font-semibold mb-4">قصص نجاح</span>
                    <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                        معلّمون غيّروا حياتهم
                        <span class="text-gradient">مع MuallimX</span>
                    </h2>
                </div>

                <div x-data="{active:0,total:3}" class="relative">
                    <div class="overflow-hidden rounded-3xl">
                        <div class="testimonial-track" :style="'transform:translateX('+active*100+'%)'">
                            <?php
                            $testimonials = [
                                ['name'=>'نورة العتيبي','role'=>'معلمة لغة عربية — تعمل أونلاين من الرياض','text'=>'كنت أدرّس في مدرسة بدوام كامل وراتب محدود. بعد دبلوم MuallimX بدأت أشتغل أونلاين مع 3 أكاديميات وأدوات الـAI وفّرت عليّ ساعات تحضير يومية. دخلي تضاعف والوقت صار بيدي.','rating'=>5,'initial'=>'ن','color'=>'brand'],
                                ['name'=>'أحمد المنصوري','role'=>'معلم رياضيات — يعمل بالدولار من مصر','text'=>'كنت أبحث عن طريقة أشتغل أونلاين بتخصصي. MuallimX جهّزت لي بروفايل احترافي وربطتني بأكاديمية في الخليج. الآن أدرّس أونلاين بالدولار وأنا في بيتي.','rating'=>5,'initial'=>'أ','color'=>'emerald'],
                                ['name'=>'سارة الكويتي','role'=>'معلمة قرآن كريم — حاصلة على إجازة','text'=>'برنامج الإجازات في MuallimX كان منظم واحترافي جداً. حصلت على الإجازة وبنيت بروفايل قوي وبدأت أستقبل طلاب من كل مكان. المنصة فعلاً غيّرت مساري المهني.','rating'=>5,'initial'=>'س','color'=>'purple'],
                            ];
                            ?>
                            <?php $__currentLoopData = $testimonials; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $test): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="w-full flex-shrink-0 px-2">
                                <div class="max-w-3xl mx-auto glass rounded-3xl p-8 sm:p-10 shadow-lg border border-slate-100">
                                    <div class="flex items-center gap-4 mb-6">
                                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-<?php echo e($test['color']); ?>-400 to-<?php echo e($test['color']); ?>-600 flex items-center justify-center text-white text-xl font-black shadow-lg"><?php echo e($test['initial']); ?></div>
                                        <div class="flex-1">
                                            <p class="font-heading font-bold text-lg text-navy-950"><?php echo e($test['name']); ?></p>
                                            <p class="text-sm text-slate-500"><?php echo e($test['role']); ?></p>
                                        </div>
                                        <div class="flex gap-0.5 text-amber-400"><?php for($s=0;$s<$test['rating'];$s++): ?><i class="fas fa-star text-sm"></i><?php endfor; ?></div>
                                    </div>
                                    <blockquote class="text-slate-600 text-base sm:text-lg leading-relaxed relative">
                                        <i class="fas fa-quote-right absolute -top-2 <?php echo e($isRtl?'-right-2':'-left-2'); ?> text-4xl text-brand-100 -z-10"></i>
                                        "<?php echo e($test['text']); ?>"
                                    </blockquote>
                                </div>
                            </div>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </div>
                    </div>
                    <div class="flex items-center justify-center gap-4 mt-8">
                        <button @click="active=active>0?active-1:total-1" class="w-12 h-12 rounded-full bg-white border border-slate-200 hover:border-brand-300 hover:bg-brand-50 flex items-center justify-center text-slate-500 hover:text-brand-500 transition-all shadow-sm"><i class="fas fa-arrow-right text-sm"></i></button>
                        <div class="flex gap-2"><?php for($d=0;$d<3;$d++): ?><button @click="active=<?php echo e($d); ?>" class="w-3 h-3 rounded-full transition-all duration-300" :class="active===<?php echo e($d); ?>?'bg-brand-500 w-8':'bg-slate-200 hover:bg-slate-300'"></button><?php endfor; ?></div>
                        <button @click="active=active<total-1?active+1:0" class="w-12 h-12 rounded-full bg-white border border-slate-200 hover:border-brand-300 hover:bg-brand-50 flex items-center justify-center text-slate-500 hover:text-brand-500 transition-all shadow-sm"><i class="fas fa-arrow-left text-sm"></i></button>
                    </div>
                </div>
            </div>
        </section>


        
        <section id="journey" class="py-20 md:py-28 bg-navy-950 noise relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-b from-navy-950 via-[#0c1833] to-navy-950"></div>
            <div class="absolute top-0 <?php echo e($isRtl?'left-0':'right-0'); ?> w-[500px] h-[500px] rounded-full bg-brand-500/5 blur-[120px]"></div>
            <div class="relative z-10 max-w-6xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-white/[0.06] border border-white/[0.1] text-brand-300 text-sm font-semibold mb-4">رحلتك في MuallimX</span>
                    <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-white mb-5 leading-tight">
                        من التعلّم إلى العمل
                        <br><span class="text-gradient">في 4 خطوات</span>
                    </h2>
                </div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-8 relative">
                    <div class="hidden lg:block absolute top-14 left-[12%] right-[12%] h-0.5 bg-gradient-to-l from-brand-500/50 via-blue-500/50 to-purple-500/50"></div>
                    <?php
                    $steps = [
                        ['num'=>'01','icon'=>'fa-graduation-cap','title'=>'تدرّب','desc'=>'سجّل في دبلوم أو كورس يناسب هدفك وتعلّم مع خبراء نجحوا في التعليم أونلاين.','color'=>'brand'],
                        ['num'=>'02','icon'=>'fa-tools','title'=>'جهّز أدواتك','desc'=>'استخدم مكتبة المناهج وأدوات AI لتحضير حصص احترافية في دقائق.','color'=>'purple'],
                        ['num'=>'03','icon'=>'fa-id-badge','title'=>'ابنِ بروفايلك','desc'=>'أنشئ ملفك المهني الاحترافي بكل مهاراتك وشهاداتك وفيديو تعريفي.','color'=>'blue'],
                        ['num'=>'04','icon'=>'fa-rocket','title'=>'ابدأ العمل','desc'=>'تقدّم لفرص العمل أو استقبل طلاب مباشرة من خلال بروفايلك على المنصة.','color'=>'emerald'],
                    ];
                    ?>
                    <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <div class="reveal stagger-<?php echo e($idx+1); ?> relative text-center">
                        <div class="w-14 h-14 mx-auto rounded-2xl bg-<?php echo e($step['color']); ?>-500/15 border border-<?php echo e($step['color']); ?>-500/25 flex items-center justify-center mb-5 relative z-10">
                            <i class="fas <?php echo e($step['icon']); ?> text-<?php echo e($step['color']); ?>-400 text-xl"></i>
                        </div>
                        <span class="font-heading text-5xl font-black text-white/[0.04] absolute top-0 <?php echo e($isRtl?'right-4':'left-4'); ?> lg:<?php echo e($isRtl?'right-auto':'left-auto'); ?> lg:mx-auto select-none"><?php echo e($step['num']); ?></span>
                        <h3 class="font-heading text-xl font-bold text-white mb-3"><?php echo e($step['title']); ?></h3>
                        <p class="text-sm text-slate-400 leading-relaxed"><?php echo e($step['desc']); ?></p>
                    </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>


        
        <section id="pricing" class="py-20 md:py-28 bg-white">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-16 reveal">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-4">الاشتراكات</span>
                    <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                        خطط مرنة تناسب
                        <span class="text-gradient">كل معلّم</span>
                    </h2>
                    <p class="text-lg text-slate-500 leading-relaxed">جميع الأسعار بالجنيه المصري (ج.م). تحديث الأسعار والمزايا من لوحة الإدارة يؤثر هنا وفي صفحة الأسعار.</p>
                </div>
                <?php
                    $planKeys = ['teacher_starter', 'teacher_pro', 'teacher_premium'];
                    $planMeta = [
                        'teacher_starter' => ['subtitle' => 'ابدأ التدريس أونلاين بسهولة', 'popular' => false, 'cta' => 'ابدأ الآن'],
                        'teacher_pro'     => ['subtitle' => 'أفضل اختيار للمعلمين الذين يريدون العمل أونلاين', 'popular' => true, 'cta' => 'اشترك الآن'],
                        'teacher_premium' => ['subtitle' => 'للمعلمين الجادين في بناء مسار مهني مستقر', 'popular' => false, 'cta' => 'اشترك الآن'],
                    ];
                    $billingPhrases = ['monthly' => 'ج.م / شهرياً', 'quarterly' => 'ج.م / 3 شهور', 'yearly' => 'ج.م / سنوياً'];
                ?>
                <div class="grid md:grid-cols-3 gap-6 lg:gap-8 items-start">
                    <?php $__currentLoopData = $planKeys; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $planKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php
                            $plan = $teacherPlans[$planKey] ?? null;
                            if (!$plan) continue;
                            $meta = $planMeta[$planKey] ?? [];
                            $name = $plan['label'] ?? $planKey;
                            $price = (float) ($plan['price'] ?? 0);
                            $cycle = $plan['billing_cycle'] ?? 'monthly';
                            $cyclePhrase = $billingPhrases[$cycle] ?? 'ج.م';
                            $features = $plan['features'] ?? [];
                            $popular = $meta['popular'] ?? false;
                        ?>
                        <div class="reveal stagger-<?php echo e($idx+1); ?> <?php echo e($popular ? 'md:-mt-4 md:mb-4' : ''); ?>">
                            <div class="rounded-3xl <?php echo e($popular ? 'bg-navy-950 text-white popular-glow border-2 border-brand-500/30' : 'bg-white border border-slate-200 shadow-sm hover:shadow-lg'); ?> p-7 sm:p-8 relative transition-shadow duration-300 h-full flex flex-col">
                                <?php if($popular): ?><span class="absolute -top-4 left-1/2 -translate-x-1/2 px-5 py-1.5 rounded-full bg-gradient-to-l from-brand-400 to-brand-600 text-white text-sm font-bold shadow-lg shadow-brand-600/30">الأكثر طلباً</span><?php endif; ?>
                                <div class="mb-6">
                                    <h3 class="font-heading text-xl font-bold <?php echo e($popular ? 'text-white' : 'text-navy-950'); ?> mb-2"><?php echo e($name); ?></h3>
                                    <p class="text-sm <?php echo e($popular ? 'text-slate-300' : 'text-slate-500'); ?>"><?php echo e($meta['subtitle'] ?? ''); ?></p>
                                </div>
                                <div class="mb-6">
                                    <span class="font-heading text-4xl sm:text-5xl font-black <?php echo e($popular ? 'text-white' : 'text-navy-950'); ?>"><?php echo e(number_format($price, 0)); ?></span>
                                    <span class="text-sm <?php echo e($popular ? 'text-slate-400' : 'text-slate-500'); ?>"> <?php echo e($cyclePhrase); ?></span>
                                </div>
                                <ul class="space-y-3 mb-8 flex-1">
                                    <?php $__currentLoopData = $features; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $featureKey): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                    <li class="flex items-center gap-3 text-sm">
                                        <span class="w-5 h-5 rounded-full <?php echo e($popular ? 'bg-brand-500/20 text-brand-300' : 'bg-emerald-50 text-emerald-500'); ?> flex items-center justify-center flex-shrink-0"><i class="fas fa-check text-[10px]"></i></span>
                                        <span class="<?php echo e($popular ? 'text-slate-200' : 'text-slate-600'); ?>"><?php echo e(__("student.subscription_feature.{$featureKey}")); ?></span>
                                    </li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </ul>
                                <a href="<?php echo e(route('public.subscription.checkout', $planKey)); ?>" class="block text-center font-bold text-base px-6 py-4 rounded-2xl transition-all duration-300 <?php echo e($popular ? 'btn-primary bg-gradient-to-l from-brand-400 to-brand-600 text-white shadow-lg shadow-brand-600/25' : 'btn-outline bg-navy-950 text-white hover:bg-navy-900'); ?>"><?php echo e($meta['cta'] ?? 'اشترك الآن'); ?></a>
                            </div>
                        </div>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>


        
        <section id="faq" class="py-20 md:py-28 bg-slate-50/50">
            <div class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-14 reveal">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-4">الأسئلة الشائعة</span>
                    <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5">كل ما تحتاج معرفته</h2>
                    <p class="text-lg text-slate-500 leading-relaxed">لم تجد جوابك؟ <a href="<?php echo e(route('public.contact')); ?>" class="text-brand-500 font-semibold hover:underline">تواصل معنا</a>.</p>
                </div>
                <div class="space-y-4">
                    <?php
                    $faqs = [
                        ['q'=>'هل MuallimX للمعلمين فقط؟','a'=>'نعم، المنصة مصممة خصيصاً للمعلمين بكل التخصصات — لغة عربية، إنجليزي، رياضيات، علوم، قرآن كريم، وغيرها. أي معلم يريد العمل أونلاين أو تطوير أدائه سيجد ما يحتاجه.'],
                        ['q'=>'كيف يساعدني مساعد AI في التحضير؟','a'=>'مساعد AI يولّد لك خطة درس كاملة، أنشطة تفاعلية، أوراق عمل، وتقييمات — فقط أدخل الموضوع والصف والمدة. يوفّر عليك +5 ساعات أسبوعياً من وقت التحضير.'],
                        ['q'=>'هل فعلاً يمكنني الحصول على فرصة عمل؟','a'=>'نعم! في خطة الماستر نبني لك بروفايل احترافي كامل ونربطك مباشرة بأكاديميات وجهات توظيف تبحث عن معلمين. أكثر من 500 معلم حصلوا على فرص عمل حقيقية من خلال المنصة.'],
                        ['q'=>'ما هي برامج الإجازات؟','a'=>'برامج متخصصة بإشراف شيوخ ومتخصصين لمعلمي القرآن الكريم والعلوم الشرعية للحصول على إجازة معتمدة تضاف لبروفايلك وترفع من قيمتك المهنية.'],
                        ['q'=>'هل أحتاج خبرة سابقة في التدريس أونلاين؟','a'=>'أبداً! برامجنا تبدأ من الصفر وتأخذك خطوة بخطوة — من إعداد بيئة التدريس الأونلاين إلى بناء بروفايل وبدء العمل فعلياً.'],
                        ['q'=>'هل يمكنني إلغاء اشتراكي في أي وقت؟','a'=>'نعم بالتأكيد. يمكنك إلغاء اشتراكك في أي لحظة. ستستمر صلاحيتك حتى نهاية الفترة المدفوعة بدون أي التزامات إضافية.'],
                    ];
                    ?>
                    <?php $__currentLoopData = $faqs; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $faq): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <details class="reveal stagger-<?php echo e(min($idx+1,6)); ?> group rounded-2xl bg-white border border-slate-100 shadow-sm hover:shadow-md transition-shadow">
                        <summary class="flex items-center justify-between gap-4 px-6 py-5 cursor-pointer select-none">
                            <span class="font-heading font-bold text-navy-950 text-base sm:text-lg"><?php echo e($faq['q']); ?></span>
                            <span class="faq-icon w-8 h-8 rounded-full bg-slate-50 group-hover:bg-brand-50 flex items-center justify-center text-slate-400 group-hover:text-brand-500 transition-all duration-300 flex-shrink-0"><i class="fas fa-plus text-xs"></i></span>
                        </summary>
                        <div class="faq-answer px-6 pb-5 text-slate-500 text-sm sm:text-base leading-relaxed"><?php echo e($faq['a']); ?></div>
                    </details>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
            </div>
        </section>


        
        <section id="cta" class="py-20 md:py-28 bg-navy-950 noise relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-[#0a1628] to-navy-950"></div>
            <div class="absolute top-[-30%] left-1/2 -translate-x-1/2 w-[800px] h-[800px] rounded-full bg-brand-500/8 blur-[150px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.3) 1px,transparent 0);background-size:40px 40px"></div>
            <div class="relative z-10 max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 text-center">
                <div class="reveal space-y-8">
                    <div class="w-20 h-20 mx-auto rounded-3xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center shadow-xl shadow-brand-600/25 pulse-ring">
                        <i class="fas fa-chalkboard-teacher text-white text-3xl"></i>
                    </div>
                    <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl lg:text-6xl font-black text-white leading-tight">
                        جاهز تبدأ مسيرتك
                        <span class="text-gradient">كمعلّم أونلاين؟</span>
                    </h2>
                    <p class="text-lg sm:text-xl text-slate-300/90 max-w-2xl mx-auto leading-relaxed">
                        انضم لآلاف المعلمين الذين اختاروا MuallimX لتطوير مهاراتهم وبناء مصدر دخل احترافي من التعليم عن بُعد.
                    </p>
                    <div class="flex flex-col sm:flex-row gap-4 justify-center items-stretch sm:items-center pt-4">
                        <a href="<?php echo e(route('register')); ?>" class="btn-primary inline-flex items-center justify-center gap-3 bg-gradient-to-l from-brand-400 to-brand-600 hover:from-brand-300 hover:to-brand-500 text-white font-bold text-lg px-10 py-5 rounded-2xl shadow-xl shadow-brand-600/30">
                            أنشئ حسابك المجاني <i class="fas fa-arrow-left text-sm"></i>
                        </a>
                        <a href="<?php echo e(route('public.contact')); ?>" class="btn-outline inline-flex items-center justify-center gap-3 border border-white/15 bg-white/[0.05] hover:bg-white/[0.1] text-white font-semibold text-lg px-10 py-5 rounded-2xl backdrop-blur-sm">
                            تواصل معنا <i class="fas fa-headset text-brand-400"></i>
                        </a>
                    </div>
                    <p class="text-sm text-slate-500 pt-2"><i class="fas fa-lock text-xs <?php echo e($isRtl?'ml-1':'mr-1'); ?>"></i>تسجيل آمن — بدون بطاقة ائتمان — ابدأ فوراً</p>
                </div>
            </div>
        </section>
    </main>


    
    <footer class="bg-navy-950 text-white relative overflow-hidden">
        <div class="absolute inset-0 opacity-[0.02]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.4) 1px,transparent 0);background-size:32px 32px"></div>
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-brand-500/40 to-transparent"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 pt-16 pb-8">
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 lg:gap-12 mb-12">

                
                <div class="col-span-2 md:col-span-4 lg:col-span-2">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg">
                            <span class="text-white font-black text-lg">M</span>
                        </div>
                        <div>
                            <p class="font-heading text-lg font-extrabold text-white">MuallimX</p>
                            <p class="text-xs text-white/40">تأهيل المعلّمين للعمل أونلاين</p>
                        </div>
                    </div>
                    <p class="text-sm text-slate-400 leading-relaxed mb-6 max-w-sm">
                        منصّة عربية متخصصة في تأهيل وتطوير المعلمين للعمل أونلاين باحتراف — من خلال تدريب تطبيقي، أدوات AI، مناهج جاهزة، وبناء بروفايل يفتح فرص عمل حقيقية.
                    </p>
                    <div class="flex gap-3">
                        <a href="https://wa.me/201044610507" target="_blank" class="w-10 h-10 rounded-xl bg-white/[0.05] hover:bg-emerald-500/20 border border-white/[0.06] hover:border-emerald-500/30 flex items-center justify-center text-slate-400 hover:text-emerald-400 transition-all duration-300" title="WhatsApp">
                            <i class="fab fa-whatsapp text-lg"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/[0.05] hover:bg-blue-500/20 border border-white/[0.06] hover:border-blue-500/30 flex items-center justify-center text-slate-400 hover:text-blue-400 transition-all duration-300" title="Facebook">
                            <i class="fab fa-facebook-f"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/[0.05] hover:bg-pink-500/20 border border-white/[0.06] hover:border-pink-500/30 flex items-center justify-center text-slate-400 hover:text-pink-400 transition-all duration-300" title="Instagram">
                            <i class="fab fa-instagram"></i>
                        </a>
                        <a href="#" class="w-10 h-10 rounded-xl bg-white/[0.05] hover:bg-sky-500/20 border border-white/[0.06] hover:border-sky-500/30 flex items-center justify-center text-slate-400 hover:text-sky-400 transition-all duration-300" title="LinkedIn">
                            <i class="fab fa-linkedin-in"></i>
                        </a>
                    </div>
                </div>

                
                <div>
                    <h4 class="font-heading font-bold text-white text-sm mb-5 flex items-center gap-2">
                        <span class="w-1 h-4 rounded-full bg-brand-500"></span> روابط سريعة
                    </h4>
                    <ul class="space-y-3 text-sm">
                        <li><a href="<?php echo e(route('home')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-brand-500/50"></i>الرئيسية</a></li>
                        <li><a href="<?php echo e(route('public.courses')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-brand-500/50"></i>البرامج التدريبية</a></li>
                        <li><a href="<?php echo e(route('public.instructors.index')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-brand-500/50"></i>المدرّبون</a></li>
                        <?php if(Route::has('public.about')): ?><li><a href="<?php echo e(route('public.about')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-brand-500/50"></i>عن المنصة</a></li><?php endif; ?>
                    </ul>
                </div>

                
                <div>
                    <h4 class="font-heading font-bold text-white text-sm mb-5 flex items-center gap-2">
                        <span class="w-1 h-4 rounded-full bg-emerald-500"></span> الدعم
                    </h4>
                    <ul class="space-y-3 text-sm">
                        <?php if(Route::has('public.contact')): ?><li><a href="<?php echo e(route('public.contact')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i>تواصل معنا</a></li><?php endif; ?>
                        <?php if(Route::has('public.faq')): ?><li><a href="<?php echo e(route('public.faq')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i>الأسئلة الشائعة</a></li><?php endif; ?>
                        <?php if(Route::has('public.help')): ?><li><a href="<?php echo e(route('public.help')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i>مركز المساعدة</a></li><?php endif; ?>
                        <?php if(Route::has('public.terms')): ?><li><a href="<?php echo e(route('public.terms')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i>الشروط والأحكام</a></li><?php endif; ?>
                        <?php if(Route::has('public.privacy')): ?><li><a href="<?php echo e(route('public.privacy')); ?>" class="text-slate-400 hover:text-white transition-colors flex items-center gap-2"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i>سياسة الخصوصية</a></li><?php endif; ?>
                    </ul>
                </div>

                
                <div class="col-span-2 md:col-span-2 lg:col-span-1">
                    <h4 class="font-heading font-bold text-white text-sm mb-5 flex items-center gap-2">
                        <span class="w-1 h-4 rounded-full bg-purple-500"></span> تواصل معنا
                    </h4>
                    <div class="space-y-4 text-sm">
                        <a href="mailto:info@mualimx.com" class="flex items-center gap-3 text-slate-400 hover:text-white transition-colors">
                            <span class="w-9 h-9 rounded-lg bg-white/[0.05] flex items-center justify-center flex-shrink-0"><i class="fas fa-envelope text-xs text-brand-400"></i></span>
                            info@mualimx.com
                        </a>
                        <a href="https://wa.me/201044610507" target="_blank" class="flex items-center gap-3 text-slate-400 hover:text-white transition-colors">
                            <span class="w-9 h-9 rounded-lg bg-white/[0.05] flex items-center justify-center flex-shrink-0"><i class="fab fa-whatsapp text-xs text-emerald-400"></i></span>
                            واتساب — 01044610507
                        </a>
                    </div>
                    <div class="mt-6">
                        <p class="text-xs text-slate-500 mb-3">اشترك في نشرتنا البريدية</p>
                        <form class="flex gap-2" onsubmit="event.preventDefault()">
                            <input type="email" placeholder="بريدك الإلكتروني" class="flex-1 px-4 py-2.5 rounded-xl bg-white/[0.05] border border-white/[0.08] text-white text-sm placeholder-slate-500 focus:outline-none focus:border-brand-500/40 transition-colors">
                            <button type="submit" class="px-4 py-2.5 rounded-xl bg-brand-500 hover:bg-brand-400 text-white text-sm font-bold transition-colors flex-shrink-0">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            
            <div class="border-t border-white/[0.06] pt-6 flex flex-col sm:flex-row items-center justify-between gap-4">
                <p class="text-xs text-slate-500">
                    &copy; <?php echo e(date('Y')); ?> <span class="text-white font-semibold">MuallimX</span> — جميع الحقوق محفوظة
                </p>
                <div class="flex items-center gap-4 text-xs text-slate-500">
                    <?php if(Route::has('public.privacy')): ?><a href="<?php echo e(route('public.privacy')); ?>" class="hover:text-white transition-colors">الخصوصية</a><span class="text-slate-700">•</span><?php endif; ?>
                    <?php if(Route::has('public.terms')): ?><a href="<?php echo e(route('public.terms')); ?>" class="hover:text-white transition-colors">الشروط</a><?php endif; ?>
                </div>
            </div>
        </div>
    </footer>

    <?php if(isset($popupAd) && $popupAd): ?>
        <?php echo $__env->make('partials.popup-ad', ['ad' => $popupAd], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <?php endif; ?>

    <script>
    (function(){
        'use strict';
        function updateScrollProgress(){var s=window.pageYOffset||document.documentElement.scrollTop,h=document.documentElement.scrollHeight-window.innerHeight,p=h>0?(s/h)*100:0,b=document.getElementById('scroll-progress');if(b)b.style.width=p+'%';}
        window.addEventListener('scroll',updateScrollProgress,{passive:true});

        function initReveal(){var t=document.querySelectorAll('.reveal,.reveal-left,.reveal-right,.reveal-scale');if(!t.length)return;var o=new IntersectionObserver(function(e){e.forEach(function(en){if(en.isIntersecting){en.target.classList.add('revealed');o.unobserve(en.target);}});},{threshold:.12,rootMargin:'0px 0px -40px 0px'});t.forEach(function(el){o.observe(el);});}

        function initCounters(){var c=document.querySelectorAll('[data-count]');if(!c.length)return;var o=new IntersectionObserver(function(e){e.forEach(function(en){if(en.isIntersecting){var el=en.target,target=parseInt(el.getAttribute('data-count')),v=el.querySelector('.counter-value');if(!v||el.dataset.counted)return;el.dataset.counted='true';var cur=0,step=Math.max(1,Math.floor(target/(2000/16)));var t=setInterval(function(){cur+=step;if(cur>=target){cur=target;clearInterval(t);}v.textContent=cur.toLocaleString('en-US');},16);o.unobserve(el);}});},{threshold:.5});c.forEach(function(el){o.observe(el);});}

        document.addEventListener('click',function(e){var l=e.target.closest('a[href^="#"]');if(l){e.preventDefault();var t=document.querySelector(l.getAttribute('href'));if(t)t.scrollIntoView({behavior:'smooth',block:'start'});}});

        function init(){initReveal();initCounters();}
        if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',init);else init();
    })();
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/welcome.blade.php ENDPATH**/ ?>
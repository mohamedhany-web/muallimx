<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>" itemscope itemtype="https://schema.org/EducationalOrganization">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title><?php echo e(__('landing.meta.title')); ?></title>
    <meta name="description" content="<?php echo e(__('landing.meta.description')); ?>">
    <meta name="theme-color" content="#283593">
    <link rel="canonical" href="<?php echo e(url('/')); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e(url('/')); ?>">
    <meta property="og:title" content="<?php echo e(__('landing.meta.og_title')); ?>">
    <meta property="og:description" content="<?php echo e(__('landing.meta.og_description')); ?>">
    <meta property="og:image" content="<?php echo e(asset('images/og-image.jpg')); ?>">
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">

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

<?php echo $__env->make('components.unified-navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

<main class="flex-1">
    
    <section class="pt-10 sm:pt-14 lg:pt-16 pb-10 sm:pb-12 overflow-hidden relative" style="background:radial-gradient(circle at 12% 80%,rgba(255,229,247,.65),transparent 28%),radial-gradient(circle at 88% 20%,rgba(40,53,147,.10),transparent 30%),linear-gradient(180deg,#f4f6ff 0%,#fbfbff 55%,#ffffff 100%)">
        <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image:radial-gradient(circle at 1px 1px,rgba(40,53,147,.08) 1px,transparent 0);background-size:30px 30px"></div>
        <div class="container-1200 relative z-10">
            <div class="max-w-4xl mx-auto text-center reveal">
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs sm:text-sm font-bold mb-6" style="background:#FFE5F7;color:#283593;border:1px solid #f5c7e8">
                    <i class="fas fa-globe"></i> منصة تعليمية عربية
                </span>
                <h1 class="font-heading text-[2rem] sm:text-[2.8rem] lg:text-[3.35rem] leading-[1.22] font-black text-mx-indigo mb-5">
                    استعد لمستقبل التعليم الحديث
                    <span class="block" style="color:#FB5607">بخطوات واضحة وهادئة</span>
                </h1>
                <p class="text-slate-600 text-base sm:text-lg leading-8 mb-7 max-w-3xl mx-auto">
                    تعلّم أحدث أدوات التدريس والتقنيات التعليمية العملية داخل تجربة مريحة تساعدك على التطور بثبات واحتراف.
                </p>
                <div class="flex flex-col sm:flex-row gap-3 sm:gap-4 justify-center">
                    <a class="btn-primary inline-flex items-center justify-center gap-2 !bg-[#FB5607] hover:!bg-[#e84d00]" href="<?php echo e(route('register')); ?>">اشترك الآن <i class="fas fa-user-plus text-xs"></i></a>
                    <a class="btn-secondary inline-flex items-center justify-center gap-2 !bg-[#283593] !text-white !border-[#283593] hover:!bg-[#1f2a7a]" href="<?php echo e(route('public.courses')); ?>">تصفح الكورسات <i class="fas fa-book text-xs"></i></a>
                </div>
            </div>

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mt-10 reveal s2">
                <article class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-white text-center shadow-[0_10px_24px_-18px_rgba(31,42,122,.25)]">
                    <p class="text-3xl sm:text-4xl font-black text-mx-indigo">+124</p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">متعلم عربي</p>
                </article>
                <article class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-white text-center shadow-[0_10px_24px_-18px_rgba(31,42,122,.25)]">
                    <p class="text-3xl sm:text-4xl font-black text-[#FB5607]">+13</p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">دورة تدريبية</p>
                </article>
                <article class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-[#FFE5F7] text-center shadow-[0_10px_24px_-18px_rgba(31,42,122,.25)]">
                    <p class="text-3xl sm:text-4xl font-black text-mx-indigo">+124</p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">شهادة</p>
                </article>
                <article class="rounded-2xl p-4 sm:p-5 border border-slate-200 bg-[#fffbea] text-center shadow-[0_10px_24px_-18px_rgba(31,42,122,.25)]">
                    <p class="text-3xl sm:text-4xl font-black text-mx-indigo">4</p>
                    <p class="text-xs sm:text-sm text-slate-600 mt-1">مسارات تعليمية</p>
                </article>
            </div>
        </div>
    </section>

    
    <section class="py-14 sm:py-16 bg-white">
        <div class="container-1200">
            <div class="flex items-end justify-between mb-7 gap-4">
                <div class="reveal max-w-2xl">
                    <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-2">الكورسات والمسارات المميزة</h2>
                    <p class="text-slate-600">اختر ما يناسب مستواك وهدفك، وابدأ بخطة واضحة.</p>
                </div>
                <a href="<?php echo e(route('public.courses')); ?>" class="btn-secondary whitespace-nowrap">كل الكورسات</a>
            </div>

            <div class="overflow-x-auto pb-3">
                <div class="flex gap-4 min-w-max">
                    <?php $__empty_1 = true; $__currentLoopData = $featuredCourses ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $course): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <?php
                        $thumbPath = $course->thumbnail ? str_replace('\\', '/', $course->thumbnail) : null;
                        $thumbUrl = $thumbPath ? asset('storage/' . $thumbPath) : null;
                        $instName = $course->instructor->name ?? __('public.instructor_fallback');
                        $priceLabel = ($course->is_free ?? false) || (float)($course->price ?? 0) <= 0
                            ? __('public.free_price')
                            : number_format((float) $course->price, 0) . ' ' . __('public.currency_egp');
                        $rating = $course->rating !== null ? number_format((float) $course->rating, 1) : null;
                    ?>
                    <a href="<?php echo e(route('public.course.show', $course->id)); ?>" class="card-base hover-lift w-[280px] <?php echo e($idx % 2 === 1 ? 'sm:w-[300px]' : ''); ?> block shrink-0">
                        <div class="rounded-xl h-36 mb-4 overflow-hidden bg-cover bg-center" style="background:linear-gradient(135deg,#e9edff,#f8f9ff)">
                            <?php if($thumbUrl): ?>
                                <img src="<?php echo e($thumbUrl); ?>" alt="<?php echo e($course->title); ?>" class="w-full h-full object-cover" loading="lazy" decoding="async">
                            <?php endif; ?>
                        </div>
                        <?php if($course->is_featured): ?>
                            <span class="inline-block text-xs font-bold px-3 py-1 rounded-full mb-3" style="background:#FFE5F7;color:#283593"><?php echo e(__('public.featured_course_badge')); ?></span>
                        <?php endif; ?>
                        <h3 class="font-heading text-lg font-extrabold text-mx-indigo leading-snug mb-2 line-clamp-2"><?php echo e($course->title); ?></h3>
                        <p class="text-sm text-slate-500 mb-3"><?php echo e($instName); ?></p>
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-amber-500">
                                <?php if($rating !== null): ?>
                                    <i class="fas fa-star"></i> <?php echo e($rating); ?>

                                <?php else: ?>
                                    <span class="text-slate-400 text-xs"><?php echo e(__('public.no_rating_yet')); ?></span>
                                <?php endif; ?>
                            </span>
                            <span class="font-bold text-mx-orange"><?php echo e($priceLabel); ?></span>
                        </div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <p class="text-slate-600 text-center w-full py-8"><?php echo e(__('public.no_courses_landing')); ?></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>

    
    <section class="py-12 sm:py-14 bg-mx-soft">
        <div class="container-1200">
            <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-7 reveal">التصنيفات</h2>
            <div class="grid grid-cols-2 lg:grid-cols-6 gap-4">
                <?php $cats = ($homeCategories ?? collect())->toArray(); ?>
                <?php $__empty_1 = true; $__currentLoopData = $cats; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $cat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                    <div class="reveal <?php echo e($i===1 ? 'lg:translate-y-2' : ''); ?> <?php echo e($i===4 ? 'lg:-translate-y-2' : ''); ?> card-base hover-lift text-center">
                        <div class="w-11 h-11 rounded-xl mx-auto mb-3 flex items-center justify-center text-mx-orange" style="background:#fff3ec"><i class="fas <?php echo e($cat['icon']); ?>"></i></div>
                        <h3 class="font-semibold text-sm text-mx-indigo"><?php echo e($cat['name']); ?></h3>
                    </div>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                    <div class="col-span-2 lg:col-span-6 text-center text-slate-500"><?php echo e(__('public.no_results')); ?></div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="py-16 sm:py-20 bg-white" id="home-instructors-section">
        <div class="container-1200">
            <div class="mb-8 reveal max-w-2xl">
                <span class="text-xs font-bold rounded-full px-3 py-1" style="background:#FFE5F7;color:#283593"><?php echo e(__('public.instructors_page_title')); ?></span>
                <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mt-4 mb-2">مدربون بخبرة حقيقية وتأثير واضح</h2>
                <p class="text-slate-600 text-sm sm:text-base"><?php echo e(__('public.instructors_subtitle')); ?></p>
            </div>

            <div class="relative">
                <div id="home-instructors-viewport"
                     class="overflow-hidden w-full pb-2"
                     tabindex="0"
                     aria-label="<?php echo e(__('public.instructors_page_title')); ?>">
                    <div id="home-instructors-track" class="flex transition-transform duration-500 ease-out will-change-transform" dir="ltr">
                    <?php $__empty_1 = true; $__currentLoopData = ($homeInstructors ?? collect()); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $name = $p->user->name ?? '';
                            $headline = $p->headline ?? '';
                            $bioRaw = $p->bio ?? '';
                            $bioText = $bioRaw !== '' ? \Illuminate\Support\Str::limit(strip_tags($bioRaw), 260) : ($headline !== '' ? $headline : __('public.course_description_fallback'));
                        ?>
                        <article class="home-instructor-slide flex-none shrink-0 w-full min-w-full box-border px-0" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
                            <div class="grid lg:grid-cols-12 gap-7 items-center">
                                <div class="lg:col-span-6 order-2 lg:order-1">
                                    <h3 class="font-heading text-xl font-extrabold text-mx-orange mb-2"><?php echo e($name); ?></h3>
                                    <?php if($headline !== ''): ?>
                                        <p class="text-sm text-slate-500 mb-4"><?php echo e($headline); ?></p>
                                    <?php endif; ?>
                                    <p class="text-slate-600 leading-8 mb-6"><?php echo e($bioText); ?></p>
                                    <a class="btn-secondary arrow-link inline-flex" href="<?php echo e(route('public.instructors.show', $p->user)); ?>"><?php echo e(__('public.view_instructor_profile')); ?></a>
                                </div>
                                <div class="lg:col-span-6 order-1 lg:order-2">
                                    <a href="<?php echo e(route('public.instructors.show', $p->user)); ?>" class="card-base !p-0 overflow-hidden block group">
                                        <div class="relative h-[280px] sm:h-[360px] overflow-hidden flex items-center justify-center p-4 sm:p-6" style="background:linear-gradient(135deg,#edf1ff,#f7f8ff)">
                                            <?php if($p->photo_path): ?>
                                                <img src="<?php echo e($p->photo_url); ?>" alt="<?php echo e($name); ?>" class="max-w-full max-h-full w-auto h-auto object-contain group-hover:scale-[1.02] transition-transform duration-500" loading="lazy" decoding="async" onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden')">
                                                <div class="hidden absolute inset-0 flex items-center justify-center" style="background:linear-gradient(135deg,#edf1ff,#f7f8ff)">
                                                    <div class="w-24 h-24 rounded-full bg-[#283593]/10 flex items-center justify-center"><i class="fas fa-user text-[#283593]/60 text-4xl"></i></div>
                                                </div>
                                            <?php else: ?>
                                                <div class="absolute inset-0 flex items-center justify-center">
                                                    <div class="w-24 h-24 rounded-full bg-[#283593]/10 flex items-center justify-center"><i class="fas fa-user text-[#283593]/60 text-4xl"></i></div>
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    </a>
                                </div>
                            </div>
                        </article>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <article class="home-instructor-slide flex-none shrink-0 w-full min-w-full box-border" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
                            <div class="grid lg:grid-cols-12 gap-7 items-center">
                                <div class="lg:col-span-6 order-2 lg:order-1">
                                    <h3 class="font-heading text-xl font-extrabold text-mx-orange mb-2">—</h3>
                                    <p class="text-slate-600 leading-8 mb-6"><?php echo e(__('public.no_instructors')); ?></p>
                                    <a class="btn-secondary arrow-link inline-flex" href="<?php echo e(route('public.instructors.index')); ?>"><?php echo e(__('public.all_instructors_link')); ?></a>
                                </div>
                                <div class="lg:col-span-6 order-1 lg:order-2">
                                    <div class="card-base !p-0 overflow-hidden">
                                        <div class="h-[280px] sm:h-[360px]" style="background:linear-gradient(135deg,#edf1ff,#f7f8ff)"></div>
                                    </div>
                                </div>
                            </div>
                        </article>
                    <?php endif; ?>
                    </div>
                </div>

                <?php if(isset($homeInstructors) && $homeInstructors->count() > 1): ?>
                    <div class="flex justify-center gap-2 mt-6" id="home-instructors-dots" role="tablist" aria-label="شرائح المدربين"></div>
                <?php endif; ?>

                <div class="mt-8 text-center lg:text-start">
                    <a class="btn-secondary arrow-link inline-flex" href="<?php echo e(route('public.instructors.index')); ?>"><?php echo e(__('public.all_instructors_link')); ?></a>
                </div>
            </div>
        </div>
    </section>

    
    <section class="py-12 sm:py-16 bg-mx-soft">
        <div class="container-1200">
            <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-8 reveal">كيف تعمل المنصة؟</h2>
            <div class="grid lg:grid-cols-3 gap-4 relative">
                <div class="hidden lg:block absolute top-11 right-[16%] left-[16%] h-px" style="background:linear-gradient(to left,#cdd6ff,#f0f3ff,#cdd6ff)"></div>
                <?php $steps=[['اختر مسارك','ابدأ بمسار يناسب خبرتك وهدفك.','fa-route'],['طوّر أدواتك','احصل على أدوات عملية جاهزة للتنفيذ.','fa-toolbox'],['انطلق مهنيًا','ابنِ بروفايلك وابدأ استقبال الفرص.','fa-rocket']]; ?>
                <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i=>$st): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                <article class="reveal s<?php echo e($i+1); ?> card-base relative text-center">
                    <div class="w-14 h-14 mx-auto rounded-2xl flex items-center justify-center mb-4 text-white" style="background:<?php echo e($i===1 ? '#FB5607':'#283593'); ?>"><i class="fas <?php echo e($st[2]); ?>"></i></div>
                    <h3 class="font-heading text-xl font-extrabold text-mx-indigo mb-2"><?php echo e($st[0]); ?></h3>
                    <p class="text-sm text-slate-600 leading-7"><?php echo e($st[1]); ?></p>
                </article>
                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        </div>
    </section>

    
    <section class="py-14 sm:py-18 bg-white">
        <div class="container-1200">
            <h2 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-7 reveal">آراء وتجارب المعلمين</h2>
            <div class="grid lg:grid-cols-12 gap-4">
                <article class="reveal card-base lg:col-span-7 hover-lift">
                    <p class="text-slate-600 leading-8">"قبل MuallimX كنت أضيع وقتًا طويلًا في التحضير. الآن صارت عندي خطة واضحة وأدوات عملية تختصر وقتي وتعطي نتائج أفضل."</p>
                    <p class="mt-4 font-bold text-mx-indigo">نورة العتيبي</p>
                </article>
                <article class="reveal s1 card-base lg:col-span-5 hover-lift !bg-mx-indigo !text-white !border-mx-indigo">
                    <p class="leading-8">"المنصة ساعدتني أبني حضوري المهني وأوصل لفرص تدريس حقيقية خلال فترة قصيرة."</p>
                    <p class="mt-4 font-bold" style="color:#FFE569">أحمد المنصوري</p>
                </article>
                <article class="reveal s2 card-base lg:col-span-4 hover-lift"><p class="text-slate-600 leading-7">"تجربة عربية واضحة وسلسة جدًا."</p><p class="mt-3 text-sm font-bold text-mx-indigo">سارة الكويتي</p></article>
                <article class="reveal s3 card-base lg:col-span-8 hover-lift"><p class="text-slate-600 leading-8">"المزيج بين المحتوى والتطبيق العملي هو أفضل ما في MuallimX."</p><p class="mt-3 text-sm font-bold text-mx-indigo">مها يوسف</p></article>
            </div>
        </div>
    </section>

    
    <section class="pt-14 sm:pt-18 pb-10 sm:pb-12" style="background:linear-gradient(180deg,#f4f7ff 0%,#ffffff 100%)">
        <div class="container-1200">
            <div class="reveal rounded-[28px] border border-slate-200 bg-white shadow-[0_20px_44px_-26px_rgba(31,42,122,.28)] px-6 sm:px-10 py-10 sm:py-12 text-center">
                <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs sm:text-sm font-bold mb-5" style="background:#FFE5F7;color:#283593">
                    <i class="fas fa-rocket"></i> انطلاقتك المهنية تبدأ الآن
                </span>
                <h2 class="font-heading text-3xl sm:text-5xl font-black text-mx-indigo mb-4">جاهز تبدأ رحلتك التعليمية الاحترافية؟</h2>
                <p class="text-slate-600 text-base sm:text-lg max-w-3xl mx-auto leading-8 mb-7">انضم إلى MuallimX اليوم وابدأ بخطوات واضحة، أدوات عملية، وتجربة تعلم عربية مصممة لتحقيق نتائج حقيقية.</p>
                <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                    <a href="<?php echo e(route('register')); ?>" class="btn-primary inline-flex items-center justify-center gap-2">إنشاء حساب مجاني <i class="fas fa-arrow-<?php echo e($isRtl ? 'left' : 'right'); ?> text-xs"></i></a>
                    <a href="<?php echo e(route('public.courses')); ?>" class="btn-secondary inline-flex items-center justify-center gap-2">استكشف البرامج</a>
                </div>
            </div>
        </div>
    </section>
</main>


<footer style="background:#283593" class="text-white">
    <div class="container-1200 pt-12 pb-8">
        <div class="grid md:grid-cols-4 gap-8 pb-8 border-b border-white/15">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-11 h-11 rounded-xl bg-mx-orange text-white font-black flex items-center justify-center">M</span>
                    <div>
                        <p class="font-heading text-xl font-black">MuallimX</p>
                        <p class="text-xs text-white/70">منصة تطوير المعلم العربي</p>
                    </div>
                </div>
                <p class="text-sm text-white/85 leading-7 max-w-md">تجربة تعليمية عربية تركز على التمكين المهني للمعلم عبر التدريب العملي وأدوات التدريس الحديثة.</p>
            </div>
            <div>
                <h3 class="font-heading font-bold mb-3 text-white">روابط سريعة</h3>
                <ul class="space-y-2 text-sm text-white/85">
                    <li><a class="hover:text-mx-gold transition-colors" href="<?php echo e(route('home')); ?>">الرئيسية</a></li>
                    <li><a class="hover:text-mx-gold transition-colors" href="<?php echo e(route('public.courses')); ?>">الكورسات</a></li>
                    <li><a class="hover:text-mx-gold transition-colors" href="<?php echo e(route('public.instructors.index')); ?>">المدربون</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-heading font-bold mb-3 text-white">تواصل معنا</h3>
                <ul class="space-y-2 text-sm text-white/85">
                    <li><a class="hover:text-mx-gold transition-colors" href="mailto:info@mualimx.com">info@mualimx.com</a></li>
                    <li><a class="hover:text-mx-gold transition-colors" href="https://wa.me/201044610507" target="_blank">واتساب: 01044610507</a></li>
                </ul>
            </div>
        </div>
        <div class="pt-5 flex flex-col sm:flex-row gap-2 justify-between text-xs text-white/75">
            <p>&copy; <?php echo e(date('Y')); ?> MuallimX — جميع الحقوق محفوظة</p>
            <p>تعليم عربي احترافي يركز على النتائج</p>
        </div>
    </div>
</footer>

<?php if(isset($popupAd) && $popupAd): ?>
    <?php echo $__env->make('partials.popup-ad', ['ad' => $popupAd], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
<?php endif; ?>

<script>
(function(){
    'use strict';
    function progress(){var s=window.pageYOffset||document.documentElement.scrollTop,h=document.documentElement.scrollHeight-window.innerHeight,p=h>0?(s/h)*100:0,b=document.getElementById('scroll-progress');if(b)b.style.width=p+'%';}
    window.addEventListener('scroll',progress,{passive:true});

    function reveal(){var els=document.querySelectorAll('.reveal');if(!els.length)return;var io=new IntersectionObserver(function(entries){entries.forEach(function(e){if(e.isIntersecting){e.target.classList.add('revealed');io.unobserve(e.target);}});},{threshold:.12,rootMargin:'0px 0px -50px 0px'});els.forEach(function(el){io.observe(el)});}
    if(document.readyState==='loading'){document.addEventListener('DOMContentLoaded',reveal);}else{reveal();}
})();

(function(){
    var viewport=document.getElementById('home-instructors-viewport');
    var track=document.getElementById('home-instructors-track');
    if(!viewport||!track||track.children.length<2)return;
    var dotsWrap=document.getElementById('home-instructors-dots');
    var slides=[].slice.call(track.children);
    var ci=0;
    var n=slides.length;
    function slideW(){ return viewport.offsetWidth||0; }
    function setTransform(){
        var w=slideW();
        if(w<1)return;
        track.style.transform='translateX('+(-ci*w)+'px)';
    }
    function bindSlideWidths(){
        var w=slideW();
        if(w<1)return;
        slides.forEach(function(s){
            s.style.width=w+'px';
            s.style.minWidth=w+'px';
            s.style.maxWidth=w+'px';
        });
        setTransform();
    }
    function activeDot(){
        if(!dotsWrap)return;
        [].forEach.call(dotsWrap.children,function(d,i){
            d.className=i===ci?'w-2.5 h-2.5 rounded-full bg-mx-indigo':'w-2 h-2 rounded-full bg-slate-300';
        });
    }
    if(dotsWrap){
        slides.forEach(function(_,i){
            var b=document.createElement('button');
            b.type='button';
            b.className='w-2 h-2 rounded-full bg-slate-300 transition-all';
            b.setAttribute('aria-label','Slide '+(i+1));
            b.addEventListener('click',function(){go(i);});
            dotsWrap.appendChild(b);
        });
    }
    function go(i){
        ci=((i%n)+n)%n;
        setTransform();
        activeDot();
    }
    var iv=setInterval(function(){go(ci+1);},5500);
    viewport.addEventListener('mouseenter',function(){clearInterval(iv);});
    viewport.addEventListener('mouseleave',function(){iv=setInterval(function(){go(ci+1);},5500);});
    window.addEventListener('resize',function(){bindSlideWidths();},{passive:true});
    if(document.readyState==='loading'){
        document.addEventListener('DOMContentLoaded',function(){ requestAnimationFrame(bindSlideWidths); });
    }else{
        requestAnimationFrame(bindSlideWidths);
    }
    activeDot();
})();
</script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/welcome.blade.php ENDPATH**/ ?>
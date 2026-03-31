<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $socials = $profile->social_links ?? [];
    $hasSocials = !empty($socials['linkedin']) || !empty($socials['twitter']) || !empty($socials['youtube']) || !empty($socials['facebook']) || !empty($socials['website']);
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title><?php echo e($profile->user->name ?? __('public.instructor_fallback')); ?> - <?php echo e(__('public.site_suffix')); ?></title>
    <meta name="description" content="<?php echo e(Str::limit($profile->bio ?? $profile->headline ?? '', 160)); ?>">
    <meta name="theme-color" content="#0F172A">
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo e(asset('logo-removebg-preview.png')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config={theme:{extend:{colors:{navy:{50:'#f0f4ff',100:'#dbe4ff',200:'#bac8ff',300:'#91a7ff',400:'#748ffc',500:'#5c7cfa',600:'#4c6ef5',700:'#4263eb',800:'#3b5bdb',900:'#364fc7',950:'#0F172A'},brand:{50:'#ecfeff',100:'#cffafe',200:'#a5f3fc',300:'#67e8f9',400:'#22d3ee',500:'#06b6d4',600:'#0891b2',700:'#0e7490',800:'#155e75',900:'#164e63'}},fontFamily:{heading:['Tajawal','IBM Plex Sans Arabic','sans-serif'],body:['IBM Plex Sans Arabic','Tajawal','sans-serif']}}}}
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        [x-cloak]{display:none!important}
        *{font-family:'IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif}
        h1,h2,h3,h4,h5,h6,.font-heading{font-family:'Tajawal','IBM Plex Sans Arabic',sans-serif}
        html{scroll-behavior:smooth;overflow-x:hidden!important}
        body{overflow-x:hidden!important;background:#fff;min-height:100vh;display:flex;flex-direction:column}
        body>*{flex-shrink:0}
        .reveal{opacity:0;transform:translateY(40px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}
        .reveal.revealed{opacity:1;transform:translateY(0)}
        .stagger-1{transition-delay:.05s}.stagger-2{transition-delay:.1s}.stagger-3{transition-delay:.15s}.stagger-4{transition-delay:.2s}
        .text-gradient{background:linear-gradient(135deg,#06b6d4 0%,#3b82f6 50%,#8b5cf6 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .btn-primary{position:relative;overflow:hidden;transition:all .4s cubic-bezier(.16,1,.3,1)}
        .btn-primary::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .6s}
        .btn-primary:hover::before{left:100%}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 20px 40px -12px rgba(6,182,212,.4)}
        .btn-outline{transition:all .3s cubic-bezier(.16,1,.3,1)}
        .btn-outline:hover{transform:translateY(-2px);box-shadow:0 10px 30px -10px rgba(15,23,42,.2)}
        .card-hover{transition:all .4s cubic-bezier(.16,1,.3,1)}
        .card-hover:hover{transform:translateY(-8px);box-shadow:0 25px 60px -15px rgba(0,0,0,.15)}
        .glass-dark{background:rgba(15,23,42,.55);backdrop-filter:blur(20px) saturate(200%);-webkit-backdrop-filter:blur(20px) saturate(200%);border:1px solid rgba(255,255,255,.08)}
        .noise::after{content:'';position:absolute;inset:0;opacity:.02;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");pointer-events:none}
        #scroll-progress{position:fixed;top:0;left:0;width:0%;height:3px;background:linear-gradient(90deg,#06b6d4,#3b82f6,#8b5cf6);z-index:9999;transition:width .1s linear}
        .line-clamp-3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
        @media(max-width:768px){.reveal{transition-duration:.5s}.stagger-1,.stagger-2,.stagger-3,.stagger-4{transition-delay:0s}}
    </style>
</head>
<body class="bg-white text-navy-950 antialiased font-body">
    <div id="scroll-progress"></div>
    <?php echo $__env->make('components.unified-navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>.navbar-spacer{display:none}</style>
    <script>(function(){var n=document.getElementById('navbar');if(n){n.classList.add('nav-transparent');n.classList.remove('nav-solid');}})();</script>

    <main class="flex-1">
        
        <section class="relative overflow-hidden bg-navy-950 noise pt-24 pb-20 md:pb-28">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-[#0c1833] to-navy-950"></div>
            <div class="absolute top-[-15%] <?php echo e($isRtl?'left-[-8%]':'right-[-8%]'); ?> w-[500px] h-[500px] rounded-full bg-brand-500/10 blur-[120px]"></div>
            <div class="absolute bottom-[-10%] <?php echo e($isRtl?'right-[-5%]':'left-[-5%]'); ?> w-[400px] h-[400px] rounded-full bg-purple-600/8 blur-[100px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.3) 1px,transparent 0);background-size:40px 40px"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 w-full">
                
                <nav class="reveal text-sm text-slate-400/80 mb-10 flex items-center gap-2 flex-wrap">
                    <a href="<?php echo e(url('/')); ?>" class="hover:text-white transition-colors"><?php echo e(__('public.home')); ?></a>
                    <i class="fas fa-chevron-<?php echo e($isRtl?'left':'right'); ?> text-[8px] text-slate-600"></i>
                    <a href="<?php echo e(route('public.instructors.index')); ?>" class="hover:text-white transition-colors"><?php echo e(__('public.instructors_page_title')); ?></a>
                    <i class="fas fa-chevron-<?php echo e($isRtl?'left':'right'); ?> text-[8px] text-slate-600"></i>
                    <span class="text-white/90 font-medium"><?php echo e($profile->user->name); ?></span>
                </nav>

                <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start">
                    
                    <div class="reveal flex-shrink-0">
                        <div class="w-40 h-40 md:w-48 md:h-48 rounded-3xl overflow-hidden border-2 border-white/10 shadow-2xl bg-gradient-to-br from-brand-500 via-blue-500 to-navy-700">
                            <?php if($profile->photo_path): ?>
                                <img src="<?php echo e($profile->photo_url); ?>" alt="<?php echo e($profile->user->name); ?>"
                                     class="w-full h-full object-cover"
                                     onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden')">
                                <div class="hidden w-full h-full flex items-center justify-center">
                                    <i class="fas fa-user text-white/60 text-6xl"></i>
                                </div>
                            <?php else: ?>
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-user text-white/60 text-6xl"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="reveal stagger-1 flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-bold">
                                <i class="fas fa-check-circle text-[10px]"></i>
                                مدرّب معتمد
                            </span>
                        </div>

                        <h1 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-3">
                            <?php echo e($profile->user->name); ?>

                        </h1>

                        <p class="text-brand-300 text-lg sm:text-xl font-medium mb-5">
                            <?php echo e($profile->headline ?? __('public.instructor_fallback')); ?>

                        </p>

                        <?php if($profile->bio): ?>
                        <p class="text-slate-300/90 text-base leading-relaxed mb-6 max-w-2xl line-clamp-3">
                            <?php echo e($profile->bio); ?>

                        </p>
                        <?php endif; ?>

                        
                        <div class="flex flex-wrap gap-3 mb-6">
                            <?php if($courses->count() > 0): ?>
                            <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white/[0.06] border border-white/[0.08] text-white/90 text-sm font-medium">
                                <i class="fas fa-book-open text-brand-400"></i>
                                <span><?php echo e($courses->count()); ?> <?php echo e($courses->count() > 1 ? 'كورسات' : 'كورس'); ?></span>
                            </div>
                            <?php endif; ?>
                            <?php if(count($profile->skills_list) > 0): ?>
                            <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white/[0.06] border border-white/[0.08] text-white/90 text-sm font-medium">
                                <i class="fas fa-cogs text-purple-400"></i>
                                <span><?php echo e(count($profile->skills_list)); ?> مهارة</span>
                            </div>
                            <?php endif; ?>
                            <?php if(count($profile->experience_list) > 0): ?>
                            <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white/[0.06] border border-white/[0.08] text-white/90 text-sm font-medium">
                                <i class="fas fa-briefcase text-amber-400"></i>
                                <span><?php echo e(count($profile->experience_list)); ?> خبرة</span>
                            </div>
                            <?php endif; ?>
                        </div>

                        
                        <?php if(isset($consultationSetting) && $consultationSetting->is_active): ?>
                        <div class="flex flex-wrap items-center gap-3 mb-6">
                            <span class="text-sm text-white/80">استشارة خاصة — <strong class="text-white"><?php echo e(number_format($profile->effectiveConsultationPriceEgp(), 2)); ?></strong> ج.م</span>
                            <?php if(auth()->guard()->check()): ?>
                                <?php if(auth()->user()->isStudent()): ?>
                                    <a href="<?php echo e(route('consultations.create', $profile->user)); ?>"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-bold shadow-lg shadow-emerald-500/30 transition-all">
                                        <i class="fas fa-comments"></i>
                                        طلب استشارة
                                    </a>
                                <?php endif; ?>
                            <?php else: ?>
                                <a href="<?php echo e(route('login', ['redirect' => route('consultations.create', $profile->user)])); ?>"
                                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-400 text-white text-sm font-bold shadow-lg shadow-emerald-500/30 transition-all">
                                    <i class="fas fa-comments"></i>
                                    طلب استشارة
                                </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        <?php if($hasSocials): ?>
                        <div class="flex flex-wrap gap-2.5">
                            <?php if(!empty($socials['linkedin'])): ?>
                            <a href="<?php echo e($socials['linkedin']); ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-[#0A66C2]/90 text-white flex items-center justify-center shadow-lg hover:bg-[#004182] hover:scale-110 transition-all">
                                <i class="fab fa-linkedin-in text-lg"></i>
                            </a>
                            <?php endif; ?>
                            <?php if(!empty($socials['twitter'])): ?>
                            <a href="<?php echo e($socials['twitter']); ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-navy-800 text-white flex items-center justify-center shadow-lg hover:bg-navy-900 hover:scale-110 transition-all border border-white/10">
                                <i class="fab fa-x-twitter text-lg"></i>
                            </a>
                            <?php endif; ?>
                            <?php if(!empty($socials['youtube'])): ?>
                            <a href="<?php echo e($socials['youtube']); ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-red-600/90 text-white flex items-center justify-center shadow-lg hover:bg-red-700 hover:scale-110 transition-all">
                                <i class="fab fa-youtube text-lg"></i>
                            </a>
                            <?php endif; ?>
                            <?php if(!empty($socials['facebook'])): ?>
                            <a href="<?php echo e($socials['facebook']); ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-[#1877F2]/90 text-white flex items-center justify-center shadow-lg hover:bg-[#0d65d9] hover:scale-110 transition-all">
                                <i class="fab fa-facebook-f text-lg"></i>
                            </a>
                            <?php endif; ?>
                            <?php if(!empty($socials['website'])): ?>
                            <a href="<?php echo e($socials['website']); ?>" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-white/10 text-white flex items-center justify-center shadow-lg hover:bg-white/20 hover:scale-110 transition-all border border-white/10">
                                <i class="fas fa-globe text-lg"></i>
                            </a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
        </section>

        
        <section class="py-20 md:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                    
                    <div class="lg:col-span-2 space-y-8">
                        
                        <?php if($profile->bio): ?>
                        <div class="reveal card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-sm hover:shadow-xl hover:border-brand-200/50">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center"><i class="fas fa-user-circle text-brand-500 text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-navy-950">نبذة تعريفية</h2>
                            </div>
                            <div class="text-slate-600 leading-relaxed text-base whitespace-pre-line"><?php echo e($profile->bio); ?></div>
                        </div>
                        <?php endif; ?>

                        
                        <?php if($profile->experience): ?>
                        <div class="reveal stagger-1 card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-sm hover:shadow-xl hover:border-amber-200/50">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center"><i class="fas fa-briefcase text-amber-500 text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-navy-950"><?php echo e(__('public.experience')); ?></h2>
                            </div>
                            <?php if(count($profile->experience_list) > 0): ?>
                            <div class="space-y-3">
                                <?php $__currentLoopData = $profile->experience_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <div class="flex items-start gap-3 p-4 rounded-xl bg-gradient-to-br from-amber-50/60 to-slate-50/30 border border-amber-100/60">
                                    <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fas fa-check text-amber-600 text-[10px]"></i>
                                    </div>
                                    <span class="text-slate-700 text-sm leading-relaxed flex-1"><?php echo e($item); ?></span>
                                </div>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                            <?php else: ?>
                            <div class="bg-gradient-to-br from-amber-50/50 to-slate-50/30 rounded-2xl p-6 border border-amber-100/50">
                                <p class="text-slate-700 whitespace-pre-line leading-relaxed"><?php echo e($profile->experience); ?></p>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>

                        
                        <?php if($courses->count() > 0): ?>
                        <div class="reveal stagger-2 card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-sm hover:shadow-xl hover:border-blue-200/50">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-blue-50 flex items-center justify-center"><i class="fas fa-graduation-cap text-blue-500 text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-navy-950"><?php echo e(__('public.instructor_courses')); ?></h2>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <?php $__currentLoopData = $courses; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <?php $cThumb = $c->thumbnail ? str_replace('\\','/', $c->thumbnail) : null; ?>
                                <a href="<?php echo e(route('public.course.show', $c->id)); ?>" class="group flex gap-4 p-4 rounded-2xl border border-slate-100 hover:border-brand-200 hover:shadow-lg transition-all duration-300 bg-white">
                                    <div class="w-20 h-20 flex-shrink-0 rounded-xl bg-gradient-to-br from-brand-500 to-navy-600 overflow-hidden flex items-center justify-center">
                                        <?php if($cThumb): ?>
                                            <img src="<?php echo e(asset('storage/' . $cThumb)); ?>" alt="" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                        <?php else: ?>
                                            <i class="fas fa-book text-white/80 text-2xl"></i>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex-1 min-w-0 flex flex-col justify-center">
                                        <h4 class="font-bold text-navy-950 group-hover:text-brand-600 transition-colors line-clamp-2 leading-snug text-sm mb-1.5"><?php echo e($c->title); ?></h4>
                                        <div class="flex items-center gap-3 text-xs text-slate-500">
                                            <?php if($c->price > 0): ?>
                                                <span class="font-bold text-brand-600"><?php echo e(number_format($c->price, 0)); ?> <?php echo e(__('public.currency_egp')); ?></span>
                                            <?php else: ?>
                                                <span class="font-bold text-emerald-600 flex items-center gap-1"><i class="fas fa-gift text-[10px]"></i> <?php echo e(__('public.free_price')); ?></span>
                                            <?php endif; ?>
                                            <?php if($c->lessons_count ?? 0): ?>
                                            <span class="flex items-center gap-1"><i class="fas fa-play-circle text-slate-400"></i> <?php echo e($c->lessons_count); ?> <?php echo e(__('public.lesson_single')); ?></span>
                                            <?php endif; ?>
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 self-center">
                                        <span class="w-8 h-8 rounded-lg bg-slate-50 group-hover:bg-brand-50 flex items-center justify-center transition-colors">
                                            <i class="fas fa-arrow-<?php echo e($isRtl?'left':'right'); ?> text-[10px] text-slate-400 group-hover:text-brand-500 transition-colors"></i>
                                        </span>
                                    </div>
                                </a>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="lg:col-span-1">
                        <div class="reveal sticky top-24 space-y-6">
                            
                            <?php if(count($profile->skills_list) > 0): ?>
                            <div class="card-hover rounded-3xl bg-white border border-slate-100 shadow-lg overflow-hidden">
                                <div class="bg-gradient-to-<?php echo e($isRtl?'r':'l'); ?> from-purple-500 to-purple-600 p-5">
                                    <h3 class="font-heading text-lg font-bold text-white flex items-center gap-2">
                                        <i class="fas fa-cogs"></i>
                                        <?php echo e(__('public.skills')); ?>

                                    </h3>
                                </div>
                                <div class="p-5">
                                    <div class="flex flex-wrap gap-2">
                                        <?php $__currentLoopData = $profile->skills_list; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <span class="px-3 py-1.5 rounded-xl bg-gradient-to-br from-purple-50 to-slate-50 text-slate-700 text-sm font-medium border border-purple-100/60"><?php echo e($skill); ?></span>
                                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </div>
                                </div>
                            </div>
                            <?php endif; ?>

                            
                            <div class="card-hover rounded-3xl bg-white border border-slate-100 shadow-lg overflow-hidden">
                                <div class="bg-gradient-to-<?php echo e($isRtl?'r':'l'); ?> from-brand-500 to-brand-600 p-5">
                                    <h3 class="font-heading text-lg font-bold text-white flex items-center gap-2">
                                        <i class="fas fa-info-circle"></i>
                                        معلومات سريعة
                                    </h3>
                                </div>
                                <div class="p-5 space-y-3">
                                    <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                        <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-book-open text-brand-500"></i> الكورسات</span>
                                        <span class="font-bold text-navy-950"><?php echo e($courses->count()); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                        <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-cogs text-purple-500"></i> المهارات</span>
                                        <span class="font-bold text-navy-950"><?php echo e(count($profile->skills_list)); ?></span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                        <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> الحالة</span>
                                        <span class="font-bold text-emerald-600">معتمد</span>
                                    </div>
                                </div>
                                <div class="px-5 pb-5">
                                    <a href="<?php echo e(route('public.courses')); ?>" class="btn-primary block w-full text-center py-3 rounded-2xl bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold shadow-lg text-sm">
                                        <i class="fas fa-graduation-cap <?php echo e($isRtl?'ml-2':'mr-2'); ?>"></i>تصفّح جميع الكورسات
                                    </a>
                                </div>
                            </div>

                            
                            <a href="<?php echo e(route('public.instructors.index')); ?>" class="btn-outline flex items-center justify-center gap-2.5 w-full bg-white border-2 border-slate-200 hover:border-brand-300 text-navy-950 font-semibold py-3.5 rounded-2xl text-sm">
                                <i class="fas fa-arrow-<?php echo e($isRtl?'right':'left'); ?> text-brand-500"></i>
                                جميع المدرّبين
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        
        <section class="py-20 md:py-28 bg-slate-50/50">
            <div class="max-w-4xl mx-auto px-5 sm:px-8 text-center reveal">
                <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-5">ابدأ التعلم</span>
                <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                    جاهز لبدء
                    <span class="text-gradient">رحلتك؟</span>
                </h2>
                <p class="text-lg text-slate-500 mb-10 font-medium leading-relaxed max-w-2xl mx-auto">
                    انضم لآلاف المعلمين الذين طوّروا مسيرتهم المهنية مع MuallimX
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo e(route('public.courses')); ?>" class="btn-primary inline-flex items-center justify-center gap-3 bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold text-base sm:text-lg px-8 py-4 rounded-2xl shadow-xl shadow-brand-600/25">
                        تصفّح الكورسات
                        <i class="fas fa-arrow-<?php echo e($isRtl?'left':'right'); ?> text-sm"></i>
                    </a>
                    <a href="<?php echo e(route('register')); ?>" class="btn-outline inline-flex items-center justify-center gap-3 bg-white border-2 border-slate-200 hover:border-brand-300 text-navy-950 font-semibold text-base sm:text-lg px-8 py-4 rounded-2xl">
                        سجّل مجاناً
                        <i class="fas fa-arrow-<?php echo e($isRtl?'left':'right'); ?> text-sm"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    <?php echo $__env->make('components.unified-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <script>
    (function(){
        function p(){var s=window.pageYOffset||document.documentElement.scrollTop,h=document.documentElement.scrollHeight-window.innerHeight,b=document.getElementById('scroll-progress');if(b)b.style.width=(h>0?(s/h)*100:0)+'%';}
        window.addEventListener('scroll',p,{passive:true});
        function r(){var t=document.querySelectorAll('.reveal');if(!t.length)return;var o=new IntersectionObserver(function(e){e.forEach(function(n){if(n.isIntersecting){n.target.classList.add('revealed');o.unobserve(n.target);}});},{threshold:.08,rootMargin:'0px 0px -40px 0px'});t.forEach(function(el){o.observe(el);});}
        if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',r);else r();
    })();
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\instructors\show.blade.php ENDPATH**/ ?>
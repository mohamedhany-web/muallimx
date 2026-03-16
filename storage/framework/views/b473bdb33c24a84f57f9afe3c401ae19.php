<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title><?php echo e(__('public.instructors_page_title')); ?> - <?php echo e(__('public.site_suffix')); ?></title>
    <meta name="description" content="<?php echo e(__('public.instructors_subtitle')); ?>">
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
        .noise::after{content:'';position:absolute;inset:0;opacity:.02;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");pointer-events:none}
        #scroll-progress{position:fixed;top:0;left:0;width:0%;height:3px;background:linear-gradient(90deg,#06b6d4,#3b82f6,#8b5cf6);z-index:9999;transition:width .1s linear}
        .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
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
        
        <section class="relative min-h-[60vh] flex items-center overflow-hidden bg-navy-950 noise">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-[#0c1833] to-navy-950"></div>
            <div class="absolute top-[-20%] <?php echo e($isRtl?'left-[-10%]':'right-[-10%]'); ?> w-[600px] h-[600px] rounded-full bg-brand-500/10 blur-[120px]"></div>
            <div class="absolute bottom-[-10%] <?php echo e($isRtl?'right-[-5%]':'left-[-5%]'); ?> w-[500px] h-[500px] rounded-full bg-purple-600/8 blur-[100px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.3) 1px,transparent 0);background-size:40px 40px"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 pt-28 pb-16 md:pt-36 md:pb-20 w-full">
                <div class="text-center max-w-4xl mx-auto">
                    <div class="reveal">
                        <span class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-white/[0.06] border border-white/[0.1] text-brand-300 text-sm font-medium backdrop-blur-sm">
                            <i class="fas fa-chalkboard-teacher text-brand-400"></i>
                            <?php echo e(__('public.instructors_page_title')); ?>

                        </span>
                    </div>
                    <h1 class="reveal stagger-1 font-heading text-4xl sm:text-5xl md:text-6xl font-black leading-[1.15] text-white mt-6">
                        <?php echo e(__('public.instructors_heading')); ?>

                    </h1>
                    <p class="reveal stagger-2 text-lg sm:text-xl text-slate-300/90 max-w-2xl mx-auto leading-relaxed font-light mt-5">
                        <?php echo e(__('public.instructors_subtitle')); ?>

                    </p>

                    
                    <div class="reveal stagger-3 flex flex-wrap justify-center gap-6 mt-10">
                        <div class="flex items-center gap-2.5 text-white/80 text-sm">
                            <span class="w-9 h-9 rounded-xl bg-brand-500/20 flex items-center justify-center"><i class="fas fa-user-tie text-brand-400 text-sm"></i></span>
                            <span><span class="font-bold text-white text-lg"><?php echo e($profiles->count()); ?></span> مدرّب معتمد</span>
                        </div>
                        <div class="flex items-center gap-2.5 text-white/80 text-sm">
                            <span class="w-9 h-9 rounded-xl bg-emerald-500/20 flex items-center justify-center"><i class="fas fa-book-open text-emerald-400 text-sm"></i></span>
                            <span><span class="font-bold text-white text-lg"><?php echo e($profiles->sum('courses_count')); ?></span> كورس نشط</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
        </section>

        
        <section class="py-20 md:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-14 reveal">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-4">فريق التدريب</span>
                    <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                        تعرّف على
                        <span class="text-gradient">مدرّبينا</span>
                    </h2>
                    <p class="text-lg text-slate-500 leading-relaxed">خبراء في مجالاتهم يشاركونك تجاربهم الحقيقية لمساعدتك على النجاح</p>
                </div>

                <?php if($profiles->isNotEmpty()): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    <?php $__currentLoopData = $profiles; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $p): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('public.instructors.show', $p->user)); ?>"
                       class="reveal stagger-<?php echo e(min($idx + 1, 4)); ?> card-hover group block rounded-3xl bg-white border border-slate-100 overflow-hidden shadow-sm">

                        
                        <div class="relative aspect-[4/3] bg-gradient-to-br from-brand-500 via-blue-500 to-navy-700 overflow-hidden">
                            <?php if($p->photo_path): ?>
                                <img src="<?php echo e($p->photo_url); ?>" alt="<?php echo e($p->user->name); ?>"
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out"
                                     onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden')">
                                <div class="hidden absolute inset-0 flex items-center justify-center bg-gradient-to-br from-brand-500 via-blue-500 to-navy-700">
                                    <div class="w-24 h-24 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-user text-white/70 text-4xl"></i>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-24 h-24 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-user text-white/70 text-4xl"></i>
                                    </div>
                                </div>
                            <?php endif; ?>

                            
                            <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

                            
                            <?php if($p->courses_count > 0): ?>
                            <span class="absolute top-3 <?php echo e($isRtl?'right':'left'); ?>-3 px-3 py-1.5 rounded-full bg-brand-500/90 text-white text-[11px] font-bold flex items-center gap-1.5 shadow-lg backdrop-blur-sm">
                                <i class="fas fa-book-open text-[9px]"></i>
                                <?php echo e($p->courses_count); ?> <?php echo e($p->courses_count > 1 ? 'كورسات' : 'كورس'); ?>

                            </span>
                            <?php endif; ?>

                            
                            <div class="absolute bottom-3 <?php echo e($isRtl?'right':'left'); ?>-3 flex gap-2">
                                <?php if(!empty($p->social_links['linkedin'])): ?>
                                <span role="link" tabindex="0"
                                      data-url="<?php echo e($p->social_links['linkedin']); ?>"
                                      onclick="event.preventDefault();event.stopPropagation();window.open(this.dataset.url,'_blank')"
                                      class="w-9 h-9 rounded-xl bg-[#0A66C2]/90 text-white flex items-center justify-center shadow-lg hover:bg-[#004182] hover:scale-110 transition-all cursor-pointer backdrop-blur-sm"
                                      title="LinkedIn">
                                    <i class="fab fa-linkedin-in text-sm"></i>
                                </span>
                                <?php endif; ?>
                                <?php if(!empty($p->social_links['twitter'])): ?>
                                <span role="link" tabindex="0"
                                      data-url="<?php echo e($p->social_links['twitter']); ?>"
                                      onclick="event.preventDefault();event.stopPropagation();window.open(this.dataset.url,'_blank')"
                                      class="w-9 h-9 rounded-xl bg-navy-800/90 text-white flex items-center justify-center shadow-lg hover:bg-navy-900 hover:scale-110 transition-all cursor-pointer backdrop-blur-sm"
                                      title="X / Twitter">
                                    <i class="fab fa-x-twitter text-sm"></i>
                                </span>
                                <?php endif; ?>
                                <?php if(!empty($p->social_links['youtube'])): ?>
                                <span role="link" tabindex="0"
                                      data-url="<?php echo e($p->social_links['youtube']); ?>"
                                      onclick="event.preventDefault();event.stopPropagation();window.open(this.dataset.url,'_blank')"
                                      class="w-9 h-9 rounded-xl bg-red-600/90 text-white flex items-center justify-center shadow-lg hover:bg-red-700 hover:scale-110 transition-all cursor-pointer backdrop-blur-sm"
                                      title="YouTube">
                                    <i class="fab fa-youtube text-sm"></i>
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="p-5 sm:p-6">
                            <h3 class="font-heading text-xl font-bold text-navy-950 mb-1.5 group-hover:text-brand-600 transition-colors duration-300">
                                <?php echo e($p->user->name); ?>

                            </h3>
                            <p class="text-sm text-brand-600 font-medium mb-3">
                                <?php echo e($p->headline ?? __('public.instructor_fallback')); ?>

                            </p>

                            
                            <?php if(count($p->skills_list) > 0): ?>
                            <div class="flex flex-wrap gap-1.5 mb-4">
                                <?php $__currentLoopData = array_slice($p->skills_list, 0, 3); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $skill): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <span class="px-2.5 py-1 rounded-lg bg-slate-50 text-slate-600 text-[11px] font-medium border border-slate-100"><?php echo e($skill); ?></span>
                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                <?php if(count($p->skills_list) > 3): ?>
                                <span class="px-2.5 py-1 rounded-lg bg-brand-50 text-brand-600 text-[11px] font-medium">+<?php echo e(count($p->skills_list) - 3); ?></span>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            
                            <?php if($p->bio): ?>
                            <p class="text-[13px] text-slate-500 leading-relaxed line-clamp-2 mb-4"><?php echo e($p->bio); ?></p>
                            <?php endif; ?>

                            
                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2 text-xs text-slate-400">
                                    <i class="fas fa-check-circle text-emerald-500"></i>
                                    <span class="font-medium">مدرّب معتمد</span>
                                </div>
                                <span class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-gradient-to-<?php echo e($isRtl?'r':'l'); ?> from-brand-500 to-brand-600 text-white font-bold text-[12px] shadow-lg shadow-brand-600/20 group-hover:shadow-brand-600/40 group-hover:scale-105 transition-all duration-300">
                                    عرض الملف
                                    <i class="fas fa-arrow-<?php echo e($isRtl?'left':'right'); ?> text-[9px]"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>
                <?php else: ?>
                <div class="text-center py-20 reveal">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 bg-gradient-to-br from-brand-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                            <i class="fas fa-chalkboard-teacher text-brand-400 text-4xl"></i>
                        </div>
                        <h3 class="font-heading text-2xl font-bold text-navy-950 mb-3"><?php echo e(__('public.no_instructors')); ?></h3>
                        <p class="text-slate-500 mb-8 leading-relaxed">سيتم إضافة مدربين جدد قريباً</p>
                        <a href="<?php echo e(url('/')); ?>" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-brand-600/25">
                            <i class="fas fa-home"></i>
                            العودة للرئيسية
                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        
        <section class="py-20 md:py-28 bg-slate-50/50">
            <div class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 text-center reveal">
                <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-5">انضم لفريقنا</span>
                <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                    هل أنت مدرّب؟
                    <span class="text-gradient">انضم إلينا</span>
                </h2>
                <p class="text-lg text-slate-500 mb-10 font-medium leading-relaxed max-w-2xl mx-auto">
                    شارك خبراتك مع آلاف المعلمين وساهم في بناء جيل من المعلمين المحترفين
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="<?php echo e(route('register')); ?>" class="btn-primary inline-flex items-center justify-center gap-3 bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold text-base sm:text-lg px-8 py-4 rounded-2xl shadow-xl shadow-brand-600/25">
                        سجّل كمدرّب
                        <i class="fas fa-arrow-<?php echo e($isRtl?'left':'right'); ?> text-sm"></i>
                    </a>
                    <a href="<?php echo e(route('public.courses')); ?>" class="btn-outline inline-flex items-center justify-center gap-3 bg-white border-2 border-slate-200 hover:border-brand-300 text-navy-950 font-semibold text-base sm:text-lg px-8 py-4 rounded-2xl">
                        تصفّح الكورسات
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
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/instructors/index.blade.php ENDPATH**/ ?>
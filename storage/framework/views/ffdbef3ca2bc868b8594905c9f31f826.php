<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title><?php echo e(__('public.portfolio_page_title')); ?> - <?php echo e(__('public.site_suffix')); ?></title>
    <meta name="description" content="<?php echo e(__('public.portfolio_subtitle')); ?>">
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
            <div class="absolute top-[-20%] <?php echo e($isRtl?'left-[-10%]':'right-[-10%]'); ?> w-[600px] h-[600px] rounded-full bg-purple-500/10 blur-[120px]"></div>
            <div class="absolute bottom-[-10%] <?php echo e($isRtl?'right-[-5%]':'left-[-5%]'); ?> w-[500px] h-[500px] rounded-full bg-brand-600/8 blur-[100px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.3) 1px,transparent 0);background-size:40px 40px"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 pt-28 pb-16 md:pt-36 md:pb-20 w-full">
                <div class="text-center max-w-4xl mx-auto">
                    <div class="reveal">
                        <span class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-white/[0.06] border border-white/[0.1] text-brand-300 text-sm font-medium backdrop-blur-sm">
                            <i class="fas fa-code text-brand-400"></i>
                            <?php echo e(__('public.portfolio_page_title')); ?>

                        </span>
                    </div>
                    <h1 class="reveal stagger-1 font-heading text-4xl sm:text-5xl md:text-6xl font-black leading-[1.15] text-white mt-6">
                        <?php echo e(__('public.portfolio_heading')); ?>

                    </h1>
                    <p class="reveal stagger-2 text-lg sm:text-xl text-slate-300/90 max-w-2xl mx-auto leading-relaxed font-light mt-5">
                        <?php echo e(__('public.portfolio_subtitle')); ?>

                    </p>

                    
                    <?php if($learningPaths->count() > 0): ?>
                    <div class="reveal stagger-3 mt-10 flex flex-wrap justify-center gap-2.5">
                        <a href="<?php echo e(route('public.portfolio.index')); ?>"
                           class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?php echo e(!$categoryId ? 'bg-gradient-to-l from-brand-500 to-brand-600 text-white shadow-lg shadow-brand-600/25' : 'bg-white/[0.06] border border-white/[0.1] text-white/80 hover:bg-white/[0.12] backdrop-blur-sm'); ?>">
                            <i class="fas fa-th-large <?php echo e($isRtl?'ml-1.5':'mr-1.5'); ?> text-xs"></i><?php echo e(__('public.all')); ?>

                        </a>
                        <?php $__currentLoopData = $learningPaths; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $path): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('public.portfolio.index', ['path' => $path->id])); ?>"
                           class="px-5 py-2.5 rounded-xl text-sm font-semibold transition-all duration-300 <?php echo e($categoryId == $path->id ? 'bg-gradient-to-l from-brand-500 to-brand-600 text-white shadow-lg shadow-brand-600/25' : 'bg-white/[0.06] border border-white/[0.1] text-white/80 hover:bg-white/[0.12] backdrop-blur-sm'); ?>">
                            <?php echo e($path->name); ?>

                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                    <?php endif; ?>

                    
                    <div class="reveal stagger-4 flex flex-wrap justify-center gap-6 mt-8">
                        <div class="flex items-center gap-2 text-white/70 text-sm">
                            <span class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center"><i class="fas fa-project-diagram text-purple-400 text-xs"></i></span>
                            <span><span class="font-bold text-white"><?php echo e($projects->total()); ?></span> مشروع</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
        </section>

        
        <section class="py-20 md:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <?php if($projects->count() > 0): ?>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8">
                    <?php $__currentLoopData = $projects; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $project): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                    <a href="<?php echo e(route('public.portfolio.show', $project->id)); ?>"
                       class="reveal stagger-<?php echo e(min($idx + 1, 4)); ?> card-hover group block rounded-3xl bg-white border border-slate-100 overflow-hidden shadow-sm">

                        
                        <div class="relative aspect-video overflow-hidden bg-gradient-to-br from-purple-500 via-blue-500 to-brand-600">
                            <?php if($project->image_path): ?>
                                <img src="<?php echo e(asset($project->image_path)); ?>" alt="<?php echo e($project->title); ?>"
                                     class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out" loading="lazy">
                            <?php else: ?>
                                <div class="absolute inset-0 flex items-center justify-center">
                                    <div class="w-16 h-16 rounded-2xl bg-white/10 flex items-center justify-center backdrop-blur-sm">
                                        <i class="fas fa-code text-white/70 text-2xl"></i>
                                    </div>
                                </div>
                            <?php endif; ?>
                            <div class="absolute inset-0 bg-gradient-to-t from-black/50 via-transparent to-transparent"></div>

                            
                            <?php if($project->project_type): ?>
                            <span class="absolute top-3 <?php echo e($isRtl?'right':'left'); ?>-3 px-3 py-1.5 rounded-lg bg-purple-500/90 text-white text-[11px] font-bold shadow-lg backdrop-blur-sm">
                                <?php echo e($project->project_type); ?>

                            </span>
                            <?php endif; ?>

                            
                            <div class="absolute bottom-3 <?php echo e($isRtl?'right':'left'); ?>-3 flex gap-2">
                                <?php if($project->project_url): ?>
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/95 backdrop-blur text-navy-800 text-[11px] font-bold shadow-sm">
                                    <i class="fas fa-external-link-alt text-brand-500 text-[9px]"></i> رابط حي
                                </span>
                                <?php endif; ?>
                                <?php if($project->github_url): ?>
                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/95 backdrop-blur text-navy-800 text-[11px] font-bold shadow-sm">
                                    <i class="fab fa-github text-[10px]"></i> GitHub
                                </span>
                                <?php endif; ?>
                            </div>
                        </div>

                        
                        <div class="p-5 sm:p-6">
                            
                            <?php if($project->advancedCourse): ?>
                            <div class="mb-3">
                                <span class="inline-flex items-center gap-1.5 text-[11px] text-brand-700 bg-brand-50 px-3 py-1 rounded-full font-semibold">
                                    <i class="fas fa-graduation-cap text-[9px] text-brand-500"></i>
                                    <?php echo e($project->advancedCourse->title); ?>

                                </span>
                            </div>
                            <?php elseif($project->academicYear): ?>
                            <div class="mb-3">
                                <span class="inline-flex items-center gap-1.5 text-[11px] text-purple-700 bg-purple-50 px-3 py-1 rounded-full font-semibold">
                                    <i class="fas fa-bookmark text-[9px] text-purple-500"></i>
                                    <?php echo e($project->academicYear->name); ?>

                                </span>
                            </div>
                            <?php endif; ?>

                            <h3 class="font-heading text-lg font-bold text-navy-950 mb-2 line-clamp-2 leading-snug group-hover:text-brand-600 transition-colors duration-300">
                                <?php echo e($project->title); ?>

                            </h3>
                            <p class="text-[13px] text-slate-500 leading-relaxed line-clamp-2 mb-5">
                                <?php echo e(Str::limit(strip_tags($project->description ?? ''), 100)); ?>

                            </p>

                            
                            <div class="flex items-center justify-between pt-4 border-t border-slate-100">
                                <div class="flex items-center gap-2.5 min-w-0">
                                    <?php if($project->user->profile_image ?? null): ?>
                                        <img src="<?php echo e($project->user->profile_image_url); ?>" alt="" class="w-8 h-8 rounded-lg object-cover flex-shrink-0">
                                    <?php else: ?>
                                        <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-xs font-bold"><?php echo e(mb_substr($project->user->name ?? 'ط', 0, 1)); ?></span>
                                        </div>
                                    <?php endif; ?>
                                    <span class="text-sm font-medium text-navy-950 truncate"><?php echo e($project->user->name ?? __('public.student_fallback')); ?></span>
                                </div>
                                <span class="w-8 h-8 rounded-lg bg-slate-50 group-hover:bg-brand-50 flex items-center justify-center transition-colors flex-shrink-0">
                                    <i class="fas fa-arrow-<?php echo e($isRtl?'left':'right'); ?> text-[10px] text-slate-400 group-hover:text-brand-500 transition-colors"></i>
                                </span>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </div>

                
                <?php if($projects->hasPages()): ?>
                <div class="mt-12 flex justify-center">
                    <nav class="flex items-center gap-2">
                        <?php if($projects->onFirstPage()): ?>
                            <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center text-sm cursor-not-allowed">
                                <i class="fas fa-chevron-<?php echo e($isRtl?'right':'left'); ?>"></i>
                            </span>
                        <?php else: ?>
                            <a href="<?php echo e($projects->previousPageUrl()); ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 hover:border-brand-300 text-navy-950 flex items-center justify-center text-sm transition-colors">
                                <i class="fas fa-chevron-<?php echo e($isRtl?'right':'left'); ?>"></i>
                            </a>
                        <?php endif; ?>

                        <?php $__currentLoopData = $projects->getUrlRange(max(1, $projects->currentPage()-2), min($projects->lastPage(), $projects->currentPage()+2)); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php if($page == $projects->currentPage()): ?>
                                <span class="w-10 h-10 rounded-xl bg-gradient-to-l from-brand-500 to-brand-600 text-white flex items-center justify-center text-sm font-bold shadow-lg shadow-brand-600/20"><?php echo e($page); ?></span>
                            <?php else: ?>
                                <a href="<?php echo e($url); ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 hover:border-brand-300 text-navy-950 flex items-center justify-center text-sm font-medium transition-colors"><?php echo e($page); ?></a>
                            <?php endif; ?>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                        <?php if($projects->hasMorePages()): ?>
                            <a href="<?php echo e($projects->nextPageUrl()); ?>" class="w-10 h-10 rounded-xl bg-white border border-slate-200 hover:border-brand-300 text-navy-950 flex items-center justify-center text-sm transition-colors">
                                <i class="fas fa-chevron-<?php echo e($isRtl?'left':'right'); ?>"></i>
                            </a>
                        <?php else: ?>
                            <span class="w-10 h-10 rounded-xl bg-slate-100 text-slate-400 flex items-center justify-center text-sm cursor-not-allowed">
                                <i class="fas fa-chevron-<?php echo e($isRtl?'left':'right'); ?>"></i>
                            </span>
                        <?php endif; ?>
                    </nav>
                </div>
                <?php endif; ?>

                <?php else: ?>
                
                <div class="text-center py-20 reveal">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 bg-gradient-to-br from-purple-50 to-brand-50 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                            <i class="fas fa-folder-open text-purple-400 text-4xl"></i>
                        </div>
                        <h3 class="font-heading text-2xl font-bold text-navy-950 mb-3"><?php echo e(__('public.no_projects_yet')); ?></h3>
                        <p class="text-slate-500 mb-8 leading-relaxed"><?php echo e(__('public.no_projects_desc')); ?></p>
                        <a href="<?php echo e(route('public.courses')); ?>" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-brand-600/25">
                            <i class="fas fa-book-open"></i>
                            <?php echo e(__('public.browse_courses')); ?>

                        </a>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </section>

        
        <section class="py-20 md:py-28 bg-slate-50/50">
            <div class="max-w-4xl mx-auto px-5 sm:px-8 text-center reveal">
                <span class="inline-block px-4 py-1.5 rounded-full bg-purple-50 text-purple-600 text-sm font-semibold mb-5">أظهر إبداعك</span>
                <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                    لديك مشروع؟
                    <span class="text-gradient">شاركه مع العالم</span>
                </h2>
                <p class="text-lg text-slate-500 mb-10 font-medium leading-relaxed max-w-2xl mx-auto">
                    سجّل في كورساتنا وأضف مشاريعك لمعرض الأعمال ليراها الجميع
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
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/public/portfolio/index.blade.php ENDPATH**/ ?>
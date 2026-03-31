<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title><?php echo e($project->title); ?> - <?php echo e(__('public.site_suffix')); ?></title>
    <meta name="description" content="<?php echo e(Str::limit(strip_tags($project->description ?? ''), 160)); ?>">
    <meta name="theme-color" content="#0F172A">
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">
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
        @media(max-width:768px){.reveal{transition-duration:.5s}.stagger-1,.stagger-2,.stagger-3,.stagger-4{transition-delay:0s}}
    </style>
</head>
<body class="bg-white text-navy-950 antialiased font-body">
    <div id="scroll-progress"></div>
    <?php echo $__env->make('components.unified-navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>.navbar-spacer{display:none}</style>
    <script>(function(){var n=document.getElementById('navbar');if(n){n.classList.add('nav-transparent');n.classList.remove('nav-solid');}})();</script>

    <main class="flex-1">
        
        <section class="relative overflow-hidden bg-navy-950 noise pt-24 pb-16 md:pb-20">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-[#0c1833] to-navy-950"></div>
            <div class="absolute top-[-15%] <?php echo e($isRtl?'left-[-8%]':'right-[-8%]'); ?> w-[500px] h-[500px] rounded-full bg-purple-500/10 blur-[120px]"></div>
            <div class="absolute bottom-[-10%] <?php echo e($isRtl?'right-[-5%]':'left-[-5%]'); ?> w-[400px] h-[400px] rounded-full bg-brand-600/8 blur-[100px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.3) 1px,transparent 0);background-size:40px 40px"></div>

            <div class="relative z-10 max-w-5xl mx-auto px-5 sm:px-8 lg:px-12 w-full">
                <nav class="reveal text-sm text-slate-400/80 mb-8 flex items-center gap-2 flex-wrap">
                    <a href="<?php echo e(url('/')); ?>" class="hover:text-white transition-colors"><?php echo e(__('public.home')); ?></a>
                    <i class="fas fa-chevron-<?php echo e($isRtl?'left':'right'); ?> text-[8px] text-slate-600"></i>
                    <a href="<?php echo e(route('public.portfolio.index')); ?>" class="hover:text-white transition-colors"><?php echo e(__('public.portfolio_page_title')); ?></a>
                    <i class="fas fa-chevron-<?php echo e($isRtl?'left':'right'); ?> text-[8px] text-slate-600"></i>
                    <span class="text-white/90 font-medium"><?php echo e(Str::limit($project->title, 40)); ?></span>
                </nav>

                <div class="reveal stagger-1">
                    <div class="flex flex-wrap gap-2 mb-4">
                        <?php if($project->project_type): ?>
                        <span class="px-3 py-1.5 rounded-lg bg-purple-500/20 text-purple-300 text-xs font-bold"><?php echo e($project->project_type); ?></span>
                        <?php endif; ?>
                        <?php if($project->advancedCourse): ?>
                        <span class="px-3 py-1.5 rounded-lg bg-brand-500/20 text-brand-300 text-xs font-bold flex items-center gap-1.5">
                            <i class="fas fa-graduation-cap text-[10px]"></i> <?php echo e($project->advancedCourse->title); ?>

                        </span>
                        <?php endif; ?>
                        <?php if($project->academicYear): ?>
                        <span class="px-3 py-1.5 rounded-lg bg-blue-500/20 text-blue-300 text-xs font-bold flex items-center gap-1.5">
                            <i class="fas fa-bookmark text-[10px]"></i> <?php echo e($project->academicYear->name); ?>

                        </span>
                        <?php endif; ?>
                    </div>
                    <h1 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-white leading-tight mb-6">
                        <?php echo e($project->title); ?>

                    </h1>
                    <div class="flex items-center gap-4">
                        <?php if($project->user->profile_image ?? null): ?>
                            <img src="<?php echo e($project->user->profile_image_url); ?>" alt="" class="w-11 h-11 rounded-xl object-cover border border-white/10">
                        <?php else: ?>
                            <div class="w-11 h-11 rounded-xl bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center">
                                <span class="text-white font-bold"><?php echo e(mb_substr($project->user->name ?? 'ط', 0, 1)); ?></span>
                            </div>
                        <?php endif; ?>
                        <div>
                            <p class="text-white font-bold"><?php echo e($project->user->name ?? __('public.student_fallback')); ?></p>
                            <p class="text-xs text-slate-400"><?php echo e($project->published_at?->diffForHumans() ?? ''); ?></p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-20 bg-gradient-to-t from-white to-transparent"></div>
        </section>

        
        <section class="py-16 md:py-24 bg-white">
            <div class="max-w-5xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                    <div class="lg:col-span-2 space-y-8">
                        
                        <?php if($project->content_type === \App\Models\PortfolioProject::CONTENT_VIDEO && $project->video_url): ?>
                            <?php $embed = $project->videoEmbedUrl(); ?>
                            <div class="reveal rounded-3xl overflow-hidden border border-slate-100 shadow-lg bg-black aspect-video">
                                <?php if($embed): ?>
                                    <iframe src="<?php echo e($embed); ?>" class="w-full h-full" allowfullscreen></iframe>
                                <?php else: ?>
                                    <div class="w-full h-full flex items-center justify-center">
                                        <a href="<?php echo e($project->video_url); ?>" target="_blank" rel="noopener noreferrer" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-brand-600/25">
                                            <i class="fas fa-play"></i>
                                            فتح الفيديو
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php elseif($project->image_path): ?>
                            <div class="reveal rounded-3xl overflow-hidden border border-slate-100 shadow-lg">
                                <img src="<?php echo e(asset($project->image_path)); ?>" alt="<?php echo e($project->title); ?>" class="w-full object-cover" loading="lazy">
                            </div>
                        <?php endif; ?>

                        
                        <?php if($project->description): ?>
                        <div class="reveal stagger-1 card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-sm hover:shadow-xl hover:border-brand-200/50">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center"><i class="fas fa-align-right text-brand-500 text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-navy-950">وصف المشروع</h2>
                            </div>
                            <div class="text-slate-600 leading-relaxed text-base whitespace-pre-line"><?php echo e($project->description); ?></div>
                        </div>
                        <?php endif; ?>

                        
                        <?php if($project->content_type === \App\Models\PortfolioProject::CONTENT_TEXT && $project->content_text): ?>
                            <div class="reveal stagger-2 card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-sm hover:shadow-xl hover:border-brand-200/50">
                                <div class="flex items-center gap-3 mb-5">
                                    <div class="w-11 h-11 rounded-xl bg-purple-50 flex items-center justify-center"><i class="fas fa-align-right text-purple-500 text-xl"></i></div>
                                    <h2 class="font-heading text-2xl font-black text-navy-950">المحتوى</h2>
                                </div>
                                <div class="text-slate-600 leading-relaxed text-base whitespace-pre-line"><?php echo e($project->content_text); ?></div>
                            </div>
                        <?php endif; ?>
                    </div>

                    
                    <div class="lg:col-span-1">
                        <div class="reveal sticky top-24 space-y-6">
                            
                            <div class="card-hover rounded-3xl bg-white border border-slate-100 shadow-lg overflow-hidden">
                                <div class="bg-gradient-to-<?php echo e($isRtl?'r':'l'); ?> from-brand-500 to-brand-600 p-5">
                                    <h3 class="font-heading text-lg font-bold text-white flex items-center gap-2">
                                        <i class="fas fa-link"></i> روابط المشروع
                                    </h3>
                                </div>
                                <div class="p-5 space-y-3">
                                    <?php if($project->project_url): ?>
                                    <a href="<?php echo e($project->project_url); ?>" target="_blank" rel="noopener noreferrer"
                                       class="btn-primary flex items-center justify-center gap-2.5 w-full py-3 rounded-2xl bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold shadow-lg text-sm">
                                        <i class="fas fa-external-link-alt"></i> <?php echo e(__('public.view_project')); ?>

                                    </a>
                                    <?php endif; ?>
                                    <?php if($project->github_url): ?>
                                    <a href="<?php echo e($project->github_url); ?>" target="_blank" rel="noopener noreferrer"
                                       class="btn-outline flex items-center justify-center gap-2.5 w-full py-3 rounded-2xl bg-white border-2 border-slate-200 hover:border-navy-300 text-navy-950 font-semibold text-sm">
                                        <i class="fab fa-github text-lg"></i> GitHub
                                    </a>
                                    <?php endif; ?>
                                    <?php if(!$project->project_url && !$project->github_url): ?>
                                    <p class="text-center text-slate-400 text-sm py-2">لا توجد روابط خارجية</p>
                                    <?php endif; ?>
                                </div>
                            </div>

                            
                            <div class="rounded-3xl bg-white border border-slate-100 p-5 shadow-sm space-y-3">
                                <?php if($project->advancedCourse): ?>
                                <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                    <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-graduation-cap text-brand-500"></i> الكورس</span>
                                    <span class="font-bold text-navy-950 text-<?php echo e($isRtl?'left':'right'); ?> max-w-[55%] truncate"><?php echo e($project->advancedCourse->title); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if($project->academicYear): ?>
                                <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                    <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-bookmark text-purple-500"></i> المسار</span>
                                    <span class="font-bold text-navy-950"><?php echo e($project->academicYear->name); ?></span>
                                </div>
                                <?php endif; ?>
                                <?php if($project->published_at): ?>
                                <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                    <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-calendar text-blue-500"></i> تاريخ النشر</span>
                                    <span class="font-bold text-navy-950"><?php echo e($project->published_at->format('Y/m/d')); ?></span>
                                </div>
                                <?php endif; ?>
                            </div>

                            <a href="<?php echo e(route('public.portfolio.index')); ?>" class="btn-outline flex items-center justify-center gap-2.5 w-full bg-white border-2 border-slate-200 hover:border-brand-300 text-navy-950 font-semibold py-3.5 rounded-2xl text-sm">
                                <i class="fas fa-arrow-<?php echo e($isRtl?'right':'left'); ?> text-brand-500"></i>
                                <?php echo e(__('public.back_to_gallery')); ?>

                            </a>
                        </div>
                    </div>
                </div>

                
                <?php if($related->count() > 0): ?>
                <div class="mt-20 reveal">
                    <div class="text-center mb-10">
                        <span class="inline-block px-4 py-1.5 rounded-full bg-purple-50 text-purple-600 text-sm font-semibold mb-4">مشاريع مشابهة</span>
                        <h2 class="font-heading text-3xl font-black text-navy-950"><?php echo e(__('public.other_projects_same_path')); ?></h2>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <?php $__currentLoopData = $related; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $r): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <a href="<?php echo e(route('public.portfolio.show', $r->id)); ?>" class="stagger-<?php echo e(min($idx+1,4)); ?> card-hover group flex gap-4 p-5 rounded-2xl bg-white border border-slate-100 shadow-sm">
                            <?php if($r->image_path): ?>
                                <div class="w-24 h-24 flex-shrink-0 rounded-xl overflow-hidden bg-slate-100">
                                    <img src="<?php echo e(asset($r->image_path)); ?>" alt="" class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500">
                                </div>
                            <?php else: ?>
                                <div class="w-24 h-24 flex-shrink-0 rounded-xl bg-gradient-to-br from-purple-500 to-brand-600 flex items-center justify-center">
                                    <i class="fas fa-code text-white/70 text-2xl"></i>
                                </div>
                            <?php endif; ?>
                            <div class="flex-1 min-w-0 flex flex-col justify-center">
                                <h4 class="font-bold text-navy-950 group-hover:text-brand-600 transition-colors truncate mb-1"><?php echo e($r->title); ?></h4>
                                <p class="text-sm text-slate-500 flex items-center gap-2">
                                    <span class="w-5 h-5 rounded bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center flex-shrink-0">
                                        <span class="text-white text-[9px] font-bold"><?php echo e(mb_substr($r->user->name ?? 'ط', 0, 1)); ?></span>
                                    </span>
                                    <?php echo e($r->user->name ?? __('public.student_fallback')); ?>

                                </p>
                            </div>
                            <span class="self-center w-8 h-8 rounded-lg bg-slate-50 group-hover:bg-brand-50 flex items-center justify-center flex-shrink-0 transition-colors">
                                <i class="fas fa-arrow-<?php echo e($isRtl?'left':'right'); ?> text-[10px] text-slate-400 group-hover:text-brand-500 transition-colors"></i>
                            </span>
                        </a>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
                <?php endif; ?>
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
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\public\portfolio\show.blade.php ENDPATH**/ ?>
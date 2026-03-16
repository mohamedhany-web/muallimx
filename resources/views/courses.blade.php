@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ __('public.courses_page_title') }} - {{ __('public.site_suffix') }}</title>
    <meta name="description" content="{{ __('public.courses_subtitle') }}">
    <meta name="theme-color" content="#0F172A">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo-removebg-preview.png') }}">
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
        body.overflow-hidden{overflow:hidden!important;position:fixed!important;width:100%!important}
        body>*{flex-shrink:0}

        .reveal{opacity:0;transform:translateY(40px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}
        .reveal.revealed{opacity:1;transform:translateY(0)}
        .stagger-1{transition-delay:.05s}.stagger-2{transition-delay:.1s}.stagger-3{transition-delay:.15s}.stagger-4{transition-delay:.2s}

        .glass{background:rgba(255,255,255,.75);backdrop-filter:blur(20px) saturate(180%);-webkit-backdrop-filter:blur(20px) saturate(180%);border:1px solid rgba(255,255,255,.5)}
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

<body class="bg-white text-navy-950 antialiased font-body"
      x-data="{
        searchQuery: '',
        selectedLevel: '',
        courses: @js($courses ?? []),
        get filteredCourses() {
            const q = this.searchQuery.toLowerCase().trim();
            const level = this.selectedLevel;
            return this.courses.filter(c => {
                const matchQ = !q || (c.title && c.title.toLowerCase().includes(q)) || (c.description && c.description.toLowerCase().includes(q));
                const matchL = !level || (c.level || '') === level;
                return matchQ && matchL;
            });
        }
      }">
    <div id="scroll-progress"></div>
    @include('components.unified-navbar')
    <style>.navbar-spacer{display:none}</style>
    <script>(function(){var n=document.getElementById('navbar');if(n){n.classList.add('nav-transparent');n.classList.remove('nav-solid');}})();</script>

    <main class="flex-1">
        {{-- ══════ HERO ══════ --}}
        <section class="relative min-h-[70vh] flex items-center overflow-hidden bg-navy-950 noise">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-[#0c1833] to-navy-950"></div>
            <div class="absolute top-[-20%] {{ $isRtl?'left-[-10%]':'right-[-10%]' }} w-[600px] h-[600px] rounded-full bg-brand-500/10 blur-[120px]"></div>
            <div class="absolute bottom-[-10%] {{ $isRtl?'right-[-5%]':'left-[-5%]' }} w-[500px] h-[500px] rounded-full bg-blue-600/8 blur-[100px]"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.3) 1px,transparent 0);background-size:40px 40px"></div>

            <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 pt-28 pb-16 md:pt-36 md:pb-20 w-full">
                <div class="text-center max-w-4xl mx-auto">
                    <div class="reveal">
                        <span class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-white/[0.06] border border-white/[0.1] text-brand-300 text-sm font-medium backdrop-blur-sm">
                            <i class="fas fa-graduation-cap text-brand-400"></i>
                            {{ __('public.courses_page_title') }}
                        </span>
                    </div>
                    <h1 class="reveal stagger-1 font-heading text-4xl sm:text-5xl md:text-6xl font-black leading-[1.15] text-white mt-6">
                        {{ __('public.courses_hero') }}
                        <br>
                        <span class="text-gradient">{{ __('public.courses_hero_highlight') }}</span>
                    </h1>
                    <p class="reveal stagger-2 text-lg sm:text-xl text-slate-300/90 max-w-2xl mx-auto leading-relaxed font-light mt-4">
                        {{ __('public.courses_subtitle') }}
                    </p>

                    {{-- Search & Filter --}}
                    <div class="reveal stagger-3 mt-10 max-w-2xl mx-auto">
                        <div class="flex flex-col sm:flex-row gap-3 items-stretch sm:items-center bg-white/[0.05] border border-white/[0.1] rounded-2xl p-3 backdrop-blur-sm">
                            <div class="flex-1 flex items-center gap-3 px-4 py-3 rounded-xl bg-white/[0.05] border border-white/[0.08]">
                                <i class="fas fa-search text-slate-400"></i>
                                <input type="text" x-model="searchQuery"
                                       placeholder="{{ __('public.search_course_placeholder') }}"
                                       class="flex-1 bg-transparent border-0 outline-none text-white placeholder-slate-500 text-sm sm:text-base">
                            </div>
                            <div class="relative min-w-[160px]">
                                <select x-model="selectedLevel"
                                        class="w-full appearance-none bg-white/[0.05] border border-white/[0.08] rounded-xl px-4 py-3 {{ $isRtl?'pl-10':'pr-10' }} text-white text-sm sm:text-base focus:outline-none focus:border-brand-500/50">
                                    <option value="">{{ __('public.all_levels') }}</option>
                                    <option value="beginner">{{ __('public.level_beginner') }}</option>
                                    <option value="intermediate">{{ __('public.level_intermediate') }}</option>
                                    <option value="advanced">{{ __('public.level_advanced') }}</option>
                                </select>
                                <div class="absolute {{ $isRtl?'left':'right' }}-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                    <i class="fas fa-chevron-down text-xs"></i>
                                </div>
                            </div>
                        </div>
                        <p x-show="searchQuery || selectedLevel" x-cloak x-transition class="mt-3 text-sm text-slate-400">
                            <span x-text="filteredCourses.length"></span> {{ __('public.course_available') }}
                        </p>
                    </div>

                    {{-- Quick stats --}}
                    <div class="reveal stagger-4 flex flex-wrap justify-center gap-6 mt-10">
                        <div class="flex items-center gap-2 text-white/70 text-sm">
                            <span class="w-8 h-8 rounded-lg bg-brand-500/20 flex items-center justify-center"><i class="fas fa-book-open text-brand-400 text-xs"></i></span>
                            <span><span class="font-bold text-white" x-text="courses.length">0</span> {{ __('public.course_available') }}</span>
                        </div>
                        <div class="flex items-center gap-2 text-white/70 text-sm">
                            <span class="w-8 h-8 rounded-lg bg-emerald-500/20 flex items-center justify-center"><i class="fas fa-gift text-emerald-400 text-xs"></i></span>
                            <span><span class="font-bold text-white" x-text="courses.filter(c=>!c.price||c.price==0).length">0</span> كورس مجاني</span>
                        </div>
                        <div class="flex items-center gap-2 text-white/70 text-sm">
                            <span class="w-8 h-8 rounded-lg bg-amber-500/20 flex items-center justify-center"><i class="fas fa-star text-amber-400 text-xs"></i></span>
                            <span><span class="font-bold text-white" x-text="courses.filter(c=>c.is_featured).length">0</span> كورس مميز</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="absolute bottom-0 left-0 right-0 h-24 bg-gradient-to-t from-white to-transparent"></div>
        </section>

        {{-- ══════ COURSES GRID ══════ --}}
        <section id="courses" class="py-20 md:py-28 bg-white">
            <div class="max-w-7xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="text-center max-w-3xl mx-auto mb-14 reveal">
                    <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-4">{{ __('public.courses_section_title') }}</span>
                    <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                        {{ __('public.courses_section_title') }}
                        <span class="text-gradient">{{ __('public.courses_hero_highlight') }}</span>
                    </h2>
                    <p class="text-lg text-slate-500 leading-relaxed">{{ __('public.courses_section_subtitle') }}</p>
                </div>

                @if(isset($courses) && is_array($courses) && count($courses) > 0)
                <template x-if="filteredCourses && filteredCourses.length > 0">
                    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-6 lg:gap-8">
                        <template x-for="(course, index) in filteredCourses" :key="course.id">
                            <a :href="'{{ url('/course') }}/' + course.id"
                               class="reveal card-hover group block h-full rounded-3xl bg-white border border-slate-100 overflow-hidden shadow-sm"
                               :class="'stagger-' + (Math.min(index + 1, 4))">

                                {{-- Thumbnail --}}
                                <div class="relative aspect-[16/10] bg-gradient-to-br from-brand-500 via-blue-500 to-navy-700 overflow-hidden">
                                    <template x-if="course.thumbnail">
                                        <img :src="'{{ asset('storage') }}/' + course.thumbnail" :alt="course.title"
                                             class="absolute inset-0 w-full h-full object-cover group-hover:scale-110 transition-transform duration-700 ease-out">
                                    </template>
                                    <template x-if="!course.thumbnail">
                                        <div class="absolute inset-0 flex items-center justify-center">
                                            <div class="w-20 h-20 rounded-full bg-white/10 flex items-center justify-center backdrop-blur-sm">
                                                <i class="fas fa-play text-white/80 text-2xl {{ $isRtl?'mr-[-2px]':'ml-1' }}"></i>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Overlay gradient --}}
                                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-black/10 to-transparent"></div>

                                    {{-- Featured badge --}}
                                    <template x-if="course.is_featured">
                                        <span class="absolute top-3 {{ $isRtl?'right':'left' }}-3 px-3 py-1.5 rounded-full bg-amber-400/95 text-amber-900 text-[11px] font-bold flex items-center gap-1.5 shadow-lg shadow-amber-500/20">
                                            <i class="fas fa-star text-[9px]"></i>
                                            {{ __('public.featured_badge') }}
                                        </span>
                                    </template>

                                    {{-- Level badge --}}
                                    <div class="absolute top-3 {{ $isRtl?'left':'right' }}-3">
                                        <span class="px-2.5 py-1 rounded-lg text-[11px] font-bold backdrop-blur-md shadow-sm"
                                              :class="course.level === 'beginner' ? 'bg-emerald-500/90 text-white' : (course.level === 'intermediate' ? 'bg-blue-500/90 text-white' : 'bg-purple-500/90 text-white')"
                                              x-text="course.level === 'beginner' ? '{{ __('public.level_beginner') }}' : (course.level === 'intermediate' ? '{{ __('public.level_intermediate') }}' : '{{ __('public.level_advanced') }}')">
                                        </span>
                                    </div>

                                    {{-- Bottom overlay info --}}
                                    <div class="absolute bottom-0 inset-x-0 p-4 flex items-end justify-between">
                                        <div class="flex items-center gap-3">
                                            <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/95 backdrop-blur text-navy-800 text-[11px] font-bold shadow-sm">
                                                <i class="fas fa-play-circle text-brand-500 text-[10px]"></i>
                                                <span x-text="(course.lessons_count || 0) + ' {{ __('public.lesson_single') }}'"></span>
                                            </span>
                                            <template x-if="course.duration_hours && course.duration_hours > 0">
                                                <span class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-white/95 backdrop-blur text-navy-800 text-[11px] font-bold shadow-sm">
                                                    <i class="fas fa-clock text-blue-500 text-[10px]"></i>
                                                    <span x-text="course.duration_hours + ' {{ __('public.hours') }}'"></span>
                                                </span>
                                            </template>
                                        </div>
                                    </div>
                                </div>

                                {{-- Card body --}}
                                <div class="p-5 sm:p-6 flex flex-col flex-1">
                                    {{-- Subject tag --}}
                                    <template x-if="course.academic_subject && course.academic_subject.name">
                                        <div class="mb-3">
                                            <span class="inline-flex items-center gap-1.5 text-[11px] text-brand-700 bg-brand-50 px-3 py-1 rounded-full font-semibold">
                                                <i class="fas fa-book-open text-[9px] text-brand-500"></i>
                                                <span x-text="course.academic_subject.name"></span>
                                            </span>
                                        </div>
                                    </template>

                                    {{-- Title --}}
                                    <h3 class="font-heading text-lg sm:text-xl font-bold text-navy-950 mb-2.5 line-clamp-2 leading-snug group-hover:text-brand-600 transition-colors duration-300"
                                        x-text="course.title || '{{ addslashes(__('public.no_title_fallback')) }}'"></h3>

                                    {{-- Description --}}
                                    <p class="text-[13px] text-slate-500 leading-relaxed mb-5 flex-1 line-clamp-2"
                                       x-text="(course.description || '').substring(0, 120) + ((course.description && course.description.length > 120) ? '...' : '')"></p>

                                    {{-- Instructor --}}
                                    <template x-if="course.instructor && course.instructor.name">
                                        <div class="flex items-center gap-2.5 mb-4 pb-4 border-b border-slate-100">
                                            <div class="w-8 h-8 rounded-lg bg-gradient-to-br from-brand-400 to-brand-600 flex items-center justify-center flex-shrink-0">
                                                <i class="fas fa-chalkboard-teacher text-white text-[11px]"></i>
                                            </div>
                                            <div class="min-w-0">
                                                <p class="text-[11px] text-slate-400 leading-none mb-0.5">{{ __('public.instructor_label') }}</p>
                                                <p class="text-sm font-semibold text-navy-950 truncate" x-text="course.instructor.name"></p>
                                            </div>
                                        </div>
                                    </template>

                                    {{-- Footer: Price + CTA --}}
                                    <div class="flex items-center justify-between mt-auto">
                                        <div>
                                            <template x-if="course.price && course.price > 0">
                                                <div>
                                                    <span class="text-xl font-black text-brand-600" x-text="course.price"></span>
                                                    <span class="text-xs text-slate-400 font-medium">{{ __('public.currency_egp') }}</span>
                                                </div>
                                            </template>
                                            <template x-if="!course.price || course.price == 0">
                                                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-emerald-50 text-emerald-600 font-bold text-sm">
                                                    <i class="fas fa-gift text-xs"></i>
                                                    {{ __('public.free_price') }}
                                                </span>
                                            </template>
                                        </div>
                                        <span class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-gradient-to-{{ $isRtl?'r':'l' }} from-brand-500 to-brand-600 text-white font-bold text-[13px] shadow-lg shadow-brand-600/20 group-hover:shadow-brand-600/40 group-hover:scale-105 transition-all duration-300">
                                            {{ __('public.view_details') }}
                                            <i class="fas fa-arrow-{{ $isRtl?'left':'right' }} text-[10px]"></i>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        </template>
                    </div>
                </template>

                {{-- No results --}}
                <div x-show="filteredCourses && filteredCourses.length === 0" x-cloak x-transition class="text-center py-20 reveal">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 bg-gradient-to-br from-slate-100 to-slate-50 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                            <i class="fas fa-search text-slate-400 text-3xl"></i>
                        </div>
                        <h3 class="font-heading text-xl font-bold text-navy-950 mb-2">{{ __('public.no_results') }}</h3>
                        <p class="text-slate-500 mb-6">{{ __('public.no_results_hint') }}</p>
                        <button @click="searchQuery=''; selectedLevel=''" class="btn-outline inline-flex items-center gap-2 bg-white border-2 border-slate-200 hover:border-brand-300 text-navy-950 px-6 py-3 rounded-2xl font-semibold text-sm">
                            <i class="fas fa-rotate-right text-brand-500"></i>
                            إعادة تعيين البحث
                        </button>
                    </div>
                </div>
                @endif

                @if(!isset($courses) || !is_array($courses) || count($courses) === 0)
                <div class="text-center py-20 reveal">
                    <div class="max-w-md mx-auto">
                        <div class="w-24 h-24 bg-gradient-to-br from-brand-50 to-blue-50 rounded-3xl flex items-center justify-center mx-auto mb-6 shadow-sm">
                            <i class="fas fa-book-open text-brand-400 text-4xl"></i>
                        </div>
                        <h3 class="font-heading text-2xl font-bold text-navy-950 mb-3">{{ __('public.coming_soon') }}</h3>
                        <p class="text-slate-500 mb-8 leading-relaxed">{{ __('public.coming_soon_courses') }}</p>
                        <a href="{{ route('register') }}" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-brand-600/25">
                            <i class="fas fa-bell"></i>
                            {{ __('public.subscribe_updates') }}
                        </a>
                    </div>
                </div>
                @endif
            </div>
        </section>

        {{-- ══════ CTA ══════ --}}
        <section class="py-20 md:py-28 bg-slate-50/50">
            <div class="max-w-4xl mx-auto px-5 sm:px-8 lg:px-12 text-center reveal">
                <span class="inline-block px-4 py-1.5 rounded-full bg-brand-50 text-brand-600 text-sm font-semibold mb-5">ابدأ رحلتك الآن</span>
                <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                    {{ __('public.cta_programming_title') }}
                </h2>
                <p class="text-lg text-slate-500 mb-10 font-medium leading-relaxed max-w-2xl mx-auto">
                    {{ __('public.cta_programming_desc') }}
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    <a href="{{ route('register') }}" class="btn-primary inline-flex items-center justify-center gap-3 bg-gradient-to-l from-brand-500 to-brand-600 hover:from-brand-400 hover:to-brand-500 text-white font-bold text-base sm:text-lg px-8 py-4 rounded-2xl shadow-xl shadow-brand-600/25">
                        {{ __('public.register_free_now') }}
                        <i class="fas fa-arrow-{{ $isRtl?'left':'right' }} text-sm"></i>
                    </a>
                    <a href="{{ route('login') }}" class="btn-outline inline-flex items-center justify-center gap-3 bg-white border-2 border-slate-200 hover:border-brand-300 text-navy-950 font-semibold text-base sm:text-lg px-8 py-4 rounded-2xl">
                        {{ __('public.have_account') }}
                        <i class="fas fa-arrow-{{ $isRtl?'left':'right' }} text-sm"></i>
                    </a>
                </div>
            </div>
        </section>
    </main>

    @include('components.unified-footer')
    <script>
    (function(){
        'use strict';
        function p(){var s=window.pageYOffset||document.documentElement.scrollTop,h=document.documentElement.scrollHeight-window.innerHeight,b=document.getElementById('scroll-progress');if(b)b.style.width=(h>0?(s/h)*100:0)+'%';}
        window.addEventListener('scroll',p,{passive:true});
        function r(){var t=document.querySelectorAll('.reveal');if(!t.length)return;var o=new IntersectionObserver(function(e){e.forEach(function(n){if(n.isIntersecting){n.target.classList.add('revealed');o.unobserve(n.target);}});},{threshold:.08,rootMargin:'0px 0px -40px 0px'});t.forEach(function(el){o.observe(el);});}
        document.addEventListener('click',function(e){var l=e.target.closest('a[href^="#"]');if(l){e.preventDefault();var t=document.querySelector(l.getAttribute('href'));if(t)t.scrollIntoView({behavior:'smooth',block:'start'});}});
        if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',r);else r();
    })();
    </script>
</body>
</html>

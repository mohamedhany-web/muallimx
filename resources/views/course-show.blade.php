@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $thumbUrl = ($course->thumbnail ?? null) ? asset('storage/' . str_replace('\\','/', $course->thumbnail)) : null;
    $introVideoUrl = trim((string)($course->video_url ?? ''));
    $introEmbedUrl = \App\Helpers\VideoHelper::getEmbedUrl($introVideoUrl);
    if (!$introEmbedUrl && $introVideoUrl !== '') {
        if (preg_match('/(?:youtube\.com\/watch\?v=|youtu\.be\/|youtube\.com\/embed\/)([a-zA-Z0-9_-]{11})/', $introVideoUrl, $m)) {
            $introEmbedUrl = 'https://www.youtube.com/embed/' . $m[1] . '?rel=0&modestbranding=1';
        } elseif (preg_match('/vimeo\.com\/(?:video\/)?(\d+)/', $introVideoUrl, $m)) {
            $introEmbedUrl = 'https://player.vimeo.com/video/' . $m[1];
        }
    }
    $introDirectVideo = null;
    if (!$introEmbedUrl && $introVideoUrl !== '' && filter_var($introVideoUrl, FILTER_VALIDATE_URL)) {
        if (preg_match('/\.(mp4|webm|ogg)(\?.*)?$/i', $introVideoUrl)) {
            $introDirectVideo = $introVideoUrl;
        }
    }
    $levelLabel = match($course->level ?? 'beginner') {
        'intermediate' => __('public.level_intermediate'),
        'advanced' => __('public.level_advanced'),
        default => __('public.level_beginner'),
    };
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    @php
        $courseOgImg  = $thumbUrl ?? asset('images/og-image.jpg');
        $courseDesc   = Str::limit(strip_tags($course->description ?? ''), 160);
        $courseTitle  = ($course->title ?? __('public.course_detail_title')) . ' | MuallimX';
        $courseUrl    = url('/course/' . ($course->id ?? ''));
    @endphp
    <title>{{ $courseTitle }}</title>
    <meta name="title"       content="{{ $courseTitle }}">
    <meta name="description" content="{{ $courseDesc }}">
    <meta name="keywords"    content="{{ $course->title ?? 'كورس' }}, تعلم أونلاين, كورسات عربية, MuallimX, {{ $levelLabel ?? '' }}">
    <meta name="author"      content="{{ ($course->instructor->name ?? null) ?? 'MuallimX' }}">
    <meta name="robots"      content="index, follow, max-image-preview:large, max-snippet:-1">
    <meta name="theme-color" content="#283593">
    <link rel="canonical"    href="{{ $courseUrl }}">
    <link rel="alternate" hreflang="ar"        href="{{ $courseUrl }}?lang=ar">
    <link rel="alternate" hreflang="en"        href="{{ $courseUrl }}?lang=en">
    <link rel="alternate" hreflang="x-default" href="{{ $courseUrl }}">
    <!-- Open Graph -->
    <meta property="og:type"             content="article">
    <meta property="og:url"              content="{{ $courseUrl }}">
    <meta property="og:title"            content="{{ $courseTitle }}">
    <meta property="og:description"      content="{{ $courseDesc }}">
    <meta property="og:image"            content="{{ $courseOgImg }}">
    <meta property="og:image:alt"        content="{{ $course->title ?? 'كورس' }}">
    <meta property="og:image:width"      content="1200">
    <meta property="og:image:height"     content="630">
    <meta property="og:locale"           content="{{ $locale === 'ar' ? 'ar_AR' : 'en_US' }}">
    <meta property="og:site_name"        content="MuallimX">
    <!-- Twitter Card -->
    <meta name="twitter:card"        content="summary_large_image">
    <meta name="twitter:site"        content="@MuallimX">
    <meta name="twitter:url"         content="{{ $courseUrl }}">
    <meta name="twitter:title"       content="{{ $courseTitle }}">
    <meta name="twitter:description" content="{{ $courseDesc }}">
    <meta name="twitter:image"       content="{{ $courseOgImg }}">
    <meta name="twitter:image:alt"   content="{{ $course->title ?? 'كورس' }}">
    @include('partials.seo-jsonld', ['jsonldType' => 'course', 'course' => $course])
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo-removebg-preview.png') }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config={theme:{extend:{colors:{navy:{50:'#f0f4ff',100:'#dbe4ff',200:'#bac8ff',300:'#91a7ff',400:'#748ffc',500:'#5c7cfa',600:'#4c6ef5',700:'#4263eb',800:'#3b5bdb',900:'#364fc7',950:'#283593'},brand:{50:'#FFF3E0',100:'#FFE0B2',200:'#FFCC80',300:'#FFB74D',400:'#FFA726',500:'#FB5607',600:'#E04D00',700:'#BF360C',800:'#8D2600',900:'#5D1A00'},mx:{navy:'#283593',indigo:'#1F2A7A',orange:'#FB5607',rose:'#FFE5F7',gold:'#FFE569'}},fontFamily:{heading:['Cairo','Tajawal','IBM Plex Sans Arabic','sans-serif'],body:['Cairo','IBM Plex Sans Arabic','Tajawal','sans-serif']}}}}
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        [x-cloak]{display:none!important}
        *{font-family:'Cairo','IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif}
        h1,h2,h3,h4,h5,h6,.font-heading{font-family:'Cairo','Tajawal','IBM Plex Sans Arabic',sans-serif}
        html{scroll-behavior:smooth;overflow-x:hidden!important}
        body{overflow-x:hidden!important;background:#fff;min-height:100vh;display:flex;flex-direction:column}
        body>*{flex-shrink:0}

        .container-1200{max-width:1200px;margin-inline:auto;padding-inline:24px}
        @media (max-width:768px){.container-1200{padding-inline:16px}}
        .reveal{opacity:0;transform:translateY(40px);transition:opacity .8s cubic-bezier(.16,1,.3,1),transform .8s cubic-bezier(.16,1,.3,1)}
        .reveal.revealed{opacity:1;transform:translateY(0)}
        .stagger-1{transition-delay:.05s}.stagger-2{transition-delay:.1s}.stagger-3{transition-delay:.15s}.stagger-4{transition-delay:.2s}

        .glass{background:rgba(255,255,255,.75);backdrop-filter:blur(20px) saturate(180%);-webkit-backdrop-filter:blur(20px) saturate(180%);border:1px solid rgba(255,255,255,.5)}
        .glass-dark{background:rgba(15,23,42,.55);backdrop-filter:blur(20px) saturate(200%);-webkit-backdrop-filter:blur(20px) saturate(200%);border:1px solid rgba(255,255,255,.08)}

        .text-gradient{background:linear-gradient(135deg,#FB5607 0%,#283593 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
        .btn-primary{position:relative;overflow:hidden;transition:all .4s cubic-bezier(.16,1,.3,1)}
        .btn-primary::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.2),transparent);transition:left .6s}
        .btn-primary:hover::before{left:100%}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 20px 40px -12px rgba(251,86,7,.35)}
        .btn-outline{transition:all .3s cubic-bezier(.16,1,.3,1)}
        .btn-outline:hover{transform:translateY(-2px);box-shadow:0 10px 30px -10px rgba(15,23,42,.2)}
        .card-hover{transition:all .4s cubic-bezier(.16,1,.3,1)}
        .card-hover:hover{transform:translateY(-8px);box-shadow:0 25px 60px -15px rgba(0,0,0,.12)}
        .noise::after{content:'';position:absolute;inset:0;opacity:.02;background-image:url("data:image/svg+xml,%3Csvg viewBox='0 0 256 256' xmlns='http://www.w3.org/2000/svg'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='4' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");pointer-events:none}
        #scroll-progress{position:fixed;top:0;left:0;width:0%;height:3px;background:linear-gradient(90deg,#FB5607,#FFE569);z-index:9999;transition:width .1s linear}
        .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .line-clamp-3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
        #navbar,#navbar.nav-transparent,#navbar.nav-solid{background:rgba(31,42,122,.92)!important;backdrop-filter:blur(12px)!important;-webkit-backdrop-filter:blur(12px)!important;border-bottom:1px solid rgba(255,255,255,.08)!important}
        @media(max-width:768px){.reveal{transition-duration:.5s}.stagger-1,.stagger-2,.stagger-3,.stagger-4{transition-delay:0s}}
    </style>
</head>
<body class="bg-white text-slate-800 antialiased font-body">
    <div id="scroll-progress"></div>
    @include('components.unified-navbar')
    <style>.navbar-spacer{display:block}</style>

    <main class="flex-1">
        {{-- Flash messages --}}
        @foreach(['success' => 'emerald', 'info' => 'brand', 'error' => 'red'] as $type => $color)
            @if(session($type))
            <div class="max-w-5xl mx-auto px-5 sm:px-8 pt-24 pb-2" x-data="{s:true}" x-show="s" @if($type!=='error') x-init="setTimeout(()=>s=false,6000)" @endif>
                <div class="rounded-2xl border border-{{ $color }}-200 bg-{{ $color }}-50 px-5 py-4 flex items-center gap-3 shadow-sm">
                    <i class="fas fa-{{ $type==='success'?'check-circle':($type==='info'?'info-circle':'exclamation-circle') }} text-{{ $color }}-600"></i>
                    <p class="text-{{ $color }}-800 font-semibold flex-1">{{ session($type) }}</p>
                    <button @click="s=false" class="text-{{ $color }}-600 hover:text-{{ $color }}-800"><i class="fas fa-times"></i></button>
                </div>
            </div>
            @endif
        @endforeach

        {{-- ══════ HERO ══════ --}}
        <section class="pt-10 sm:pt-14 lg:pt-16 pb-10 sm:pb-12 overflow-hidden relative" style="background:radial-gradient(circle at 12% 80%,rgba(255,229,247,.65),transparent 28%),radial-gradient(circle at 88% 20%,rgba(40,53,147,.10),transparent 30%),linear-gradient(180deg,#f4f6ff 0%,#fbfbff 55%,#ffffff 100%)">
            <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image:radial-gradient(circle at 1px 1px,rgba(40,53,147,.08) 1px,transparent 0);background-size:30px 30px"></div>

            <div class="container-1200 relative z-10">
                {{-- Breadcrumb --}}
                <nav class="reveal text-sm text-slate-500 mb-8 flex items-center gap-2 flex-wrap">
                    <a href="{{ url('/') }}" class="hover:text-mx-indigo transition-colors">{{ __('public.home') }}</a>
                    <i class="fas fa-chevron-{{ $isRtl?'left':'right' }} text-[8px] text-slate-400"></i>
                    <a href="{{ route('public.courses') }}" class="hover:text-mx-indigo transition-colors">{{ __('public.courses') }}</a>
                    <i class="fas fa-chevron-{{ $isRtl?'left':'right' }} text-[8px] text-slate-400"></i>
                    <span class="text-mx-indigo font-semibold">{{ Str::limit($course->title ?? '', 40) }}</span>
                </nav>

                <div class="grid grid-cols-1 lg:grid-cols-5 gap-8 lg:gap-12 items-start">
                    {{-- Left: Info (3 cols) --}}
                    <div class="lg:col-span-3 reveal">
                        @if($course->is_featured ?? false)
                            <span class="inline-flex items-center gap-1.5 px-4 py-1.5 rounded-full bg-amber-400/90 text-amber-900 text-xs font-bold mb-5 shadow-lg shadow-amber-500/20">
                                <i class="fas fa-star"></i>
                                {{ __('public.featured_course_badge') }}
                            </span>
                        @endif

                        <h1 class="font-heading text-3xl sm:text-4xl lg:text-[2.75rem] font-black text-mx-indigo leading-[1.15] mb-5">
                            {{ $course->title ?? __('public.course_title_fallback') }}
                        </h1>

                        <p class="text-slate-600 text-base sm:text-lg leading-relaxed line-clamp-3 mb-8 max-w-2xl">
                            {{ $course->description ?? __('public.course_desc_fallback') }}
                        </p>

                        {{-- Stats row --}}
                        <div class="flex flex-wrap gap-3 mb-8">
                            @php
                            $heroBadges = [
                                ['icon'=>'fa-play-circle','label'=>($course->lessons_count ?? 0).' '.__('public.lesson_single'),'color'=>'brand'],
                                ['icon'=>'fa-clock','label'=>($course->duration_hours ?? 0).' '.__('public.hours'),'color'=>'blue'],
                                ['icon'=>'fa-signal','label'=>$levelLabel,'color'=>'purple'],
                            ];
                            @endphp
                            @foreach($heroBadges as $badge)
                            <div class="flex items-center gap-2.5 px-4 py-2.5 rounded-xl bg-white border border-slate-200 text-mx-indigo text-sm font-semibold shadow-sm">
                                <i class="fas {{ $badge['icon'] }} text-{{ $badge['color'] }}-400"></i>
                                <span>{{ $badge['label'] }}</span>
                            </div>
                            @endforeach
                        </div>

                        @if($course->instructor)
                            <div class="flex items-center gap-3 mb-8">
                                <div class="w-10 h-10 rounded-xl bg-[#FFE5F7] flex items-center justify-center">
                                    <i class="fas fa-chalkboard-teacher text-[#FB5607]"></i>
                                </div>
                                <div>
                                    <p class="text-xs text-slate-500 font-medium">{{ __('public.instructor_label') }}</p>
                                    @if(\App\Models\InstructorProfile::where('user_id', $course->instructor->id)->where('status', 'approved')->exists())
                                        <a href="{{ route('public.instructors.show', $course->instructor) }}" class="text-mx-indigo font-bold hover:text-[#FB5607] transition-colors">{{ $course->instructor->name }}</a>
                                    @else
                                        <span class="text-mx-indigo font-bold">{{ $course->instructor->name }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        {{-- CTAs --}}
                        <div class="flex flex-wrap gap-3">
                            @auth
                                @if($isEnrolled ?? false)
                                    <a href="{{ route('my-courses.show', $course) }}" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-brand-600/25 text-base">
                                        <i class="fas fa-play-circle"></i> {{ __('public.start_learning_now') }}
                                    </a>
                                @elseif(($course->price ?? 0) > 0 && !($course->is_free ?? false))
                                    <a href="{{ route('public.course.checkout', $course->id) }}" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-brand-600/25 text-base">
                                        <i class="fas fa-shopping-cart"></i> {{ __('public.buy_now') }}
                                    </a>
                                @else
                                    <form action="{{ route('public.course.enroll.free', $course->id) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-emerald-500 to-emerald-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-emerald-600/25 text-base cursor-pointer">
                                            <i class="fas fa-gift"></i> {{ __('public.register_free') }}
                                        </button>
                                    </form>
                                @endif
                            @endauth
                            @guest
                                @if(($course->price ?? 0) > 0 && !($course->is_free ?? false))
                                    <a href="{{ route('register', ['redirect' => route('public.course.checkout', $course->id)]) }}" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-brand-500 to-brand-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-brand-600/25 text-base">
                                        <i class="fas fa-shopping-cart"></i> {{ __('public.buy_now') }}
                                    </a>
                                @else
                                    <a href="{{ route('register', ['redirect' => route('public.course.show', $course->id)]) }}" class="btn-primary inline-flex items-center gap-2.5 bg-gradient-to-l from-emerald-500 to-emerald-600 text-white px-7 py-3.5 rounded-2xl font-bold shadow-xl shadow-emerald-600/25 text-base">
                                        <i class="fas fa-gift"></i> {{ __('public.register_free') }}
                                    </a>
                                @endif
                            @endguest
                            <a href="{{ route('public.courses') }}" class="btn-outline inline-flex items-center gap-2 bg-white border-2 border-slate-200 text-mx-indigo hover:bg-slate-50 px-6 py-3.5 rounded-2xl font-bold text-base">
                                <i class="fas fa-arrow-{{ $isRtl?'right':'left' }} text-sm"></i>
                                {{ __('public.all_courses') }}
                            </a>
                        </div>
                    </div>

                    {{-- Right: Intro video (نفس مكان كارد السعر سابقاً) --}}
                    <div class="lg:col-span-2 reveal stagger-2">
                        @if($introEmbedUrl)
                        <div class="card-hover rounded-3xl overflow-hidden border border-slate-200 shadow-xl bg-slate-900 ring-1 ring-slate-200/80">
                            <div class="aspect-video w-full">
                                <iframe src="{{ $introEmbedUrl }}" title="{{ __('public.course_intro_video') }}"
                                    class="w-full h-full min-h-[220px]"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; fullscreen"
                                    allowfullscreen loading="lazy"></iframe>
                            </div>
                        </div>
                        @elseif($introDirectVideo)
                        <div class="card-hover rounded-3xl overflow-hidden border border-slate-200 shadow-xl bg-black ring-1 ring-slate-200/80">
                            <div class="aspect-video w-full">
                                <video src="{{ $introDirectVideo }}" controls playsinline preload="metadata" class="w-full h-full object-contain">
                                    {{ __('public.course_intro_video_unsupported') }}
                                </video>
                            </div>
                        </div>
                        @elseif($thumbUrl)
                        <div class="card-hover rounded-3xl overflow-hidden border border-slate-200 shadow-xl aspect-video bg-white ring-1 ring-slate-200/80">
                            <img src="{{ $thumbUrl }}" alt="{{ $course->title }}" class="w-full h-full object-cover">
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════ COURSE DETAILS ══════ --}}
        <section class="py-20 md:py-28 bg-white">
            <div class="container-1200">

                {{-- Quick info strip --}}
                <div class="reveal grid grid-cols-2 sm:grid-cols-4 gap-4 mb-16">
                    @php
                    $infoCards = [
                        ['icon'=>'fa-clock','label'=>__('public.duration'),'value'=>($course->duration_hours ?? 0).' '.__('public.hours'),'color'=>'brand'],
                        ['icon'=>'fa-layer-group','label'=>__('public.lessons_count_label'),'value'=>($course->lessons_count ?? 0).' '.__('public.lesson_single'),'color'=>'blue'],
                        ['icon'=>'fa-signal','label'=>__('public.level_label'),'value'=>$levelLabel,'color'=>'purple'],
                        ['icon'=>'fa-book','label'=>'المادة','value'=>$course->academicSubject->name ?? 'غير محدد','color'=>'emerald'],
                    ];
                    @endphp
                    @foreach($infoCards as $idx => $ic)
                    <div class="card-hover rounded-3xl bg-white border border-slate-100 p-5 sm:p-6 shadow-sm hover:shadow-xl hover:border-{{ $ic['color'] }}-200/50 text-center">
                        <div class="w-12 h-12 rounded-2xl bg-{{ $ic['color'] }}-50 flex items-center justify-center mx-auto mb-3">
                            <i class="fas {{ $ic['icon'] }} text-{{ $ic['color'] }}-500 text-xl"></i>
                        </div>
                        <p class="font-heading text-xl sm:text-2xl font-black text-navy-950 mb-1">{{ $ic['value'] }}</p>
                        <p class="text-xs text-slate-500 font-medium">{{ $ic['label'] }}</p>
                    </div>
                    @endforeach
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                    {{-- Main content --}}
                    <div class="lg:col-span-2 space-y-8">
                        {{-- About --}}
                        <div class="reveal card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-sm hover:shadow-xl hover:border-brand-200/50">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-brand-50 flex items-center justify-center"><i class="fas fa-info-circle text-brand-500 text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-navy-950">{{ __('public.about_course') }}</h2>
                            </div>
                            <div class="text-slate-600 leading-relaxed text-base">
                                <p>{{ $course->description ?? __('public.course_desc_fallback') }}</p>
                                @if($course->objectives)
                                <div class="mt-6">
                                    <h3 class="font-heading text-lg font-bold text-navy-950 mb-3">{{ __('public.course_objectives') }}</h3>
                                    <div class="bg-gradient-to-br from-brand-50/60 to-blue-50/40 rounded-2xl p-6 border border-brand-100/50">
                                        <p class="whitespace-pre-line text-slate-700">{{ $course->objectives }}</p>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>

                        {{-- What you'll learn --}}
                        @if($course->what_you_learn)
                        <div class="reveal stagger-1 card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-sm hover:shadow-xl hover:border-emerald-200/50">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-emerald-50 flex items-center justify-center"><i class="fas fa-graduation-cap text-emerald-500 text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-navy-950">{{ __('public.what_you_learn') }}</h2>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                @foreach(array_filter(explode("\n", $course->what_you_learn)) as $point)
                                <div class="flex items-start gap-3 p-4 rounded-xl bg-gradient-to-br from-emerald-50/60 to-brand-50/30 border border-emerald-100/60 hover:border-emerald-200 transition-colors">
                                    <i class="fas fa-check-circle text-emerald-500 mt-0.5 flex-shrink-0"></i>
                                    <span class="text-slate-700 text-sm leading-relaxed">{{ trim($point) }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>
                        @endif

                        {{-- Requirements --}}
                        @if($course->requirements)
                        <div class="reveal stagger-2 card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-sm hover:shadow-xl hover:border-amber-200/50">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center"><i class="fas fa-list-check text-amber-500 text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-navy-950">{{ __('public.requirements') }}</h2>
                            </div>
                            <div class="bg-gradient-to-br from-amber-50/50 to-slate-50/30 rounded-2xl p-6 border border-amber-100/50">
                                <p class="text-slate-700 whitespace-pre-line leading-relaxed">{{ $course->requirements }}</p>
                            </div>
                        </div>
                        @endif
                    </div>

                    {{-- Sidebar --}}
                    <div class="lg:col-span-1">
                        {{-- Sticky purchase card --}}
                        <div class="reveal sticky top-24 space-y-6">
                            <div class="card-hover rounded-3xl bg-white border border-slate-100 shadow-lg overflow-hidden">
                                {{-- Colored header --}}
                                <div class="bg-gradient-to-l from-brand-500 to-brand-600 p-5 text-center">
                                    @if(($course->price ?? 0) > 0)
                                        <div class="text-3xl font-black text-white">{{ number_format($course->price, 0) }} <span class="text-lg font-medium text-white/80">{{ __('public.currency_egp') }}</span></div>
                                    @else
                                        <div class="text-2xl font-black text-white flex items-center justify-center gap-2"><i class="fas fa-gift text-xl"></i>{{ __('public.free_price') }}</div>
                                    @endif
                                </div>
                                <div class="p-6">
                                    <dl class="space-y-3 mb-6">
                                        <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                            <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-clock text-brand-500"></i> {{ __('public.duration') }}</span>
                                            <span class="font-bold text-navy-950">{{ $course->duration_hours ?? 0 }} {{ __('public.hours') }}</span>
                                        </div>
                                        <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                            <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-layer-group text-blue-500"></i> {{ __('public.lessons_count_label') }}</span>
                                            <span class="font-bold text-navy-950">{{ $course->lessons_count ?? 0 }}</span>
                                        </div>
                                        <div class="flex justify-between items-center p-3 bg-slate-50/80 rounded-xl text-sm">
                                            <span class="text-slate-500 flex items-center gap-2"><i class="fas fa-signal text-purple-500"></i> {{ __('public.level_label') }}</span>
                                            <span class="font-bold text-navy-950">{{ $levelLabel }}</span>
                                        </div>
                                    </dl>
                                    @auth
                                        @if($isEnrolled ?? false)
                                            <a href="{{ route('my-courses.show', $course) }}" class="btn-primary block w-full text-center py-3.5 rounded-2xl bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold shadow-lg">
                                                <i class="fas fa-play-circle {{ $isRtl?'ml-2':'mr-2' }}"></i>{{ __('public.start_learning_now') }}
                                            </a>
                                        @elseif(($course->price ?? 0) > 0 && !($course->is_free ?? false))
                                            <a href="{{ route('public.course.checkout', $course->id) }}" class="btn-primary block w-full text-center py-3.5 rounded-2xl bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold shadow-lg">
                                                <i class="fas fa-shopping-cart {{ $isRtl?'ml-2':'mr-2' }}"></i>{{ __('public.buy_now') }}
                                            </a>
                                        @else
                                            <form action="{{ route('public.course.enroll.free', $course->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn-primary block w-full text-center py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold shadow-lg cursor-pointer">
                                                    <i class="fas fa-gift {{ $isRtl?'ml-2':'mr-2' }}"></i>{{ __('public.register_free') }}
                                                </button>
                                            </form>
                                        @endif
                                    @endauth
                                    @guest
                                        @if(($course->price ?? 0) > 0 && !($course->is_free ?? false))
                                            <a href="{{ route('register', ['redirect' => route('public.course.checkout', $course->id)]) }}" class="btn-primary block w-full text-center py-3.5 rounded-2xl bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold shadow-lg">
                                                <i class="fas fa-shopping-cart {{ $isRtl?'ml-2':'mr-2' }}"></i>{{ __('public.buy_now') }}
                                            </a>
                                        @else
                                            <a href="{{ route('register', ['redirect' => route('public.course.show', $course->id)]) }}" class="btn-primary block w-full text-center py-3.5 rounded-2xl bg-emerald-500 hover:bg-emerald-600 text-white font-bold shadow-lg">
                                                <i class="fas fa-gift {{ $isRtl?'ml-2':'mr-2' }}"></i>{{ __('public.register_free') }}
                                            </a>
                                        @endif
                                    @endguest
                                </div>
                            </div>

                            {{-- Related courses --}}
                            @if(isset($relatedCourses) && $relatedCourses->isNotEmpty())
                            <div class="rounded-3xl bg-white border border-slate-100 p-6 shadow-sm">
                                <h3 class="font-heading text-lg font-bold text-navy-950 mb-4 flex items-center gap-2">
                                    <i class="fas fa-bookmark text-brand-500"></i>
                                    كورسات ذات صلة
                                </h3>
                                <div class="space-y-3">
                                    @foreach($relatedCourses->take(3) as $related)
                                        @php $relThumb = $related->thumbnail ? str_replace('\\','/', $related->thumbnail) : null; @endphp
                                        <a href="{{ route('public.course.show', $related->id) }}" class="flex gap-3 p-3 rounded-2xl border border-slate-100 hover:border-brand-200 hover:shadow-md transition-all duration-300 group">
                                            <div class="w-16 h-16 flex-shrink-0 rounded-xl bg-gradient-to-br from-brand-500 to-navy-600 overflow-hidden flex items-center justify-center">
                                                @if($relThumb)
                                                    <img src="{{ asset('storage/' . $relThumb) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                                                @else
                                                    <i class="fas fa-book text-white/80 text-lg"></i>
                                                @endif
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <h4 class="font-bold text-navy-950 text-sm group-hover:text-brand-600 transition-colors line-clamp-2 leading-snug">{{ $related->title }}</h4>
                                                @if(($related->price ?? 0) > 0)
                                                    <span class="text-xs font-bold text-brand-600 mt-1 block">{{ number_format($related->price, 0) }} {{ __('public.currency_egp') }}</span>
                                                @else
                                                    <span class="text-xs font-bold text-emerald-600 mt-1 block">{{ __('public.free_price') }}</span>
                                                @endif
                                            </div>
                                        </a>
                                    @endforeach
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- ══════ CTA ══════ --}}
        <section class="py-20 md:py-28 bg-slate-50/50">
            <div class="container-1200 text-center reveal">
                <h2 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black text-navy-950 mb-5 leading-tight">
                    جاهز للانطلاق في هذا الكورس؟
                </h2>
                <p class="text-lg text-slate-500 leading-relaxed mb-10 font-medium">
                    سجّل الآن وابدأ التعلم بخطوات واضحة وتجربة احترافية متكاملة.
                </p>
                <div class="flex flex-col sm:flex-row gap-4 justify-center">
                    @auth
                        <a href="{{ route('my-courses.show', $course) }}" class="btn-primary inline-flex items-center justify-center gap-3 bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold text-lg px-8 py-4 rounded-2xl shadow-xl shadow-brand-600/25">
                            {{ __('public.start_learning_now') }} <i class="fas fa-arrow-{{ $isRtl?'left':'right' }} text-sm"></i>
                        </a>
                    @endauth
                    @guest
                        <a href="{{ route('register') }}" class="btn-primary inline-flex items-center justify-center gap-3 bg-gradient-to-l from-brand-500 to-brand-600 text-white font-bold text-lg px-8 py-4 rounded-2xl shadow-xl shadow-brand-600/25">
                            {{ __('public.register_free_now') }} <i class="fas fa-arrow-{{ $isRtl?'left':'right' }} text-sm"></i>
                        </a>
                    @endguest
                    <a href="{{ route('public.courses') }}" class="btn-outline inline-flex items-center justify-center gap-3 bg-white border-2 border-slate-200 hover:border-brand-300 text-navy-950 font-semibold text-lg px-8 py-4 rounded-2xl">
                        {{ __('public.all_courses') }} <i class="fas fa-arrow-{{ $isRtl?'left':'right' }} text-sm"></i>
                    </a>
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
                        <li><a class="hover:text-mx-gold transition-colors" href="{{ route('home') }}">الرئيسية</a></li>
                        <li><a class="hover:text-mx-gold transition-colors" href="{{ route('public.courses') }}">الكورسات</a></li>
                        <li><a class="hover:text-mx-gold transition-colors" href="{{ route('public.instructors.index') }}">المدربون</a></li>
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
                <p>&copy; {{ date('Y') }} MuallimX — جميع الحقوق محفوظة</p>
                <p>تعليم عربي احترافي يركز على النتائج</p>
            </div>
        </div>
    </footer>
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

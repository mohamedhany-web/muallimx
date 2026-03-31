@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $socials = $profile->social_links ?? [];
    $hasSocials = !empty($socials['linkedin']) || !empty($socials['twitter']) || !empty($socials['youtube']) || !empty($socials['facebook']) || !empty($socials['website']);
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>{{ $profile->user->name ?? __('public.instructor_fallback') }} - {{ __('public.site_suffix') }}</title>
    <meta name="description" content="{{ Str::limit($profile->bio ?? $profile->headline ?? '', 160) }}">
    <meta name="theme-color" content="#283593">
    <link rel="icon" type="image/x-icon" href="{{ asset('favicon.ico') }}">
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('logo-removebg-preview.png') }}">
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
    };
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
        body>*{flex-shrink:0}
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
        .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        .line-clamp-3{display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden}
        .navbar-spacer{display:block!important}
        #navbar,#navbar.nav-transparent,#navbar.nav-solid{
            background:rgba(31,42,122,.92)!important;
            backdrop-filter:blur(12px)!important;
            -webkit-backdrop-filter:blur(12px)!important;
            border-bottom:1px solid rgba(255,255,255,.08)!important;
        }
    </style>
</head>
<body class="font-body text-slate-800">
    <div id="scroll-progress"></div>
    @include('components.unified-navbar')

    <main class="flex-1">
        {{-- Hero — نفس أجواء صفحة المدربين --}}
        <section class="pt-10 sm:pt-14 lg:pt-16 pb-10 sm:pb-14 overflow-hidden relative" style="background:radial-gradient(circle at 12% 80%,rgba(255,229,247,.65),transparent 28%),radial-gradient(circle at 88% 20%,rgba(40,53,147,.10),transparent 30%),linear-gradient(180deg,#f4f6ff 0%,#fbfbff 55%,#ffffff 100%)">
            <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image:radial-gradient(circle at 1px 1px,rgba(40,53,147,.08) 1px,transparent 0);background-size:30px 30px"></div>

            <div class="container-1200 relative z-10">
                <nav class="reveal text-sm text-slate-500 mb-8 flex items-center gap-2 flex-wrap">
                    <a href="{{ url('/') }}" class="hover:text-mx-indigo transition-colors">{{ __('public.home') }}</a>
                    <i class="fas fa-chevron-{{ $isRtl ? 'left' : 'right' }} text-[8px] text-slate-400"></i>
                    <a href="{{ route('public.instructors.index') }}" class="hover:text-mx-indigo transition-colors">{{ __('public.instructors_page_title') }}</a>
                    <i class="fas fa-chevron-{{ $isRtl ? 'left' : 'right' }} text-[8px] text-slate-400"></i>
                    <span class="text-mx-indigo font-semibold">{{ $profile->user->name }}</span>
                </nav>

                <div class="flex flex-col md:flex-row gap-8 md:gap-12 items-start">
                    <div class="reveal flex-shrink-0 mx-auto md:mx-0">
                        <div class="w-40 h-40 md:w-48 md:h-48 rounded-2xl overflow-hidden border border-slate-200 shadow-[0_20px_44px_-26px_rgba(31,42,122,.28)] bg-white flex items-center justify-center" style="background:linear-gradient(135deg,#e9edff,#f8f9ff)">
                            @if($profile->photo_path)
                                <img src="{{ $profile->photo_url }}" alt="{{ $profile->user->name }}"
                                     class="w-full h-full object-cover"
                                     onerror="this.style.display='none';this.nextElementSibling.classList.remove('hidden')">
                                <div class="hidden w-full h-full flex items-center justify-center">
                                    <i class="fas fa-user text-[#283593]/50 text-6xl"></i>
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <i class="fas fa-user text-[#283593]/50 text-6xl"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="reveal s1 flex-1 min-w-0 text-center md:text-start">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold mb-3" style="background:#FFE5F7;color:#283593">
                            <i class="fas fa-check-circle text-[10px]"></i>
                            مدرّب معتمد
                        </span>

                        <h1 class="font-heading text-3xl sm:text-4xl lg:text-5xl font-black text-mx-indigo leading-tight mb-2">
                            {{ $profile->user->name }}
                        </h1>

                        <p class="text-lg sm:text-xl font-semibold mb-5" style="color:#FB5607">
                            {{ $profile->headline ?? __('public.instructor_fallback') }}
                        </p>

                        @if($profile->bio)
                        <p class="text-slate-600 text-base leading-relaxed mb-6 max-w-2xl mx-auto md:mx-0 line-clamp-3">
                            {{ $profile->bio }}
                        </p>
                        @endif

                        <div class="flex flex-wrap gap-3 justify-center md:justify-start mb-6">
                            @if($courses->count() > 0)
                            <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-mx-indigo text-sm font-semibold shadow-sm">
                                <i class="fas fa-book-open" style="color:#FB5607"></i>
                                <span>{{ $courses->count() }} {{ $courses->count() > 1 ? 'كورسات' : 'كورس' }}</span>
                            </div>
                            @endif
                            @if(count($profile->skills_list) > 0)
                            <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-mx-indigo text-sm font-semibold shadow-sm">
                                <i class="fas fa-cogs text-[#283593]"></i>
                                <span>{{ count($profile->skills_list) }} {{ __('public.skills') }}</span>
                            </div>
                            @endif
                            @if(count($profile->experience_list) > 0)
                            <div class="flex items-center gap-2 px-4 py-2.5 rounded-xl border border-slate-200 bg-white text-mx-indigo text-sm font-semibold shadow-sm">
                                <i class="fas fa-briefcase text-amber-600"></i>
                                <span>{{ count($profile->experience_list) }} خبرة</span>
                            </div>
                            @endif
                        </div>

                        @if(isset($consultationSetting) && $consultationSetting->is_active)
                        <div class="flex flex-wrap items-center gap-3 justify-center md:justify-start mb-6">
                            <span class="text-sm text-slate-600">استشارة خاصة — <strong class="text-mx-indigo">{{ number_format($profile->effectiveConsultationPriceEgp(), 2) }}</strong> ج.م</span>
                            @auth
                                @if(auth()->user()->isStudent())
                                    <a href="{{ route('consultations.create', $profile->user) }}"
                                       class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#FB5607] hover:bg-[#e84d00] text-white text-sm font-bold shadow-md transition-all">
                                        <i class="fas fa-comments"></i>
                                        طلب استشارة
                                    </a>
                                @endif
                            @else
                                <a href="{{ route('login', ['redirect' => route('consultations.create', $profile->user)]) }}"
                                   class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-[#FB5607] hover:bg-[#e84d00] text-white text-sm font-bold shadow-md transition-all">
                                    <i class="fas fa-comments"></i>
                                    طلب استشارة
                                </a>
                            @endauth
                        </div>
                        @endif

                        @if($hasSocials)
                        <div class="flex flex-wrap gap-2.5 justify-center md:justify-start">
                            @if(!empty($socials['linkedin']))
                            <a href="{{ $socials['linkedin'] }}" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-[#0A66C2] text-white flex items-center justify-center shadow-md hover:bg-[#004182] hover:scale-105 transition-all">
                                <i class="fab fa-linkedin-in text-lg"></i>
                            </a>
                            @endif
                            @if(!empty($socials['twitter']))
                            <a href="{{ $socials['twitter'] }}" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-mx-indigo text-white flex items-center justify-center shadow-md hover:bg-[#0f172a] hover:scale-105 transition-all">
                                <i class="fab fa-x-twitter text-lg"></i>
                            </a>
                            @endif
                            @if(!empty($socials['youtube']))
                            <a href="{{ $socials['youtube'] }}" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-red-600 text-white flex items-center justify-center shadow-md hover:bg-red-700 hover:scale-105 transition-all">
                                <i class="fab fa-youtube text-lg"></i>
                            </a>
                            @endif
                            @if(!empty($socials['facebook']))
                            <a href="{{ $socials['facebook'] }}" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl bg-[#1877F2] text-white flex items-center justify-center shadow-md hover:bg-[#0d65d9] hover:scale-105 transition-all">
                                <i class="fab fa-facebook-f text-lg"></i>
                            </a>
                            @endif
                            @if(!empty($socials['website']))
                            <a href="{{ $socials['website'] }}" target="_blank" rel="noopener noreferrer" class="w-11 h-11 rounded-xl border border-slate-200 bg-white text-mx-indigo flex items-center justify-center shadow-sm hover:border-mx-indigo hover:scale-105 transition-all">
                                <i class="fas fa-globe text-lg"></i>
                            </a>
                            @endif
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </section>

        {{-- المحتوى --}}
        <section class="py-14 md:py-20 bg-white">
            <div class="container-1200">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                    <div class="lg:col-span-2 space-y-8">
                        @if($profile->bio)
                        <div class="reveal card-base hover-lift !p-6 sm:!p-8">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#FFE5F7"><i class="fas fa-user-circle text-mx-indigo text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-mx-indigo">نبذة تعريفية</h2>
                            </div>
                            <div class="text-slate-600 leading-relaxed text-base whitespace-pre-line">{{ $profile->bio }}</div>
                        </div>
                        @endif

                        @if($profile->experience)
                        <div class="reveal s1 card-base hover-lift !p-6 sm:!p-8">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl bg-amber-50 flex items-center justify-center"><i class="fas fa-briefcase text-amber-600 text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-mx-indigo">{{ __('public.experience') }}</h2>
                            </div>
                            @if(count($profile->experience_list) > 0)
                            <div class="space-y-3">
                                @foreach($profile->experience_list as $item)
                                <div class="flex items-start gap-3 p-4 rounded-xl border border-amber-100/80 bg-gradient-to-br from-amber-50/50 to-slate-50/30">
                                    <div class="w-7 h-7 rounded-lg bg-amber-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fas fa-check text-amber-700 text-[10px]"></i>
                                    </div>
                                    <span class="text-slate-700 text-sm leading-relaxed flex-1">{{ $item }}</span>
                                </div>
                                @endforeach
                            </div>
                            @else
                            <div class="rounded-2xl p-6 border border-amber-100/60 bg-amber-50/30">
                                <p class="text-slate-700 whitespace-pre-line leading-relaxed">{{ $profile->experience }}</p>
                            </div>
                            @endif
                        </div>
                        @endif

                        @if($courses->count() > 0)
                        <div class="reveal s2 card-base hover-lift !p-6 sm:!p-8">
                            <div class="flex items-center gap-3 mb-5">
                                <div class="w-11 h-11 rounded-xl flex items-center justify-center" style="background:#e9edff"><i class="fas fa-graduation-cap text-mx-indigo text-xl"></i></div>
                                <h2 class="font-heading text-2xl font-black text-mx-indigo">{{ __('public.instructor_courses') }}</h2>
                            </div>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                @foreach($courses as $c)
                                @php $cThumb = $c->thumbnail ? str_replace('\\','/', $c->thumbnail) : null; @endphp
                                <a href="{{ route('public.course.show', $c->id) }}" class="group flex gap-4 p-4 rounded-2xl border border-slate-100 hover:border-[#283593]/25 hover:shadow-lg transition-all duration-300 bg-white">
                                    <div class="w-20 h-20 flex-shrink-0 rounded-xl overflow-hidden flex items-center justify-center" style="background:linear-gradient(135deg,#283593,#1F2A7A)">
                                        @if($cThumb)
                                            <img src="{{ asset('storage/' . $cThumb) }}" alt="" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                                        @else
                                            <i class="fas fa-book text-white/90 text-2xl"></i>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0 flex flex-col justify-center">
                                        <h4 class="font-bold text-mx-indigo group-hover:text-[#FB5607] transition-colors line-clamp-2 leading-snug text-sm mb-1.5">{{ $c->title }}</h4>
                                        <div class="flex items-center gap-3 text-xs text-slate-500">
                                            @if($c->price > 0)
                                                <span class="font-bold" style="color:#FB5607">{{ number_format($c->price, 0) }} {{ __('public.currency_egp') }}</span>
                                            @else
                                                <span class="font-bold text-emerald-600 flex items-center gap-1"><i class="fas fa-gift text-[10px]"></i> {{ __('public.free_price') }}</span>
                                            @endif
                                            @if($c->lessons_count ?? 0)
                                            <span class="flex items-center gap-1"><i class="fas fa-play-circle text-slate-400"></i> {{ $c->lessons_count }} {{ __('public.lesson_single') }}</span>
                                            @endif
                                        </div>
                                    </div>
                                    <div class="flex-shrink-0 self-center">
                                        <span class="w-8 h-8 rounded-lg bg-slate-50 group-hover:bg-[#FFE5F7] flex items-center justify-center transition-colors">
                                            <i class="fas fa-arrow-{{ $isRtl ? 'left' : 'right' }} text-[10px] text-slate-400 group-hover:text-mx-indigo transition-colors"></i>
                                        </span>
                                    </div>
                                </a>
                                @endforeach
                            </div>
                        </div>
                        @endif
                    </div>

                    <div class="lg:col-span-1">
                        <div class="reveal lg:sticky lg:top-28 space-y-6">
                            @if(count($profile->skills_list) > 0)
                            <div class="card-base hover-lift !p-0 overflow-hidden">
                                <div class="px-5 py-4 border-b border-slate-100" style="background:linear-gradient(135deg,#283593,#1F2A7A)">
                                    <h3 class="font-heading text-lg font-bold text-white flex items-center gap-2">
                                        <i class="fas fa-cogs"></i>
                                        {{ __('public.skills') }}
                                    </h3>
                                </div>
                                <div class="p-5">
                                    <div class="flex flex-wrap gap-2">
                                        @foreach($profile->skills_list as $skill)
                                        <span class="px-3 py-1.5 rounded-xl text-slate-700 text-sm font-medium border" style="background:#f8f9ff;border-color:#eceef8">{{ $skill }}</span>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                            @endif

                            <div class="card-base hover-lift !p-0 overflow-hidden">
                                <div class="px-5 py-4 border-b border-white/10" style="background:linear-gradient(135deg,#FB5607,#e84d00)">
                                    <h3 class="font-heading text-lg font-bold text-white flex items-center gap-2">
                                        <i class="fas fa-info-circle"></i>
                                        معلومات سريعة
                                    </h3>
                                </div>
                                <div class="p-5 space-y-3">
                                    <div class="flex justify-between items-center p-3 rounded-xl text-sm border border-slate-100 bg-slate-50/80">
                                        <span class="text-slate-600 flex items-center gap-2"><i class="fas fa-book-open text-mx-indigo"></i> الكورسات</span>
                                        <span class="font-bold text-mx-indigo">{{ $courses->count() }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 rounded-xl text-sm border border-slate-100 bg-slate-50/80">
                                        <span class="text-slate-600 flex items-center gap-2"><i class="fas fa-cogs text-mx-indigo"></i> المهارات</span>
                                        <span class="font-bold text-mx-indigo">{{ count($profile->skills_list) }}</span>
                                    </div>
                                    <div class="flex justify-between items-center p-3 rounded-xl text-sm border border-slate-100 bg-slate-50/80">
                                        <span class="text-slate-600 flex items-center gap-2"><i class="fas fa-check-circle text-emerald-600"></i> الحالة</span>
                                        <span class="font-bold text-emerald-600">معتمد</span>
                                    </div>
                                </div>
                                <div class="px-5 pb-5">
                                    <a href="{{ route('public.courses') }}" class="btn-primary block w-full text-center py-3 rounded-2xl !bg-[#283593] hover:!bg-[#1f2a7a] text-white font-bold text-sm shadow-lg">
                                        <i class="fas fa-graduation-cap {{ $isRtl ? 'ml-2' : 'mr-2' }}"></i>تصفّح جميع الكورسات
                                    </a>
                                </div>
                            </div>

                            <a href="{{ route('public.instructors.index') }}" class="btn-secondary flex items-center justify-center gap-2.5 w-full !py-3.5 rounded-2xl font-semibold text-sm border-2 border-slate-200 hover:border-mx-indigo/30">
                                <i class="fas fa-arrow-{{ $isRtl ? 'right' : 'left' }}" style="color:#FB5607"></i>
                                {{ __('public.all_instructors_link') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- CTA — نفس كتلة صفحة المدربين --}}
        <section class="pt-14 sm:pt-18 pb-10 sm:pb-12" style="background:linear-gradient(180deg,#f4f7ff 0%,#ffffff 100%)">
            <div class="container-1200">
                <div class="reveal rounded-[28px] border border-slate-200 bg-white shadow-[0_20px_44px_-26px_rgba(31,42,122,.28)] px-6 sm:px-10 py-10 sm:py-12 text-center">
                    <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs sm:text-sm font-bold mb-5" style="background:#FFE5F7;color:#283593"><i class="fas fa-rocket"></i> {{ __('public.instructors_page_title') }}</span>
                    <h2 class="font-heading text-3xl sm:text-5xl font-black text-mx-indigo mb-4">
                        جاهز تبدأ
                        <span style="color:#FB5607">رحلتك؟</span>
                    </h2>
                    <p class="text-slate-600 text-base sm:text-lg max-w-3xl mx-auto leading-8 mb-7">
                        انضم لآلاف المعلمين الذين طوّروا مسيرتهم المهنية مع MuallimX
                    </p>
                    <div class="flex flex-col sm:flex-row justify-center gap-3 sm:gap-4">
                        <a href="{{ route('public.courses') }}" class="btn-primary inline-flex items-center justify-center gap-3 !bg-[#FB5607] hover:!bg-[#e84d00] text-white font-bold text-base sm:text-lg px-8 py-4 rounded-2xl">
                            {{ __('public.browse_courses') }}
                            <i class="fas fa-arrow-{{ $isRtl ? 'left' : 'right' }} text-sm"></i>
                        </a>
                        <a href="{{ route('register') }}" class="btn-secondary inline-flex items-center justify-center gap-3 !bg-[#283593] !text-white !border-[#283593] hover:!bg-[#1f2a7a] font-semibold text-base sm:text-lg px-8 py-4 rounded-2xl">
                            سجّل مجاناً
                            <i class="fas fa-arrow-{{ $isRtl ? 'left' : 'right' }} text-sm"></i>
                        </a>
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

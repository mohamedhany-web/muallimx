@php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $metaTitle = $landingPage->meta_title ?: ($landingPage->headline ?: $landingPage->title);
    $metaDesc = $landingPage->meta_description ?: ($landingPage->subheadline ?: 'اكتشف منصة Muallimx للمعلمين');
    $ogImage = $landingPage->ogImageUrl() ?: asset('images/og-image.jpg');
    $pageUrl = $landingPage->publicUrl();
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <title>{{ $metaTitle }} | Muallimx</title>
    <meta name="description" content="{{ $metaDesc }}">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="{{ $pageUrl }}">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $pageUrl }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDesc }}">
    <meta property="og:image" content="{{ $ogImage }}">
    <meta property="og:site_name" content="Muallimx">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDesc }}">
    <meta name="twitter:image" content="{{ $ogImage }}">
    <meta name="theme-color" content="#283593">
    @include('partials.favicon-links')

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    mx: { navy: '#283593', indigo: '#1F2A7A', orange: '#FB5607', soft: '#F7F8FF' }
                },
                fontFamily: { sans: ['Cairo', 'sans-serif'] }
            }
        }
    }
    </script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        body{font-family:'Cairo',sans-serif;background:#fff}
        .container-lp{max-width:960px;margin-inline:auto;padding-inline:20px}
        .btn-primary{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:14px 28px;border-radius:16px;font-weight:800;color:#fff;background:#FB5607;transition:transform .2s,box-shadow .2s}
        .btn-primary:hover{transform:scale(1.02);box-shadow:0 12px 28px -10px rgba(251,86,7,.45)}
        .btn-secondary{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:14px 28px;border-radius:16px;font-weight:800;color:#fff;background:#283593;transition:background .2s}
        .btn-secondary:hover{background:#1F2A7A}
        .btn-outline{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:14px 28px;border-radius:16px;font-weight:800;color:#283593;background:#fff;border:2px solid #283593}
        .btn-whatsapp{display:inline-flex;align-items:center;justify-content:center;gap:.5rem;padding:14px 28px;border-radius:16px;font-weight:800;color:#fff;background:#25D366}
        .video-wrap{position:relative;padding-bottom:56.25%;height:0;overflow:hidden;border-radius:18px;background:#0f172a}
        .video-wrap iframe{position:absolute;inset:0;width:100%;height:100%;border:0}
    </style>
</head>
<body class="text-slate-800">
<header class="sticky top-0 z-40 border-b border-white/10" style="background:rgba(31,42,122,.95);backdrop-filter:blur(10px)">
    <div class="container-lp flex items-center justify-between h-16">
        <a href="{{ route('home') }}" class="flex items-center gap-2 text-white font-black text-lg">
            <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm" style="background:#FB5607">M</span>
            Muallimx
        </a>
        <div class="flex items-center gap-2">
            <a href="{{ route('login') }}" class="hidden sm:inline text-white/80 hover:text-white text-sm font-bold px-3 py-2">دخول</a>
            <a href="{{ route('register') }}" class="text-sm font-bold text-white px-4 py-2 rounded-xl" style="background:#FB5607">إنشاء حساب</a>
        </div>
    </div>
</header>

<main>
    @if($landingPage->headline || $landingPage->subheadline)
        <section class="py-10 sm:py-12" style="background:linear-gradient(180deg,#f4f6ff 0%,#ffffff 100%)">
            <div class="container-lp text-center">
                @if($landingPage->headline)
                    <h1 class="text-3xl sm:text-4xl font-black text-mx-indigo leading-tight mb-3">{{ $landingPage->headline }}</h1>
                @endif
                @if($landingPage->subheadline)
                    <p class="text-slate-600 text-base sm:text-lg leading-8 max-w-2xl mx-auto">{{ $landingPage->subheadline }}</p>
                @endif
            </div>
        </section>
    @endif

    @foreach($sections as $section)
        @php $type = $section['type'] ?? ''; @endphp

        @if($type === 'hero')
            <section class="py-12 sm:py-16" style="background:radial-gradient(circle at 12% 80%,rgba(255,229,247,.55),transparent 28%),radial-gradient(circle at 88% 20%,rgba(40,53,147,.08),transparent 30%),#fff">
                <div class="container-lp text-center max-w-3xl">
                    @if(!empty($section['headline']))
                        <h2 class="text-3xl sm:text-[2.5rem] font-black text-mx-indigo leading-tight mb-4">{{ $section['headline'] }}</h2>
                    @endif
                    @if(!empty($section['text']))
                        <p class="text-slate-600 text-base sm:text-lg leading-8 mb-8 whitespace-pre-line">{{ $section['text'] }}</p>
                    @endif
                    @include('public.landing-pages._buttons', ['buttons' => $section['resolved_buttons'] ?? []])
                </div>
            </section>

        @elseif($type === 'text')
            <section class="py-12 bg-mx-soft">
                <div class="container-lp max-w-3xl">
                    @if(!empty($section['title']))
                        <h2 class="text-2xl sm:text-3xl font-black text-mx-indigo mb-4 text-center">{{ $section['title'] }}</h2>
                    @endif
                    @if(!empty($section['body']))
                        <div class="text-slate-700 leading-8 text-base sm:text-lg whitespace-pre-line bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-sm">{{ $section['body'] }}</div>
                    @endif
                </div>
            </section>

        @elseif($type === 'video')
            <section class="py-12 sm:py-14 bg-white">
                <div class="container-lp">
                    @if(!empty($section['title']))
                        <h2 class="text-2xl sm:text-3xl font-black text-mx-indigo mb-2 text-center">{{ $section['title'] }}</h2>
                    @endif
                    @if(!empty($section['description']))
                        <p class="text-slate-600 text-center mb-6 max-w-2xl mx-auto">{{ $section['description'] }}</p>
                    @endif
                    @if(!empty($section['embed_url']))
                        <div class="video-wrap max-w-3xl mx-auto shadow-xl shadow-slate-900/10">
                            <iframe
                                src="{{ $section['embed_url'] }}"
                                title="{{ $section['title'] ?? 'فيديو شرح المنصة' }}"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    @else
                        <div class="max-w-3xl mx-auto rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
                            سيتم إضافة فيديو الشرح قريباً
                        </div>
                    @endif
                </div>
            </section>

        @elseif($type === 'features')
            <section class="py-12 sm:py-14 bg-mx-soft">
                <div class="container-lp">
                    @if(!empty($section['title']))
                        <h2 class="text-2xl sm:text-3xl font-black text-mx-indigo mb-8 text-center">{{ $section['title'] }}</h2>
                    @endif
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        @foreach(($section['items'] ?? []) as $feat)
                            <article class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
                                <div class="w-11 h-11 rounded-xl mb-3 flex items-center justify-center text-[#FB5607]" style="background:#fff3ec">
                                    <i class="fas {{ $feat['icon'] ?? 'fa-check' }}"></i>
                                </div>
                                <h3 class="font-bold text-mx-indigo mb-1">{{ $feat['title'] ?? '' }}</h3>
                                <p class="text-sm text-slate-600 leading-7">{{ $feat['description'] ?? '' }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

        @elseif($type === 'testimonials')
            <section class="py-12 sm:py-14 bg-white">
                <div class="container-lp">
                    @if(!empty($section['title']))
                        <h2 class="text-2xl sm:text-3xl font-black text-mx-indigo mb-8 text-center">{{ $section['title'] }}</h2>
                    @endif
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
                        @foreach(($section['items'] ?? []) as $t)
                            <blockquote class="rounded-2xl border border-slate-100 bg-mx-soft p-5">
                                <p class="text-slate-700 leading-7 mb-4">“{{ $t['quote'] ?? '' }}”</p>
                                <footer class="text-sm">
                                    <span class="font-bold text-mx-indigo">{{ $t['name'] ?? '' }}</span>
                                    @if(!empty($t['role']))
                                        <span class="text-slate-500"> — {{ $t['role'] }}</span>
                                    @endif
                                </footer>
                            </blockquote>
                        @endforeach
                    </div>
                </div>
            </section>

        @elseif($type === 'cta')
            <section class="py-14 sm:py-16" style="background:linear-gradient(135deg,#283593 0%,#1F2A7A 100%)">
                <div class="container-lp text-center text-white max-w-2xl">
                    @if(!empty($section['title']))
                        <h2 class="text-2xl sm:text-3xl font-black mb-3">{{ $section['title'] }}</h2>
                    @endif
                    @if(!empty($section['text']))
                        <p class="text-white/80 leading-8 mb-8">{{ $section['text'] }}</p>
                    @endif
                    @include('public.landing-pages._buttons', ['buttons' => $section['resolved_buttons'] ?? [], 'onDark' => true])
                </div>
            </section>
        @endif
    @endforeach
</main>

<footer class="py-8 border-t border-slate-100 bg-white">
    <div class="container-lp flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
        <p>© {{ date('Y') }} Muallimx — منصة أدوات المعلمين</p>
        <div class="flex items-center gap-4">
            <a href="{{ route('public.pricing') }}" class="hover:text-mx-indigo font-semibold">الباقات</a>
            <a href="{{ route('public.contact') }}" class="hover:text-mx-indigo font-semibold">تواصل</a>
            <a href="{{ route('home') }}" class="hover:text-mx-indigo font-semibold">الرئيسية</a>
        </div>
    </div>
</footer>
</body>
</html>

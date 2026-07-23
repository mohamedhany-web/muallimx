<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $metaTitle = $landingPage->meta_title ?: ($landingPage->headline ?: $landingPage->title);
    $metaDesc = $landingPage->meta_description ?: ($landingPage->subheadline ?: 'اكتشف منصة Muallimx للمعلمين');
    $ogImage = $landingPage->ogImageUrl() ?: asset('images/og-image.jpg');
    $pageUrl = $landingPage->publicUrl();
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5">
    <title><?php echo e($metaTitle); ?> | Muallimx</title>
    <meta name="description" content="<?php echo e($metaDesc); ?>">
    <meta name="robots" content="index, follow">
    <link rel="canonical" href="<?php echo e($pageUrl); ?>">
    <meta property="og:type" content="website">
    <meta property="og:url" content="<?php echo e($pageUrl); ?>">
    <meta property="og:title" content="<?php echo e($metaTitle); ?>">
    <meta property="og:description" content="<?php echo e($metaDesc); ?>">
    <meta property="og:image" content="<?php echo e($ogImage); ?>">
    <meta property="og:site_name" content="Muallimx">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?php echo e($metaTitle); ?>">
    <meta name="twitter:description" content="<?php echo e($metaDesc); ?>">
    <meta name="twitter:image" content="<?php echo e($ogImage); ?>">
    <meta name="theme-color" content="#283593">
    <?php echo $__env->make('partials.favicon-links', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

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
        <a href="<?php echo e(route('home')); ?>" class="flex items-center gap-2 text-white font-black text-lg">
            <span class="w-9 h-9 rounded-full flex items-center justify-center text-sm" style="background:#FB5607">M</span>
            Muallimx
        </a>
        <div class="flex items-center gap-2">
            <a href="<?php echo e(route('login')); ?>" class="hidden sm:inline text-white/80 hover:text-white text-sm font-bold px-3 py-2">دخول</a>
            <a href="<?php echo e(route('register')); ?>" class="text-sm font-bold text-white px-4 py-2 rounded-xl" style="background:#FB5607">إنشاء حساب</a>
        </div>
    </div>
</header>

<main>
    <?php if($landingPage->headline || $landingPage->subheadline): ?>
        <section class="py-10 sm:py-12" style="background:linear-gradient(180deg,#f4f6ff 0%,#ffffff 100%)">
            <div class="container-lp text-center">
                <?php if($landingPage->headline): ?>
                    <h1 class="text-3xl sm:text-4xl font-black text-mx-indigo leading-tight mb-3"><?php echo e($landingPage->headline); ?></h1>
                <?php endif; ?>
                <?php if($landingPage->subheadline): ?>
                    <p class="text-slate-600 text-base sm:text-lg leading-8 max-w-2xl mx-auto"><?php echo e($landingPage->subheadline); ?></p>
                <?php endif; ?>
            </div>
        </section>
    <?php endif; ?>

    <?php $__currentLoopData = $sections; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $section): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
        <?php $type = $section['type'] ?? ''; ?>

        <?php if($type === 'hero'): ?>
            <section class="py-12 sm:py-16" style="background:radial-gradient(circle at 12% 80%,rgba(255,229,247,.55),transparent 28%),radial-gradient(circle at 88% 20%,rgba(40,53,147,.08),transparent 30%),#fff">
                <div class="container-lp text-center max-w-3xl">
                    <?php if(!empty($section['headline'])): ?>
                        <h2 class="text-3xl sm:text-[2.5rem] font-black text-mx-indigo leading-tight mb-4"><?php echo e($section['headline']); ?></h2>
                    <?php endif; ?>
                    <?php if(!empty($section['text'])): ?>
                        <p class="text-slate-600 text-base sm:text-lg leading-8 mb-8 whitespace-pre-line"><?php echo e($section['text']); ?></p>
                    <?php endif; ?>
                    <?php echo $__env->make('public.landing-pages._buttons', ['buttons' => $section['resolved_buttons'] ?? []], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </section>

        <?php elseif($type === 'text'): ?>
            <section class="py-12 bg-mx-soft">
                <div class="container-lp max-w-3xl">
                    <?php if(!empty($section['title'])): ?>
                        <h2 class="text-2xl sm:text-3xl font-black text-mx-indigo mb-4 text-center"><?php echo e($section['title']); ?></h2>
                    <?php endif; ?>
                    <?php if(!empty($section['body'])): ?>
                        <div class="text-slate-700 leading-8 text-base sm:text-lg whitespace-pre-line bg-white rounded-2xl border border-slate-100 p-6 sm:p-8 shadow-sm"><?php echo e($section['body']); ?></div>
                    <?php endif; ?>
                </div>
            </section>

        <?php elseif($type === 'video'): ?>
            <section class="py-12 sm:py-14 bg-white">
                <div class="container-lp">
                    <?php if(!empty($section['title'])): ?>
                        <h2 class="text-2xl sm:text-3xl font-black text-mx-indigo mb-2 text-center"><?php echo e($section['title']); ?></h2>
                    <?php endif; ?>
                    <?php if(!empty($section['description'])): ?>
                        <p class="text-slate-600 text-center mb-6 max-w-2xl mx-auto"><?php echo e($section['description']); ?></p>
                    <?php endif; ?>
                    <?php if(!empty($section['embed_url'])): ?>
                        <div class="video-wrap max-w-3xl mx-auto shadow-xl shadow-slate-900/10">
                            <iframe
                                src="<?php echo e($section['embed_url']); ?>"
                                title="<?php echo e($section['title'] ?? 'فيديو شرح المنصة'); ?>"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                allowfullscreen
                                loading="lazy"
                                referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    <?php else: ?>
                        <div class="max-w-3xl mx-auto rounded-2xl border border-dashed border-slate-300 bg-slate-50 p-10 text-center text-slate-500">
                            سيتم إضافة فيديو الشرح قريباً
                        </div>
                    <?php endif; ?>
                </div>
            </section>

        <?php elseif($type === 'features'): ?>
            <section class="py-12 sm:py-14 bg-mx-soft">
                <div class="container-lp">
                    <?php if(!empty($section['title'])): ?>
                        <h2 class="text-2xl sm:text-3xl font-black text-mx-indigo mb-8 text-center"><?php echo e($section['title']); ?></h2>
                    <?php endif; ?>
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <?php $__currentLoopData = ($section['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $feat): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <article class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm">
                                <div class="w-11 h-11 rounded-xl mb-3 flex items-center justify-center text-[#FB5607]" style="background:#fff3ec">
                                    <i class="fas <?php echo e($feat['icon'] ?? 'fa-check'); ?>"></i>
                                </div>
                                <h3 class="font-bold text-mx-indigo mb-1"><?php echo e($feat['title'] ?? ''); ?></h3>
                                <p class="text-sm text-slate-600 leading-7"><?php echo e($feat['description'] ?? ''); ?></p>
                            </article>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>

        <?php elseif($type === 'testimonials'): ?>
            <section class="py-12 sm:py-14 bg-white">
                <div class="container-lp">
                    <?php if(!empty($section['title'])): ?>
                        <h2 class="text-2xl sm:text-3xl font-black text-mx-indigo mb-8 text-center"><?php echo e($section['title']); ?></h2>
                    <?php endif; ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 max-w-4xl mx-auto">
                        <?php $__currentLoopData = ($section['items'] ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $t): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <blockquote class="rounded-2xl border border-slate-100 bg-mx-soft p-5">
                                <p class="text-slate-700 leading-7 mb-4">“<?php echo e($t['quote'] ?? ''); ?>”</p>
                                <footer class="text-sm">
                                    <span class="font-bold text-mx-indigo"><?php echo e($t['name'] ?? ''); ?></span>
                                    <?php if(!empty($t['role'])): ?>
                                        <span class="text-slate-500"> — <?php echo e($t['role']); ?></span>
                                    <?php endif; ?>
                                </footer>
                            </blockquote>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </div>
                </div>
            </section>

        <?php elseif($type === 'cta'): ?>
            <section class="py-14 sm:py-16" style="background:linear-gradient(135deg,#283593 0%,#1F2A7A 100%)">
                <div class="container-lp text-center text-white max-w-2xl">
                    <?php if(!empty($section['title'])): ?>
                        <h2 class="text-2xl sm:text-3xl font-black mb-3"><?php echo e($section['title']); ?></h2>
                    <?php endif; ?>
                    <?php if(!empty($section['text'])): ?>
                        <p class="text-white/80 leading-8 mb-8"><?php echo e($section['text']); ?></p>
                    <?php endif; ?>
                    <?php echo $__env->make('public.landing-pages._buttons', ['buttons' => $section['resolved_buttons'] ?? [], 'onDark' => true], array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
                </div>
            </section>
        <?php endif; ?>
    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
</main>

<footer class="py-8 border-t border-slate-100 bg-white">
    <div class="container-lp flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
        <p>© <?php echo e(date('Y')); ?> Muallimx — منصة أدوات المعلمين</p>
        <div class="flex items-center gap-4">
            <a href="<?php echo e(route('public.pricing')); ?>" class="hover:text-mx-indigo font-semibold">الباقات</a>
            <a href="<?php echo e(route('public.contact')); ?>" class="hover:text-mx-indigo font-semibold">تواصل</a>
            <a href="<?php echo e(route('home')); ?>" class="hover:text-mx-indigo font-semibold">الرئيسية</a>
        </div>
    </div>
</footer>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\public\landing-pages\show.blade.php ENDPATH**/ ?>
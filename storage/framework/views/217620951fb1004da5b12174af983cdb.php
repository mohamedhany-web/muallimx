<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $itemTitle = isset($course) ? ($course->title ?? 'الكورس') : (isset($learningPath) ? ($learningPath->name ?? 'الطلب') : 'الطلب');
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <title>إتمام الطلب - <?php echo e($itemTitle); ?> - <?php echo e(config('app.name')); ?></title>
    <meta name="theme-color" content="#283593">

    <link rel="icon" href="<?php echo e(asset('favicon.ico')); ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: { 50:'#f0f4ff',100:'#dbe4ff',200:'#bac8ff',300:'#91a7ff',400:'#748ffc',500:'#5c7cfa',600:'#4c6ef5',700:'#4263eb',800:'#3b5bdb',900:'#364fc7',950:'#0F172A' },
                    brand: { 50:'#FFF3E0',100:'#FFE0B2',200:'#FFCC80',300:'#FFB74D',400:'#FFA726',500:'#FB5607',600:'#E04D00',700:'#BF360C',800:'#8D2600',900:'#5D1A00' },
                    mx: { navy:'#283593', indigo:'#1F2A7A', orange:'#FB5607', rose:'#FFE5F7', gold:'#FFE569', soft:'#F7F8FF' }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        [x-cloak]{display:none !important}
        *{font-family:'Cairo','IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif}
        h1,h2,h3,.font-heading{font-family:'Cairo','Tajawal','IBM Plex Sans Arabic',sans-serif}
        html{scroll-behavior:smooth}
        body{min-height:100vh;display:flex;flex-direction:column;background:#fff}
        body>*{flex-shrink:0}
        .container-1200{max-width:1200px;margin-inline:auto;padding-inline:24px}
        @media (max-width:768px){.container-1200{padding-inline:16px}}
        .reveal{opacity:0;transform:translateY(30px);transition:opacity .6s ease,transform .6s ease}
        .reveal.revealed{opacity:1;transform:translateY(0)}
        .btn-primary{transition:all .3s ease}
        .btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 30px -8px rgba(251,86,7,.35)}
        .card-hover{transition:all .3s ease}
        .card-hover:hover{box-shadow:0 20px 40px -15px rgba(0,0,0,.08)}
        .line-clamp-2{display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
        #navbar,#navbar.nav-transparent,#navbar.nav-solid{
            background:rgba(31,42,122,.92)!important;
            backdrop-filter:blur(12px)!important;
            -webkit-backdrop-filter:blur(12px)!important;
            border-bottom:1px solid rgba(255,255,255,.08)!important;
        }
    </style>
</head>
<body class="bg-white text-slate-800 antialiased font-body" x-data="{ isSubmitting: false }">
    <?php echo $__env->make('components.unified-navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>.navbar-spacer{display:block}</style>

    <main class="flex-1 pt-20">
        
        <section class="pt-10 sm:pt-14 lg:pt-16 pb-10 sm:pb-12 overflow-hidden relative" style="background:radial-gradient(circle at 12% 80%,rgba(255,229,247,.65),transparent 28%),radial-gradient(circle at 88% 20%,rgba(40,53,147,.10),transparent 30%),linear-gradient(180deg,#f4f6ff 0%,#fbfbff 55%,#ffffff 100%)">
            <div class="absolute inset-0 pointer-events-none opacity-40" style="background-image:radial-gradient(circle at 1px 1px,rgba(40,53,147,.08) 1px,transparent 0);background-size:30px 30px"></div>
            <div class="container-1200 relative z-10">
                <nav class="text-sm text-slate-500 mb-6 flex items-center gap-2 flex-wrap">
                    <a href="<?php echo e(url('/')); ?>" class="hover:text-mx-indigo transition-colors">الرئيسية</a>
                    <span>/</span>
                    <a href="<?php echo e(route('public.courses')); ?>" class="hover:text-mx-indigo transition-colors">الكورسات</a>
                    <span>/</span>
                    <?php if(isset($course)): ?>
                        <a href="<?php echo e(route('public.course.show', $course->id)); ?>" class="hover:text-mx-indigo transition-colors"><?php echo e(Str::limit($course->title ?? 'الكورس', 30)); ?></a>
                    <?php endif; ?>
                    <span>/</span>
                    <span class="text-mx-indigo font-semibold">إتمام الطلب</span>
                </nav>
                <div class="text-center max-w-2xl mx-auto">
                    <span class="inline-flex items-center gap-2 rounded-full px-4 py-1.5 text-xs sm:text-sm font-bold mb-4 reveal" style="background:#FFE5F7;color:#283593;border:1px solid #f5c7e8">
                        <i class="fas fa-credit-card"></i> صفحة الدفع
                    </span>
                    <h1 class="font-heading text-3xl sm:text-4xl font-black text-mx-indigo mb-3 reveal">إتمام الطلب</h1>
                    <p class="text-slate-600 text-lg reveal">خطوة أخيرة للحصول على <?php echo e(isset($course) ? 'الكورس' : 'المسار التعليمي'); ?></p>
                </div>
            </div>
        </section>

        
        <section class="py-12 lg:py-16 bg-white">
            <div class="container-1200">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 lg:gap-10">
                    
                    <div class="lg:col-span-1 order-2 lg:order-1">
                        <div class="reveal card-hover sticky top-24 rounded-3xl bg-white border border-slate-100 p-6 shadow-lg">
                            <h3 class="font-heading text-xl font-black text-navy-950 mb-6 flex items-center gap-2">
                                <i class="fas fa-shopping-bag text-[#FB5607]"></i>
                                ملخص الطلب
                            </h3>
                            <div class="flex items-start gap-4 mb-6 pb-6 border-b border-slate-100">
                                <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-[#FB5607] to-[#283593] flex items-center justify-center flex-shrink-0 shadow-lg">
                                    <?php if(isset($course)): ?>
                                        <i class="fas fa-graduation-cap text-white text-xl"></i>
                                    <?php else: ?>
                                        <i class="fas fa-route text-white text-xl"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h4 class="font-bold text-navy-950 text-base line-clamp-2">
                                        <?php if(isset($course)): ?><?php echo e($course->title); ?><?php elseif(isset($learningPath)): ?><?php echo e($learningPath->name); ?><?php else: ?> الطلب <?php endif; ?>
                                    </h4>
                                    <p class="text-sm text-slate-500 mt-0.5">
                                        <?php if(isset($course)): ?><?php echo e($course->academicSubject->name ?? 'غير محدد'); ?><?php else: ?> مسار تعليمي شامل <?php endif; ?>
                                    </p>
                                </div>
                            </div>
                            <div class="space-y-3 mb-6">
                                <div class="flex justify-between items-center">
                                    <span class="text-slate-600">السعر</span>
                                    <span class="font-bold text-[#FB5607] text-lg">
                                        <?php echo e(number_format(isset($course) ? $course->price : (isset($learningPath) ? ($learningPath->price ?? 0) : 0), 0)); ?>

                                        <span class="text-slate-500 text-sm font-medium">ج.م</span>
                                    </span>
                                </div>
                                <div class="flex justify-between items-center pt-4 border-t-2 border-slate-100">
                                    <span class="font-bold text-navy-950">الإجمالي</span>
                                    <span class="text-2xl font-black text-[#FB5607]">
                                        <?php echo e(number_format(isset($course) ? $course->price : (isset($learningPath) ? ($learningPath->price ?? 0) : 0), 0)); ?>

                                        <span class="text-slate-500 text-base font-medium">ج.م</span>
                                    </span>
                                </div>
                            </div>
                            <ul class="space-y-2 text-sm text-slate-600">
                                <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> وصول مدى الحياة</li>
                                <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> شهادة إتمام</li>
                                <li class="flex items-center gap-2"><i class="fas fa-check-circle text-emerald-500"></i> دعم فني</li>
                            </ul>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-2 order-1 lg:order-2">
                        <div class="reveal card-hover rounded-3xl bg-white border border-slate-100 p-6 sm:p-8 shadow-lg">
                            <h2 class="font-heading text-2xl font-black text-navy-950 mb-6 flex items-center gap-3">
                                <i class="fas fa-credit-card text-[#FB5607]"></i>
                                طرق الدفع المتاحة
                            </h2>

                            <?php if(session('error')): ?>
                                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                                    <p class="text-red-700 text-sm flex items-center gap-2"><i class="fas fa-exclamation-circle"></i> <?php echo e(session('error')); ?></p>
                                </div>
                            <?php endif; ?>
                            <?php if($errors->any()): ?>
                                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl">
                                    <ul class="list-disc list-inside space-y-1 text-red-700 text-sm">
                                        <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $error): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($error); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                            <?php if(session('success')): ?>
                                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl">
                                    <p class="text-emerald-700 text-sm flex items-center gap-2"><i class="fas fa-check-circle"></i> <?php echo e(session('success')); ?></p>
                                </div>
                            <?php endif; ?>

                            <div class="mb-6 p-5 bg-amber-50 rounded-2xl border border-amber-200">
                                <p class="text-sm font-bold text-amber-900 mb-2 flex items-center gap-2">
                                    <i class="fas fa-circle-info"></i>
                                    الدفع اليدوي فقط
                                </p>
                                <p class="text-sm text-amber-800">
                                    تم إيقاف بوابة الدفع أونلاين حالياً. ارفع إيصال التحويل وسيظهر الطلب في صفحة الطلبات حتى تتم مراجعته والموافقة عليه.
                                </p>
                            </div>

                            <form action="<?php echo e(isset($course) ? route('public.course.checkout.complete', $course->id) : (isset($learningPath) ? route('public.learning-path.checkout.complete', Str::slug($learningPath->name)) : '#')); ?>" method="POST" enctype="multipart/form-data" @submit="isSubmitting = true" x-data="{paymentMethod:'bank_transfer'}">
                                <?php echo csrf_field(); ?>
                                <div class="space-y-4 mb-6">
                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">طريقة الدفع</label>
                                        <select name="payment_method" x-model="paymentMethod" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-[#283593] focus:border-[#283593]" required>
                                            <option value="bank_transfer">تحويل بنكي / محفظة</option>
                                            <option value="cash">دفع نقدي</option>
                                            <option value="other">طريقة أخرى</option>
                                        </select>
                                    </div>

                                    <div x-show="paymentMethod === 'bank_transfer'" x-cloak>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">اختر حساب التحويل</label>
                                        <select name="wallet_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-[#283593] focus:border-[#283593]"
                                                :required="paymentMethod === 'bank_transfer'">
                                            <option value="">اختر الحساب</option>
                                            <?php $__currentLoopData = ($wallets ?? []); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $wallet): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                <option value="<?php echo e($wallet->id); ?>">
                                                    <?php echo e($wallet->name ?? 'حساب منصة'); ?> — <?php echo e($wallet->account_number ?? $wallet->phone ?? 'بدون رقم'); ?>

                                                </option>
                                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </select>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">إيصال الدفع</label>
                                        <input type="file" name="payment_proof" accept="image/*" required
                                               class="w-full rounded-xl border border-slate-300 px-4 py-3 file:mr-3 file:rounded-lg file:border-0 file:bg-[#FFE5F7] file:px-3 file:py-2 file:text-[#283593] hover:file:bg-[#f8dff1]">
                                        <p class="mt-1 text-xs text-slate-500">الصيغ المسموحة: JPG, PNG - الحد الأقصى 40 ميجابايت.</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-semibold text-gray-700 mb-2">ملاحظات (اختياري)</label>
                                        <textarea name="notes" rows="3" class="w-full rounded-xl border border-slate-300 px-4 py-3 focus:ring-2 focus:ring-[#283593] focus:border-[#283593]" placeholder="أي تفاصيل إضافية عن التحويل"></textarea>
                                    </div>
                                </div>

                                <div class="flex flex-col sm:flex-row gap-4">
                                    <button type="submit" :disabled="isSubmitting"
                                            class="btn-primary flex-1 inline-flex items-center justify-center gap-2 bg-gradient-to-l from-[#FB5607] to-[#e84d00] text-white px-6 py-4 rounded-2xl font-bold shadow-lg disabled:opacity-60 disabled:cursor-not-allowed disabled:transform-none">
                                        <i class="fas fa-file-upload" x-show="!isSubmitting"></i>
                                        <i class="fas fa-spinner fa-spin" x-show="isSubmitting" x-cloak></i>
                                        <span x-text="isSubmitting ? 'جاري إرسال الطلب...' : 'إرسال الطلب ورفع الإيصال'"></span>
                                    </button>
                                    <a href="<?php echo e(route('orders.index')); ?>"
                                       :class="{ 'pointer-events-none opacity-50': isSubmitting }"
                                       class="inline-flex items-center justify-center gap-2 bg-white border-2 border-slate-200 text-navy-950 px-6 py-4 rounded-2xl font-bold hover:border-slate-300 hover:bg-slate-50 transition-all">
                                        <i class="fas fa-arrow-<?php echo e($isRtl ? 'right' : 'left'); ?>"></i>
                                        إلغاء
                                    </a>
                                </div>
                                <p class="mt-4 text-xs text-slate-500 text-center flex items-center justify-center gap-1.5">
                                    <i class="fas fa-shield-alt text-[#283593]"></i>
                                    يظهر الطلب في صفحة الطلبات ويتم التفعيل بعد الموافقة
                                </p>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    </main>

    <?php echo $__env->make('components.unified-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
    (function(){
        function initReveal(){var t=document.querySelectorAll('.reveal');if(!t.length)return;var o=new IntersectionObserver(function(e){e.forEach(function(en){if(en.isIntersecting){en.target.classList.add('revealed');o.unobserve(en.target);}});},{threshold:.1});t.forEach(function(el){o.observe(el);});}
        if(document.readyState==='loading')document.addEventListener('DOMContentLoaded',initReveal);else initReveal();
    })();
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\public\checkout.blade.php ENDPATH**/ ?>
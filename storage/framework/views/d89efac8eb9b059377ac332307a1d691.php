<?php
    $locale = app()->getLocale();
    $isRtl = $locale === 'ar';
    $wallets = $wallets ?? collect();
?>
<!DOCTYPE html>
<html lang="<?php echo e($locale); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>دفع اشتراك الباقة - <?php echo e($plan['label'] ?? 'الباقة'); ?> - <?php echo e(config('app.name')); ?></title>

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: { 950:'#020617' },
                    brand: { 50:'#ecfeff',100:'#cffafe',200:'#a5f3fc',300:'#67e8f9',400:'#22d3ee',500:'#06b6d4',600:'#0891b2',700:'#0e7490',800:'#155e75',900:'#164e63' }
                },
                fontFamily: {
                    heading: ['Tajawal','IBM Plex Sans Arabic','sans-serif'],
                    body: ['IBM Plex Sans Arabic','Tajawal','sans-serif'],
                }
            }
        }
    }
    </script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

    <style>
        *{font-family:'IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif}
        h1,h2,h3,h4,h5,h6,.font-heading{font-family:'Tajawal','IBM Plex Sans Arabic',sans-serif}
        html{scroll-behavior:smooth;overflow-x:hidden}
        body{background:#fff;overflow-x:hidden}
        .card-hover{transition:all .4s cubic-bezier(.16,1,.3,1)}
        .card-hover:hover{transform:translateY(-6px);box-shadow:0 20px 40px -18px rgba(15,23,42,.35)}
        .btn-primary{position:relative;overflow:hidden;transition:all .3s cubic-bezier(.16,1,.3,1)}
        .btn-primary::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.25),transparent);transition:left .5s}
        .btn-primary:hover::before{left:100%}
        .btn-primary:hover{transform:translateY(-1px)}
    </style>
</head>
<body class="bg-white text-slate-900 antialiased">
    <?php echo $__env->make('components.unified-navbar', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
    <style>.navbar-spacer{display:none}</style>
    <script>(function(){var n=document.getElementById('navbar');if(n){n.classList.add('nav-transparent');n.classList.remove('nav-solid');}})();</script>

    <main class="flex-1">
        
        <section class="relative min-h-[50vh] flex items-center overflow-hidden bg-navy-950"
                 style="background: radial-gradient(circle at top, rgba(34,211,238,0.12), transparent 60%), linear-gradient(135deg,#020617 0%,#020617 40%,#0f172a 100%);">
            <div class="absolute inset-0 opacity-[0.03]"
                 style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.4) 1px,transparent 0);background-size:32px 32px"></div>
            <div class="absolute top-[-20%] <?php echo e($isRtl ? 'left-[-10%]' : 'right-[-10%]'); ?> w-[480px] h-[480px] rounded-full bg-cyan-500/15 blur-[110px]"></div>
            <div class="absolute bottom-[-15%] <?php echo e($isRtl ? 'right-[-10%]' : 'left-[-10%]'); ?> w-[520px] h-[520px] rounded-full bg-blue-600/10 blur-[120px]"></div>

            <div class="relative z-10 max-w-6xl mx-auto px-5 sm:px-8 lg:px-12 pt-28 pb-16 w-full text-center">
                <nav class="text-sm text-slate-400 mb-6 flex items-center justify-center gap-2 flex-wrap">
                    <a href="<?php echo e(url('/')); ?>" class="hover:text-white transition-colors">الرئيسية</a>
                    <span>/</span>
                    <a href="<?php echo e(route('public.pricing')); ?>" class="hover:text-white transition-colors">الأسعار والباقات</a>
                    <span>/</span>
                    <span class="text-white font-medium">دفع الاشتراك</span>
                </nav>
                <div class="inline-flex items-center gap-2.5 px-5 py-2 rounded-full bg-white/[0.06] border border-white/[0.12] text-brand-300 text-sm font-medium backdrop-blur-sm mb-6">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    تحويل مبلغ الاشتراك ثم رفع إيصال الدفع
                </div>
                <h1 class="font-heading text-3xl sm:text-4xl md:text-5xl font-black leading-tight text-white mb-4">
                    دفع اشتراك الباقة
                    <br>
                    <span class="bg-clip-text text-transparent"
                          style="background-image:linear-gradient(135deg,#22d3ee 0%,#3b82f6 40%,#8b5cf6 100%);">
                        <?php echo e($plan['label'] ?? 'باقة المعلم'); ?>

                    </span>
                </h1>
                <p class="text-lg text-slate-200/90 max-w-2xl mx-auto">
                    قم بتحويل <strong class="text-white"><?php echo e(number_format($plan['price'] ?? 0, 0)); ?> ج.م</strong> إلى أحد الحسابات أدناه، ثم ارفع صورة إيصال الدفع ليتم مراجعته وتفعيل اشتراكك.
                </p>
            </div>
        </section>

        
        <section class="py-16 md:py-20 bg-white">
            <div class="max-w-6xl mx-auto px-5 sm:px-8 lg:px-12">
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                    
                    <div class="lg:col-span-1">
                        <div class="bg-white rounded-3xl shadow-lg border border-slate-100 p-6 sticky top-24 card-hover">
                            <h3 class="font-heading text-xl font-black text-slate-900 mb-4 flex items-center gap-2">
                                <i class="fas fa-receipt text-sky-500"></i>
                                ملخص الدفع
                            </h3>
                            <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 mb-4">
                                <p class="text-sm font-semibold text-amber-800 mb-1">مبلغ الاشتراك المطلوب تحويله</p>
                                <p class="text-3xl font-black text-amber-900">
                                    <?php echo e(number_format($plan['price'] ?? 0, 0)); ?>

                                    <span class="text-lg font-bold text-amber-700">ج.م</span>
                                </p>
                            </div>
                            <p class="text-sm text-slate-600 mb-2"><strong><?php echo e($plan['label'] ?? 'الباقة'); ?></strong> · <?php echo e($billingLabel); ?></p>
                            <?php if(!empty($plan['features'])): ?>
                                <p class="text-xs font-semibold text-slate-500 mt-3 mb-2">ما ستحصل عليه:</p>
                                <ul class="space-y-1.5 text-xs text-slate-600">
                                    <?php $__currentLoopData = array_slice($plan['features'], 0, 5); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $f): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <li class="flex items-center gap-2"><i class="fas fa-check text-sky-500 text-[10px]"></i> <?php echo e(__("student.subscription_feature.{$f}") ?: $f); ?></li>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                    <?php if(count($plan['features']) > 5): ?>
                                        <li class="text-slate-500">+ <?php echo e(count($plan['features']) - 5); ?> ميزة أخرى</li>
                                    <?php endif; ?>
                                </ul>
                            <?php endif; ?>
                        </div>
                    </div>

                    
                    <div class="lg:col-span-2 space-y-6">
                        <?php if($wallets->count() > 0): ?>
                            <div class="bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden card-hover">
                                <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                                    <h3 class="font-heading text-lg font-black text-slate-900 flex items-center gap-2">
                                        <i class="fas fa-university text-sky-500"></i>
                                        قم بتحويل المبلغ إلى أحد الحسابات التالية
                                    </h3>
                                    <p class="text-xs text-slate-600 mt-1">اختر المحفظة أو الحساب الذي ستُجري التحويل إليه ثم ارفع إيصال الدفع أدناه.</p>
                                </div>
                                <div class="p-6 space-y-4">
                                    <?php $__currentLoopData = $wallets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                        <div class="rounded-xl border border-slate-200 bg-slate-50/50 p-4">
                                            <div class="flex items-center gap-3 mb-2">
                                                <span class="inline-flex items-center justify-center w-10 h-10 rounded-lg bg-sky-100 text-sky-600">
                                                    <i class="fas fa-<?php echo e($w->type === 'bank_transfer' ? 'university' : 'wallet'); ?>"></i>
                                                </span>
                                                <span class="font-bold text-slate-900"><?php echo e($w->name ?? \App\Models\Wallet::typeLabel($w->type)); ?></span>
                                            </div>
                                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2 text-sm text-slate-700">
                                                <?php if($w->account_number): ?>
                                                    <p><span class="text-slate-500">رقم الحساب:</span> <strong class="font-mono"><?php echo e($w->account_number); ?></strong></p>
                                                <?php endif; ?>
                                                <?php if($w->bank_name): ?>
                                                    <p><span class="text-slate-500">البنك:</span> <?php echo e($w->bank_name); ?></p>
                                                <?php endif; ?>
                                                <?php if($w->account_holder): ?>
                                                    <p><span class="text-slate-500">صاحب الحساب:</span> <?php echo e($w->account_holder); ?></p>
                                                <?php endif; ?>
                                            </div>
                                            <?php if($w->notes): ?>
                                                <p class="text-xs text-slate-500 mt-2 pt-2 border-t border-slate-200"><?php echo e($w->notes); ?></p>
                                            <?php endif; ?>
                                        </div>
                                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        
                        <div class="bg-white rounded-3xl shadow-lg border border-slate-100 overflow-hidden card-hover">
                            <div class="px-6 py-4 bg-slate-50 border-b border-slate-100">
                                <h3 class="font-heading text-lg font-black text-slate-900 flex items-center gap-2">
                                    <i class="fas fa-file-invoice text-sky-500"></i>
                                    بعد التحويل ارفع إيصال الدفع
                                </h3>
                                <p class="text-xs text-slate-600 mt-1">سيظهر طلبك في لوحة الإدارة لمراجعة الدفع وتفعيل الاشتراك.</p>
                            </div>
                            <div class="p-6">
                                <?php if(session('error')): ?>
                                    <div class="mb-4 p-4 bg-rose-50 border border-rose-200 rounded-xl text-rose-700 text-sm">
                                        <?php echo e(session('error')); ?>

                                    </div>
                                <?php endif; ?>
                                <?php if($errors->any()): ?>
                                    <div class="mb-4 p-4 bg-rose-50 border border-rose-200 rounded-xl">
                                        <ul class="list-disc list-inside text-rose-700 text-sm space-y-1">
                                            <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?><li><?php echo e($err); ?></li><?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                        </ul>
                                    </div>
                                <?php endif; ?>

                                <form action="<?php echo e(route('public.subscription.checkout.store')); ?>" method="POST" enctype="multipart/form-data" class="space-y-5">
                                    <?php echo csrf_field(); ?>
                                    <input type="hidden" name="plan" value="<?php echo e($planKey); ?>">

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">طريقة الدفع <span class="text-rose-500">*</span></label>
                                        <select name="payment_method" id="payment_method" required class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                            <option value="bank_transfer" <?php echo e(old('payment_method', $wallets->count() > 0 ? '' : 'bank_transfer') === 'bank_transfer' ? 'selected' : ''); ?>>تحويل بنكي</option>
                                            <?php if($wallets->count() > 0): ?>
                                            <option value="wallet" <?php echo e(old('payment_method') === 'wallet' ? 'selected' : ''); ?>>محفظة إلكترونية</option>
                                            <?php endif; ?>
                                        </select>
                                    </div>

                                    <?php if($wallets->count() > 0): ?>
                                        <div id="wallet_id_wrap" class="<?php echo e(old('payment_method') === 'wallet' ? '' : 'hidden'); ?>">
                                            <label class="block text-sm font-bold text-slate-700 mb-2">المحفظة / الحساب الذي تم التحويل إليه <span class="text-rose-500">*</span></label>
                                            <select name="wallet_id" id="wallet_id" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:ring-2 focus:ring-sky-500 focus:border-sky-500">
                                                <option value="">اختر المحفظة أو الحساب</option>
                                                <?php $__currentLoopData = $wallets; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $w): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                                    <option value="<?php echo e($w->id); ?>" <?php echo e((string)old('wallet_id') === (string)$w->id ? 'selected' : ''); ?>>
                                                        <?php echo e($w->name ?? \App\Models\Wallet::typeLabel($w->type)); ?>

                                                        <?php if($w->account_number): ?> — <?php echo e($w->account_number); ?> <?php endif; ?>
                                                    </option>
                                                <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                                            </select>
                                        </div>
                                    <?php endif; ?>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">صورة إيصال الدفع <span class="text-rose-500">*</span></label>
                                        <input type="file" name="payment_proof" accept="image/jpeg,image/png,image/jpg" required
                                               class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:ring-2 focus:ring-sky-500 focus:border-sky-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-sky-50 file:text-sky-700 file:font-semibold">
                                        <p class="text-xs text-slate-500 mt-1">صيغ مقبولة: jpeg, png, jpg — حجم أقصى 2 ميجابايت</p>
                                    </div>

                                    <div>
                                        <label class="block text-sm font-bold text-slate-700 mb-2">ملاحظات (اختياري)</label>
                                        <textarea name="notes" rows="2" class="w-full rounded-xl border border-slate-300 px-4 py-3 text-slate-900 focus:ring-2 focus:ring-sky-500 focus:border-sky-500" placeholder="أي ملاحظات إضافية..."><?php echo e(old('notes')); ?></textarea>
                                    </div>

                                    <button type="submit" class="btn-primary w-full inline-flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-sky-600 hover:bg-sky-700 text-white font-bold text-base shadow-lg">
                                        <i class="fas fa-paper-plane"></i>
                                        إرسال إيصال الدفع
                                    </button>
                                </form>
                                <p class="text-xs text-slate-500 mt-4 text-center">
                                    بعد الإرسال سيظهر طلبك في لوحة الإدارة. عند التحقق من الدفع سيتم تفعيل اشتراكك وتظهر أقسام الباقة في لوحتك.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <a href="<?php echo e(route('public.pricing')); ?>" class="mt-8 inline-flex items-center gap-2 text-sky-600 hover:text-sky-700 font-semibold transition-colors">
                    <i class="fas fa-arrow-<?php echo e($isRtl ? 'right' : 'left'); ?>"></i>
                    العودة إلى الأسعار والباقات
                </a>
            </div>
        </section>
    </main>

    <?php echo $__env->make('components.unified-footer', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>

    <script>
    (function(){
        var method = document.getElementById('payment_method');
        var wrap = document.getElementById('wallet_id_wrap');
        var walletSelect = document.getElementById('wallet_id');
        if (method && wrap) {
            function toggle() {
                wrap.classList.toggle('hidden', method.value !== 'wallet');
                if (walletSelect) walletSelect.required = (method.value === 'wallet');
            }
            method.addEventListener('change', toggle);
            toggle();
        }
    })();
    </script>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/public/subscription-checkout.blade.php ENDPATH**/ ?>
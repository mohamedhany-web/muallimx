<?php $isRtl = app()->getLocale() === 'ar'; ?>
<!DOCTYPE html>
<html lang="<?php echo e(app()->getLocale()); ?>" dir="<?php echo e($isRtl ? 'rtl' : 'ltr'); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=5, user-scalable=yes">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>تسجيل الدخول — MuallimX</title>
    <link rel="icon" type="image/x-icon" href="<?php echo e(asset('favicon.ico')); ?>">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&family=Tajawal:wght@400;500;700;800;900&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    navy: { 950:'#0F172A' },
                    brand: { 400:'#22d3ee', 500:'#06b6d4', 600:'#0891b2' }
                }
            }
        }
    }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript><link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"></noscript>

    <style>
        *{font-family:'IBM Plex Sans Arabic','Tajawal',system-ui,sans-serif;margin:0;padding:0;box-sizing:border-box}
        h1,h2,h3,h4,.font-heading{font-family:'Tajawal','IBM Plex Sans Arabic',sans-serif}
        html,body{height:100%;overflow:hidden}
        @media(max-width:1023px){html,body{overflow:auto;height:auto}}

        @keyframes float-slow{0%,100%{transform:translateY(0) rotate(0deg)}50%{transform:translateY(-18px) rotate(2deg)}}
        @keyframes float-delayed{0%,100%{transform:translateY(0)}50%{transform:translateY(-12px) rotate(-1.5deg)}}
        .float-slow{animation:float-slow 8s ease-in-out infinite}
        .float-delayed{animation:float-delayed 10s ease-in-out infinite 2s}

        .text-gradient{background:linear-gradient(135deg,#06b6d4 0%,#3b82f6 50%,#8b5cf6 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

        .input-field{background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:14px;padding:14px 16px;font-size:15px;font-weight:500;color:#0f172a;transition:all .25s ease;width:100%}
        .input-field:hover{border-color:#cbd5e1;background:#f1f5f9}
        .input-field:focus{outline:none;border-color:#06b6d4;box-shadow:0 0 0 3px rgba(6,182,212,.12);background:#fff}
        .input-field::placeholder{color:#94a3b8}
        .input-field.has-error{border-color:#ef4444}
        .input-field.has-error:focus{box-shadow:0 0 0 3px rgba(239,68,68,.12)}

        .btn-login{position:relative;overflow:hidden;background:linear-gradient(135deg,#06b6d4,#0891b2);color:#fff;border:none;border-radius:14px;padding:15px;font-size:16px;font-weight:700;cursor:pointer;transition:all .3s ease;width:100%}
        .btn-login:hover{transform:translateY(-1px);box-shadow:0 12px 32px -8px rgba(6,182,212,.4)}
        .btn-login::before{content:'';position:absolute;top:0;left:-100%;width:100%;height:100%;background:linear-gradient(90deg,transparent,rgba(255,255,255,.15),transparent);transition:left .5s}
        .btn-login:hover::before{left:100%}
    </style>
</head>
<body class="bg-white" x-data="{ showPassword: false }">
    <div class="flex min-h-screen lg:h-screen">

        
        <div class="hidden lg:flex lg:w-[55%] relative items-center justify-center overflow-hidden bg-navy-950">
            <div class="absolute inset-0 bg-gradient-to-br from-navy-950 via-[#0c1833] to-navy-950"></div>
            <div class="absolute top-[-15%] <?php echo e($isRtl?'left-[-8%]':'right-[-8%]'); ?> w-[500px] h-[500px] rounded-full bg-brand-500/10 blur-[100px] float-slow"></div>
            <div class="absolute bottom-[-10%] <?php echo e($isRtl?'right-[-5%]':'left-[-5%]'); ?> w-[400px] h-[400px] rounded-full bg-blue-600/8 blur-[80px] float-delayed"></div>
            <div class="absolute inset-0 opacity-[0.03]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.3) 1px,transparent 0);background-size:40px 40px"></div>

            <div class="relative z-10 max-w-md px-10 text-center">
                <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center gap-3 mb-10 group">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg shadow-cyan-500/25 group-hover:shadow-cyan-500/40 transition-shadow">
                        <span class="text-white font-black text-xl">M</span>
                    </div>
                    <span class="text-white font-extrabold text-2xl" style="font-family:Tajawal,sans-serif">MuallimX</span>
                </a>

                <h1 class="font-heading text-3xl xl:text-4xl font-black text-white leading-tight mb-5">
                    مرحبًا بعودتك
                    <br><span class="text-gradient">لعالم التعليم الأونلاين</span>
                </h1>
                <p class="text-slate-400 text-base leading-relaxed mb-10">
                    سجّل دخولك واستمر في تطوير مهاراتك، الوصول لأدوات AI، واكتشاف فرص عمل جديدة.
                </p>

                <div class="space-y-4">
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/[0.04] border border-white/[0.06] backdrop-blur-sm">
                        <span class="w-10 h-10 rounded-xl bg-brand-500/15 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-wand-magic-sparkles text-brand-400"></i>
                        </span>
                        <div class="text-<?php echo e($isRtl?'right':'left'); ?>">
                            <p class="text-white font-bold text-sm">مساعد AI للتحضير</p>
                            <p class="text-slate-500 text-xs">وفّر +5 ساعات أسبوعياً</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/[0.04] border border-white/[0.06] backdrop-blur-sm">
                        <span class="w-10 h-10 rounded-xl bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-briefcase text-emerald-400"></i>
                        </span>
                        <div class="text-<?php echo e($isRtl?'right':'left'); ?>">
                            <p class="text-white font-bold text-sm">فرص عمل حقيقية</p>
                            <p class="text-slate-500 text-xs">+500 معلم وجد عمل من خلالنا</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-4 p-4 rounded-2xl bg-white/[0.04] border border-white/[0.06] backdrop-blur-sm">
                        <span class="w-10 h-10 rounded-xl bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-graduation-cap text-purple-400"></i>
                        </span>
                        <div class="text-<?php echo e($isRtl?'right':'left'); ?>">
                            <p class="text-white font-bold text-sm">120+ برنامج تدريبي</p>
                            <p class="text-slate-500 text-xs">دبلومات وكورسات عملية</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        
        <div class="flex-1 flex flex-col items-center justify-center px-5 sm:px-8 py-10 lg:py-0 bg-white relative overflow-y-auto">

            
            <div class="lg:hidden w-full max-w-md mb-8">
                <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg">
                        <span class="text-white font-black text-lg">M</span>
                    </div>
                    <span class="text-navy-950 font-extrabold text-xl" style="font-family:Tajawal,sans-serif">MuallimX</span>
                </a>
            </div>

            <div class="w-full max-w-md">
                <div class="text-center lg:text-<?php echo e($isRtl?'right':'left'); ?> mb-8">
                    <h2 class="font-heading text-2xl sm:text-3xl font-black text-navy-950 mb-2">
                        تسجيل الدخول
                    </h2>
                    <p class="text-slate-500 text-sm sm:text-base">أدخل بياناتك للوصول لحسابك</p>
                </div>

                <form action="<?php echo e(route('login')); ?>" method="POST" class="space-y-5">
                    <?php echo csrf_field(); ?>

                    <?php if(session('status')): ?>
                    <div class="flex items-center gap-3 p-4 rounded-2xl bg-emerald-50 border border-emerald-200 text-emerald-700 text-sm font-medium">
                        <i class="fas fa-check-circle text-emerald-500"></i>
                        <?php echo e(session('status')); ?>

                    </div>
                    <?php endif; ?>

                    
                    <div style="display:none" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off">
                    </div>

                    
                    <div>
                        <label for="email" class="block text-sm font-bold text-navy-950 mb-2">البريد الإلكتروني</label>
                        <div class="relative">
                            <span class="absolute <?php echo e($isRtl?'right-4':'left-4'); ?> top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><i class="fas fa-envelope text-sm"></i></span>
                            <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" required autocomplete="email" autofocus
                                   class="input-field <?php echo e($isRtl?'pr-11':'pl-11'); ?> <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   placeholder="example@email.com" dir="ltr">
                        </div>
                        <?php $__errorArgs = ['email'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-xs text-red-500 font-medium"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div>
                        <label for="password" class="block text-sm font-bold text-navy-950 mb-2">كلمة المرور</label>
                        <div class="relative">
                            <span class="absolute <?php echo e($isRtl?'right-4':'left-4'); ?> top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><i class="fas fa-lock text-sm"></i></span>
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" required
                                   class="input-field <?php echo e($isRtl?'pr-11 pl-11':'pl-11 pr-11'); ?> <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?> has-error <?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>"
                                   placeholder="••••••••">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute <?php echo e($isRtl?'left-4':'right-4'); ?> top-1/2 -translate-y-1/2 text-slate-400 hover:text-brand-500 transition-colors">
                                <i x-show="!showPassword" class="fas fa-eye text-sm"></i>
                                <i x-show="showPassword" class="fas fa-eye-slash text-sm"></i>
                            </button>
                        </div>
                        <?php $__errorArgs = ['password'];
$__bag = $errors->getBag($__errorArgs[1] ?? 'default');
if ($__bag->has($__errorArgs[0])) :
if (isset($message)) { $__messageOriginal = $message; }
$message = $__bag->first($__errorArgs[0]); ?><p class="mt-1.5 text-xs text-red-500 font-medium"><?php echo e($message); ?></p><?php unset($message);
if (isset($__messageOriginal)) { $message = $__messageOriginal; }
endif;
unset($__errorArgs, $__bag); ?>
                    </div>

                    
                    <div class="flex items-center justify-between">
                        <label class="flex items-center gap-2 cursor-pointer group">
                            <input type="checkbox" name="remember" class="w-4 h-4 rounded-md border-slate-300 text-brand-500 focus:ring-brand-500/20 transition-colors">
                            <span class="text-sm text-slate-500 group-hover:text-slate-700 transition-colors">تذكّرني</span>
                        </label>
                        <a href="<?php echo e(route('password.request')); ?>" class="text-sm font-semibold text-brand-500 hover:text-brand-600 transition-colors">
                            نسيت كلمة المرور؟
                        </a>
                    </div>

                    
                    <button type="submit" class="btn-login flex items-center justify-center gap-2">
                        <span>تسجيل الدخول</span>
                        <i class="fas fa-arrow-<?php echo e($isRtl?'left':'right'); ?> text-sm"></i>
                    </button>
                </form>

                
                <div class="mt-8 pt-6 border-t border-slate-100 text-center">
                    <p class="text-sm text-slate-500">
                        ليس لديك حساب؟
                        <a href="<?php echo e(route('register')); ?>" class="font-bold text-brand-500 hover:text-brand-600 transition-colors">أنشئ حسابك مجاناً</a>
                    </p>
                </div>

                
                <div class="mt-6 text-center">
                    <a href="<?php echo e(route('home')); ?>" class="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-slate-600 transition-colors">
                        <i class="fas fa-arrow-<?php echo e($isRtl?'right':'left'); ?> text-xs"></i>
                        العودة للرئيسية
                    </a>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\auth\login.blade.php ENDPATH**/ ?>
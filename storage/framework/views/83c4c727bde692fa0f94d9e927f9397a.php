<footer class="bg-[#0F172A] text-white relative overflow-hidden mt-auto" style="flex-shrink:0">
    <div class="absolute inset-0 opacity-[0.02]" style="background-image:radial-gradient(circle at 1px 1px,rgba(255,255,255,.4) 1px,transparent 0);background-size:32px 32px"></div>
    <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-[#06b6d4]/40 to-transparent"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-5 sm:px-8 lg:px-12 pt-14 pb-6">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8 lg:gap-12 mb-10">

            
            <div class="col-span-2 md:col-span-4 lg:col-span-2">
                <div class="flex items-center gap-3 mb-5">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-cyan-400 to-blue-600 flex items-center justify-center shadow-lg">
                        <span class="text-white font-black text-lg">M</span>
                    </div>
                    <div>
                        <p class="font-bold text-lg text-white" style="font-family:Tajawal,sans-serif">MuallimX</p>
                        <p class="text-[11px] text-white/40">تأهيل المعلّمين للعمل أونلاين</p>
                    </div>
                </div>
                <p class="text-sm text-slate-400 leading-relaxed mb-5 max-w-sm">
                    منصّة عربية متخصصة في تأهيل وتطوير المعلمين للعمل أونلاين باحتراف — تدريب تطبيقي، أدوات AI، مناهج جاهزة، وبروفايل يفتح فرص عمل حقيقية.
                </p>
                
                <div class="flex gap-3"></div>
            </div>

            
            <div>
                <h4 class="font-bold text-white text-sm mb-4 flex items-center gap-2" style="font-family:Tajawal,sans-serif">
                    <span class="w-1 h-4 rounded-full bg-[#06b6d4]"></span> <?php echo e(__('public.quick_links')); ?>

                </h4>
                <ul class="space-y-2.5 text-sm">
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-[#06b6d4]/50"></i><?php echo e(__('public.home')); ?></span></li>
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-[#06b6d4]/50"></i><?php echo e(__('public.courses')); ?></span></li>
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-[#06b6d4]/50"></i>المدرّبون</span></li>
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-[#06b6d4]/50"></i><?php echo e(__('public.about')); ?></span></li>
                </ul>
            </div>

            
            <div>
                <h4 class="font-bold text-white text-sm mb-4 flex items-center gap-2" style="font-family:Tajawal,sans-serif">
                    <span class="w-1 h-4 rounded-full bg-emerald-500"></span> <?php echo e(__('public.support')); ?>

                </h4>
                <ul class="space-y-2.5 text-sm">
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i><?php echo e(__('public.contact_us')); ?></span></li>
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i><?php echo e(__('public.faq')); ?></span></li>
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i><?php echo e(__('public.help_center')); ?></span></li>
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i><?php echo e(__('public.terms_conditions')); ?></span></li>
                    <li><span class="text-slate-400 flex items-center gap-2 cursor-default pointer-events-none select-none" tabindex="-1"><i class="fas fa-chevron-left text-[8px] text-emerald-500/50"></i><?php echo e(__('public.privacy_policy')); ?></span></li>
                </ul>
            </div>

            
            <div class="col-span-2 md:col-span-2 lg:col-span-1">
                <h4 class="font-bold text-white text-sm mb-4 flex items-center gap-2" style="font-family:Tajawal,sans-serif">
                    <span class="w-1 h-4 rounded-full bg-purple-500"></span> تواصل معنا
                </h4>
                <div class="space-y-3 text-sm text-slate-500">
                    
                </div>
            </div>
        </div>

        
        <div class="border-t border-white/[0.06] pt-5 flex flex-col sm:flex-row items-center justify-between gap-3">
            <p class="text-xs text-slate-500">
                &copy; <?php echo e(date('Y')); ?> <span class="text-white font-semibold">MuallimX</span> — جميع الحقوق محفوظة
            </p>
            <div class="flex items-center gap-4 text-xs text-slate-500">
                <span class="cursor-default pointer-events-none select-none" tabindex="-1"><?php echo e(__('public.privacy_short')); ?></span>
                <span class="text-slate-700">&bull;</span>
                <span class="cursor-default pointer-events-none select-none" tabindex="-1"><?php echo e(__('public.terms_short')); ?></span>
            </div>
        </div>
    </div>
</footer>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\components\unified-footer.blade.php ENDPATH**/ ?>
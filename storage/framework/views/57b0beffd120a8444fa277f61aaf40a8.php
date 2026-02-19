

<?php $__env->startSection('title', __('auth.register')); ?>

<?php $__env->startSection('content'); ?>
<div class="w-full max-w-md">
    <div class="bg-slate-800/90 backdrop-blur border border-slate-700 rounded-2xl shadow-2xl p-6 sm:p-8">
        <h1 class="text-2xl font-black text-white mb-1"><?php echo e(__('auth.register')); ?></h1>
        <p class="text-slate-400 text-sm mb-6"><?php echo e(__('auth.register_subtitle')); ?></p>

        <?php if($errors->any()): ?>
            <div class="mb-4 p-3 rounded-xl bg-red-500/20 border border-red-500/50 text-red-200 text-sm space-y-1">
                <?php $__currentLoopData = $errors->all(); $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $err): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?> <p><?php echo e($err); ?></p> <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo e(route('community.register.post')); ?>" class="space-y-4">
            <?php echo csrf_field(); ?>
            <div>
                <label for="name" class="block text-sm font-semibold text-slate-300 mb-1"><?php echo e(__('auth.full_name')); ?></label>
                <input type="text" name="name" id="name" value="<?php echo e(old('name')); ?>" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30"
                       placeholder="<?php echo e(__('auth.enter_full_name')); ?>">
            </div>
            <div>
                <label class="block text-sm font-semibold text-slate-300 mb-1"><?php echo e(__('auth.phone_number')); ?></label>
                <div class="flex rounded-xl overflow-hidden border border-slate-600 bg-slate-700/50 focus-within:border-cyan-500 focus-within:ring-2 focus-within:ring-cyan-500/30">
                    <select name="country_code" required class="bg-slate-700/80 border-0 text-white py-3 px-3 text-sm min-w-[5rem]" dir="ltr">
                        <?php $__currentLoopData = $countries ?? []; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $c): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($c['dial_code'] ?? ''); ?>" <?php echo e(old('country_code', $defaultCountry['dial_code'] ?? '+20') === ($c['dial_code'] ?? '') ? 'selected' : ''); ?>><?php echo e($c['dial_code'] ?? ''); ?> <?php echo e($c['name_ar'] ?? $c['name'] ?? ''); ?></option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                    <input type="tel" name="phone" value="<?php echo e(old('phone')); ?>" required dir="ltr"
                           class="flex-1 min-w-0 py-3 px-4 bg-transparent border-0 text-white placeholder-slate-500 focus:ring-0">
                </div>
            </div>
            <div>
                <label for="email" class="block text-sm font-semibold text-slate-300 mb-1"><?php echo e(__('auth.email_optional')); ?></label>
                <input type="email" name="email" id="email" value="<?php echo e(old('email')); ?>" dir="ltr"
                       class="w-full px-4 py-3 rounded-xl bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30">
            </div>
            <div>
                <label for="password" class="block text-sm font-semibold text-slate-300 mb-1"><?php echo e(__('auth.password')); ?></label>
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30">
            </div>
            <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-slate-300 mb-1"><?php echo e(__('auth.password_confirmation')); ?></label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full px-4 py-3 rounded-xl bg-slate-700/50 border border-slate-600 text-white placeholder-slate-500 focus:border-cyan-500 focus:ring-2 focus:ring-cyan-500/30">
            </div>
            <div class="flex items-start gap-2">
                <input type="checkbox" name="terms" id="terms" required class="mt-1 rounded border-slate-600 bg-slate-700 text-cyan-500">
                <label for="terms" class="text-xs text-slate-400"><?php echo e(__('auth.agree_terms')); ?> <a href="<?php echo e(url('/terms')); ?>" class="text-cyan-400 hover:underline"><?php echo e(__('auth.terms_of_use')); ?></a> <?php echo e(__('auth.and')); ?> <a href="<?php echo e(url('/privacy')); ?>" class="text-cyan-400 hover:underline"><?php echo e(__('auth.privacy_policy')); ?></a></label>
            </div>
            <button type="submit" class="w-full py-3 rounded-xl bg-gradient-to-r from-cyan-500 to-blue-600 text-white font-bold hover:from-cyan-600 hover:to-blue-700 transition-all shadow-lg">
                <?php echo e(__('auth.create_account_btn')); ?>

            </button>
        </form>

        <p class="mt-6 text-center text-slate-400 text-sm">
            <?php echo e(__('auth.already_have_account')); ?>

            <a href="<?php echo e(route('community.login')); ?>" class="text-cyan-400 font-bold hover:text-cyan-300"><?php echo e(__('auth.go_to_login')); ?></a>
        </p>
        <p class="mt-2 text-center text-slate-500 text-xs">
            <a href="<?php echo e(route('register')); ?>" class="hover:text-slate-400">إنشاء حساب من المنصة الرئيسية</a>
        </p>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('community.layouts.guest', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mindly tics\Mindlytics\resources\views/community/auth/register.blade.php ENDPATH**/ ?>
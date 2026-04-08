
<?php
    $size = $size ?? 'md';
    $dim = match ($size) {
        'xs' => 'w-8 h-8 text-[10px]',
        'sm' => 'w-9 h-9 text-xs',
        'lg' => 'w-14 h-14 text-xl',
        default => 'w-12 h-12 text-lg',
    };
?>
<?php if(!empty($user->profile_image) && $user->profile_image_url): ?>
    <img src="<?php echo e($user->profile_image_url); ?>" alt="" class="<?php echo e($dim); ?> rounded-xl object-cover border border-slate-200 shadow-sm shrink-0 bg-white dark:bg-slate-800" loading="lazy" width="48" height="48">
<?php else: ?>
    <div class="avatar-gradient <?php echo e($dim); ?> rounded-xl flex items-center justify-center text-white font-bold shadow-md shrink-0">
        <?php echo e(mb_substr($user->name ?? '?', 0, 1, 'UTF-8')); ?>

    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/admin/partials/user-profile-thumb.blade.php ENDPATH**/ ?>
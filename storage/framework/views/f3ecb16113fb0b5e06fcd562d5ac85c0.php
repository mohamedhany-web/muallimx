<?php
    $buttons = $buttons ?? [];
    $onDark = $onDark ?? false;
?>
<?php if(count($buttons) > 0): ?>
    <div class="flex flex-col sm:flex-row flex-wrap gap-3 justify-center">
        <?php $__currentLoopData = $buttons; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $i => $btn): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
            <?php
                $action = $btn['action'] ?? 'custom';
                $class = match($action) {
                    'whatsapp' => 'btn-whatsapp',
                    'pricing' => $onDark ? 'btn-outline !border-white !text-white hover:!bg-white/10' : 'btn-secondary',
                    'register' => 'btn-primary',
                    default => $i === 0 ? 'btn-primary' : ($onDark ? 'btn-outline !border-white !text-white' : 'btn-secondary'),
                };
            ?>
            <a href="<?php echo e($btn['url']); ?>"
               class="<?php echo e($class); ?>"
               <?php if(in_array($action, ['whatsapp', 'custom'], true)): ?> target="_blank" rel="noopener noreferrer" <?php endif; ?>>
                <?php if($action === 'whatsapp'): ?>
                    <i class="fab fa-whatsapp"></i>
                <?php elseif($action === 'pricing'): ?>
                    <i class="fas fa-tags"></i>
                <?php elseif($action === 'register'): ?>
                    <i class="fas fa-user-plus"></i>
                <?php else: ?>
                    <i class="fas fa-external-link-alt"></i>
                <?php endif; ?>
                <?php echo e($btn['label']); ?>

            </a>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
    </div>
<?php endif; ?>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/public/landing-pages/_buttons.blade.php ENDPATH**/ ?>
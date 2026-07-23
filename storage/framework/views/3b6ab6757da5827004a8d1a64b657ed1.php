<?php
    $isLocked = (empty($hasFullAccess) || !$hasFullAccess)
        && !empty($usedFreePreview)
        && (int) ($previewVideoId ?? 0) !== (int) $item->id;
    $href = $isLocked
        ? route('student.features.show', ['feature' => 'video_library_access'])
        : route('video-library.show', $item);
?>
<a href="<?php echo e($href); ?>" class="group block <?php echo e($isLocked ? 'opacity-90' : ''); ?>">
    <div class="relative aspect-video rounded-xl overflow-hidden bg-slate-900 shadow-sm ring-1 ring-slate-200/80 dark:ring-slate-700">
        <img src="<?php echo e($item->displayThumbnail()); ?>" alt="<?php echo e($item->title); ?>"
             class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-[1.04]"
             loading="lazy"
             onerror="this.src='https://img.youtube.com/vi/<?php echo e($item->youtube_id); ?>/hqdefault.jpg'">
        <div class="absolute inset-0 bg-gradient-to-t from-black/55 via-transparent to-transparent opacity-80"></div>
        <span class="absolute inset-0 flex items-center justify-center opacity-0 group-hover:opacity-100 transition-opacity">
            <span class="w-14 h-14 rounded-full bg-rose-600/95 text-white flex items-center justify-center shadow-xl shadow-rose-900/40">
                <i class="fas <?php echo e($isLocked ? 'fa-lock' : 'fa-play'); ?> text-lg <?php echo e($isLocked ? '' : 'ms-0.5'); ?>"></i>
            </span>
        </span>
        <?php if($item->formattedDuration()): ?>
            <span class="absolute bottom-2 end-2 text-[11px] font-bold bg-black/80 text-white px-1.5 py-0.5 rounded"><?php echo e($item->formattedDuration()); ?></span>
        <?php endif; ?>
        <?php if($item->is_featured): ?>
            <span class="absolute top-2 start-2 text-[10px] font-black bg-amber-400 text-amber-950 px-2 py-0.5 rounded-md">مميز</span>
        <?php endif; ?>
    </div>
    <div class="mt-3 flex gap-3">
        <span class="w-9 h-9 rounded-full shrink-0 flex items-center justify-center text-white text-sm"
              style="background: <?php echo e($item->category->cover_color ?? '#c62828'); ?>">
            <i class="fas <?php echo e($item->category->icon ?? 'fa-play-circle'); ?> text-xs"></i>
        </span>
        <div class="min-w-0">
            <h3 class="font-bold text-slate-900 dark:text-slate-100 text-sm leading-snug line-clamp-2 group-hover:text-rose-600 transition-colors">
                <?php echo e($item->title); ?>

            </h3>
            <p class="text-xs text-slate-500 mt-1 truncate">
                <?php echo e($item->category->name ?? 'مكتبة الفيديو'); ?>

                · <?php echo e(number_format($item->views_count)); ?> مشاهدة
            </p>
            <?php if($item->description): ?>
                <p class="text-xs text-slate-500 mt-1.5 line-clamp-2 leading-relaxed"><?php echo e($item->description); ?></p>
            <?php endif; ?>
        </div>
    </div>
</a>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views\student\video-library\_video-card.blade.php ENDPATH**/ ?>
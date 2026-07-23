<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>فصل <?php echo e($teacher->name); ?> — Muallimx Classroom</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <style>*{font-family:'IBM Plex Sans Arabic',system-ui,sans-serif}</style>
</head>
<body class="bg-slate-950 text-white min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md rounded-2xl bg-slate-800/90 border border-slate-600 p-6 shadow-2xl shadow-black/30 text-center">
        <div class="w-16 h-16 rounded-2xl bg-cyan-500/20 text-cyan-400 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-video text-3xl"></i>
        </div>
        <h1 class="text-xl font-bold text-white mb-1">Muallimx Classroom</h1>
        <p class="text-slate-300 text-sm mb-4">فصل: <span class="font-bold"><?php echo e($teacher->name); ?></span></p>

        <?php if(! $serviceAvailable): ?>
            <div class="rounded-xl bg-amber-500/10 border border-amber-500/30 text-amber-200 text-sm p-4 leading-relaxed">
                خدمة Classroom غير متاحة حالياً لهذا المعلم (الباقة أو الميزة غير مفعّلة).
            </div>
        <?php elseif($activeMeeting): ?>
            <p class="text-emerald-300 text-sm font-semibold mb-2">الجلسة مباشرة الآن</p>
            <?php if($activeMeeting->title): ?>
                <p class="text-slate-400 text-sm mb-4"><?php echo e($activeMeeting->title); ?></p>
            <?php endif; ?>
            <a href="<?php echo e(url('classroom/join/'.$activeMeeting->code)); ?>"
               class="inline-flex w-full items-center justify-center gap-2 px-6 py-3 rounded-xl bg-rose-500 hover:bg-rose-400 text-white font-bold transition-colors">
                <i class="fas fa-video"></i>
                انضم للجلسة الآن
            </a>
            <p class="text-slate-500 text-xs mt-4">سيتم توجيهك لصفحة الانضمام.</p>
            <script>setTimeout(function(){ window.location.href = <?php echo json_encode(url('classroom/join/'.$activeMeeting->code), 15, 512) ?>; }, 800);</script>
        <?php else: ?>
            <div id="wait-box">
                <div class="rounded-xl bg-slate-700/50 border border-slate-600 text-slate-200 text-sm p-4 leading-relaxed mb-4">
                    <i class="fas fa-clock text-cyan-400 ml-1"></i>
                    المعلم لم يبدأ الجلسة بعد. ابقَ في هذه الصفحة — سننقلك تلقائياً عند بدء اللايف.
                </div>
                <p id="wait-status" class="text-slate-500 text-xs mb-3">جاري التحقق كل بضع ثوانٍ...</p>
                <div class="flex items-center justify-center gap-2 text-cyan-300 text-sm">
                    <i class="fas fa-spinner fa-spin"></i>
                    <span>في انتظار بدء المعلم</span>
                </div>
            </div>
        <?php endif; ?>

        <p class="text-slate-600 text-[11px] mt-6 break-all" dir="ltr"><?php echo e($fixedUrl); ?></p>
    </div>

    <?php if($serviceAvailable && ! $activeMeeting): ?>
    <script>
        const statusUrl = <?php echo json_encode($statusUrl, 15, 512) ?>;
        async function poll() {
            try {
                const res = await fetch(statusUrl, { headers: { 'Accept': 'application/json' } });
                const data = await res.json();
                if (data.live && data.join_url) {
                    document.getElementById('wait-status').textContent = 'بدأت الجلسة — جاري التحويل...';
                    window.location.href = data.join_url;
                    return;
                }
                if (data.service_available === false) {
                    document.getElementById('wait-status').textContent = data.message || 'الخدمة غير متاحة';
                }
            } catch (e) {}
            setTimeout(poll, 4000);
        }
        setTimeout(poll, 2500);
    </script>
    <?php endif; ?>
</body>
</html>
<?php /**PATH C:\xampp\htdocs\Muallimx\resources\views/classroom/join-fixed.blade.php ENDPATH**/ ?>
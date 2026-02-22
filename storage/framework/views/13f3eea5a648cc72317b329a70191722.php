

<?php $__env->startSection('title', ($dataset->title ?? 'مجموعة بيانات') . ' - مجتمع الذكاء الاصطناعي'); ?>

<?php $__env->startSection('content'); ?>
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12" style="padding-top: 6rem;">
    <div class="mb-6">
        <a href="<?php echo e(route('community.data.index')); ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-semibold mb-4">
            <i class="fas fa-arrow-right"></i>
            <span>العودة لمجموعات البيانات</span>
        </a>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2"><?php echo e($dataset->title); ?></h1>
        <div class="flex flex-wrap items-center gap-2 mt-2">
            <?php if($dataset->category): ?>
                <a href="<?php echo e(route('community.data.index', ['category' => $dataset->category])); ?>" class="inline-flex px-3 py-1 rounded-lg text-sm font-bold bg-slate-100 text-slate-600 hover:bg-slate-200"><?php echo e($dataset->category_label); ?></a>
            <?php endif; ?>
            <?php if($dataset->creator): ?>
                <span class="text-slate-500 text-sm"><?php echo e($dataset->creator->name); ?></span>
            <?php endif; ?>
            <?php if($dataset->file_size): ?>
                <span class="text-slate-500 text-sm">الحجم: <?php echo e($dataset->file_size); ?></span>
            <?php endif; ?>
        </div>
    </div>

    <?php if($dataset->description): ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm mb-6">
            <h2 class="text-lg font-black text-slate-900 mb-3 flex items-center gap-2">
                <i class="fas fa-align-right text-blue-600"></i>
                وصف مجموعة البيانات
            </h2>
            <div class="text-slate-600 leading-relaxed whitespace-pre-line"><?php echo e($dataset->description); ?></div>
        </div>
    <?php endif; ?>

    <?php if($dataset->file_url): ?>
        <div class="mb-6">
            <a href="<?php echo e($dataset->file_url); ?>" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-colors shadow-md">
                <i class="fas fa-external-link-alt"></i>
                <span>فتح رابط التحميل</span>
            </a>
        </div>
    <?php endif; ?>

    <?php $filesList = $dataset->files_list; ?>
    <?php if(!empty($filesList)): ?>
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
            
            <div class="lg:col-span-4 xl:col-span-3">
                <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden sticky top-24">
                    <div class="px-4 py-3 border-b border-slate-200 bg-slate-50">
                        <h2 class="text-base font-black text-slate-900 flex items-center gap-2">
                            <i class="fas fa-folder-open text-cyan-600"></i>
                            الملفات (<?php echo e(count($filesList)); ?>)
                        </h2>
                        <?php if(count($filesList) > 1): ?>
                            <a href="<?php echo e(route('community.data.download-all', $dataset)); ?>" class="mt-3 inline-flex items-center gap-2 w-full justify-center px-4 py-2.5 rounded-xl bg-amber-500 hover:bg-amber-600 text-white font-bold text-sm transition-colors">
                                <i class="fas fa-file-archive"></i>
                                تحميل الكل كـ ZIP
                            </a>
                        <?php endif; ?>
                    </div>
                    <ul class="divide-y divide-slate-100 max-h-[60vh] overflow-y-auto">
                        <?php $__currentLoopData = $filesList; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $idx => $file): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <?php
                                $name = $file['original_name'] ?? basename($file['path'] ?? '');
                                $size = $file['size'] ?? '';
                                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                                $isZip = ($ext === 'zip');
                            ?>
                            <li class="dataset-file-item border-b border-slate-100 last:border-0" data-index="<?php echo e($idx); ?>">
                                <div class="flex items-center gap-3 p-3 hover:bg-slate-50 cursor-pointer transition-colors border-r-4 border-transparent data-[active=yes]:bg-cyan-50 data-[active=yes]:border-cyan-500" data-active="">
                                    <span class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0
                                        <?php echo e($isZip ? 'bg-amber-100 text-amber-600' : 'bg-slate-100 text-slate-600'); ?>">
                                        <i class="fas <?php echo e($isZip ? 'fa-file-archive' : 'fa-file'); ?> text-sm"></i>
                                    </span>
                                    <div class="min-w-0 flex-1">
                                        <p class="font-semibold text-slate-800 truncate text-sm" title="<?php echo e($name); ?>"><?php echo e($name); ?></p>
                                        <?php if($size): ?>
                                            <p class="text-xs text-slate-500"><?php echo e($size); ?></p>
                                        <?php endif; ?>
                                    </div>
                                    <div class="flex items-center gap-1 shrink-0">
                                        <button type="button" class="dataset-file-preview p-2 rounded-lg text-cyan-600 hover:bg-cyan-100 transition-colors" title="عرض">
                                            <i class="fas fa-eye text-sm"></i>
                                        </button>
                                        <a href="<?php echo e(route('community.data.download-file', [$dataset, $idx])); ?>" class="p-2 rounded-lg text-slate-600 hover:bg-slate-200 transition-colors" title="تحميل">
                                            <i class="fas fa-download text-sm"></i>
                                        </a>
                                    </div>
                                </div>
                            </li>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </ul>
                </div>
            </div>

            
            <div class="lg:col-span-8 xl:col-span-9">
                <div id="previewContainer" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
                    <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between flex-wrap gap-2">
                        <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                            <i class="fas fa-table text-blue-600"></i>
                            <span id="previewTitle">معاينة البيانات</span>
                        </h2>
                        <span id="previewCount" class="text-slate-500 text-sm"></span>
                    </div>
                    <div id="previewLoading" class="p-8 text-center text-slate-500">
                        <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
                        <p>اختر ملفاً من القائمة لعرضه</p>
                    </div>
                    <div id="previewTableWrap" class="overflow-auto max-h-[70vh] border-b border-slate-100 hidden">
                        <table class="w-full min-w-full border-collapse text-right" id="previewTable">
                            <thead class="sticky top-0 z-10 bg-slate-100 border-b-2 border-slate-200"><tr id="previewThead"></tr></thead>
                            <tbody class="divide-y divide-slate-100" id="previewTbody"></tbody>
                        </table>
                    </div>
                    <div id="previewZipWrap" class="overflow-auto max-h-[70vh] p-4 hidden">
                        <h3 class="text-base font-bold text-slate-800 mb-3 flex items-center gap-2">
                            <i class="fas fa-file-archive text-amber-600"></i>
                            محتويات الملف المضغوط
                        </h3>
                        <ul id="previewZipList" class="divide-y divide-slate-100 space-y-1"></ul>
                    </div>
                    <div id="previewEmpty" class="p-6 text-center text-slate-500 text-sm hidden"></div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl border border-slate-200 p-8 text-center text-slate-500">
            <i class="fas fa-inbox text-4xl mb-3 text-slate-300"></i>
            <p>لا توجد ملفات مرفقة لهذه المجموعة.</p>
        </div>
    <?php endif; ?>

    <?php $__env->startPush('scripts'); ?>
    <?php if(!empty($filesList)): ?>
    <script>
    (function() {
        var previewUrl = <?php echo json_encode(route('community.data.preview', $dataset), 512) ?>;
        var zipContentsUrl = <?php echo json_encode(route('community.data.zip-contents', $dataset), 512) ?>;
        var fileCount = <?php echo e(count($filesList)); ?>;

        function hideAllPreviews() {
            document.getElementById('previewLoading').classList.add('hidden');
            document.getElementById('previewTableWrap').classList.add('hidden');
            document.getElementById('previewZipWrap').classList.add('hidden');
            document.getElementById('previewEmpty').classList.add('hidden');
        }
        function setActiveFile(index) {
            document.querySelectorAll('.dataset-file-item').forEach(function(li) {
                var div = li.querySelector('[data-active]');
                if (div) div.setAttribute('data-active', li.getAttribute('data-index') === String(index) ? 'yes' : '');
            });
        }
        function loadPreview(index) {
            setActiveFile(index);
            hideAllPreviews();
            document.getElementById('previewLoading').classList.remove('hidden');
            document.getElementById('previewLoading').querySelector('p').textContent = 'جاري تحميل المعاينة...';
            document.getElementById('previewTitle').textContent = 'معاينة البيانات';

            fetch(previewUrl + '?file=' + index, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    document.getElementById('previewLoading').classList.add('hidden');
                    if (data.zip && data.entries) {
                        document.getElementById('previewTitle').textContent = 'محتويات الملف المضغوط';
                        var wrap = document.getElementById('previewZipWrap');
                        var list = document.getElementById('previewZipList');
                        list.innerHTML = '';
                        data.entries.forEach(function(entry) {
                            var li = document.createElement('li');
                            li.className = 'flex items-center justify-between gap-3 py-2.5 px-3 rounded-lg hover:bg-slate-50';
                            var sizeStr = entry.size >= 1024 ? (entry.size / 1024).toFixed(1) + ' KB' : entry.size + ' B';
                            li.innerHTML = '<span class="flex items-center gap-2 truncate"><i class="fas fa-file text-slate-400 text-sm shrink-0"></i><span class="truncate text-sm text-slate-800">' + escapeHtml(entry.name) + '</span></span><span class="text-xs text-slate-500 shrink-0">' + sizeStr + '</span>';
                            list.appendChild(li);
                        });
                        document.getElementById('previewCount').textContent = data.entries.length + ' ملف داخل الأرشيف';
                        wrap.classList.remove('hidden');
                    } else {
                        var headers = data.headers || [];
                        var rows = data.rows || [];
                        if (headers.length || rows.length) {
                            var thead = document.getElementById('previewThead');
                            thead.innerHTML = '';
                            headers.forEach(function(cell) {
                                var th = document.createElement('th');
                                th.className = 'px-4 py-3 text-sm font-bold text-slate-800 whitespace-nowrap border-l border-slate-200';
                                th.textContent = cell;
                                thead.appendChild(th);
                            });
                            var tbody = document.getElementById('previewTbody');
                            tbody.innerHTML = '';
                            rows.forEach(function(row) {
                                var tr = document.createElement('tr');
                                tr.className = 'hover:bg-slate-50/80 transition-colors';
                                headers.forEach(function(_, i) {
                                    var td = document.createElement('td');
                                    td.className = 'px-4 py-2.5 text-sm text-slate-700 whitespace-nowrap border-l border-slate-100';
                                    td.textContent = row[i] != null ? row[i] : '';
                                    tr.appendChild(td);
                                });
                                tbody.appendChild(tr);
                            });
                            document.getElementById('previewCount').textContent = 'أول ' + rows.length + ' صف';
                            document.getElementById('previewTableWrap').classList.remove('hidden');
                        } else {
                            var empty = document.getElementById('previewEmpty');
                            empty.textContent = 'تعذر قراءة معاينة الملف أو الملف غير مدعوم. يمكنك تحميل الملف من القائمة.';
                            empty.classList.remove('hidden');
                        }
                    }
                })
                .catch(function() {
                    document.getElementById('previewLoading').classList.add('hidden');
                    var empty = document.getElementById('previewEmpty');
                    empty.textContent = 'تعذر تحميل المعاينة. جرّب تحديث الصفحة.';
                    empty.classList.remove('hidden');
                });
        }
        function escapeHtml(s) {
            var div = document.createElement('div');
            div.textContent = s;
            return div.innerHTML;
        }

        document.querySelectorAll('.dataset-file-item').forEach(function(li) {
            var index = parseInt(li.getAttribute('data-index'), 10);
            li.querySelector('.dataset-file-preview').addEventListener('click', function(e) { e.preventDefault(); loadPreview(index); });
            li.querySelector('div[data-active]').addEventListener('click', function() { loadPreview(index); });
        });
        loadPreview(0);
    })();
    </script>
    <?php endif; ?>
    <?php $__env->stopPush(); ?>

    <div class="mt-8">
        <a href="<?php echo e(route('community.data.index')); ?>" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-semibold">
            <i class="fas fa-arrow-right"></i>
            العودة لمجموعات البيانات
        </a>
    </div>
</div>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.public', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\xampp\htdocs\mindly tics\Mindlytics\resources\views/public/community/dataset-show.blade.php ENDPATH**/ ?>
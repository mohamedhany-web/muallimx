@extends('layouts.public')

@section('title', ($dataset->title ?? 'مجموعة بيانات') . ' - مجتمع الذكاء الاصطناعي')

@section('content')
<div class="max-w-6xl mx-auto px-4 sm:px-6 py-12" style="padding-top: 6rem;">
    <div class="mb-6">
        <a href="{{ route('community.data.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 text-sm font-semibold mb-4">
            <i class="fas fa-arrow-right"></i>
            <span>العودة لمجموعات البيانات</span>
        </a>
        <h1 class="text-2xl sm:text-3xl font-black text-slate-900 mb-2">{{ $dataset->title }}</h1>
        <div class="flex flex-wrap items-center gap-2 mt-2">
            @if($dataset->category)
                <a href="{{ route('community.data.index', ['category' => $dataset->category]) }}" class="inline-flex px-3 py-1 rounded-lg text-sm font-bold bg-slate-100 text-slate-600 hover:bg-slate-200">{{ $dataset->category_label }}</a>
            @endif
            @if($dataset->creator)
                <span class="text-slate-500 text-sm">{{ $dataset->creator->name }}</span>
            @endif
            @if($dataset->file_size)
                <span class="text-slate-500 text-sm">الحجم: {{ $dataset->file_size }}</span>
            @endif
        </div>
    </div>

    @if($dataset->description)
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm mb-6">
            <h2 class="text-lg font-black text-slate-900 mb-3 flex items-center gap-2">
                <i class="fas fa-align-right text-blue-600"></i>
                وصف مجموعة البيانات
            </h2>
            <div class="text-slate-600 leading-relaxed whitespace-pre-line">{{ $dataset->description }}</div>
        </div>
    @endif

    @if($dataset->file_url)
        <div class="mb-6">
            <a href="{{ $dataset->file_url }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-colors shadow-md">
                <i class="fas fa-external-link-alt"></i>
                <span>فتح رابط التحميل</span>
            </a>
        </div>
    @endif

    @if($dataset->file_path || !empty($dataset->files))
        <div class="mb-2 flex flex-wrap items-center gap-3">
            <a href="{{ route('community.data.download', $dataset) }}" class="inline-flex items-center gap-2 text-blue-600 hover:text-blue-700 font-bold text-sm">
                <i class="fas fa-download"></i>
                <span>تحميل الملف</span>
            </a>
            @if(count($dataset->files_list ?? []) > 1)
                <span class="text-slate-500 text-sm">({{ count($dataset->files_list) }} ملفات)</span>
            @endif
        </div>
    @endif

    <div id="previewContainer" class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
        <div class="px-4 py-3 border-b border-slate-200 bg-slate-50 flex items-center justify-between flex-wrap gap-2">
            <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                <i class="fas fa-table text-blue-600"></i>
                معاينة البيانات
            </h2>
            <span id="previewCount" class="text-slate-500 text-sm"></span>
        </div>
        <div id="previewLoading" class="p-8 text-center text-slate-500">
            <i class="fas fa-spinner fa-spin text-2xl mb-2"></i>
            <p>جاري تحميل المعاينة...</p>
        </div>
        <div id="previewTableWrap" class="overflow-auto max-h-[70vh] border-b border-slate-100 hidden">
            <table class="w-full min-w-full border-collapse text-right" id="previewTable">
                <thead class="sticky top-0 z-10 bg-slate-100 border-b-2 border-slate-200"><tr id="previewThead"></tr></thead>
                <tbody class="divide-y divide-slate-100" id="previewTbody"></tbody>
            </table>
        </div>
        <div id="previewEmpty" class="p-6 text-center text-slate-500 text-sm hidden"></div>
    </div>

    @push('scripts')
    <script>
    (function() {
        var previewUrl = @json($previewUrl ?? null);
        if (!previewUrl) {
            document.getElementById('previewLoading').classList.add('hidden');
            var empty = document.getElementById('previewEmpty');
            empty.textContent = 'لا يوجد ملف مرفق لهذه المجموعة أو المعاينة غير متاحة.';
            empty.classList.remove('hidden');
            return;
        }
        fetch(previewUrl, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                document.getElementById('previewLoading').classList.add('hidden');
                var headers = data.headers || [];
                var rows = data.rows || [];
                if (headers.length || rows.length) {
                    var thead = document.getElementById('previewThead');
                    headers.forEach(function(cell) {
                        var th = document.createElement('th');
                        th.className = 'px-4 py-3 text-sm font-bold text-slate-800 whitespace-nowrap border-l border-slate-200';
                        th.textContent = cell;
                        thead.appendChild(th);
                    });
                    var tbody = document.getElementById('previewTbody');
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
                    empty.textContent = 'تعذر قراءة معاينة الملف أو الملف غير مدعوم. يمكنك تحميل الملف أعلاه.';
                    empty.classList.remove('hidden');
                }
            })
            .catch(function() {
                document.getElementById('previewLoading').classList.add('hidden');
                var empty = document.getElementById('previewEmpty');
                empty.textContent = 'تعذر تحميل المعاينة. جرّب تحديث الصفحة.';
                empty.classList.remove('hidden');
            });
    })();
    </script>
    @endpush

    <div class="mt-8">
        <a href="{{ route('community.data.index') }}" class="inline-flex items-center gap-2 text-slate-600 hover:text-slate-900 font-semibold">
            <i class="fas fa-arrow-right"></i>
            العودة لمجموعات البيانات
        </a>
    </div>
</div>
@endsection

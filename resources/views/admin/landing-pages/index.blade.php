@extends('layouts.admin')
@section('title', 'صفحات الهبوط')
@section('header', 'صفحات الهبوط (إعلانات ممولة)')
@section('content')
<div class="w-full space-y-6">
    <div class="rounded-3xl bg-white/95 backdrop-blur border border-slate-200 shadow-lg overflow-hidden">
        <div class="px-5 py-6 sm:px-8 border-b border-slate-200 flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-slate-900">صفحات الهبوط</h1>
                <p class="text-slate-500 mt-1">أنشئ صفحات مخصّصة للإعلانات الممولة. كل صفحة لها رابط مستقل مثل <code class="text-xs bg-slate-100 px-1 rounded">/lp/اسم-الصفحة</code> ويدعم فيديوهات يوتيوب.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.landing-pages.create', ['template' => 1]) }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-white border-2 border-sky-500 text-sky-700 rounded-xl font-semibold hover:bg-sky-50 transition-all">
                    <i class="fas fa-magic"></i>
                    <span>من قالب إعلان</span>
                </a>
                <a href="{{ route('admin.landing-pages.create') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gradient-to-r from-sky-500 to-blue-600 hover:from-sky-600 hover:to-blue-700 text-white rounded-xl font-semibold shadow-lg shadow-sky-500/30 transition-all">
                    <i class="fas fa-plus"></i>
                    <span>صفحة جديدة</span>
                </a>
            </div>
        </div>
        <div class="p-5 sm:p-8">
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-800">{{ session('success') }}</div>
            @endif

            <form method="GET" action="{{ route('admin.landing-pages.index') }}" class="flex flex-wrap gap-3 mb-6">
                <input type="search" name="search" value="{{ request('search') }}" placeholder="بحث بالعنوان أو الرابط أو الحملة..."
                       class="flex-1 min-w-[200px] px-4 py-2 border border-slate-200 rounded-xl text-sm">
                <select name="status" class="px-4 py-2 border border-slate-200 rounded-xl text-sm">
                    <option value="">كل الحالات</option>
                    <option value="active" @selected(request('status')==='active')>نشط</option>
                    <option value="inactive" @selected(request('status')==='inactive')>معطل</option>
                </select>
                <button type="submit" class="px-4 py-2 rounded-xl bg-slate-800 text-white text-sm font-semibold">تصفية</button>
            </form>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-slate-200 text-right text-sm">
                    <thead class="bg-slate-50">
                        <tr class="text-xs font-semibold uppercase text-slate-500">
                            <th class="px-4 py-3">العنوان</th>
                            <th class="px-4 py-3">الرابط</th>
                            <th class="px-4 py-3">الحملة</th>
                            <th class="px-4 py-3">الحالة</th>
                            <th class="px-4 py-3">تحديث</th>
                            <th class="px-4 py-3">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 bg-white">
                        @forelse($pages as $page)
                            <tr class="hover:bg-slate-50/80">
                                <td class="px-4 py-3 font-semibold text-slate-900">{{ $page->title }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <code class="text-xs bg-slate-100 px-2 py-1 rounded" dir="ltr">/lp/{{ $page->slug }}</code>
                                        <button type="button"
                                                class="text-xs font-bold text-sky-600 hover:text-sky-800"
                                                data-copy-url="{{ $page->publicUrl() }}"
                                                onclick="navigator.clipboard.writeText(this.dataset.copyUrl).then(()=>{this.textContent='تم النسخ'; setTimeout(()=>this.textContent='نسخ اللينك',1500)})">
                                            نسخ اللينك
                                        </button>
                                        <a href="{{ $page->publicUrl() }}" target="_blank" class="text-xs text-slate-500 hover:text-sky-600">
                                            <i class="fas fa-external-link-alt"></i>
                                        </a>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-slate-600">
                                    @if($page->utm_campaign || $page->utm_source)
                                        <span class="text-xs">{{ $page->utm_source ?: '—' }} / {{ $page->utm_campaign ?: '—' }}</span>
                                    @else
                                        <span class="text-slate-400">—</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    @if($page->isPublishedNow())
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-emerald-50 text-emerald-700">منشورة</span>
                                    @elseif($page->is_active)
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-amber-50 text-amber-700">مجدولة / خارج الفترة</span>
                                    @else
                                        <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold bg-slate-100 text-slate-600">معطّلة</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-slate-500 whitespace-nowrap">{{ $page->updated_at?->format('Y-m-d H:i') }}</td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2 justify-end">
                                        <a href="{{ route('admin.landing-pages.edit', $page) }}" class="px-3 py-1.5 rounded-lg bg-sky-50 text-sky-700 text-xs font-bold hover:bg-sky-100">تعديل</a>
                                        <form method="POST" action="{{ route('admin.landing-pages.destroy', $page) }}" onsubmit="return confirm('حذف صفحة الهبوط؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-1.5 rounded-lg bg-rose-50 text-rose-700 text-xs font-bold hover:bg-rose-100">حذف</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-10 text-center text-slate-500">لا توجد صفحات هبوط بعد. ابدأ بإنشاء صفحة من قالب الإعلان.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="mt-6">{{ $pages->links() }}</div>
        </div>
    </div>
</div>
@endsection

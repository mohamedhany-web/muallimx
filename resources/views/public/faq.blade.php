@extends('layouts.public')

@section('title', __('public.faq_page_title') . ' - ' . __('public.site_suffix'))

@push('styles')
<style>
    .hero-faq {
        background: linear-gradient(135deg, #1e40af 0%, #1e3a8a 45%, #1d4ed8 100%);
        position: relative;
        overflow: hidden;
    }
    .hero-faq::before {
        content: '';
        position: absolute;
        inset: 0;
        background: url("data:image/svg+xml,%3Csvg width='40' height='40' viewBox='0 0 40 40' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='%23ffffff' fill-opacity='0.03'%3E%3Cpath d='M0 0h40v40H0V0zm2 2h36v36H2V2z'/%3E%3C/g%3E%3C/svg%3E");
        opacity: 0.6;
    }
    .faq-card {
        transition: all 0.25s ease;
        border: 2px solid #e2e8f0;
    }
    .faq-card:hover {
        border-color: rgba(59, 130, 246, 0.35);
        box-shadow: 0 10px 30px rgba(59, 130, 246, 0.08);
    }
    .faq-card[aria-expanded="true"] {
        border-color: rgba(59, 130, 246, 0.5);
        box-shadow: 0 12px 32px rgba(59, 130, 246, 0.12);
    }
    .faq-toggle-icon {
        transition: transform 0.25s ease;
    }
    .faq-card[aria-expanded="true"] .faq-toggle-icon {
        transform: rotate(180deg);
    }
    .section-title-bar {
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, #3b82f6, #10b981);
        border-radius: 2px;
    }
    [x-cloak] { display: none !important; }
</style>
@endpush

@section('content')
{{-- Hero - نفس أسلوب الصفحة الرئيسية والمنصة --}}
<section class="hero-faq min-h-[42vh] flex items-center relative pt-24 pb-16 lg:pt-28 lg:pb-20">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
        <h1 class="text-4xl md:text-5xl lg:text-6xl font-black text-white leading-tight mb-4" style="text-shadow: 0 2px 12px rgba(0,0,0,0.3);">
            الأسئلة الشائعة
        </h1>
        <p class="text-lg md:text-xl text-blue-100 max-w-2xl mx-auto" style="text-shadow: 0 1px 4px rgba(0,0,0,0.2);">
            إجابات واضحة عن كل ما يهمك حول المنصة، التسجيل، الدفع، والشهادات
        </p>
    </div>
</section>

{{-- محتوى الأسئلة --}}
<section class="py-12 md:py-16 bg-gradient-to-b from-slate-50 to-white">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 max-w-4xl">
        @php
            $defaultGrouped = collect($defaultFaqs ?? [])->groupBy('category');
            $hasDbFaqs = isset($faqs) && $faqs->isNotEmpty();
        @endphp

        {{-- فلتر حسب التصنيف (إن وُجدت تصنيفات) --}}
        @if(isset($categories) && $categories->isNotEmpty())
        <div class="flex flex-wrap justify-center gap-2 mb-8">
            <button type="button" class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold bg-blue-600 text-white shadow-md hover:shadow-lg transition-all" data-category="all">
                الكل
            </button>
            @foreach($categories as $cat)
            <button type="button" class="filter-btn px-4 py-2 rounded-xl text-sm font-semibold bg-white text-slate-700 border-2 border-slate-200 hover:border-blue-400 hover:bg-blue-50 transition-all" data-category="{{ $cat }}">
                {{ $cat }}
            </button>
            @endforeach
        </div>
        @endif

        {{-- أسئلة من الإدارة (قاعدة البيانات) --}}
        @if($hasDbFaqs)
        @foreach($faqs as $categoryName => $categoryFaqs)
        <div class="faq-block mb-12" data-category="{{ $categoryName ?? 'general' }}">
            @if($categoryName)
            <div class="flex items-center gap-3 mb-6">
                <div class="section-title-bar rounded-full"></div>
                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-folder-open text-blue-500"></i>
                    {{ $categoryName }}
                </h2>
            </div>
            @endif
            <div class="space-y-3">
                @foreach($categoryFaqs as $faq)
                <div class="faq-card bg-white rounded-2xl shadow-md overflow-hidden" x-data="{ open: false }" aria-expanded="false" :aria-expanded="open">
                    <button type="button" @click="open = !open" class="w-full px-6 py-4 text-right flex items-center justify-between gap-4 hover:bg-slate-50/80 transition-colors">
                        <span class="text-base font-bold text-slate-800 flex-1">{{ $faq->question }}</span>
                        <i class="fas fa-chevron-down faq-toggle-icon text-blue-500 flex-shrink-0"></i>
                    </button>
                    <div x-show="open" x-cloak class="border-t border-slate-100">
                        <div class="px-6 py-4 text-slate-600 leading-relaxed">
                            {!! nl2br(e($faq->answer)) !!}
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif

        {{-- أسئلة شائعة عن Mindlytics (محتوى افتراضي مفيد) --}}
        @if($defaultGrouped->isNotEmpty())
        <div id="default" class="faq-block default-faqs" data-category="default">
            <div class="flex items-center gap-3 mb-6">
                <div class="section-title-bar rounded-full"></div>
                <h2 class="text-2xl font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-graduation-cap text-emerald-500"></i>
                    تعرف على Mindlytics
                </h2>
            </div>
            <div class="space-y-3">
                @foreach($defaultGrouped as $catName => $items)
                <div class="mb-8">
                    @if($catName)
                    <h3 class="text-lg font-bold text-slate-700 mb-4 flex items-center gap-2">
                        <i class="fas fa-tag text-blue-400 text-sm"></i>
                        {{ $catName }}
                    </h3>
                    @endif
                    <div class="space-y-3">
                        @foreach($items as $item)
                        <div class="faq-card bg-white rounded-2xl shadow-md overflow-hidden" x-data="{ open: false }" :aria-expanded="open">
                            <button type="button" @click="open = !open" class="w-full px-6 py-4 text-right flex items-center justify-between gap-4 hover:bg-slate-50/80 transition-colors">
                                <span class="text-base font-bold text-slate-800 flex-1">{{ $item['question'] }}</span>
                                <i class="fas fa-chevron-down faq-toggle-icon text-blue-500 flex-shrink-0"></i>
                            </button>
                            <div x-show="open" x-cloak class="border-t border-slate-100">
                                <div class="px-6 py-4 text-slate-600 leading-relaxed">
                                    {!! nl2br(e($item['answer'] ?? '')) !!}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif

        {{-- لا يوجد أي محتوى --}}
        @if(!$hasDbFaqs && $defaultGrouped->isEmpty())
        <div class="text-center py-16">
            <div class="w-20 h-20 bg-slate-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <i class="fas fa-question-circle text-slate-400 text-4xl"></i>
            </div>
            <p class="text-slate-600 text-lg">لا توجد أسئلة شائعة متاحة حالياً.</p>
            <a href="{{ route('public.contact') }}" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition-colors">
                <i class="fas fa-envelope"></i>
                تواصل معنا
            </a>
        </div>
        @endif
    </div>
</section>

{{-- دعوة للتواصل --}}
<section class="py-14 bg-white border-t border-slate-200">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 text-center max-w-2xl">
        <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-blue-100 text-blue-600 mb-4">
            <i class="fas fa-headset text-2xl"></i>
        </div>
        <h3 class="text-2xl font-bold text-slate-800 mb-2">لم تجد إجابة مناسبة؟</h3>
        <p class="text-slate-600 mb-6">فريقنا جاهز لمساعدتك. تواصل معنا وسنرد في أقرب وقت.</p>
        <a href="{{ route('public.contact') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-gradient-to-r from-blue-600 to-blue-500 text-white rounded-xl font-semibold shadow-lg hover:shadow-xl hover:from-blue-700 hover:to-blue-600 transition-all">
            <i class="fas fa-envelope"></i>
            اتصل بنا
        </a>
    </div>
</section>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    var buttons = document.querySelectorAll('.filter-btn');
    var blocks = document.querySelectorAll('.faq-block');
    if (buttons.length === 0 || blocks.length === 0) return;
    buttons.forEach(function(btn) {
        btn.addEventListener('click', function() {
            var cat = this.getAttribute('data-category');
            buttons.forEach(function(b) {
                if (b.getAttribute('data-category') === cat) {
                    b.classList.add('bg-blue-600', 'text-white');
                    b.classList.remove('bg-white', 'text-slate-700', 'border-2', 'border-slate-200');
                } else {
                    b.classList.remove('bg-blue-600', 'text-white');
                    b.classList.add('bg-white', 'text-slate-700', 'border-2', 'border-slate-200');
                }
            });
            blocks.forEach(function(block) {
                var blockCat = block.getAttribute('data-category');
                block.style.display = (cat === 'all' || blockCat === cat) ? 'block' : 'none';
            });
        });
    });
});
</script>
@endpush
@endsection

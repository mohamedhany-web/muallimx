{{-- بطاقة رأي — شريط رئيسية (عرض ثابت) أو شبكة صفحة الآراء ($fluid) --}}
@php
    /** @var \App\Models\SiteTestimonial $t */
    $fluid = $fluid ?? false;
    $featured = $t->is_featured;
    $widthClass = $fluid
        ? 'w-full'
        : ($featured
            ? 'min-w-[min(92vw,380px)] max-w-[min(92vw,380px)] sm:min-w-[360px] sm:max-w-[360px]'
            : 'min-w-[min(88vw,300px)] max-w-[min(88vw,300px)] sm:min-w-[280px] sm:max-w-[280px]');
@endphp
<article class="{{ $widthClass }} {{ $fluid ? '' : 'flex-shrink-0' }} rounded-[18px] overflow-hidden flex flex-col shadow-[0_14px_40px_-22px_rgba(31,42,122,.4)] border {{ $featured ? 'bg-[#283593] border-[#283593]' : 'bg-white border-slate-200/90' }}">
    @if($t->isImageType() && $t->publicImageUrl())
        {{-- حاوية بأبعاد ثابتة + object-contain لتوحيد الشكل مع اختلاف مقاسات الصور --}}
        <div class="w-full aspect-[4/3] min-h-[10.5rem] max-h-[15rem] sm:max-h-[17rem] flex items-center justify-center overflow-hidden {{ $featured ? 'bg-white/10' : 'bg-slate-100' }}">
            <img src="{{ $t->publicImageUrl() }}" alt="" class="max-h-full max-w-full h-auto w-auto object-contain object-center" loading="lazy" decoding="async">
        </div>
    @endif
    <div class="p-5 flex flex-col flex-1">
        @if($t->body)
            <p class="text-sm leading-8 flex-1 {{ $featured ? 'text-white/95' : 'text-slate-600' }}">
                @if($t->isImageType())
                    {{ Str::limit(strip_tags($t->body), 160) }}
                @else
                    «{{ Str::limit(strip_tags($t->body), 260) }}»
                @endif
            </p>
        @endif
        @if($t->author_name)
            <p class="mt-4 font-bold text-sm {{ $featured ? 'text-[#FFE569]' : 'text-mx-indigo' }}">{{ $t->author_name }}</p>
        @endif
        @if($t->role_label)
            <p class="text-xs mt-1 {{ $featured ? 'text-white/75' : 'text-slate-500' }}">{{ $t->role_label }}</p>
        @endif
    </div>
</article>

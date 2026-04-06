@php
    $pf = $publicFooter ?? \App\Services\PublicFooterSettings::payload();
    $isRtl = app()->getLocale() === 'ar';
    $telHref = '';
    if (! empty($pf['phone'])) {
        $digits = preg_replace('/[^\d+]/', '', $pf['phone']);
        $telHref = $digits !== '' ? 'tel:'.$digits : '';
    }
@endphp
{{-- فوتر موحّد للصفحة الرئيسية والصفحات العامة — البيانات من إعدادات النظام في لوحة الإدارة --}}
<footer style="background:#283593" class="text-white {{ $footerExtraClass ?? '' }}">
    <div class="container-1200 pt-12 pb-8">
        <div class="grid md:grid-cols-4 gap-8 pb-8 border-b border-white/15">
            <div class="md:col-span-2">
                <div class="flex items-center gap-3 mb-4">
                    <span class="w-11 h-11 rounded-xl bg-mx-orange text-white font-black flex items-center justify-center">M</span>
                    <div>
                        <p class="font-heading text-xl font-black">MuallimX</p>
                        <p class="text-xs text-white/70">{{ $pf['brand_tagline'] }}</p>
                    </div>
                </div>
                <p class="text-sm text-white/85 leading-7 max-w-md">{{ $pf['blurb'] }}</p>
            </div>
            <div>
                <h3 class="font-heading font-bold mb-3 text-white">{{ __('public.quick_links') }}</h3>
                <ul class="space-y-2 text-sm text-white/85">
                    <li><a class="hover:text-mx-gold transition-colors" href="{{ route('home') }}">{{ __('public.home') }}</a></li>
                    @if(\Illuminate\Support\Facades\Route::has('public.services.index'))
                    <li><a class="hover:text-mx-gold transition-colors" href="{{ route('public.services.index') }}">{{ __('public.services_page_title') }}</a></li>
                    @endif
                    <li><a class="hover:text-mx-gold transition-colors" href="{{ route('public.courses') }}">{{ __('public.courses') }}</a></li>
                    <li><a class="hover:text-mx-gold transition-colors" href="{{ route('public.instructors.index') }}">{{ __('landing.nav.instructors') }}</a></li>
                </ul>
            </div>
            <div>
                <h3 class="font-heading font-bold mb-3 text-white">{{ __('public.contact_us') }}</h3>
                <ul class="space-y-2 text-sm text-white/85">
                    @if(! empty($pf['email']))
                    <li>
                        <a class="hover:text-mx-gold transition-colors" href="mailto:{{ e($pf['email']) }}">{{ $pf['email'] }}</a>
                    </li>
                    @endif
                    @if(! empty($pf['phone']) && $telHref !== '')
                    <li>
                        <a class="hover:text-mx-gold transition-colors" href="{{ $telHref }}" rel="nofollow">{{ $pf['phone'] }}</a>
                    </li>
                    @elseif(! empty($pf['phone']))
                    <li><span class="text-white/85">{{ $pf['phone'] }}</span></li>
                    @endif
                    @if(! empty($pf['whatsapp_url']))
                    <li>
                        <a class="hover:text-mx-gold transition-colors inline-flex items-center gap-2" href="{{ e($pf['whatsapp_url']) }}" target="_blank" rel="noopener noreferrer">
                            <i class="fab fa-whatsapp text-lg"></i>
                            @if(! empty($pf['phone']))
                                {{ $isRtl ? 'واتساب: ' : 'WhatsApp: ' }}{{ $pf['phone'] }}
                            @else
                                WhatsApp
                            @endif
                        </a>
                    </li>
                    @endif
                </ul>
                @if(! empty($pf['socials']))
                <p class="text-xs font-bold text-white/90 mt-4 mb-2">{{ __('public.follow_us') }}</p>
                <div class="flex flex-wrap gap-2">
                    @foreach($pf['socials'] as $soc)
                    <a href="{{ e($soc['url']) }}"
                       target="_blank"
                       rel="noopener noreferrer"
                       class="w-9 h-9 rounded-lg bg-white/10 hover:bg-white/20 flex items-center justify-center text-white transition-colors"
                       aria-label="{{ e($soc['label']) }}"
                       title="{{ e($soc['label']) }}">
                        <i class="{{ e($soc['icon']) }} text-sm"></i>
                    </a>
                    @endforeach
                </div>
                @endif
            </div>
        </div>
        <div class="pt-5 flex flex-col sm:flex-row gap-2 justify-between text-xs text-white/75">
            <p>&copy; {{ date('Y') }} MuallimX — {{ $isRtl ? 'جميع الحقوق محفوظة' : 'All rights reserved' }}</p>
            @if(! empty($pf['bottom_tagline']))
            <p>{{ $pf['bottom_tagline'] }}</p>
            @endif
        </div>
    </div>
</footer>

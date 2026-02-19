@extends('community.layouts.app')

@section('title', __('auth.dashboard'))

@section('content')
<div class="w-full space-y-6">
    {{-- بطاقة الترحيب --}}
    <div class="bg-gradient-to-br from-cyan-600 via-blue-600 to-slate-800 rounded-3xl border border-cyan-500/20 p-6 sm:p-8 shadow-xl text-white overflow-hidden relative">
        <div class="absolute top-0 left-0 w-40 h-40 bg-white/10 rounded-full -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-32 h-32 bg-white/5 rounded-full translate-x-1/3 translate-y-1/3"></div>
        <div class="relative z-10">
            <p class="text-cyan-200 text-sm font-bold mb-1">مجتمع البيانات والذكاء الاصطناعي</p>
            <h1 class="text-2xl sm:text-3xl font-black mb-2">مرحباً، {{ $user->name }}!</h1>
            <p class="text-white/90 text-sm sm:text-base max-w-xl">مكانك للمسابقات، مجموعات البيانات، والمناقشات. انضم للمجتمع وارتقِ بمهاراتك.</p>
        </div>
    </div>

    {{-- كروت الإحصائيات — تعبّر عن المجتمع --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        <a href="{{ route('community.competitions.index') }}" class="group block bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-lg hover:border-cyan-200 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold mb-1">المسابقات</p>
                    <p class="text-3xl font-black text-cyan-600">{{ $stats['competitions_count'] ?? 0 }}</p>
                    <p class="text-xs text-slate-400 mt-1">مسابقة نشطة</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-cyan-100 text-cyan-600 flex items-center justify-center group-hover:bg-cyan-500 group-hover:text-white transition-colors">
                    <i class="fas fa-trophy text-xl"></i>
                </div>
            </div>
        </a>
        <a href="{{ route('community.datasets.index') }}" class="group block bg-white rounded-2xl border border-slate-200 p-5 shadow-sm hover:shadow-lg hover:border-blue-200 transition-all duration-300">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold mb-1">مجموعات البيانات</p>
                    <p class="text-3xl font-black text-blue-600">{{ $stats['datasets_count'] ?? 0 }}</p>
                    <p class="text-xs text-slate-400 mt-1">مجموعة متاحة</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-100 text-blue-600 flex items-center justify-center group-hover:bg-blue-500 group-hover:text-white transition-colors">
                    <i class="fas fa-database text-xl"></i>
                </div>
            </div>
        </a>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold mb-1">التقديمات</p>
                    <p class="text-3xl font-black text-green-600">0</p>
                    <p class="text-xs text-slate-400 mt-1">قريباً</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-100 text-green-600 flex items-center justify-center">
                    <i class="fas fa-paper-plane text-xl"></i>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-5 shadow-sm">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-slate-500 text-sm font-semibold mb-1">المناقشات</p>
                    <p class="text-3xl font-black text-amber-600">0</p>
                    <p class="text-xs text-slate-400 mt-1">قريباً</p>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-100 text-amber-600 flex items-center justify-center">
                    <i class="fas fa-comments text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- كيف تبدأ — كارد إرشادي --}}
    <div class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 shadow-sm">
        <h2 class="text-lg font-black text-slate-900 mb-2 flex items-center gap-2">
            <span class="w-8 h-8 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center"><i class="fas fa-rocket"></i></span>
            كيف تبدأ اليوم
        </h2>
        <p class="text-slate-600 mb-6">اختر تركيزك: تصفح المسابقات أو مجموعات البيانات، أو انتقل إلى الكورسات لتعزيز مهاراتك.</p>
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('community.competitions.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-cyan-600 text-white font-bold hover:bg-cyan-700 transition-colors shadow-md hover:shadow-lg">
                <i class="fas fa-trophy"></i>
                <span>المسابقات</span>
            </a>
            <a href="{{ route('community.datasets.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-blue-600 text-white font-bold hover:bg-blue-700 transition-colors shadow-md hover:shadow-lg">
                <i class="fas fa-database"></i>
                <span>مجموعات البيانات</span>
            </a>
            <a href="{{ route('public.courses') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-slate-100 text-slate-700 font-bold hover:bg-slate-200 transition-colors">
                <i class="fas fa-book"></i>
                <span>تصفح الكورسات</span>
            </a>
            <a href="{{ route('public.community.index') }}" class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl border-2 border-slate-200 text-slate-700 font-bold hover:border-slate-300 hover:bg-slate-50 transition-colors">
                <i class="fas fa-users"></i>
                <span>عن المجتمع</span>
            </a>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- كارد مسابقات قادمة / حديثة --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fas fa-trophy text-cyan-600"></i>
                    مسابقات المجتمع
                </h2>
                <a href="{{ route('community.competitions.index') }}" class="text-sm font-bold text-cyan-600 hover:underline">عرض الكل</a>
            </div>
            <div class="p-4">
                @if($recentCompetitions->isNotEmpty())
                    <ul class="space-y-3">
                        @foreach($recentCompetitions as $c)
                            <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-cyan-50/50 border border-transparent hover:border-cyan-100 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-cyan-100 text-cyan-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-trophy"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ $c->title }}</p>
                                    @if($c->start_at || $c->end_at)
                                        <p class="text-xs text-slate-500">
                                            @if($c->start_at) {{ $c->start_at->translatedFormat('Y-m-d') }} @endif
                                            @if($c->end_at) — {{ $c->end_at->translatedFormat('Y-m-d') }} @endif
                                        </p>
                                    @endif
                                </div>
                                <a href="{{ route('community.competitions.index') }}" class="text-cyan-600 hover:text-cyan-700 text-sm font-semibold flex-shrink-0">عرض</a>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-8 text-slate-500">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-trophy text-2xl text-slate-400"></i>
                        </div>
                        <p class="text-sm font-semibold">لا توجد مسابقات نشطة حالياً</p>
                        <p class="text-xs mt-1">ستُضاف المسابقات قريباً. تابع التحديثات.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- كارد مجموعات البيانات --}}
        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm overflow-hidden">
            <div class="p-4 border-b border-slate-100 bg-slate-50/50 flex items-center justify-between">
                <h2 class="text-lg font-black text-slate-900 flex items-center gap-2">
                    <i class="fas fa-database text-blue-600"></i>
                    مجموعات البيانات
                </h2>
                <a href="{{ route('community.datasets.index') }}" class="text-sm font-bold text-blue-600 hover:underline">عرض الكل</a>
            </div>
            <div class="p-4">
                @if($recentDatasets->isNotEmpty())
                    <ul class="space-y-3">
                        @foreach($recentDatasets as $d)
                            <li class="flex items-center gap-3 p-3 rounded-xl bg-slate-50 hover:bg-blue-50/50 border border-transparent hover:border-blue-100 transition-colors">
                                <div class="w-10 h-10 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center flex-shrink-0">
                                    <i class="fas fa-database"></i>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-bold text-slate-900 truncate">{{ $d->title }}</p>
                                    @if($d->file_size)
                                        <p class="text-xs text-slate-500">{{ $d->file_size }}</p>
                                    @endif
                                </div>
                                @if($d->file_url)
                                    <a href="{{ $d->file_url }}" target="_blank" rel="noopener" class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex-shrink-0 flex items-center gap-1">
                                        <i class="fas fa-download"></i> تحميل
                                    </a>
                                @else
                                    <a href="{{ route('community.datasets.index') }}" class="text-blue-600 hover:text-blue-700 text-sm font-semibold flex-shrink-0">عرض</a>
                                @endif
                            </li>
                        @endforeach
                    </ul>
                @else
                    <div class="text-center py-8 text-slate-500">
                        <div class="w-14 h-14 rounded-2xl bg-slate-100 flex items-center justify-center mx-auto mb-3">
                            <i class="fas fa-database text-2xl text-slate-400"></i>
                        </div>
                        <p class="text-sm font-semibold">لا توجد مجموعات بيانات متاحة حالياً</p>
                        <p class="text-xs mt-1">ستُضاف مجموعات البيانات قريباً للتحميل والاستخدام.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- كارد المناقشات والتقديمات (قريباً) + تواصل --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900 mb-3 flex items-center gap-2">
                <i class="fas fa-comments text-amber-500"></i>
                المناقشات
            </h2>
            <p class="text-slate-600 text-sm mb-4">ناقش مع الأعضاء، اسأل وأجب، وشارك في حوارات المجتمع. القسم قيد الإعداد.</p>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-amber-100 text-amber-700 text-sm font-semibold">
                <i class="fas fa-clock"></i> قريباً
            </span>
        </div>
        <div class="bg-white rounded-2xl border border-slate-200 p-6 shadow-sm">
            <h2 class="text-lg font-black text-slate-900 mb-3 flex items-center gap-2">
                <i class="fas fa-paper-plane text-green-500"></i>
                التقديمات
            </h2>
            <p class="text-slate-600 text-sm mb-4">قدّم حلولك في المسابقات وترقّ في لوحة المتصدرين. القسم قيد الإعداد.</p>
            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-green-100 text-green-700 text-sm font-semibold">
                <i class="fas fa-clock"></i> قريباً
            </span>
        </div>
    </div>

    {{-- كارد تواصل المجتمع --}}
    <div class="bg-gradient-to-r from-slate-100 to-slate-50 rounded-2xl border border-slate-200 p-6 text-center">
        <p class="text-slate-600 text-sm">هل لديك اقتراح أو سؤال عن المجتمع؟ تواصل معنا من صفحة <a href="{{ route('public.contact') }}" class="text-cyan-600 font-bold hover:underline">التواصل</a>.</p>
    </div>
</div>
@endsection

@extends('layouts.admin')

@section('title', 'وارد الإشعارات')
@section('header', 'وارد الإشعارات')

@section('content')
<div class="space-y-6">
    <section class="rounded-2xl bg-white border border-slate-200 shadow-lg overflow-hidden">
        <div class="px-6 py-5 bg-slate-50 border-b border-slate-200 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
                <h2 class="text-xl font-black text-slate-900">وارد الإشعارات</h2>
                <p class="text-sm text-slate-600 mt-1">
                    التنبيهات الموجهة لحسابك (مثل تذاكر الدعم الفني من الطلاب). صفحة «إدارة الإشعارات» في القائمة مخصصة لإرسال تنبيهات للطلاب.
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @if($stats['unread'] > 0)
                <form action="{{ route('admin.notifications.inbox.mark-all-read') }}" method="post" class="inline" id="inbox-mark-all-form">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 transition-colors">
                        <i class="fas fa-check-double"></i>
                        تعيين الكل كمقروء
                    </button>
                </form>
                @endif
                @if(auth()->user()->isSuperAdmin())
                <a href="{{ route('admin.notifications.index') }}" class="inline-flex items-center gap-2 rounded-xl bg-gradient-to-r from-blue-600 to-blue-500 hover:from-blue-700 hover:to-blue-600 px-4 py-2.5 text-sm font-semibold text-white shadow-md transition-colors">
                    <i class="fas fa-paper-plane"></i>
                    مركز إرسال الإشعارات للطلاب
                </a>
                @endif
            </div>
        </div>
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap gap-2 items-center">
            <span class="text-xs font-semibold text-slate-500">غير مقروء: {{ number_format($stats['unread']) }}</span>
            <span class="text-slate-300">|</span>
            <span class="text-xs font-semibold text-slate-500">الإجمالي: {{ number_format($stats['total']) }}</span>
            <span class="grow"></span>
            <a href="{{ route('admin.notifications.inbox', ['status' => 'unread']) }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'unread' ? 'bg-sky-100 text-sky-800' : 'text-slate-600 hover:bg-slate-100' }}">غير مقروء فقط</a>
            <a href="{{ route('admin.notifications.inbox', ['status' => 'read']) }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg {{ request('status') === 'read' ? 'bg-sky-100 text-sky-800' : 'text-slate-600 hover:bg-slate-100' }}">مقروء</a>
            <a href="{{ route('admin.notifications.inbox') }}" class="text-xs font-semibold px-3 py-1.5 rounded-lg {{ ! request()->filled('status') ? 'bg-sky-100 text-sky-800' : 'text-slate-600 hover:bg-slate-100' }}">الكل</a>
        </div>
        <div class="divide-y divide-slate-100">
            @forelse ($notifications as $notification)
                <a href="{{ $notification->action_url ?: route('admin.notifications.show', $notification) }}"
                   class="flex items-start gap-4 px-6 py-4 hover:bg-slate-50 transition-colors {{ ! $notification->is_read ? 'bg-amber-50/40' : '' }}">
                    <div class="mt-0.5 shrink-0 w-10 h-10 rounded-xl flex items-center justify-center text-sm {{ $notification->is_read ? 'bg-slate-100 text-slate-500' : 'bg-amber-100 text-amber-700' }}">
                        <i class="{{ $notification->type_icon }}"></i>
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-bold text-slate-900 truncate">{{ $notification->title }}</p>
                        <p class="text-xs text-slate-600 mt-1 line-clamp-2">{{ $notification->message }}</p>
                        <p class="text-[10px] text-slate-400 mt-2">{{ $notification->created_at->diffForHumans() }}</p>
                    </div>
                    @if(! $notification->is_read)
                        <span class="shrink-0 w-2 h-2 rounded-full bg-rose-500 mt-2" title="غير مقروء"></span>
                    @endif
                </a>
            @empty
                <div class="px-6 py-16 text-center text-sm text-slate-500">
                    لا توجد إشعارات في الوارد حالياً.
                </div>
            @endforelse
        </div>
        @if($notifications->hasPages())
            <div class="px-6 py-4 border-t border-slate-100">
                {{ $notifications->links() }}
            </div>
        @endif
    </section>
</div>

@push('scripts')
<script>
document.getElementById('inbox-mark-all-form')?.addEventListener('submit', function (e) {
    e.preventDefault();
    var form = this;
    var token = document.querySelector('meta[name="csrf-token"]');
    fetch(form.action, {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': token ? token.getAttribute('content') : ''
        },
        body: new FormData(form),
        credentials: 'same-origin'
    }).then(function () { window.location.reload(); }).catch(function () { form.submit(); });
});
</script>
@endpush
@endsection

@extends('layouts.admin')

@section('title', 'مراجعة الملف التعريفي — التسويق الشخصي')
@section('header', 'مراجعة ملفات التسويق الشخصي للطلاب')

@section('content')
<div class="space-y-6">
    @if(session('success'))
        <div class="rounded-2xl bg-green-50 dark:bg-green-900/20 border-2 border-green-200 dark:border-green-800 px-6 py-4 flex items-center gap-3">
            <i class="fas fa-check-circle text-green-600 text-xl"></i>
            <span class="font-bold text-green-800 dark:text-green-300">{{ session('success') }}</span>
        </div>
    @endif
    @if(session('error'))
        <div class="rounded-2xl bg-red-50 dark:bg-red-900/20 border-2 border-red-200 dark:border-red-800 px-6 py-4 flex items-center gap-3">
            <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
            <span class="font-bold text-red-800 dark:text-red-300">{{ session('error') }}</span>
        </div>
    @endif

    <div class="rounded-2xl bg-white dark:bg-slate-800 border border-slate-200 dark:border-slate-700 shadow-sm p-6">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 dark:text-slate-100">ملف التعريفي للتسويق الشخصي (بورتفوليو)</h1>
                <p class="text-sm text-slate-600 dark:text-slate-300 mt-1">عند حفظ الطالب لملفه من «my-portfolio/profile» يُرسل للمراجعة هنا. المعتمد يُعرض للزوار في المعرض العام.</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('admin.students-control.paid-features') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-bold">
                    <i class="fas fa-layer-group"></i>
                    إدارة المزايا المدفوعة
                </a>
                <a href="{{ route('admin.portfolio.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl border border-slate-200 dark:border-slate-700 text-slate-700 dark:text-slate-200 hover:bg-slate-50 dark:hover:bg-slate-700 text-sm font-bold">
                    <i class="fas fa-images"></i>
                    مراجعة مشاريع المعرض
                </a>
            </div>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('admin.portfolio-marketing-profiles.index', ['status' => 'pending_review']) }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $status === 'pending_review' ? 'bg-amber-600 text-white' : 'bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-200' }}">
            قيد المراجعة @if($pendingCount > 0)<span class="mr-1 opacity-90">({{ $pendingCount }})</span>@endif
        </a>
        <a href="{{ route('admin.portfolio-marketing-profiles.index', ['status' => 'rejected']) }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $status === 'rejected' ? 'bg-red-600 text-white' : 'bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-200' }}">مرفوض</a>
        <a href="{{ route('admin.portfolio-marketing-profiles.index', ['status' => 'approved']) }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $status === 'approved' ? 'bg-emerald-600 text-white' : 'bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-200' }}">معتمد</a>
        <a href="{{ route('admin.portfolio-marketing-profiles.index', ['status' => 'all']) }}" class="px-4 py-2 rounded-xl text-sm font-bold {{ $status === 'all' ? 'bg-slate-700 text-white' : 'bg-gray-200 dark:bg-slate-700 text-gray-700 dark:text-slate-200' }}">الكل</a>
    </div>

    <div class="bg-white dark:bg-slate-800 rounded-2xl border-2 border-gray-200 dark:border-slate-700 overflow-hidden shadow-lg">
        <div class="overflow-x-auto">
            <table class="w-full text-right">
                <thead class="bg-gray-50 dark:bg-slate-900/60 border-b-2 border-gray-200 dark:border-slate-700">
                    <tr>
                        <th class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-slate-100">الطالب</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-slate-100">الهاتف</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-slate-100">الحالة</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-slate-100">آخر إرسال</th>
                        <th class="px-4 py-3 text-sm font-bold text-gray-900 dark:text-slate-100">إجراء</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-slate-700">
                    @forelse($users as $u)
                        <tr class="hover:bg-gray-50/80 dark:hover:bg-slate-700/40">
                            <td class="px-4 py-3 text-sm font-semibold text-gray-900 dark:text-slate-100">{{ $u->name }}</td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300">{{ $u->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                @if($u->portfolio_profile_status === \App\Models\User::PORTFOLIO_PROFILE_PENDING)
                                    <span class="px-2 py-1 rounded-lg bg-amber-100 text-amber-800 text-xs font-bold">قيد المراجعة</span>
                                @elseif($u->portfolio_profile_status === \App\Models\User::PORTFOLIO_PROFILE_REJECTED)
                                    <span class="px-2 py-1 rounded-lg bg-red-100 text-red-800 text-xs font-bold">مرفوض</span>
                                @elseif($u->portfolio_profile_status === \App\Models\User::PORTFOLIO_PROFILE_APPROVED)
                                    <span class="px-2 py-1 rounded-lg bg-emerald-100 text-emerald-800 text-xs font-bold">معتمد</span>
                                @else
                                    <span class="text-gray-500 dark:text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-sm text-gray-600 dark:text-slate-300">{{ $u->portfolio_profile_submitted_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="px-4 py-3 text-sm">
                                <a href="{{ route('admin.portfolio-marketing-profiles.show', $u) }}" class="text-sky-600 dark:text-sky-400 font-bold hover:underline">عرض ومراجعة</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-10 text-center text-gray-500 dark:text-slate-400">لا توجد طلبات في هذا التبويب.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="px-4 py-3 border-t border-gray-200 dark:border-slate-700 bg-gray-50 dark:bg-slate-900/30">
            {{ $users->links() }}
        </div>
    </div>
</div>
@endsection

@extends('layouts.app')

@section('title', __('instructor.withdrawal_requests') . ' - Mindlytics')
@section('header', __('instructor.withdrawal_requests'))

@section('content')
<div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    {{-- إجمالي الماليات من كله --}}
    <div class="mb-6">
        <h2 class="text-lg font-black text-slate-800 mb-4 flex items-center gap-2">
            <i class="fas fa-chart-pie text-amber-600"></i>
            إجمالي الماليات
        </h2>
    </div>
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-emerald-100 text-sm font-semibold mb-2">{{ __('instructor.total_earned') }}</p>
                    <p class="text-3xl font-black">{{ number_format($stats['total_earned'], 2) }} ج.م</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-money-bill-wave text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-blue-500 to-indigo-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm font-semibold mb-2">{{ __('instructor.total_withdrawn') }}</p>
                    <p class="text-3xl font-black">{{ number_format($stats['total_withdrawn'], 2) }} ج.م</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-arrow-down text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-amber-500 to-orange-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-amber-100 text-sm font-semibold mb-2">{{ __('instructor.pending_withdrawals') }}</p>
                    <p class="text-3xl font-black">{{ number_format($stats['pending_withdrawals'], 2) }} ج.م</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-clock text-2xl"></i>
                </div>
            </div>
        </div>
        
        <div class="bg-gradient-to-br from-purple-500 to-pink-600 rounded-2xl p-6 text-white shadow-lg">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm font-semibold mb-2">{{ __('instructor.available_amount') }}</p>
                    <p class="text-3xl font-black">{{ number_format($stats['available_amount'], 2) }} ج.م</p>
                </div>
                <div class="w-16 h-16 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="fas fa-wallet text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Create Withdrawal Button -->
    @if($stats['available_amount'] > 0)
    <div class="mb-6 flex justify-end">
        <a href="{{ route('instructor.withdrawals.create') }}" 
           class="inline-flex items-center gap-2 bg-gradient-to-r from-amber-500 to-orange-600 hover:from-amber-600 hover:to-orange-700 text-white px-6 py-3 rounded-xl font-bold shadow-lg transition-all">
            <i class="fas fa-plus"></i>
            {{ __('instructor.new_withdrawal_request') }}
        </a>
    </div>
    @endif

    <!-- Withdrawals List -->
    <div class="bg-white rounded-2xl shadow-lg overflow-hidden">
        <div class="px-6 py-5 border-b border-gray-200">
            <h2 class="text-2xl font-black text-gray-900 flex items-center gap-3">
                <i class="fas fa-money-bill-wave text-amber-600"></i>
                {{ __('instructor.withdrawal_requests') }}
            </h2>
        </div>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-900">{{ __('instructor.request_number') }}</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-900">{{ __('instructor.amount') }}</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-900">{{ __('instructor.payment_method') }}</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-900">{{ __('common.status') }}</th>
                        <th class="px-6 py-4 text-right text-sm font-bold text-gray-900">{{ __('instructor.request_date') }}</th>
                        <th class="px-6 py-4 text-center text-sm font-bold text-gray-900">{{ __('instructor.actions') }}</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($withdrawals as $withdrawal)
                    <tr class="hover:bg-gray-50 transition-colors">
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-900">{{ $withdrawal->request_number ?? '#' . $withdrawal->id }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="font-bold text-gray-900 text-lg">{{ number_format($withdrawal->amount, 2) }} ج.م</p>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">
                                @if($withdrawal->payment_method == 'bank_transfer')
                                    <i class="fas fa-university ml-1"></i> {{ __('instructor.bank_transfer') }}
                                @elseif($withdrawal->payment_method == 'wallet')
                                    <i class="fas fa-wallet ml-1"></i> {{ __('instructor.wallet') }}
                                @elseif($withdrawal->payment_method == 'cash')
                                    <i class="fas fa-money-bill ml-1"></i> {{ __('instructor.cash') }}
                                @else
                                    <i class="fas fa-ellipsis-h ml-1"></i> {{ __('instructor.other') }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold
                                @if($withdrawal->status == 'completed') bg-emerald-100 text-emerald-700
                                @elseif($withdrawal->status == 'processing') bg-blue-100 text-blue-700
                                @elseif($withdrawal->status == 'approved') bg-amber-100 text-amber-700
                                @elseif($withdrawal->status == 'pending') bg-gray-100 text-gray-700
                                @elseif($withdrawal->status == 'rejected') bg-rose-100 text-rose-700
                                @else bg-slate-100 text-slate-700
                                @endif">
                                @if($withdrawal->status == 'completed') {{ __('instructor.completed') }}
                                @elseif($withdrawal->status == 'processing') {{ __('instructor.processing') }}
                                @elseif($withdrawal->status == 'approved') {{ __('instructor.approved') }}
                                @elseif($withdrawal->status == 'pending') {{ __('instructor.pending_status') }}
                                @elseif($withdrawal->status == 'rejected') {{ __('instructor.rejected') }}
                                @elseif($withdrawal->status == 'cancelled') {{ __('instructor.cancelled') }}
                                @else {{ $withdrawal->status }}
                                @endif
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-600">{{ $withdrawal->created_at->format('Y-m-d H:i') }}</p>
                        </td>
                        <td class="px-6 py-4 text-center">
                            <div class="flex items-center justify-center gap-2">
                                <a href="{{ route('instructor.withdrawals.show', $withdrawal) }}" 
                                   class="inline-flex items-center justify-center w-10 h-10 bg-amber-100 hover:bg-amber-200 text-amber-700 rounded-xl transition-colors"
                                   title="{{ __('common.view') }}">
                                    <i class="fas fa-eye"></i>
                                </a>
                                @if(in_array($withdrawal->status, ['pending', 'approved']))
                                <form action="{{ route('instructor.withdrawals.cancel', $withdrawal) }}" 
                                      method="POST" 
                                      onsubmit="return confirm('{{ __('instructor.confirm_cancel_withdrawal') }}');"
                                      class="inline">
                                    @csrf
                                    <button type="submit" 
                                            class="inline-flex items-center justify-center w-10 h-10 bg-rose-100 hover:bg-rose-200 text-rose-700 rounded-xl transition-colors"
                                            title="{{ __('instructor.cancel') }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <div class="flex flex-col items-center gap-4">
                                <div class="w-16 h-16 bg-gray-100 rounded-full flex items-center justify-center">
                                    <i class="fas fa-money-bill-wave text-gray-400 text-2xl"></i>
                                </div>
                                <div>
                                    <p class="font-bold text-gray-900">{{ __('instructor.no_withdrawals') }}</p>
                                    <p class="text-sm text-gray-600 mt-1">{{ __('instructor.no_withdrawals_description') }}</p>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        @if($withdrawals->hasPages())
        <div class="px-6 py-4 border-t border-gray-200 bg-gray-50">
            {{ $withdrawals->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

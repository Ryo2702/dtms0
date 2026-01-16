@extends('layouts.app')
@section('title', 'Archive')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Archive</h1>
                    <p class="text-gray-600 mt-1">View completed and cancelled transactions</p>
                </div>
            </div>
        </div>

        {{-- Filters --}}
        <x-card class="mb-6">
            <form method="GET" action="{{ route('transactions.archive.index') }}" class="space-y-4">
                {{-- Status Tabs --}}
                <div class="flex flex-wrap gap-2 border-b pb-4">
                    <a href="{{ route('transactions.archive.index', array_merge(request()->except('status'), ['status' => 'all'])) }}"
                        class="px-4 py-2 rounded-lg font-medium {{ request('status', 'all') === 'all' ? 'bg-primary text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        All ({{ $stats['all'] }})
                    </a>
                    <a href="{{ route('transactions.archive.index', array_merge(request()->except('status'), ['status' => 'completed'])) }}"
                        class="px-4 py-2 rounded-lg font-medium {{ request('status') === 'completed' ? 'bg-success text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Completed ({{ $stats['completed'] }})
                    </a>
                    <a href="{{ route('transactions.archive.index', array_merge(request()->except('status'), ['status' => 'cancelled'])) }}"
                        class="px-4 py-2 rounded-lg font-medium {{ request('status') === 'cancelled' ? 'bg-error text-white' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' }}">
                        Cancelled ({{ $stats['cancelled'] }})
                    </a>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    {{-- Search --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Search</label>
                        <input type="text" name="search" value="{{ request('search') }}"
                            placeholder="Transaction code, purpose, subject..."
                            class="input input-bordered w-full">
                    </div>

                    {{-- Date From --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                        <input type="date" name="date_from" value="{{ request('date_from') }}"
                            class="input input-bordered w-full">
                    </div>

                    {{-- Date To --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date To</label>
                        <input type="date" name="date_to" value="{{ request('date_to') }}"
                            class="input input-bordered w-full">
                    </div>
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Filter
                    </button>
                    <a href="{{ route('transactions.archive.index') }}" class="btn btn-ghost">
                        <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                        Clear
                    </a>
                </div>
            </form>
        </x-card>

        {{-- Transactions Table --}}
        <x-data-table 
            :headers="['Code', 'Type', 'Purpose', 'Department', 'Created By', 'Status', 'Completed/Cancelled At', 'Actions']"
            :sortableFields="[]" 
            :paginator="$transactions">

            @forelse ($transactions as $transaction)
                <tr class="hover:bg-gray-50">
                    <td class="px-4 py-3">
                        <span class="font-mono font-bold text-primary">{{ $transaction->transaction_code }}</span>
                    </td>
                    <td class="px-4 py-3">
                        {{ $transaction->workflow->transaction_name ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3 max-w-xs truncate">
                        {{ $transaction->purpose ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3">
                        {{ $transaction->department->name ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3">
                        {{ $transaction->creator->name ?? 'N/A' }}
                    </td>
                    <td class="px-4 py-3">
                        @if ($transaction->transaction_status === 'completed')
                            <span class="badge badge-success">Completed</span>
                        @elseif ($transaction->transaction_status === 'cancelled')
                            <span class="badge badge-error">Cancelled</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-600">
                        @if ($transaction->completed_at)
                            {{ $transaction->completed_at->format('M d, Y h:i A') }}
                        @elseif ($transaction->updated_at)
                            {{ $transaction->updated_at->format('M d, Y h:i A') }}
                        @else
                            N/A
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex gap-2">
                            <a href="{{ route('transactions.show', $transaction) }}" 
                                class="btn btn-sm btn-ghost" 
                                title="View Details">
                                <i data-lucide="eye" class="w-4 h-4"></i>
                            </a>
                            <a href="{{ route('transactions.history', $transaction) }}" 
                                class="btn btn-sm btn-ghost" 
                                title="View History">
                                <i data-lucide="history" class="w-4 h-4"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">
                        <div class="flex flex-col items-center gap-2">
                            <i data-lucide="archive" class="w-12 h-12 text-gray-300"></i>
                            <p>No archived transactions found</p>
                        </div>
                    </td>
                </tr>
            @endforelse
        </x-data-table>
    </x-container>
@endsection

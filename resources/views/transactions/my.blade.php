@extends('layouts.app')

@section('title', 'My Transactions')

@section('content')
    <x-container>
        {{-- Header with Tabs --}}
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-end gap-4 mb-4">
                <div>
                    <h1 class="text-3xl font-bold">My Transactions</h1>
                    <p class="text-gray-600 mt-1">Track and manage your submitted transactions</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('transactions.index') }}"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition-colors">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        New Transaction
                    </a>
                </div>
            </div>

            {{-- Tabs --}}
            <div class="border-b border-gray-200">
                <nav class="flex space-x-1 overflow-x-auto" aria-label="Tabs">
                    <a href="{{ route('transactions.my', ['tab' => 'all']) }}"
                        class="whitespace-nowrap px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $tab === 'all' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        All
                    </a>
                    <a href="{{ route('transactions.my', ['tab' => 'in_progress']) }}"
                        class="whitespace-nowrap px-4 py-3 text-sm font-medium border-b-2 transition-colors inline-flex items-center gap-2 {{ $tab === 'in_progress' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        In Progress
                        @if ($stats['in_progress'] > 0)
                            <span
                                class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-blue-500 rounded-full">{{ $stats['in_progress'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('transactions.my', ['tab' => 'rejected']) }}"
                        class="whitespace-nowrap px-4 py-3 text-sm font-medium border-b-2 transition-colors inline-flex items-center gap-2 {{ $tab === 'rejected' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Rejected
                        @if (($stats['rejected'] ?? 0) > 0)
                            <span
                                class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-red-500 rounded-full">{{ $stats['rejected'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('transactions.my', ['tab' => 'pending_receipt']) }}"
                        class="whitespace-nowrap px-4 py-3 text-sm font-medium border-b-2 transition-colors inline-flex items-center gap-2 {{ $tab === 'pending_receipt' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Pending Receipt
                        @if (($stats['pending_receipt'] ?? 0) > 0)
                            <span
                                class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-bold text-white bg-yellow-500 rounded-full">{{ $stats['pending_receipt'] }}</span>
                        @endif
                    </a>
                    <a href="{{ route('transactions.my', ['tab' => 'completed']) }}"
                        class="whitespace-nowrap px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $tab === 'completed' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Completed
                    </a>
                    <a href="{{ route('transactions.my', ['tab' => 'cancelled']) }}"
                        class="whitespace-nowrap px-4 py-3 text-sm font-medium border-b-2 transition-colors {{ $tab === 'cancelled' ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                        Cancelled
                    </a>
                </nav>
            </div>
        </div>

        {{-- Date Filter Section --}}
        <x-card class="mb-6">
            <div class="flex items-center gap-2 mb-4 pb-4 border-b">
                <i data-lucide="list-filter" class="w-5 h-5 text-primary"></i>
                <h3 class="text-lg font-semibold">Filter Transactions</h3>
            </div>
            <form method="GET" action="{{ route('transactions.my') }}" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="tab" value="{{ $tab }}" />
                <div class="flex-1">
                    <label for="transaction_code" class="label">
                        <span class="label-text">Transaction Number</span>
                    </label>
                    <input type="text" id="transaction_code" name="transaction_code"
                        value="{{ request('transaction_code') }}" placeholder="Search by transaction code..."
                        class="input input-bordered w-full" />
                </div>
                <div class="flex-1">
                    <label for="date_from" class="label">
                        <span class="label-text">From Date</span>
                    </label>
                    <input type="date" id="date_from" name="date_from" value="{{ request('date_from') }}"
                        class="input input-bordered w-full" />
                </div>
                <div class="flex-1">
                    <label for="date_to" class="label">
                        <span class="label-text">To Date</span>
                    </label>
                    <input type="date" id="date_to" name="date_to" value="{{ request('date_to') }}"
                        class="input input-bordered w-full" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Filter
                    </button>
                    @if (request('date_from') || request('date_to') || request('transaction_code'))
                        <a href="{{ route('transactions.my', ['tab' => $tab]) }}" class="btn btn-outline">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        {{-- Rejected Transactions Alert --}}
        @if ($stats['rejected'] > 0)
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start gap-3">
                    <i data-lucide="alert-circle" class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5"></i>
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-red-800 mb-1">
                            {{ $stats['rejected'] }} {{ Str::plural('Transaction', $stats['rejected']) }} Requires
                            Attention
                        </h3>
                        <p class="text-sm text-red-700 mb-3">
                            You have transactions that have been rejected and returned to you for corrections. Please review
                            the rejection reasons and resubmit after making the necessary changes.
                        </p>
                        <a href="{{ route('transactions.my', ['tab' => 'rejected']) }}"
                            class="inline-flex items-center px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i data-lucide="clipboard-list" class="w-4 h-4 mr-2"></i>
                            View Rejected Transactions
                        </a>
                    </div>
                </div>
            </div>
        @endif

        {{-- Transactions Table --}}
        <x-card>
            <x-data-table :headers="[
                'Transaction Number',
                'Workflow',
                'Status',
                'Current Step',
                'Urgency',
                'Created',
                'Due Date',
                'Actions',
            ]" :paginator="$transactions" emptyMessage="You haven't created any transactions yet.">
                @foreach ($transactions as $transaction)
                    <tr
                        class="hover:bg-gray-50 {{ str_starts_with($transaction->current_state, 'returned_to_') ? 'bg-red-50 border-l-4 border-l-red-500' : '' }}">
                        <td class="px-4 py-3">
                            <span class="font-mono font-bold text-blue-600">{{ $transaction->transaction_code }}</span>
                            @if (str_starts_with($transaction->current_state, 'returned_to_'))
                                <div class="mt-1">
                                    <span
                                        class="inline-flex items-center px-2 py-1 text-xs font-semibold text-red-800 bg-red-100 rounded-full">
                                        <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                                        Action Required
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $transaction->workflow->transaction_name ?? 'N/A' }}</div>
                            @if ($transaction->department)
                                <div class="text-xs text-gray-500">Current: {{ $transaction->department->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$transaction->transaction_status" :labels="[
                                'draft' => 'Draft',
                                'in_progress' => 'In Progress',
                                'completed' => 'Completed',
                                'cancelled' => 'Cancelled',
                                'overdue' => 'Overdue',
                            ]" :variants="[
                                'draft' => 'badge-ghost',
                                'in_progress' => 'badge-info',
                                'completed' => 'badge-success',
                                'cancelled' => 'badge-error',
                                'overdue' => 'badge-warning',
                            ]" />
                            @if ($transaction->transaction_status === 'completed')
                                @if ($transaction->receiving_status === 'pending')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-orange-700 bg-orange-100 rounded mt-1">
                                        <i data-lucide="clock" class="w-3 h-3 mr-1"></i>
                                        Awaiting Receipt
                                    </span>
                                @elseif($transaction->receiving_status === 'received')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-green-700 bg-green-100 rounded mt-1">
                                        <i data-lucide="check-circle" class="w-3 h-3 mr-1"></i>
                                        Received
                                    </span>
                                @elseif($transaction->receiving_status === 'not_received')
                                    <span
                                        class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded mt-1">
                                        <i data-lucide="x-circle" class="w-3 h-3 mr-1"></i>
                                        Not Received
                                    </span>
                                @endif
                            @endif
                            @if ($transaction->isReturnedState())
                                <span
                                    class="inline-flex items-center px-2 py-0.5 text-xs font-semibold text-red-700 bg-red-100 rounded mt-1">
                                    <i data-lucide="rotate-ccw" class="w-3 h-3 mr-1"></i>
                                    Returned
                                </span>
                                @php
                                    $lastRejection = $transaction
                                        ->reviewers()
                                        ->where('status', 'rejected')
                                        ->latest('reviewed_at')
                                        ->first();
                                @endphp
                                @if ($lastRejection && $lastRejection->rejection_reason)
                                    <div class="mt-2 p-2 bg-red-50 border border-red-200 rounded text-xs">
                                        <div class="font-semibold text-red-800 mb-1">Rejection Reason:</div>
                                        <div class="text-red-700">{{ Str::limit($lastRejection->rejection_reason, 100) }}
                                        </div>
                                        @if ($lastRejection->reviewer)
                                            <div class="text-red-600 mt-1 text-[10px]">
                                                — {{ $lastRejection->reviewer->name }}
                                                ({{ $lastRejection->department->name ?? 'N/A' }})
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            @endif
                            @if ($transaction->transaction_status === 'completed' && $transaction->receiving_status)
                                <div class="mt-1">
                                    @if ($transaction->receiving_status === 'pending')
                                        <span class="badge badge-warning badge-sm">Awaiting Confirmation</span>
                                    @elseif($transaction->receiving_status === 'received')
                                        <span class="badge badge-success badge-sm">Received</span>
                                    @elseif($transaction->receiving_status === 'not_received')
                                        <span class="badge badge-error badge-sm">Not Received</span>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="text-sm font-medium">
                                    {{ $transaction->current_workflow_step }} / {{ $transaction->total_workflow_steps }}
                                </span>
                                <progress class="progress progress-primary w-16"
                                    value="{{ $transaction->current_workflow_step }}"
                                    max="{{ $transaction->total_workflow_steps }}">
                                </progress>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$transaction->level_of_urgency" :labels="[
                                'normal' => 'Normal',
                                'urgent' => 'Urgent',
                                'highly_urgent' => 'Highly Urgent',
                            ]" :variants="[
                                'normal' => 'badge-ghost',
                                'urgent' => 'badge-warning',
                                'highly_urgent' => 'badge-error',
                            ]" />
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">
                            <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $transaction->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">
                            @if ($transaction->due_date)
                                <div
                                    class="{{ $transaction->due_date->isPast() && $transaction->transaction_status !== 'completed' ? 'text-red-600 font-semibold' : '' }}">
                                    {{ $transaction->due_date->format('M d, Y') }}
                                </div>
                                <div
                                    class="text-xs {{ $transaction->due_date->isPast() && $transaction->transaction_status !== 'completed' ? 'text-red-500' : 'text-gray-400' }}">
                                    {{ $transaction->due_date->diffForHumans() }}
                                </div>
                            @else
                                <span class="text-gray-400 text-xs">No due date</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-2">
                                {{-- View Details - Always available --}}
                                <a href="{{ route('transactions.show', $transaction) }}"
                                    class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                                    title="View Details">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>

                                @if ($transaction->transaction_status === 'in_progress')
                                    {{-- Check if transaction is rejected (returned_to state) --}}
                                    @if (str_starts_with($transaction->current_state, 'returned_to_'))
                                        {{-- Resubmit button for rejected transactions - More prominent --}}
                                        <form action="{{ route('transactions.creator-resubmit', $transaction) }}"
                                            method="POST" class="inline-block"
                                            onsubmit="return confirm('Are you sure you want to resubmit this transaction? Make sure you have made the required corrections.')">
                                            @csrf
                                            <button type="submit"
                                                class="inline-flex items-center px-3 py-2 bg-yellow-600 hover:bg-yellow-700 text-white text-sm font-medium rounded-lg transition-colors"
                                                title="Resubmit after corrections">
                                                <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                                                Resubmit
                                            </button>
                                        </form>
                                        {{-- Cancel button also available for rejected transactions --}}
                                        <button onclick="window['cancel-modal-{{ $transaction->id }}'].showModal()"
                                            class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                            title="Cancel Transaction">
                                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                                        </button>
                                    @else
                                        {{-- Cancel button for in_progress (not rejected) --}}
                                        <button onclick="window['cancel-modal-{{ $transaction->id }}'].showModal()"
                                            class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                            title="Cancel Transaction">
                                            <i data-lucide="x-circle" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                @else
                                    {{-- For other statuses: Show all other actions --}}
                                    <a href="{{ route('transactions.tracker', $transaction) }}"
                                        class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                                        title="Track Progress">
                                        <i data-lucide="map-pin" class="w-4 h-4"></i>
                                    </a>
                                    <a href="{{ route('transactions.history', $transaction) }}"
                                        class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                                        title="View History">
                                        <i data-lucide="history" class="w-4 h-4"></i>
                                    </a>
                                    @if (!$transaction->isCompleted() && !$transaction->isCancelled())
                                        <a href="{{ route('transactions.edit', $transaction) }}"
                                            class="inline-flex items-center px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-sm font-medium rounded-lg transition-colors"
                                            title="Edit">
                                            <i data-lucide="edit" class="w-4 h-4"></i>
                                        </a>
                                    @endif
                                    {{-- Receiving confirmation buttons for completed transactions --}}
                                    @if (
                                        $transaction->transaction_status === 'completed' &&
                                            $transaction->receiving_status === 'pending' &&
                                            auth()->user()->department_id === $transaction->origin_department_id)
                                        <button
                                            onclick="document.getElementById('confirm-modal-{{ $transaction->id }}').showModal()"
                                            class="inline-flex items-center px-3 py-2 bg-green-600 hover:bg-green-700 text-white text-sm font-medium rounded-lg transition-colors"
                                            title="Confirm Received">
                                            <i data-lucide="check" class="w-4 h-4"></i>
                                        </button>
                                        <button
                                            onclick="document.getElementById('not-received-modal-{{ $transaction->id }}').showModal()"
                                            class="inline-flex items-center px-3 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors"
                                            title="Mark Not Received">
                                            <i data-lucide="x" class="w-4 h-4"></i>
                                        </button>
                                    @endif
                                @endif
                            </div>

                            {{-- Cancel Transaction Modal --}}
                            @if ($transaction->transaction_status === 'in_progress')
                                <x-modal id="cancel-modal-{{ $transaction->id }}" size="md">
                                    <h3 class="font-bold text-lg text-error mb-4">Cancel Transaction</h3>
                                    <p class="mb-2">Are you sure you want to cancel this transaction?</p>
                                    <p class="text-sm text-gray-500 mb-4">Transaction:
                                        <strong>{{ $transaction->transaction_code }}</strong>
                                    </p>

                                    <form id="cancel-form-{{ $transaction->id }}"
                                        action="{{ route('transactions.cancel', $transaction) }}" method="POST">
                                        @csrf
                                        <div class="mb-4">
                                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                                Reason for Cancellation <span class="text-error">*</span>
                                            </label>
                                            <textarea name="reason"
                                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-primary focus:border-transparent"
                                                rows="3" placeholder="Explain why you are cancelling this transaction..." required></textarea>
                                        </div>

                                        <div class="flex justify-end gap-3 mt-6">
                                            <button type="button" class="btn btn-ghost"
                                                onclick="window['cancel-modal-{{ $transaction->id }}'].close()">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-error">
                                                <i data-lucide="x-circle" class="w-4 h-4 mr-2"></i>
                                                Confirm Cancellation
                                            </button>
                                        </div>
                                    </form>
                                </x-modal>
                            @endif

                            {{-- Confirm Received Modal --}}
                            @if ($transaction->transaction_status === 'completed' && $transaction->receiving_status === 'pending')
                                <x-modal id="confirm-modal-{{ $transaction->id }}" title="Confirm Receipt"
                                    size="md">
                                    <p class="py-4">Are you sure you have received this completed transaction?</p>
                                    <p class="text-sm text-gray-500">Transaction:
                                        <strong>{{ $transaction->transaction_code }}</strong>
                                    </p>

                                    <div class="flex justify-end gap-3 mt-6">
                                        <button type="button" class="btn btn-ghost"
                                            onclick="window['confirm-modal-{{ $transaction->id }}'].close()">
                                            Cancel
                                        </button>
                                        <form action="{{ route('transactions.confirm-received', $transaction) }}"
                                            method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-success">
                                                <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                                                Confirm Received
                                            </button>
                                        </form>
                                    </div>
                                </x-modal>

                                {{-- Not Received Modal --}}
                                <x-modal id="not-received-modal-{{ $transaction->id }}" title="Mark as Not Received"
                                    size="md">
                                    <p class="py-4">Please provide a reason why this transaction was not received.</p>
                                    <form action="{{ route('transactions.mark-not-received', $transaction) }}"
                                        method="POST">
                                        @csrf
                                        <div class="form-control mb-4">
                                            <label class="label">
                                                <span class="label-text">Reason <span class="text-error">*</span></span>
                                            </label>
                                            <textarea name="reason" class="textarea textarea-bordered" rows="3"
                                                placeholder="Explain why the transaction was not received..." required></textarea>
                                        </div>

                                        <div class="flex justify-end gap-3 mt-6">
                                            <button type="button" class="btn btn-ghost"
                                                onclick="window['not-received-modal-{{ $transaction->id }}'].close()">
                                                Cancel
                                            </button>
                                            <button type="submit" class="btn btn-error">
                                                <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                                                Mark Not Received
                                            </button>
                                        </div>
                                    </form>
                                </x-modal>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>
    </x-container>
@endsection

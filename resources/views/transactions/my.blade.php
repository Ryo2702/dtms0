@extends('layouts.app')

@section('title', 'My Transactions')

@section('content')
    <x-container>
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">My Transactions</h1>
                    <p class="text-gray-600 mt-1">Track and manage your submitted transactions</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('transactions.index') }}" class="btn btn-primary">
                        <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                        New Transaction
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="stat bg-base-100 rounded-lg shadow-sm border">
                <div class="stat-title">Total</div>
                <div class="stat-value text-2xl">{{ $stats['all'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-lg shadow-sm border">
                <div class="stat-title">In Progress</div>
                <div class="stat-value text-2xl text-info">{{ $stats['in_progress'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-lg shadow-sm border">
                <div class="stat-title">Completed</div>
                <div class="stat-value text-2xl text-success">{{ $stats['completed'] }}</div>
            </div>
            <div class="stat bg-base-100 rounded-lg shadow-sm border">
                <div class="stat-title">Cancelled</div>
                <div class="stat-value text-2xl text-error">{{ $stats['cancelled'] }}</div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="tabs tabs-boxed bg-base-200 p-1 mb-6 w-fit">
            <a href="{{ route('transactions.my', ['tab' => 'all']) }}" 
               class="tab {{ $tab === 'all' ? 'tab-active' : '' }}">
                All
            </a>
            <a href="{{ route('transactions.my', ['tab' => 'in_progress']) }}" 
               class="tab {{ $tab === 'in_progress' ? 'tab-active' : '' }}">
                In Progress
                @if($stats['in_progress'] > 0)
                    <span class="badge badge-info badge-sm ml-1">{{ $stats['in_progress'] }}</span>
                @endif
            </a>
            <a href="{{ route('transactions.my', ['tab' => 'pending_receipt']) }}" 
               class="tab {{ $tab === 'pending_receipt' ? 'tab-active' : '' }}">
                Pending Receipt
                @if(($stats['pending_receipt'] ?? 0) > 0)
                    <span class="badge badge-warning badge-sm ml-1">{{ $stats['pending_receipt'] }}</span>
                @endif
            </a>
            <a href="{{ route('transactions.my', ['tab' => 'completed']) }}" 
               class="tab {{ $tab === 'completed' ? 'tab-active' : '' }}">
                Completed
            </a>
            <a href="{{ route('transactions.my', ['tab' => 'cancelled']) }}" 
               class="tab {{ $tab === 'cancelled' ? 'tab-active' : '' }}">
                Cancelled
            </a>
        </div>

        {{-- Transactions Table --}}
        <x-card>
            <x-data-table 
                :headers="['Code', 'Workflow', 'Status', 'Current Step', 'Urgency', 'Created', 'Actions']"
                :paginator="$transactions"
                emptyMessage="You haven't created any transactions yet."
            >
                @foreach($transactions as $transaction)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3">
                            <span class="font-mono font-bold text-primary">{{ $transaction->transaction_code }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $transaction->workflow->transaction_name ?? 'N/A' }}</div>
                            @if($transaction->department)
                                <div class="text-xs text-gray-500">Current: {{ $transaction->department->name }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <x-status-badge 
                                :status="$transaction->transaction_status" 
                                :labels="[
                                    'draft' => 'Draft',
                                    'in_progress' => 'In Progress',
                                    'completed' => 'Completed',
                                    'cancelled' => 'Cancelled',
                                    'overdue' => 'Overdue'
                                ]"
                                :variants="[
                                    'draft' => 'badge-ghost',
                                    'in_progress' => 'badge-info',
                                    'completed' => 'badge-success',
                                    'cancelled' => 'badge-error',
                                    'overdue' => 'badge-warning'
                                ]"
                            />
                            @if($transaction->isReturnedState())
                                <span class="badge badge-warning badge-sm mt-1">Returned</span>
                            @endif
                            @if($transaction->transaction_status === 'completed' && $transaction->receiving_status)
                                <div class="mt-1">
                                    @if($transaction->receiving_status === 'pending')
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
                                <progress 
                                    class="progress progress-primary w-16" 
                                    value="{{ $transaction->current_workflow_step }}" 
                                    max="{{ $transaction->total_workflow_steps }}">
                                </progress>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <x-status-badge 
                                :status="$transaction->level_of_urgency" 
                                :labels="[
                                    'normal' => 'Normal',
                                    'urgent' => 'Urgent',
                                    'highly_urgent' => 'Highly Urgent'
                                ]"
                                :variants="[
                                    'normal' => 'badge-ghost',
                                    'urgent' => 'badge-warning',
                                    'highly_urgent' => 'badge-error'
                                ]"
                            />
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">
                            <div>{{ $transaction->created_at->format('M d, Y') }}</div>
                            <div class="text-xs text-gray-400">{{ $transaction->created_at->diffForHumans() }}</div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <a href="{{ route('transactions.show', $transaction) }}" 
                                   class="btn btn-sm btn-ghost" title="View Details">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('transactions.tracker', $transaction) }}" 
                                   class="btn btn-sm btn-ghost" title="Track Progress">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('transactions.history', $transaction) }}" 
                                   class="btn btn-sm btn-ghost" title="View History">
                                    <i data-lucide="history" class="w-4 h-4"></i>
                                </a>
                                @if(!$transaction->isCompleted() && !$transaction->isCancelled())
                                    <a href="{{ route('transactions.edit', $transaction) }}" 
                                       class="btn btn-sm btn-ghost" title="Edit">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                @endif
                                {{-- Receiving confirmation buttons for completed transactions --}}
                                @if($transaction->transaction_status === 'completed' && 
                                    $transaction->receiving_status === 'pending' && 
                                    auth()->user()->department_id === $transaction->origin_department_id)
                                    <button onclick="document.getElementById('confirm-modal-{{ $transaction->id }}').showModal()" 
                                            class="btn btn-sm btn-success" title="Confirm Received">
                                        <i data-lucide="check" class="w-4 h-4"></i>
                                    </button>
                                    <button onclick="document.getElementById('not-received-modal-{{ $transaction->id }}').showModal()" 
                                            class="btn btn-sm btn-error" title="Mark Not Received">
                                        <i data-lucide="x" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>

                            {{-- Confirm Received Modal --}}
                            @if($transaction->transaction_status === 'completed' && $transaction->receiving_status === 'pending')
                                <dialog id="confirm-modal-{{ $transaction->id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="font-bold text-lg">Confirm Receipt</h3>
                                        <p class="py-4">Are you sure you have received this completed transaction?</p>
                                        <p class="text-sm text-gray-500">Transaction: <strong>{{ $transaction->transaction_code }}</strong></p>
                                        <div class="modal-action">
                                            <form method="dialog">
                                                <button class="btn btn-ghost">Cancel</button>
                                            </form>
                                            <form action="{{ route('transactions.confirm-received', $transaction) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-success">
                                                    <i data-lucide="check" class="w-4 h-4 mr-2"></i>
                                                    Confirm Received
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                    <form method="dialog" class="modal-backdrop">
                                        <button>close</button>
                                    </form>
                                </dialog>

                                {{-- Not Received Modal --}}
                                <dialog id="not-received-modal-{{ $transaction->id }}" class="modal">
                                    <div class="modal-box">
                                        <h3 class="font-bold text-lg text-error">Mark as Not Received</h3>
                                        <p class="py-4">Please provide a reason why this transaction was not received.</p>
                                        <form action="{{ route('transactions.mark-not-received', $transaction) }}" method="POST">
                                            @csrf
                                            <div class="form-control mb-4">
                                                <label class="label">
                                                    <span class="label-text">Reason <span class="text-error">*</span></span>
                                                </label>
                                                <textarea name="reason" class="textarea textarea-bordered" rows="3" 
                                                    placeholder="Explain why the transaction was not received..." required></textarea>
                                            </div>
                                            <div class="modal-action">
                                                <button type="button" class="btn btn-ghost" onclick="document.getElementById('not-received-modal-{{ $transaction->id }}').close()">Cancel</button>
                                                <button type="submit" class="btn btn-error">
                                                    <i data-lucide="x" class="w-4 h-4 mr-2"></i>
                                                    Mark Not Received
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                    <form method="dialog" class="modal-backdrop">
                                        <button>close</button>
                                    </form>
                                </dialog>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>
    </x-container>
@endsection

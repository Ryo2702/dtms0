@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Transactions</h1>
                    <p class="text-gray-600 mt-1">Create and manage document transactions</p>
                </div>

                <div class="flex gap-2">
                    <a href="{{ route('transactions.reviews.index') }}" class="btn btn-outline btn-primary">
                        <i data-lucide="clipboard-check" class="w-4 h-4 mr-2"></i>
                        My Reviews
                    </a>
                </div>
            </div>
        </div>

        {{-- Available Workflows Section --}}
        <x-card title="Available Workflows" subtitle="Select a workflow to create a transaction">
            @if($workflows->count() > 0)
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($workflows as $workflow)
                        <div class="border rounded-lg p-4 hover:shadow-md transition-shadow bg-white">
                            <div class="flex justify-between items-start mb-3">
                                <h3 class="font-semibold text-lg">{{ $workflow->transaction_name }}</h3>
                                @if($workflow->difficulty)
                                    <x-status-badge 
                                        :status="$workflow->difficulty" 
                                        :labels="['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard']"
                                        :variants="['easy' => 'badge-success', 'medium' => 'badge-warning', 'hard' => 'badge-error']"
                                    />
                                @endif
                            </div>
                            
                            @if($workflow->description)
                                <p class="text-sm text-gray-600 mb-3">{{ Str::limit($workflow->description, 80) }}</p>
                            @endif

                            <div class="flex items-center gap-2 text-sm text-gray-500 mb-3">
                                <i data-lucide="layers" class="w-4 h-4"></i>
                                <span>{{ count($workflow->getWorkflowSteps()) }} steps</span>
                            </div>

                            @if($workflow->documentTags->count() > 0)
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($workflow->documentTags->take(3) as $tag)
                                        <span class="badge badge-sm badge-ghost">{{ $tag->name }}</span>
                                    @endforeach
                                    @if($workflow->documentTags->count() > 3)
                                        <span class="badge badge-sm badge-ghost">+{{ $workflow->documentTags->count() - 3 }}</span>
                                    @endif
                                </div>
                            @endif

                            <a href="{{ route('transactions.create', ['workflow_id' => $workflow->id]) }}" 
                               class="btn btn-primary btn-sm w-full">
                                <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                                Create Transaction
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="text-center py-8 text-gray-500">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                    <p>No workflows available for your department.</p>
                </div>
            @endif
        </x-card>

        {{-- My Transactions Section --}}
        <x-card title="My Transactions" subtitle="{{ $transactions->total() }} total">
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
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm">
                                Step {{ $transaction->current_workflow_step }} / {{ $transaction->total_workflow_steps }}
                            </span>
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
                            {{ $transaction->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <a href="{{ route('transactions.show', $transaction) }}" 
                                   class="btn btn-sm btn-ghost" title="View">
                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                </a>
                                <a href="{{ route('transactions.tracker', $transaction) }}" 
                                   class="btn btn-sm btn-ghost" title="Track">
                                    <i data-lucide="map-pin" class="w-4 h-4"></i>
                                </a>
                                @if(!$transaction->isCompleted() && !$transaction->isCancelled())
                                    <a href="{{ route('transactions.edit', $transaction) }}" 
                                       class="btn btn-sm btn-ghost" title="Edit">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>
    </x-container>
@endsection

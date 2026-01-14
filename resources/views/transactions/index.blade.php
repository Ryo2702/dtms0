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

        {{-- Tabs for All Transactions --}}
        <div class="bg-white rounded-lg shadow mb-6 border border-gray-200">
            <div class="flex border-b border-gray-200 overflow-x-auto">
                <a href="{{ route('transactions.index', ['tab' => 'all']) }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ (request('tab', 'all') === 'all') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="inbox" class="w-4 h-4"></i>
                    <span>All Transactions</span>
                </a>
                <a href="{{ route('transactions.index', ['tab' => 'in_progress']) }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ (request('tab') === 'in_progress') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="clock" class="w-4 h-4"></i>
                    <span>In Progress</span>
                </a>
                <a href="{{ route('transactions.index', ['tab' => 'rejected']) }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ (request('tab') === 'rejected') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="x-circle" class="w-4 h-4"></i>
                    <span>Rejected</span>
                </a>
                <a href="{{ route('transactions.index', ['tab' => 'completed']) }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ (request('tab') === 'completed') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="check-circle" class="w-4 h-4"></i>
                    <span>Completed</span>
                </a>
                <a href="{{ route('transactions.index', ['tab' => 'pending_receipt']) }}"
                    class="flex items-center gap-2 px-6 py-3 text-sm font-medium border-b-2 transition-colors whitespace-nowrap {{ (request('tab') === 'pending_receipt') ? 'border-blue-600 text-blue-600' : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300' }}">
                    <i data-lucide="hourglass" class="w-4 h-4"></i>
                    <span>Pending Receipt</span>
                </a>
            </div>
        </div>

        {{-- Date Filter Section --}}
        <x-card class="mb-6">
            <div class="flex items-center gap-2 mb-4 pb-4 border-b">
                <i data-lucide="list-filter" class="w-5 h-5 text-primary"></i>
                <h3 class="text-lg font-semibold">Filter Transactions</h3>
            </div>
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                <input type="hidden" name="tab" value="{{ request('tab', 'all') }}" />
                <div class="flex-1">
                    <label for="date_from" class="label">
                        <span class="label-text">From Date</span>
                    </label>
                    <input type="date" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}"
                           class="input input-bordered w-full" />
                </div>
                <div class="flex-1">
                    <label for="date_to" class="label">
                        <span class="label-text">To Date</span>
                    </label>
                    <input type="date" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}"
                           class="input input-bordered w-full" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Filter
                    </button>
                    @if(request('date_from') || request('date_to'))
                        <a href="{{ route('transactions.index', ['tab' => request('tab', 'all')]) }}" class="btn btn-outline">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

        {{-- My Transactions Section --}}
        <x-card class="mb-6">
            <div class="p-4 border-b">
                <h2 class="text-lg font-semibold flex items-center gap-2">
                    <i data-lucide="file-text" class="w-5 h-5 text-primary"></i>
                    My Transactions
                </h2>
                <p class="text-sm text-gray-500 mt-1">Track and manage your submitted transactions</p>
            </div>

            @if ($transactions->isEmpty())
                <div class="p-8 text-center text-gray-500">
                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-4 text-gray-400"></i>
                    <p class="text-lg font-medium">No transactions</p>
                    <p class="text-sm">You haven't created any transactions yet.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="table w-full">
                        <thead class="bg-primary text-white">
                            <tr>
                                <th class="px-4 py-3">Transaction Code</th>
                                <th class="px-4 py-3">Workflow</th>
                                <th class="px-4 py-3">Status</th>
                                <th class="px-4 py-3">Progress</th>
                                <th class="px-4 py-3">Urgency</th>
                                <th class="px-4 py-3">Created</th>
                                <th class="px-4 py-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transactions as $transaction)
                                <tr class="hover:bg-gray-50 {{ str_starts_with($transaction->current_state ?? '', 'returned_to_') ? 'bg-red-50' : '' }}">
                                    <td class="px-4 py-3">
                                        <a href="{{ route('transactions.show', $transaction) }}"
                                            class="font-mono font-bold text-primary hover:underline">
                                            {{ $transaction->transaction_code }}
                                        </a>
                                        @if(str_starts_with($transaction->current_state ?? '', 'returned_to_'))
                                            <div class="mt-1">
                                                <span class="badge badge-error badge-sm">
                                                    <i data-lucide="alert-circle" class="w-3 h-3 mr-1"></i>
                                                    Returned
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="font-medium">{{ $transaction->workflow->transaction_name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($transaction->transaction_status === 'in_progress')
                                            <span class="badge badge-info">In Progress</span>
                                        @elseif($transaction->transaction_status === 'completed')
                                            <span class="badge badge-success">Completed</span>
                                            @if($transaction->receiving_status === 'pending')
                                                <span class="badge badge-warning badge-sm ml-1">Awaiting Receipt</span>
                                            @elseif($transaction->receiving_status === 'received')
                                                <span class="badge badge-success badge-sm ml-1">Received</span>
                                            @endif
                                        @elseif($transaction->transaction_status === 'cancelled')
                                            <span class="badge badge-error">Cancelled</span>
                                        @else
                                            <span class="badge">{{ ucfirst(str_replace('_', ' ', $transaction->transaction_status)) }}</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm font-medium">{{ $transaction->current_workflow_step }} / {{ $transaction->total_workflow_steps }}</span>
                                            <progress 
                                                class="progress progress-primary w-20" 
                                                value="{{ $transaction->current_workflow_step }}" 
                                                max="{{ $transaction->total_workflow_steps }}">
                                            </progress>
                                        </div>
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($transaction->urgency === 'high')
                                            <span class="badge badge-error">High</span>
                                        @elseif($transaction->urgency === 'medium')
                                            <span class="badge badge-warning">Medium</span>
                                        @elseif($transaction->urgency === 'low')
                                            <span class="badge badge-info">Low</span>
                                        @else
                                            <span class="badge badge-ghost">Normal</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3">
                                        <span class="text-sm text-gray-600">{{ $transaction->created_at->format('M d, Y') }}</span>
                                    </td>
                                    <td class="px-4 py-3">
                                        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-ghost">
                                            <i data-lucide="eye" class="w-4 h-4"></i>
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($transactions->hasPages())
                    <div class="p-4 border-t">
                        {{ $transactions->links() }}
                    </div>
                @endif
            @endif
        </x-card>

        {{-- Date Filter Section --}}
        <x-card title="Filter" class="mb-6">
            <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
                <div class="flex-1">
                    <label for="date_from" class="label">
                        <span class="label-text">From Date</span>
                    </label>
                    <input type="date" id="date_from" name="date_from" 
                           value="{{ request('date_from') }}"
                           class="input input-bordered w-full" />
                </div>
                <div class="flex-1">
                    <label for="date_to" class="label">
                        <span class="label-text">To Date</span>
                    </label>
                    <input type="date" id="date_to" name="date_to" 
                           value="{{ request('date_to') }}"
                           class="input input-bordered w-full" />
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                        Filter
                    </button>
                    @if(request('date_from') || request('date_to'))
                        <a href="{{ route('transactions.index', ['tab' => request('tab', 'all')]) }}" class="btn btn-outline">
                            <i data-lucide="x" class="w-4 h-4"></i>
                        </a>
                    @endif
                </div>
            </form>
        </x-card>

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

                            @php
                                $steps = $workflow->getWorkflowSteps();
                                $stepCount = count($steps);
                                $totalHours = collect($steps)->sum(function($step) {
                                    $value = $step['process_time_value'] ?? 0;
                                    $unit = $step['process_time_unit'] ?? 'days';
                                    return match($unit) {
                                        'hours' => $value,
                                        'days' => $value * 24,
                                        'weeks' => $value * 24 * 7,
                                        default => $value * 24
                                    };
                                });
                                
                                $weeks = floor($totalHours / (24 * 7));
                                $days = floor(($totalHours % (24 * 7)) / 24);
                                $hours = $totalHours % 24;
                                
                                $estimatedTime = collect([
                                    $weeks > 0 ? "$weeks week" . ($weeks != 1 ? 's' : '') : null,
                                    $days > 0 ? "$days day" . ($days != 1 ? 's' : '') : null,
                                    $hours > 0 ? "$hours hour" . ($hours != 1 ? 's' : '') : null,
                                ])->filter()->join(', ') ?: '0 hours';
                            @endphp

                            <div class="space-y-2 mb-3">
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <i data-lucide="layers" class="w-4 h-4"></i>
                                    <span class="workflow-step-count" data-workflow-id="{{ $workflow->id }}">{{ $stepCount }} step{{ $stepCount != 1 ? 's' : '' }}</span>
                                </div>
                                <div class="flex items-center gap-2 text-sm text-gray-500">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                    <span class="workflow-estimated-time" data-workflow-id="{{ $workflow->id }}">Est. {{ $estimatedTime }}</span>
                                </div>
                            </div>

                            @if($workflow->documentTags->count() > 0)
                                <div class="flex flex-wrap gap-1 mb-3">
                                    @foreach($workflow->documentTags->take(3) as $tag)
                                        <span class="badge badge-sm badge-ghost" style="background-color: #10b981; color: white;">{{ $tag->name }}</span>
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

    </x-container>

    @push('scripts')
    <script>
        // Update workflow info if custom routes exist in localStorage
        document.addEventListener('DOMContentLoaded', function() {
            const stepCountElements = document.querySelectorAll('.workflow-step-count');
            const estimatedTimeElements = document.querySelectorAll('.workflow-estimated-time');
            
            // Check each workflow for custom configuration
            stepCountElements.forEach(element => {
                const workflowId = element.getAttribute('data-workflow-id');
                const storageKey = `workflow_custom_${workflowId}`;
                
                try {
                    const savedConfig = localStorage.getItem(storageKey);
                    if (savedConfig) {
                        const customWorkflow = JSON.parse(savedConfig);
                        
                        if (customWorkflow.steps && Array.isArray(customWorkflow.steps)) {
                            // Update step count
                            const stepCount = customWorkflow.steps.length;
                            element.textContent = `${stepCount} step${stepCount !== 1 ? 's' : ''}`;
                            
                            // Calculate and update estimated time
                            const totalHours = calculateWorkflowTime(customWorkflow.steps);
                            const formattedTime = formatEstimatedTime(totalHours);
                            
                            const timeElement = document.querySelector(`.workflow-estimated-time[data-workflow-id="${workflowId}"]`);
                            if (timeElement) {
                                timeElement.innerHTML = `Est. ${formattedTime} <span class="badge badge-xs badge-warning ml-1">Custom</span>`;
                            }
                        }
                    }
                } catch (e) {
                    console.error('Error loading custom workflow:', e);
                }
            });
        });
        
        // Calculate total hours from workflow steps
        function calculateWorkflowTime(steps) {
            let totalHours = 0;
            steps.forEach(step => {
                const value = parseInt(step.process_time_value) || 0;
                const unit = step.process_time_unit || 'days';
                
                switch(unit) {
                    case 'hours':
                        totalHours += value;
                        break;
                    case 'days':
                        totalHours += value * 24;
                        break;
                    case 'weeks':
                        totalHours += value * 24 * 7;
                        break;
                }
            });
            return totalHours;
        }
        
        // Format hours into readable time
        function formatEstimatedTime(hours) {
            if (hours === 0) return '0 hours';
            
            const weeks = Math.floor(hours / (24 * 7));
            const days = Math.floor((hours % (24 * 7)) / 24);
            const remainingHours = hours % 24;
            
            const parts = [];
            if (weeks > 0) parts.push(`${weeks} week${weeks !== 1 ? 's' : ''}`);
            if (days > 0) parts.push(`${days} day${days !== 1 ? 's' : ''}`);
            if (remainingHours > 0) parts.push(`${remainingHours} hour${remainingHours !== 1 ? 's' : ''}`);
            
            return parts.join(', ') || '0 hours';
        }
    </script>
    @endpush
@endsection

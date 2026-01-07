@extends('layouts.app')

@section('title', 'Track Transaction')

@section('content')
    <x-container>
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <a href="{{ route('transactions.show', $transaction) }}" class="hover:text-primary">{{ $transaction->transaction_code }}</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Tracker</span>
            </div>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Transaction Tracker</h1>
                    <p class="text-gray-600 mt-1">{{ $transaction->transaction_code }} - {{ $transaction->workflow->transaction_name ?? 'Unknown' }}</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Details
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Progress Overview --}}
            <div class="lg:col-span-1">
                <x-card title="Progress Overview">
                    <div class="text-center mb-4">
                        @php
                            $progress = $transaction->total_workflow_steps > 0 
                                ? round(($transaction->current_workflow_step / $transaction->total_workflow_steps) * 100) 
                                : 0;
                        @endphp
                        <div class="radial-progress text-primary" style="--value:{{ $progress }}; --size:8rem; --thickness:8px;" role="progressbar">
                            <span class="text-2xl font-bold">{{ $progress }}%</span>
                        </div>
                    </div>
                    
                    <dl class="space-y-3">
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Current Step</dt>
                            <dd class="font-medium">{{ $transaction->current_workflow_step }} / {{ $transaction->total_workflow_steps }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
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
                            </dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-sm text-gray-500">Urgency</dt>
                            <dd>
                                <x-status-badge 
                                    :status="$transaction->level_of_urgency" 
                                    :labels="['normal' => 'Normal', 'urgent' => 'Urgent', 'highly_urgent' => 'Highly Urgent']"
                                    :variants="['normal' => 'badge-ghost', 'urgent' => 'badge-warning', 'highly_urgent' => 'badge-error']"
                                />
                            </dd>
                        </div>
                        @if($transaction->revision_number > 0)
                            <div class="flex justify-between">
                                <dt class="text-sm text-gray-500">Revisions</dt>
                                <dd class="font-medium">{{ $transaction->revision_number }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card>

                {{-- Current Location --}}
                <x-card title="Current Location" class="mt-6">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center">
                            <i data-lucide="building-2" class="w-6 h-6 text-primary"></i>
                        </div>
                        <div>
                            <div class="font-medium">{{ $transaction->department->name ?? 'Unknown' }}</div>
                            <div class="text-sm text-gray-500">Step {{ $transaction->current_workflow_step }}</div>
                        </div>
                    </div>
                </x-card>
            </div>

            {{-- Timeline --}}
            <div class="lg:col-span-3">
                <x-card title="Workflow Timeline">
                    @if(isset($workflowProgress['steps']) && count($workflowProgress['steps']) > 0)
                        <div class="relative">
                            {{-- Vertical Line - Blue for completed portion, gray for pending --}}
                            @php
                                $completedSteps = collect($workflowProgress['steps'])->filter(fn($s) => $s['status'] === 'completed')->count();
                                $totalSteps = count($workflowProgress['steps']);
                                $progressPercentage = $totalSteps > 0 ? ($completedSteps / $totalSteps) * 100 : 0;
                            @endphp
                            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200">
                                <div class="w-full bg-blue-500 transition-all duration-500" style="height: {{ $progressPercentage }}%"></div>
                            </div>

                            <div class="space-y-6">
                                @foreach($workflowProgress['steps'] as $index => $step)
                                    @php
                                        $isCompleted = $step['status'] === 'completed';
                                        $isCurrent = $step['status'] === 'current';
                                        $isPending = $step['status'] === 'pending';
                                    @endphp
                                    
                                    <div class="relative flex items-start gap-4 pl-2">
                                        {{-- Step Indicator --}}
                                        <div class="relative z-10 flex-shrink-0 w-10 h-10 rounded-full flex items-center justify-center
                                            {{ $isCompleted ? 'bg-blue-500 text-white' : ($isCurrent ? 'bg-blue-600 text-white animate-pulse ring-4 ring-blue-200' : 'bg-gray-300 text-gray-500') }}">
                                            @if($isCompleted)
                                                <i data-lucide="check" class="w-5 h-5"></i>
                                            @elseif($isCurrent)
                                                <i data-lucide="loader" class="w-5 h-5"></i>
                                            @else
                                                <span class="text-sm font-bold">{{ $index + 1 }}</span>
                                            @endif
                                        </div>

                                        {{-- Step Content --}}
                                        <div class="flex-1 pb-6">
                                            <div class="p-4 rounded-lg border {{ $isCurrent ? 'border-blue-500 bg-blue-50 shadow-md' : ($isCompleted ? 'border-blue-300 bg-blue-50/50' : 'border-gray-200 bg-gray-50') }}">
                                                <div class="flex flex-col sm:flex-row justify-between items-start gap-2 mb-2">
                                                    <div>
                                                        <h4 class="font-semibold {{ $isCurrent ? 'text-blue-700' : ($isCompleted ? 'text-blue-600' : 'text-gray-500') }}">
                                                            {{ $step['department_name'] ?? 'Unknown Department' }}
                                                        </h4>
                                                        <p class="text-sm {{ $isCurrent ? 'text-blue-500' : 'text-gray-500' }}">Step {{ $index + 1 }} of {{ count($workflowProgress['steps']) }}</p>
                                                    </div>
                                                    <div>
                                                        @if($isCompleted)
                                                            <span class="badge bg-blue-500 text-white border-blue-500">Completed</span>
                                                        @elseif($isCurrent)
                                                            <span class="badge bg-blue-600 text-white border-blue-600 animate-pulse">In Progress</span>
                                                        @else
                                                            <span class="badge bg-gray-300 text-gray-600 border-gray-300">Pending</span>
                                                        @endif
                                                    </div>
                                                </div>

                                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
                                                    <div>
                                                        <span class="text-gray-500">Expected Time:</span>
                                                        <span class="ml-1">{{ $step['process_time_value'] ?? 0 }} {{ $step['process_time_unit'] ?? 'days' }}</span>
                                                    </div>
                                                    
                                                    @if($isCompleted && isset($step['completed_at']))
                                                        <div>
                                                            <span class="text-gray-500">Completed:</span>
                                                            <span class="ml-1 text-success">{{ \Carbon\Carbon::parse($step['completed_at'])->format('M d, Y h:i A') }}</span>
                                                        </div>
                                                    @endif

                                                    @if(isset($step['reviewer_name']))
                                                        <div>
                                                            <span class="text-gray-500">Reviewer:</span>
                                                            <span class="ml-1">{{ $step['reviewer_name'] }}</span>
                                                        </div>
                                                    @endif

                                                    @if(isset($step['action']))
                                                        <div>
                                                            <span class="text-gray-500">Action:</span>
                                                            <span class="ml-1 capitalize">{{ $step['action'] }}</span>
                                                        </div>
                                                    @endif
                                                </div>

                                                @if(isset($step['remarks']) && $step['remarks'])
                                                    <div class="mt-3 p-2 bg-gray-50 rounded text-sm">
                                                        <span class="text-gray-500">Remarks:</span>
                                                        <p class="mt-1">{{ $step['remarks'] }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="route" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>No workflow progress available.</p>
                        </div>
                    @endif
                </x-card>
            </div>
        </div>
    </x-container>
@endsection

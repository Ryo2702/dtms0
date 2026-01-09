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

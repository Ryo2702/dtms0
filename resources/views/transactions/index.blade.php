@extends('layouts.app')

@section('title', 'Transactions')

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <h1 class="text-2xl font-bold text-gray-900">Transactions</h1>
            <p class="text-gray-600 mt-1">Select a workflow to create a new transaction</p>
        </div>

        {{-- Available Workflows --}}
        @if($workflows->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($workflows as $workflow)
                    @php
                        $steps = $workflow->getWorkflowSteps();
                        $totalTime = 0;
                        foreach ($steps as $step) {
                            $value = $step['process_time_value'] ?? 0;
                            $unit = $step['process_time_unit'] ?? 'days';
                            if ($unit === 'hours') {
                                $totalTime += $value / 24;
                            } elseif ($unit === 'weeks') {
                                $totalTime += $value * 7;
                            } else {
                                $totalTime += $value;
                            }
                        }
                        $totalTime = round($totalTime, 1);
                        $documentTags = $workflow->documentTags()->where('status', true)->get();
                    @endphp
                    
                    <div class="card bg-base-100 shadow-md hover:shadow-lg transition-shadow">
                        <div class="card-body">
                            {{-- Workflow Header --}}
                            <div class="flex items-start justify-between mb-3">
                                <div>
                                    <span class="badge badge-primary badge-sm font-mono mb-2">{{ $workflow->id }}</span>
                                    <h2 class="card-title text-lg">{{ $workflow->transaction_name }}</h2>
                                </div>
                                <span class="badge {{ $workflow->getDifficultBadgeClass() }}">
                                    {{ ucfirst(str_replace('_', ' ', $workflow->difficulty)) }}
                                </span>
                            </div>

                            @if($workflow->description)
                                <p class="text-sm text-gray-600 mb-3">{{ Str::limit($workflow->description, 100) }}</p>
                            @endif

                            {{-- Workflow Stats --}}
                            <div class="flex items-center gap-4 text-sm text-gray-500 mb-4">
                                <div class="flex items-center gap-1">
                                    <i data-lucide="git-branch" class="w-4 h-4"></i>
                                    <span>{{ count($steps) }} steps</span>
                                </div>
                                <div class="flex items-center gap-1">
                                    <i data-lucide="clock" class="w-4 h-4"></i>
                                    <span>{{ $totalTime }} {{ $totalTime == 1 ? 'day' : 'days' }}</span>
                                </div>
                            </div>

                            {{-- Workflow Route Preview --}}
                            @if(count($steps) > 0)
                                <div class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Route</p>
                                    <div class="flex flex-wrap items-center gap-1">
                                        @foreach($steps as $index => $step)
                                            <span class="badge badge-sm badge-outline">
                                                {{ $step['department_name'] ?? 'Dept ' . ($index + 1) }}
                                            </span>
                                            @if($index < count($steps) - 1)
                                                <i data-lucide="arrow-right" class="w-3 h-3 text-gray-400"></i>
                                            @endif
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Required Documents --}}
                            @if($documentTags->count() > 0)
                                <div class="mb-4">
                                    <p class="text-xs font-semibold text-gray-500 uppercase mb-2">Required Documents</p>
                                    <div class="flex flex-wrap gap-1">
                                        @foreach($documentTags->take(4) as $tag)
                                            <span class="badge badge-sm badge-ghost" title="{{ $tag->description }}">
                                                <i data-lucide="file-text" class="w-3 h-3 mr-1"></i>
                                                {{ Str::limit($tag->name, 15) }}
                                            </span>
                                        @endforeach
                                        @if($documentTags->count() > 4)
                                            <span class="badge badge-sm badge-ghost">+{{ $documentTags->count() - 4 }} more</span>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            {{-- Action Button --}}
                            <div class="card-actions justify-end mt-auto">
                                <a href="{{ route('transactions.create', ['workflow_id' => $workflow->id]) }}" 
                                   class="btn btn-primary btn-sm">
                                    <i data-lucide="plus" class="w-4 h-4 mr-1"></i>
                                    Create Transaction
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card bg-base-100 shadow-md">
                <div class="card-body text-center py-12">
                    <i data-lucide="inbox" class="w-16 h-16 mx-auto text-gray-300 mb-4"></i>
                    <h3 class="text-lg font-medium text-gray-600">No Workflows Available</h3>
                    <p class="text-gray-500 mt-2">There are no active workflows available for your department.</p>
                </div>
            </div>
        @endif
    </div>
@endsection

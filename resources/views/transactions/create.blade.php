@extends('layouts.app')

@section('title', 'Create Transaction - ' . $workflow->transaction_name)

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Create New</span>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Create Transaction</h1>
            <p class="text-gray-600 mt-1">{{ $workflow->transaction_name }}</p>
        </div>

        <form action="{{ route('transactions.store') }}" method="POST" id="transaction-form">
            @csrf
            <input type="hidden" name="workflow_id" value="{{ $workflow->id }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Form Section --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Workflow Route Details (Editable) --}}
                    <div class="card bg-base-100 shadow-md">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <i data-lucide="git-branch" class="w-5 h-5 text-primary"></i>
                                Workflow Route
                            </h2>
                            <p class="text-sm text-gray-500 mb-4">
                                Review and customize the workflow route. You can adjust processing times as needed.
                            </p>

                            <div id="workflow-steps" class="space-y-4">
                                @foreach($workflowSteps as $index => $step)
                                    <div class="step-item border border-base-300 rounded-lg p-4 bg-base-50" data-index="{{ $index }}">
                                        <div class="flex items-start gap-4">
                                            {{-- Step Number --}}
                                            <div class="flex-shrink-0">
                                                <span class="flex items-center justify-center w-8 h-8 rounded-full bg-primary text-primary-content text-sm font-bold">
                                                    {{ $index + 1 }}
                                                </span>
                                            </div>

                                            {{-- Step Details --}}
                                            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4">
                                                {{-- Department (Read-only) --}}
                                                <div>
                                                    <label class="label">
                                                        <span class="label-text font-medium">Department</span>
                                                    </label>
                                                    <input type="text" 
                                                           value="{{ $step['department_name'] ?? 'Unknown Department' }}" 
                                                           class="input input-bordered w-full bg-base-200" 
                                                           readonly>
                                                    <input type="hidden" 
                                                           name="workflow_snapshot[steps][{{ $index }}][department_id]" 
                                                           value="{{ $step['department_id'] }}">
                                                    <input type="hidden" 
                                                           name="workflow_snapshot[steps][{{ $index }}][department_name]" 
                                                           value="{{ $step['department_name'] ?? '' }}">
                                                </div>

                                                {{-- Processing Time --}}
                                                <div>
                                                    <label class="label">
                                                        <span class="label-text font-medium">Processing Time</span>
                                                    </label>
                                                    <div class="flex gap-2">
                                                        <input type="number" 
                                                               name="workflow_snapshot[steps][{{ $index }}][process_time_value]" 
                                                               value="{{ $step['process_time_value'] ?? 1 }}" 
                                                               min="1"
                                                               class="input input-bordered w-20">
                                                        <select name="workflow_snapshot[steps][{{ $index }}][process_time_unit]" 
                                                                class="select select-bordered flex-1">
                                                            <option value="minutes" {{ ($step['process_time_unit'] ?? '') === 'minutes' ? 'selected' : '' }}>Minutes</option>
                                                            <option value="hours" {{ ($step['process_time_unit'] ?? '') === 'hours' ? 'selected' : '' }}>Hours</option>
                                                            <option value="days" {{ ($step['process_time_unit'] ?? 'days') === 'days' ? 'selected' : '' }}>Days</option>
                                                            <option value="weeks" {{ ($step['process_time_unit'] ?? '') === 'weeks' ? 'selected' : '' }}>Weeks</option>
                                                        </select>
                                                    </div>
                                                </div>

                                                {{-- Notes (Optional) --}}
                                                <div class="md:col-span-2">
                                                    <label class="label">
                                                        <span class="label-text font-medium">Notes (Optional)</span>
                                                    </label>
                                                    <input type="text" 
                                                           name="workflow_snapshot[steps][{{ $index }}][notes]" 
                                                           value="{{ $step['notes'] ?? '' }}" 
                                                           class="input input-bordered w-full"
                                                           placeholder="Additional notes for this step">
                                                </div>
                                            </div>
                                        </div>

                                        @if($index < count($workflowSteps) - 1)
                                            <div class="flex justify-center my-2">
                                                <i data-lucide="arrow-down" class="w-5 h-5 text-primary"></i>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Required Documents --}}
                    @php
                        $documentTags = $workflow->documentTags()->where('status', true)->get();
                    @endphp
                    @if($documentTags->count() > 0)
                        <div class="card bg-base-100 shadow-md">
                            <div class="card-body">
                                <h2 class="card-title text-lg mb-4">
                                    <i data-lucide="file-text" class="w-5 h-5 text-primary"></i>
                                    Required Documents
                                </h2>
                                
                                <div class="space-y-3">
                                    @foreach($documentTags as $tag)
                                        <div class="flex items-center justify-between p-3 border border-base-300 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                <div class="flex items-center justify-center w-10 h-10 bg-base-200 rounded-lg">
                                                    <i data-lucide="file" class="w-5 h-5 text-gray-600"></i>
                                                </div>
                                                <div>
                                                    <p class="font-medium">{{ $tag->name }}</p>
                                                    @if($tag->description)
                                                        <p class="text-sm text-gray-500">{{ $tag->description }}</p>
                                                    @endif
                                                </div>
                                            </div>
                                            @if($tag->pivot->is_required)
                                                <span class="badge badge-error badge-sm">Required</span>
                                            @else
                                                <span class="badge badge-ghost badge-sm">Optional</span>
                                            @endif
                                        </div>
                                        <input type="hidden" name="document_tags_id" value="{{ $tag->id }}">
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Transaction Settings --}}
                    <div class="card bg-base-100 shadow-md">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <i data-lucide="settings" class="w-5 h-5 text-primary"></i>
                                Transaction Settings
                            </h2>

                            {{-- Assign Staff --}}
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Assign Staff <span class="text-error">*</span></span>
                                </label>
                                <select name="assign_staff_id" class="select select-bordered w-full" required>
                                    <option value="">Select staff member</option>
                                    @foreach($assignStaff as $staff)
                                        <option value="{{ $staff->id }}" {{ old('assign_staff_id') == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->full_name }} - {{ $staff->position }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assign_staff_id')
                                    <label class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </label>
                                @enderror
                            </div>

                            {{-- Level of Urgency --}}
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Level of Urgency</span>
                                </label>
                                <select name="level_of_urgency" class="select select-bordered w-full">
                                    <option value="normal" {{ old('level_of_urgency', 'normal') === 'normal' ? 'selected' : '' }}>
                                        Normal
                                    </option>
                                    <option value="urgent" {{ old('level_of_urgency') === 'urgent' ? 'selected' : '' }}>
                                        Urgent
                                    </option>
                                    <option value="highly_urgent" {{ old('level_of_urgency') === 'highly_urgent' ? 'selected' : '' }}>
                                        Highly Urgent
                                    </option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Workflow Summary --}}
                    <div class="card bg-base-100 shadow-md">
                        <div class="card-body">
                            <h2 class="card-title text-lg mb-4">
                                <i data-lucide="info" class="w-5 h-5 text-primary"></i>
                                Summary
                            </h2>

                            <div class="space-y-3 text-sm">
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Workflow ID</span>
                                    <span class="font-mono font-medium">{{ $workflow->id }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Total Steps</span>
                                    <span class="font-medium">{{ count($workflowSteps) }}</span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Difficulty</span>
                                    <span class="badge {{ $workflow->getDifficultBadgeClass() }} badge-sm">
                                        {{ ucfirst(str_replace('_', ' ', $workflow->difficulty)) }}
                                    </span>
                                </div>
                                <div class="flex justify-between">
                                    <span class="text-gray-500">Documents</span>
                                    <span class="font-medium">{{ $documentTags->count() ?? 0 }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <div class="flex flex-col gap-3">
                        <button type="submit" class="btn btn-primary w-full">
                            <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                            Create Transaction
                        </button>
                        <a href="{{ route('transactions.index') }}" class="btn btn-ghost w-full">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Initialize Lucide icons
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @endpush
@endsection

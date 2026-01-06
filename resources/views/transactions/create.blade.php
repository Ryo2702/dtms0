@extends('layouts.app')

@section('title', 'Create Transaction')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Create</span>
            </div>
            <h1 class="text-3xl font-bold">Create Transaction</h1>
            <p class="text-gray-600 mt-1">{{ $workflow->transaction_name }}</p>
        </div>

        <form action="{{ route('transactions.store') }}" method="POST" enctype="multipart/form-data" id="createTransactionForm">
            @csrf
            <input type="hidden" name="workflow_id" value="{{ $workflow->id }}">

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                {{-- Main Form --}}
                <div class="lg:col-span-2 space-y-6">
                    {{-- Transaction Details --}}
                    <x-card title="Transaction Details">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Level of Urgency</span>
                                </label>
                                <select name="level_of_urgency" class="select select-bordered w-full" required>
                                    <option value="normal" {{ old('level_of_urgency') == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="urgent" {{ old('level_of_urgency') == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="highly_urgent" {{ old('level_of_urgency') == 'highly_urgent' ? 'selected' : '' }}>Highly Urgent</option>
                                </select>
                                @error('level_of_urgency')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Assign Staff (Optional)</span>
                                </label>
                                <select name="assign_staff_id" class="select select-bordered w-full">
                                    <option value="">-- Select Staff --</option>
                                    @foreach($assignStaff as $staff)
                                        <option value="{{ $staff->id }}" {{ old('assign_staff_id') == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assign_staff_id')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        @if($workflow->documentTags->count() > 0)
                            <div class="form-control mt-4">
                                <label class="label">
                                    <span class="label-text">Document Type</span>
                                </label>
                                <select name="document_tags_id" class="select select-bordered w-full">
                                    <option value="">-- Select Document Type --</option>
                                    @foreach($workflow->documentTags as $tag)
                                        <option value="{{ $tag->id }}" {{ old('document_tags_id') == $tag->id ? 'selected' : '' }}>
                                            {{ $tag->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('document_tags_id')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </x-card>

                    {{-- Workflow Steps Preview --}}
                    <x-card title="Workflow Route" subtitle="{{ count($workflowSteps) }} steps">
                        <div class="space-y-3">
                            @foreach($workflowSteps as $index => $step)
                                <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold">
                                        {{ $index + 1 }}
                                    </div>
                                    <div class="flex-1">
                                        <div class="font-medium">{{ $step['department_name'] ?? 'Unknown Department' }}</div>
                                        <div class="text-sm text-gray-500">
                                            Process time: {{ $step['process_time_value'] ?? 0 }} {{ $step['process_time_unit'] ?? 'days' }}
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </x-card>
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Workflow Info --}}
                    <x-card title="Workflow Information">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm text-gray-500">Workflow</dt>
                                <dd class="font-medium">{{ $workflow->transaction_name }}</dd>
                            </div>
                            @if($workflow->description)
                                <div>
                                    <dt class="text-sm text-gray-500">Description</dt>
                                    <dd class="text-sm">{{ $workflow->description }}</dd>
                                </div>
                            @endif
                            <div>
                                <dt class="text-sm text-gray-500">Total Steps</dt>
                                <dd class="font-medium">{{ count($workflowSteps) }}</dd>
                            </div>
                            @if($workflow->difficulty)
                                <div>
                                    <dt class="text-sm text-gray-500">Difficulty</dt>
                                    <dd>
                                        <x-status-badge 
                                            :status="$workflow->difficulty" 
                                            :labels="['easy' => 'Easy', 'medium' => 'Medium', 'hard' => 'Hard']"
                                            :variants="['easy' => 'badge-success', 'medium' => 'badge-warning', 'hard' => 'badge-error']"
                                        />
                                    </dd>
                                </div>
                            @endif
                        </dl>
                    </x-card>

                    {{-- Actions --}}
                    <x-card>
                        <div class="space-y-3">
                            <button type="submit" class="btn btn-primary w-full">
                                <i data-lucide="send" class="w-4 h-4 mr-2"></i>
                                Submit Transaction
                            </button>
                            <a href="{{ route('transactions.index') }}" class="btn btn-ghost w-full">
                                Cancel
                            </a>
                        </div>
                    </x-card>
                </div>
            </div>
        </form>
    </x-container>
@endsection

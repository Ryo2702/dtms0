@extends('layouts.app')

@section('title', 'Edit Transaction')

@section('content')
    <x-container>
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <a href="{{ route('transactions.show', $transaction) }}" class="hover:text-primary">{{ $transaction->transaction_code }}</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>Edit</span>
            </div>
            <h1 class="text-3xl font-bold">Edit Transaction</h1>
            <p class="text-gray-600 mt-1">{{ $transaction->transaction_code }}</p>
        </div>

        <form action="{{ route('transactions.update', $transaction) }}" method="POST" enctype="multipart/form-data" id="editTransactionForm">
            @csrf
            @method('PUT')

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
                                    <option value="normal" {{ old('level_of_urgency', $transaction->level_of_urgency) == 'normal' ? 'selected' : '' }}>Normal</option>
                                    <option value="urgent" {{ old('level_of_urgency', $transaction->level_of_urgency) == 'urgent' ? 'selected' : '' }}>Urgent</option>
                                    <option value="highly_urgent" {{ old('level_of_urgency', $transaction->level_of_urgency) == 'highly_urgent' ? 'selected' : '' }}>Highly Urgent</option>
                                </select>
                                @error('level_of_urgency')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>

                            <div class="form-control">
                                <label class="label">
                                    <span class="label-text">Assign Staff</span>
                                </label>
                                <select name="assign_staff_id" class="select select-bordered w-full">
                                    <option value="">-- Select Staff --</option>
                                    @foreach($assignStaff as $staff)
                                        <option value="{{ $staff->id }}" {{ old('assign_staff_id', $transaction->assign_staff_id) == $staff->id ? 'selected' : '' }}>
                                            {{ $staff->full_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('assign_staff_id')
                                    <p class="text-error text-sm mt-1">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </x-card>

                    {{-- Workflow Steps (Only editable at step 1) --}}
                    @if($canEditWorkflow)
                        <x-card title="Workflow Route" subtitle="You can modify the workflow route">
                            <div class="alert alert-info mb-4">
                                <i data-lucide="info" class="w-4 h-4"></i>
                                <span>You can only edit the workflow route at the first step.</span>
                            </div>
                            
                            <div class="space-y-3" id="workflowStepsContainer">
                                @foreach($workflowSteps as $index => $step)
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg workflow-step" data-index="{{ $index }}">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-sm font-bold step-number">
                                            {{ $index + 1 }}
                                        </div>
                                        <div class="flex-1">
                                            <select name="workflow_snapshot[steps][{{ $index }}][department_id]" class="select select-bordered select-sm w-full">
                                                @foreach($departments as $dept)
                                                    <option value="{{ $dept->id }}" {{ ($step['department_id'] ?? '') == $dept->id ? 'selected' : '' }}>
                                                        {{ $dept->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="hidden" name="workflow_snapshot[steps][{{ $index }}][department_name]" value="{{ $step['department_name'] ?? '' }}">
                                            <input type="hidden" name="workflow_snapshot[steps][{{ $index }}][process_time_value]" value="{{ $step['process_time_value'] ?? 1 }}">
                                            <input type="hidden" name="workflow_snapshot[steps][{{ $index }}][process_time_unit]" value="{{ $step['process_time_unit'] ?? 'days' }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mt-4 flex gap-2">
                                <button type="button" class="btn btn-sm btn-outline" onclick="resetWorkflowRoute()">
                                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-1"></i>
                                    Reset to Default
                                </button>
                            </div>
                        </x-card>
                    @else
                        <x-card title="Workflow Route" subtitle="Read-only after step 1">
                            <div class="alert alert-warning mb-4">
                                <i data-lucide="alert-triangle" class="w-4 h-4"></i>
                                <span>Workflow route cannot be modified after the first step.</span>
                            </div>
                            
                            <div class="space-y-3">
                                @foreach($workflowSteps as $index => $step)
                                    <div class="flex items-start gap-3 p-3 bg-gray-50 rounded-lg">
                                        <div class="flex-shrink-0 w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                                            {{ $index + 1 < $transaction->current_workflow_step ? 'bg-success text-white' : ($index + 1 == $transaction->current_workflow_step ? 'bg-primary text-white' : 'bg-gray-300') }}">
                                            @if($index + 1 < $transaction->current_workflow_step)
                                                <i data-lucide="check" class="w-4 h-4"></i>
                                            @else
                                                {{ $index + 1 }}
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <div class="font-medium">{{ $step['department_name'] ?? 'Unknown Department' }}</div>
                                            <div class="text-sm text-gray-500">
                                                {{ $step['process_time_value'] ?? 0 }} {{ $step['process_time_unit'] ?? 'days' }}
                                            </div>
                                        </div>
                                        @if($index + 1 == $transaction->current_workflow_step)
                                            <span class="badge badge-primary badge-sm">Current</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </x-card>
                    @endif
                </div>

                {{-- Sidebar --}}
                <div class="space-y-6">
                    {{-- Transaction Info --}}
                    <x-card title="Transaction Info">
                        <dl class="space-y-3">
                            <div>
                                <dt class="text-sm text-gray-500">Transaction Code</dt>
                                <dd class="font-mono font-bold text-primary">{{ $transaction->transaction_code }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Status</dt>
                                <dd>
                                    <x-status-badge 
                                        :status="$transaction->transaction_status" 
                                        :labels="[
                                            'draft' => 'Draft',
                                            'in_progress' => 'In Progress',
                                            'completed' => 'Completed',
                                            'cancelled' => 'Cancelled'
                                        ]"
                                        :variants="[
                                            'draft' => 'badge-ghost',
                                            'in_progress' => 'badge-info',
                                            'completed' => 'badge-success',
                                            'cancelled' => 'badge-error'
                                        ]"
                                    />
                                </dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Current Step</dt>
                                <dd class="font-medium">{{ $transaction->current_workflow_step }} / {{ $transaction->total_workflow_steps }}</dd>
                            </div>
                            <div>
                                <dt class="text-sm text-gray-500">Created</dt>
                                <dd class="font-medium">{{ $transaction->created_at->format('M d, Y') }}</dd>
                            </div>
                        </dl>
                    </x-card>

                    {{-- Actions --}}
                    <x-card>
                        <div class="space-y-3">
                            <button type="submit" class="btn btn-primary w-full">
                                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                                Save Changes
                            </button>
                            <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-ghost w-full">
                                Cancel
                            </a>
                        </div>
                    </x-card>
                </div>
            </div>
        </form>
    </x-container>
@endsection

@push('scripts')
<script>
    function resetWorkflowRoute() {
        if (confirm('Are you sure you want to reset the workflow route to default?')) {
            fetch('{{ route('transactions.workflow-config', $transaction) }}')
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        location.reload();
                    }
                });
        }
    }
</script>
@endpush

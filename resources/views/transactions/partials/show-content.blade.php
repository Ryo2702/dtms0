{{-- Transaction Show Content - For AJAX modal loading --}}
<div class="space-y-6">
    {{-- Transaction Header --}}
    <div class="flex flex-col sm:flex-row justify-between items-start gap-4">
        <div>
            <h2 class="text-2xl font-bold font-mono text-primary">{{ $transaction->transaction_code }}</h2>
            <p class="text-gray-600">{{ $transaction->workflow->transaction_name ?? 'Unknown Workflow' }}</p>
        </div>
        <div class="flex gap-2">
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
            <x-status-badge 
                :status="$transaction->level_of_urgency" 
                :labels="['normal' => 'Normal', 'urgent' => 'Urgent', 'highly_urgent' => 'Highly Urgent']"
                :variants="['normal' => 'badge-ghost', 'urgent' => 'badge-warning', 'highly_urgent' => 'badge-error']"
            />
        </div>
    </div>

    {{-- Transaction Details Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
        <div>
            <dt class="text-sm text-gray-500">Current Step</dt>
            <dd class="font-medium">{{ $transaction->current_workflow_step }} / {{ $transaction->total_workflow_steps }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Department</dt>
            <dd class="font-medium">{{ $transaction->department->name ?? 'N/A' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-gray-500">Created By</dt>
            <dd class="font-medium">{{ $transaction->creator->full_name ?? 'N/A' }}</dd>
        </div>
        @if($transaction->assignStaff)
            <div>
                <dt class="text-sm text-gray-500">Assigned Staff</dt>
                <dd class="font-medium">{{ $transaction->assignStaff->full_name }}</dd>
            </div>
        @endif
        <div>
            <dt class="text-sm text-gray-500">Created</dt>
            <dd class="font-medium">{{ $transaction->created_at->format('M d, Y h:i A') }}</dd>
        </div>
        @if($transaction->revision_number > 0)
            <div>
                <dt class="text-sm text-gray-500">Revisions</dt>
                <dd class="font-medium">{{ $transaction->revision_number }}</dd>
            </div>
        @endif
    </div>

    {{-- Workflow Progress --}}
    <div>
        <h3 class="font-semibold mb-3">Workflow Progress</h3>
        @if(isset($workflowProgress['steps']) && count($workflowProgress['steps']) > 0)
            <div class="flex items-center gap-2 flex-wrap">
                @foreach($workflowProgress['steps'] as $index => $step)
                    @php
                        $isCompleted = $step['status'] === 'completed';
                        $isCurrent = $step['status'] === 'current';
                    @endphp
                    <div class="flex items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center text-sm font-bold
                            {{ $isCompleted ? 'bg-success text-white' : ($isCurrent ? 'bg-primary text-white' : 'bg-gray-200') }}">
                            @if($isCompleted)
                                <i data-lucide="check" class="w-4 h-4"></i>
                            @else
                                {{ $index + 1 }}
                            @endif
                        </div>
                        @if($index < count($workflowProgress['steps']) - 1)
                            <div class="w-8 h-0.5 {{ $isCompleted ? 'bg-success' : 'bg-gray-200' }}"></div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- Available Actions --}}
    @if(count($availableActions) > 0)
        <div class="border-t pt-4">
            <h3 class="font-semibold mb-3">Quick Actions</h3>
            <div class="flex flex-wrap gap-2">
                @foreach($availableActions as $action)
                    <button type="button" 
                            class="btn btn-sm {{ $action === 'approve' ? 'btn-success' : ($action === 'reject' ? 'btn-error' : 'btn-ghost') }}"
                            onclick="submitQuickAction('{{ $action }}')">
                        {{ ucfirst($action) }}
                    </button>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Links --}}
    <div class="border-t pt-4 flex flex-wrap gap-2">
        <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-sm btn-outline">
            <i data-lucide="external-link" class="w-4 h-4 mr-1"></i>
            Full Details
        </a>
        <a href="{{ route('transactions.tracker', $transaction) }}" class="btn btn-sm btn-outline">
            <i data-lucide="map-pin" class="w-4 h-4 mr-1"></i>
            Track
        </a>
        <a href="{{ route('transactions.history', $transaction) }}" class="btn btn-sm btn-outline">
            <i data-lucide="history" class="w-4 h-4 mr-1"></i>
            History
        </a>
    </div>
</div>

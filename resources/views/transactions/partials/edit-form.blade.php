{{-- Transaction Edit Form - For AJAX modal loading --}}
<form action="{{ route('transactions.update', $transaction) }}" method="POST" id="modalEditForm">
    @csrf
    @method('PUT')

    <div class="space-y-4">
        {{-- Transaction Info Header --}}
        <div class="flex justify-between items-center pb-4 border-b">
            <div>
                <h2 class="text-xl font-bold font-mono text-primary">{{ $transaction->transaction_code }}</h2>
                <p class="text-sm text-gray-500">{{ $transaction->workflow->transaction_name ?? 'Unknown' }}</p>
            </div>
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
        </div>

        {{-- Form Fields --}}
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
            </div>
        </div>

        {{-- Workflow Preview (Read-only if not step 1) --}}
        @if(!$canEditWorkflow)
            <div class="alert alert-info">
                <i data-lucide="info" class="w-4 h-4"></i>
                <span>Workflow route cannot be modified after the first step.</span>
            </div>
        @endif

        <div>
            <label class="label">
                <span class="label-text font-medium">Workflow Route</span>
            </label>
            <div class="flex items-center gap-2 flex-wrap p-3 bg-gray-50 rounded-lg">
                @foreach($workflowSteps as $index => $step)
                    <div class="flex items-center">
                        <div class="px-3 py-1 rounded text-sm
                            {{ $index + 1 < $transaction->current_workflow_step ? 'bg-success text-white' : 
                               ($index + 1 == $transaction->current_workflow_step ? 'bg-primary text-white' : 'bg-gray-200') }}">
                            {{ $step['department_name'] ?? 'Dept ' . ($index + 1) }}
                        </div>
                        @if($index < count($workflowSteps) - 1)
                            <i data-lucide="chevron-right" class="w-4 h-4 text-gray-400 mx-1"></i>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Form Actions --}}
        <div class="flex justify-end gap-2 pt-4 border-t">
            <button type="button" class="btn btn-ghost" onclick="closeEditModal()">Cancel</button>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                Save Changes
            </button>
        </div>
    </div>
</form>

@extends('layouts.app')

@section('content')
    <div class="container max-w-6xl mx-auto">
        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">

            <!-- Main Review Panel -->
            <div class="lg:col-span-2">
                <div class="shadow-xl card bg-white-secondary">
                    <div class="card-body">
                        <h2 class="mb-6 card-title">Document Review: {{ $review->document_id }}</h2>

                        <!-- Status Alert -->
                        @if ($review->status === 'approved' && $review->created_by === auth()->id())
                            <div class="mb-6 alert alert-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 stroke-current shrink-0" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h3 class="font-bold">Document Ready for Download!</h3>
                                    <div class="text-sm">Review process completed. You can now download the document for
                                        signature.</div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                            <div>
                                <h3 class="mb-2 text-lg font-semibold">Document Information</h3>
                                <p><strong>Type:</strong> {{ $review->document_type }}</p>
                                <p><strong>Client:</strong> {{ $review->client_name }}</p>
                                <p><strong>Created By:</strong> {{ $review->creator->name }}
                                    ({{ $review->creator->department?->name }})</p>
                                <p><strong>Current Reviewer:</strong> {{ $review->reviewer?->name }}
                                    ({{ $review->reviewer?->department?->name }})</p>
                                <p><strong>Submitted:</strong> {{ $review->submitted_at->format('M d, Y H:i') }}</p>
                            </div>

                            <div>
                                <h3 class="mb-2 text-lg font-semibold">Review Status</h3>
                                <p><strong>Status:</strong>
                                    <span
                                        class="badge 
                                    @if ($review->status === 'pending') badge-warning
                                    @elseif($review->status === 'approved') badge-success
                                    @elseif($review->status === 'rejected') badge-error
                                    @elseif($review->status === 'canceled') badge-neutral
                                    @else badge-info @endif">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </p>
                                @if ($review->status === 'pending' && !$review->is_overdue)
                                    <p><strong>Time Remaining:</strong> {{ $review->remaining_time }} minutes</p>
                                @elseif($review->is_overdue)
                                    <p class="text-error"><strong>Status:</strong> Overdue</p>
                                @endif
                                @if ($review->reviewed_at)
                                    <p><strong>Reviewed At:</strong> {{ $review->reviewed_at->format('M d, Y H:i') }}</p>
                                @endif
                            </div>
                        </div>

                        <!-- Document Data -->
                        <div class="divider">Document Details</div>
                        
                        <div class="mb-6">
                            <div class="p-4 space-y-2 rounded bg-base-200">
                                @foreach ($review->document_data as $key => $value)
                                    @if (!in_array($key, ['action', 'reviewer_id', 'process_time', '_token', 'initial_notes']))
                                        <p><strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong>
                                            {{ $value }}</p>
                                    @endif
                                @endforeach
                            </div>
                        </div>

                        <!-- Review Actions for Heads Only -->
                        @if (auth()->user()->type === 'Head' && $review->assigned_to === auth()->id() && $review->status === 'pending')
                            <div class="divider">Review Actions (Department Head Only)</div>

                            <form action="{{ route('documents.reviews.update', $review->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <!-- OR Number Update -->
                                <div class="mb-4 form-control">
                                    <label class="label">
                                        <span class="font-semibold label-text">Update OR Number</span>
                                        <span class="label-text-alt">Add/update official receipt number</span>
                                    </label>
                                    <input type="text" name="or_number_update" class="input input-bordered"
                                        value="{{ $review->official_receipt_number }}" placeholder="Enter OR number">
                                </div>

                                <div class="mb-4 form-control">
                                    <label class="label">
                                        <span class="font-semibold label-text">Review Action *</span>
                                    </label>
                                    <select name="action" class="select select-bordered" required
                                        onchange="toggleActionOptions(this.value)">
                                        <option value="">Select Action</option>
                                        <option value="forward">Forward to Another Department</option>
                                        <option value="complete">Complete Review & Return to Staff</option>
                                        <option value="reject">Reject Document</option>
                                        <option value="cancel">Cancel Document</option>
                                    </select>
                                </div>

                                <div class="mb-4 form-control">
                                    <label class="label">
                                        <span class="font-semibold label-text">Review Notes *</span>
                                    </label>
                                    <textarea name="review_notes" class="textarea textarea-bordered" required
                                        placeholder="Add your review comments and instructions..."></textarea>
                                </div>

                                <!-- Forward Options -->
                                <div id="forward_options" style="display: none;">
                                    <div class="mb-4 border border-blue-200 card bg-blue-50">
                                        <div class="card-body">
                                            <h4 class="text-blue-800 card-title">Forward to Another Department</h4>

                                            <div class="mb-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Forward To Department Head
                                                        *</span>
                                                </label>
                                                <select name="forward_to" class="select select-bordered">
                                                    <option value="">Select Department Head</option>
                                                    @foreach (\App\Models\User::with('department')->where('type', 'Head')->where('id', '!=', auth()->id())->get()->groupBy('department.name') as $deptName => $users)
                                                        <optgroup label="{{ $deptName ?? 'No Department' }}">
                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->id }}">{{ $user->name }}
                                                                </option>
                                                            @endforeach
                                                        </optgroup>
                                                    @endforeach
                                                </select>
                                            </div>

                                            <div class="mb-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Forward Instructions *</span>
                                                </label>
                                                <textarea name="forward_notes" class="textarea textarea-bordered"
                                                    placeholder="e.g., Please process payment and add OR number, Verify client eligibility, etc."></textarea>
                                            </div>

                                            <div class="mb-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Time Limit *</span>
                                                </label>
                                                <select name="forward_process_time" class="select select-bordered">
                                                    @for ($i = 1; $i <= 10; $i++)
                                                        <option value="{{ $i }}">{{ $i }}
                                                            minute{{ $i > 1 ? 's' : '' }}</option>
                                                    @endfor
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Complete Review Options -->
                                <div id="complete_options" style="display: none;">
                                    <div class="mb-4 border border-green-200 card bg-green-50">
                                        <div class="card-body">
                                            <h4 class="text-green-800 card-title">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Complete Review Process
                                            </h4>
                                            <div class="alert alert-success">
                                                <div>
                                                    <h4 class="font-bold">Review Completed Successfully</h4>
                                                    <p class="text-sm">This will mark the document as approved and return
                                                        it to the original staff member for download. Include your
                                                        completion notes above in the Review Notes field.</p>
                                                </div>
                                            </div>

                                            <div class="mt-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Additional Completion Notes
                                                        (Optional)</span>
                                                    <span class="label-text-alt">Any extra details about the completed
                                                        review</span>
                                                </label>
                                                <textarea name="completion_summary" class="textarea textarea-bordered" rows="3"
                                                    placeholder="e.g., Document reviewed and verified. OR Number added. Ready for client signature."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Options -->
                                <div id="reject_options" style="display: none;">
                                    <div class="mb-4 border border-red-200 card bg-red-50">
                                        <div class="card-body">
                                            <h4 class="text-red-800 card-title">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M6 18L18 6M6 6l12 12" />
                                                </svg>
                                                Reject Document
                                            </h4>
                                            <div class="alert alert-error">
                                                <div>
                                                    <h4 class="font-bold">Document Will Be Rejected</h4>
                                                    <p class="text-sm">Please provide clear reasons for rejection so the
                                                        staff member can address the issues.</p>
                                                </div>
                                            </div>

                                            <div class="mt-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Rejection Reason *</span>
                                                    <span class="label-text-alt">Be specific about what needs to be
                                                        fixed</span>
                                                </label>
                                                <textarea name="rejection_reason" class="textarea textarea-bordered" rows="3"
                                                    placeholder="e.g., Missing required documents, Invalid client information, Fee calculation error, etc."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Cancel Options -->
                                <div id="cancel_options" style="display: none;">
                                    <div class="mb-4 border border-gray-200 card bg-gray-50">
                                        <div class="card-body">
                                            <h4 class="text-gray-800 card-title">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                                Cancel Document
                                            </h4>
                                            <div class="alert alert-warning">
                                                <div>
                                                    <h4 class="font-bold">Document Will Be Canceled</h4>
                                                    <p class="text-sm">This action will permanently cancel the document
                                                        review process.</p>
                                                </div>
                                            </div>

                                            <div class="mt-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Cancellation Reason *</span>
                                                    <span class="label-text-alt">Please explain why this document is being
                                                        canceled</span>
                                                </label>
                                                <textarea name="cancellation_reason" class="textarea textarea-bordered" rows="3"
                                                    placeholder="e.g., Client request cancellation, Duplicate submission, Process no longer needed, etc."></textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-control">
                                    <button type="submit" class="btn btn-primary">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                        Submit Review
                                    </button>
                                </div>
                            </form>
                        @endif

                        <!-- Download Option for Original Creator -->
                        @if ($review->status === 'approved' && $review->created_by === auth()->id())
                            <div class="mt-6">
                                <a href="{{ route('documents.reviews.download', $review->id) }}"
                                    class="btn btn-success btn-lg">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5 mr-2" fill="none"
                                        viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                    </svg>
                                    Download Document for Signature
                                </a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Forwarding Chain Panel -->
            <div class="lg:col-span-1">
                <div class="shadow-xl card bg-base-100">
                    <div class="card-body">
                        <h3 class="mb-4 card-title">
                            Document Journey
                            @if (method_exists($review, 'getProgressPercentageAttribute'))
                                <div class="badge badge-primary">{{ $review->progress_percentage ?? 0 }}% Complete</div>
                            @endif
                        </h3>

                        @if ($review->forwarding_chain && count($review->forwarding_chain) > 0)
                            <div class="space-y-4">
                                @foreach ($review->forwarding_chain as $index => $step)
                                    @php
                                        // Fix: Add default values for missing keys
                                        $stepStatus = $step['status'] ?? 'completed';
                                        $stepNumber = $step['step'] ?? $index + 1;
                                        $stepAction = $step['action'] ?? 'forwarded';
                                        $stepTimestamp = $step['timestamp'] ?? ($step['forwarded_at'] ?? now());
                                        $fromUserName = $step['from_user_name'] ?? 'Unknown User';
                                        $fromUserType =
                                            $step['from_user_type'] ?? ($step['from_user_id'] ? 'Staff' : 'System');
                                        $fromDepartment = $step['from_department'] ?? null;
                                        $toUserName = $step['to_user_name'] ?? null;
                                        $toUserType = $step['to_user_type'] ?? null;
                                        $toDepartment = $step['to_department'] ?? null;
                                        $processTime = $step['process_time'] ?? null;
                                        $notes = $step['notes'] ?? null;
                                    @endphp

                                    <div
                                        class="border-l-4 
                                        @if ($stepStatus === 'completed') border-success
                                        @elseif($stepStatus === 'pending') border-warning
                                        @else border-gray-300 @endif pl-4 pb-4">

                                        <div class="flex items-center gap-2 mb-2">
                                            <div
                                                class="badge 
                                                @if ($stepStatus === 'completed') badge-success
                                                @elseif($stepStatus === 'pending') badge-warning
                                                @else badge-ghost @endif badge-sm">
                                                {{ $stepNumber }}
                                            </div>

                                            <span class="text-sm font-semibold">
                                                {{ \Carbon\Carbon::parse($stepTimestamp)->format('M d, H:i') }}
                                            </span>

                                            @if ($stepStatus === 'completed')
                                                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-success"
                                                    fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M5 13l4 4L19 7" />
                                                </svg>
                                            @elseif($stepStatus === 'pending')
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4 text-warning animate-spin" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @endif
                                        </div>

                                        <div class="space-y-1 text-sm">
                                            <p><strong>Action:</strong>
                                                <span class="badge badge-outline badge-xs">
                                                    {{ ucwords(str_replace('_', ' ', $stepAction)) }}
                                                </span>
                                            </p>

                                            <p><strong>From:</strong> {{ $fromUserName }}
                                                <span class="badge badge-xs">{{ $fromUserType }}</span>
                                            </p>

                                            @if ($fromDepartment)
                                                <p><strong>Dept:</strong> {{ $fromDepartment }}</p>
                                            @endif

                                            @if ($toUserName)
                                                <p><strong>To:</strong> {{ $toUserName }}
                                                    <span class="badge badge-xs">{{ $toUserType }}</span>
                                                </p>
                                                @if ($toDepartment)
                                                    <p><strong>Dept:</strong> {{ $toDepartment }}</p>
                                                @endif
                                            @endif

                                            @if ($processTime)
                                                <p><strong>Time Limit:</strong> {{ $processTime }}
                                                    minute{{ $processTime > 1 ? 's' : '' }}</p>
                                            @endif

                                            @if ($notes)
                                                <div class="p-2 mt-2 text-xs rounded bg-base-200">
                                                    <strong>Notes:</strong> {{ $notes }}
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <!-- Current Status Summary -->
                            <div class="p-3 mt-4 rounded bg-base-200">
                                <div class="text-sm">
                                    <strong>Current Status:</strong>
                                    <span
                                        class="badge 
                                        @if ($review->status === 'pending') badge-warning
                                        @elseif($review->status === 'approved') badge-success
                                        @elseif($review->status === 'rejected') badge-error
                                        @elseif($review->status === 'downloaded') badge-info
                                        @else badge-ghost @endif">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </div>

                                @if ($review->current_step ?? false)
                                    <div class="mt-1 text-xs text-base-content/70">
                                        Step {{ $review->current_step['step'] ?? 'N/A' }}:
                                        {{ ucwords(str_replace('_', ' ', $review->current_step['action'] ?? 'unknown')) }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <div class="py-4 text-center">
                                <div class="mb-2 text-base-content/70">Review process tracking</div>
                                <div class="text-sm">
                                    <div class="flex items-center justify-between p-2 mb-2 rounded bg-base-200">
                                        <span>📝 Document Created</span>
                                        <span class="badge badge-success badge-sm">✓</span>
                                    </div>
                                    <div class="flex items-center justify-between p-2 mb-2 rounded bg-base-200">
                                        <span>📤 Sent for Review</span>
                                        <span class="badge badge-warning badge-sm">⏳</span>
                                    </div>
                                    <div class="flex items-center justify-between p-2 rounded opacity-50 bg-base-200">
                                        <span>✅ Review Complete</span>
                                        <span class="badge badge-ghost badge-sm">-</span>
                                    </div>
                                    <div class="flex items-center justify-between p-2 rounded opacity-50 bg-base-200">
                                        <span>📥 Ready for Download</span>
                                        <span class="badge badge-ghost badge-sm">-</span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleActionOptions(action) {
            const forwardOptions = document.getElementById('forward_options');
            const completeOptions = document.getElementById('complete_options');
            const rejectOptions = document.getElementById('reject_options');
            const cancelOptions = document.getElementById('cancel_options');

            // Hide all options first
            forwardOptions.style.display = 'none';
            completeOptions.style.display = 'none';
            rejectOptions.style.display = 'none';
            cancelOptions.style.display = 'none';

            // Clear all field values and requirements
            document.querySelectorAll('#forward_options select, #forward_options textarea').forEach(field => {
                field.removeAttribute('required');
                if (field.tagName === 'SELECT') field.value = '';
                if (field.tagName === 'TEXTAREA') field.value = '';
            });

            document.querySelectorAll('#reject_options textarea').forEach(field => {
                field.removeAttribute('required');
                field.value = '';
            });

            document.querySelectorAll('#cancel_options textarea').forEach(field => {
                field.removeAttribute('required');
                field.value = '';
            });

            // Show relevant options and set requirements
            if (action === 'forward') {
                forwardOptions.style.display = 'block';
                document.querySelector('select[name="forward_to"]').setAttribute('required', 'required');
                document.querySelector('textarea[name="forward_notes"]').setAttribute('required', 'required');
                document.querySelector('select[name="forward_process_time"]').setAttribute('required', 'required');
            } else if (action === 'complete') {
                completeOptions.style.display = 'block';
                // No required fields for complete action
            } else if (action === 'reject') {
                rejectOptions.style.display = 'block';
                document.querySelector('textarea[name="rejection_reason"]').setAttribute('required', 'required');
            } else if (action === 'cancel') {
                cancelOptions.style.display = 'block';
                document.querySelector('textarea[name="cancellation_reason"]').setAttribute('required', 'required');
            }
        }

        // Form submission validation
        document.querySelector('form')?.addEventListener('submit', function(e) {
            const action = document.querySelector('select[name="action"]').value;

            if (!action) {
                e.preventDefault();
                alert('Please select a review action.');
                return false;
            }

            const reviewNotes = document.querySelector('textarea[name="review_notes"]').value.trim();
            if (!reviewNotes) {
                e.preventDefault();
                alert('Please add review notes.');
                return false;
            }

            // Validate based on action
            if (action === 'forward') {
                const forwardTo = document.querySelector('select[name="forward_to"]').value;
                const forwardNotes = document.querySelector('textarea[name="forward_notes"]').value.trim();

                if (!forwardTo || !forwardNotes) {
                    e.preventDefault();
                    alert('Please fill in all forward details (Department Head and Instructions).');
                    return false;
                }
            }

            if (action === 'reject') {
                const rejectionReason = document.querySelector('textarea[name="rejection_reason"]').value.trim();
                if (!rejectionReason) {
                    e.preventDefault();
                    alert('Please provide a rejection reason.');
                    return false;
                }
            }

            // Confirmation dialog
            let confirmMessage = '';
            if (action === 'complete') {
                confirmMessage = 'Complete this review and return the document to the staff member for download?';
            } else if (action === 'forward') {
                const forwardToName = document.querySelector('select[name="forward_to"] option:checked').text;
                confirmMessage = `Forward this document to ${forwardToName} for further review?`;
            } else if (action === 'reject') {
                confirmMessage = 'Reject this document? The staff member will need to resubmit with corrections.';
            }

            if (!confirm(confirmMessage)) {
                e.preventDefault();
                return false;
            }

            return true;
        });
    </script>
@endsection

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
                        @if ($review->status === 'approved')
                            <div class="mb-6 alert alert-success">
                                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 stroke-current shrink-0" fill="none"
                                    viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div>
                                    <h3 class="font-bold">Document Ready for Download!</h3>
                                    <div class="text-sm">Review process completed. The document can now be downloaded for
                                        signature.</div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                            <div>
                                <h3 class="mb-2 text-lg font-semibold">Document Information</h3>
                                <p><strong>Type:</strong> {{ $review->document_type }}</p>
                                <p><strong>Client:</strong> {{ $review->client_name }}</p>
                                <p><strong>Priority:</strong> 
                                    <span class="badge 
                                        @if($review->difficulty === 'normal') bg-green-500 text-white
                                        @elseif($review->difficulty === 'important') bg-yellow-500 text-white
                                        @elseif($review->difficulty === 'urgent') bg-red-500 text-white
                                        @elseif($review->difficulty === 'immediate') bg-red-900 text-white
                                        @else bg-gray-500 text-white @endif">
                                        {{ ucfirst($review->difficulty ?? 'Normal') }}
                                    </span>
                                </p>
                                <p><strong>Assigned Staff:</strong> {{ $review->assigned_staff ?? 'Not assigned' }}</p>
                                <p><strong>Current Reviewer:</strong> {{ $review->reviewer?->name }}
                                    ({{ $review->reviewer?->department?->name }})</p>
                                <p><strong>Submitted:</strong> {{ $review->submitted_at->format('M d, Y H:i') }}</p>
                            </div>

                            <div>
                                <h3 class="mb-2 text-lg font-semibold">Review Status & Timing</h3>
                                <p><strong>Status:</strong>
                                    <span class="badge 
                                        @if ($review->status === 'pending') badge-warning
                                        @elseif($review->status === 'approved') badge-success
                                        @elseif($review->status === 'rejected') badge-error
                                        @elseif($review->status === 'canceled') badge-neutral
                                        @elseif($review->status === 'overdue') badge-error
                                        @else badge-info @endif">
                                        {{ ucfirst($review->status) }}
                                    </span>
                                </p>
                                
                                <!-- Time Information -->
                                <p><strong>Allocated Time:</strong> 
                                    {{ formatTime($review->time_value ?? $review->process_time, $review->time_unit ?? 'minutes') }}
                                </p>
                                
                                @if ($review->status === 'pending')
                                    @if(!$review->is_overdue)
                                        <p><strong>Time Remaining:</strong> 
                                            <span class="text-warning">{{ formatRemainingTime($review->remaining_time_minutes) }}</span>
                                        </p>
                                    @else
                                        <p class="text-error"><strong>Status:</strong> 
                                            <span class="badge badge-error">Overdue by {{ formatRemainingTime(abs($review->remaining_time_minutes)) }}</span>
                                        </p>
                                    @endif
                                @endif
                                
                                @if ($review->reviewed_at)
                                    <p><strong>Reviewed At:</strong> {{ $review->reviewed_at->format('M d, Y H:i') }}</p>
                                    <p><strong>Time Accomplished:</strong> 
                                        <span class="badge badge-success">{{ formatAccomplishedTime($review->submitted_at, $review->reviewed_at) }}</span>
                                    </p>
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

                        <!-- Attachment Display -->
                        @if($review->attachment_path)
                            <div class="mb-6">
                                <h4 class="font-semibold mb-2">Attachment</h4>
                                <a href="{{ Storage::url($review->attachment_path) }}" target="_blank" 
                                   class="btn btn-outline btn-sm">
                                    <i data-lucide="paperclip" class="w-4 h-4 mr-2"></i>
                                    View Attachment
                                </a>
                            </div>
                        @endif

                        <!-- Review Actions for Heads Only -->
                        @if (auth()->user()->type === 'Head' && $review->assigned_to === auth()->id() && $review->status === 'pending')
                            <div class="divider">Review Actions (Department Head Only)</div>

                            <form action="{{ route('documents.reviews.update', $review->id) }}" method="POST">
                                @csrf
                                @method('PUT')

                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                    <!-- Review Action -->
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="font-semibold label-text">Review Action *</span>
                                        </label>
                                        <select name="action" class="select select-bordered" required
                                            onchange="toggleActionOptions(this.value)">
                                            <option value="">Select Action</option>
                                            <option value="forward">Forward Review</option>
                                            <option value="complete">Complete Review</option>
                                            <option value="reject">Reject Review</option>
                                            <option value="cancel">Cancel Review</option>
                                        </select>
                                    </div>

                                    <!-- Assign Staff -->
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="font-semibold label-text">Assign/Reassign Staff</span>
                                        </label>
                                        <select name="assigned_staff" class="select select-bordered">
                                            <option value="">Keep current assignment ({{ $review->assigned_staff ?? 'None' }})</option>
                                            @php
                                                $assignedStaff = [
                                                    ['id' => 1, 'name' => 'John Doe', 'position' => 'Document Processor'],
                                                    ['id' => 2, 'name' => 'Jane Smith', 'position' => 'Senior Clerk'],
                                                    ['id' => 3, 'name' => 'Mike Johnson', 'position' => 'Administrative Assistant'],
                                                    ['id' => 4, 'name' => 'Sarah Wilson', 'position' => 'Records Officer'],
                                                    ['id' => 5, 'name' => 'David Brown', 'position' => 'Document Specialist'],
                                                ];
                                            @endphp
                                            @foreach($assignedStaff as $staff)
                                                <option value="{{ $staff['name'] }}" 
                                                    {{ $review->assigned_staff === $staff['name'] ? 'selected' : '' }}>
                                                    {{ $staff['name'] }} - {{ $staff['position'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <label class="label">
                                            <span class="label-text-alt">Optional: Reassign to different staff member</span>
                                        </label>
                                    </div>
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
                                                    <span class="font-semibold label-text">Forward To Department Head *</span>
                                                </label>
                                                <select name="forward_to" class="select select-bordered">
                                                    <option value="">Select Department Head</option>
                                                    @foreach (\App\Models\User::with('department')->where('type', 'Head')->where('id', '!=', auth()->id())->get()->groupBy('department.name') as $deptName => $users)
                                                        <optgroup label="{{ $deptName ?? 'No Department' }}">
                                                            @foreach ($users as $user)
                                                                <option value="{{ $user->id }}">{{ $user->name }}</option>
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

                                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                                <div class="form-control">
                                                    <label class="label">
                                                        <span class="font-semibold label-text">Time Value *</span>
                                                    </label>
                                                    <input type="number" name="forward_time_value" min="1" class="input input-bordered" placeholder="Enter time">
                                                </div>
                                                <div class="form-control">
                                                    <label class="label">
                                                        <span class="font-semibold label-text">Time Unit *</span>
                                                    </label>
                                                    <select name="forward_time_unit" class="select select-bordered">
                                                        <option value="minutes">Minutes</option>
                                                        <option value="days">Days</option>
                                                        <option value="weeks">Weeks</option>
                                                    </select>
                                                </div>
                                                <div class="form-control">
                                                    <label class="label">
                                                        <span class="font-semibold label-text">Assign Staff for Next Dept</span>
                                                    </label>
                                                    <select name="forward_assigned_staff" class="select select-bordered">
                                                        <option value="">Auto-assign</option>
                                                        @foreach($assignedStaff as $staff)
                                                            <option value="{{ $staff['name'] }}">
                                                                {{ $staff['name'] }} - {{ $staff['position'] }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Complete Options -->
                                <div id="complete_options" style="display: none;">
                                    <div class="mb-4 border border-green-200 card bg-green-50">
                                        <div class="card-body">
                                            <h4 class="text-green-800 card-title">Complete Review</h4>
                                            <p class="text-sm text-green-600">
                                                This will mark the document as approved and ready for download.
                                            </p>
                                            
                                            <div class="mt-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Final Staff Assignment</span>
                                                </label>
                                                <select name="final_assigned_staff" class="select select-bordered">
                                                    <option value="">Keep current assignment ({{ $review->assigned_staff ?? 'None' }})</option>
                                                    @foreach($assignedStaff as $staff)
                                                        <option value="{{ $staff['name'] }}" 
                                                            {{ $review->assigned_staff === $staff['name'] ? 'selected' : '' }}>
                                                            {{ $staff['name'] }} - {{ $staff['position'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                                <label class="label">
                                                    <span class="label-text-alt">Staff responsible for final document preparation</span>
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Reject Options -->
                                <div id="reject_options" style="display: none;">
                                    <div class="mb-4 border border-red-200 card bg-red-50">
                                        <div class="card-body">
                                            <h4 class="text-red-800 card-title">Reject Document</h4>
                                            <p class="text-sm text-red-600">
                                                This will reject the document and send it back to the creator.
                                            </p>
                                            
                                            <div class="mt-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Rejection Reason *</span>
                                                </label>
                                                <select name="rejection_reason" class="select select-bordered">
                                                    <option value="">Select reason</option>
                                                    <option value="incomplete_information">Incomplete Information</option>
                                                    <option value="invalid_documents">Invalid Documents</option>
                                                    <option value="does_not_meet_requirements">Does Not Meet Requirements</option>
                                                    <option value="missing_attachments">Missing Attachments</option>
                                                    <option value="other">Other (specify in notes)</option>
                                                </select>
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
                                        $stepStatus = $step['status'] ?? 'completed';
                                        $stepNumber = $step['step'] ?? $index + 1;
                                        $stepAction = $step['action'] ?? 'forwarded';
                                        $stepTimestamp = $step['timestamp'] ?? ($step['forwarded_at'] ?? now());
                                        $fromUserName = $step['from_user_name'] ?? 'Unknown User';
                                        $fromUserType = $step['from_user_type'] ?? 'Staff';
                                        $fromDepartment = $step['from_department'] ?? null;
                                        $toUserName = $step['to_user_name'] ?? null;
                                        $toUserType = $step['to_user_type'] ?? null;
                                        $toDepartment = $step['to_department'] ?? null;
                                        $allocatedTime = $step['allocated_time'] ?? null;
                                        $accomplishedTime = $step['accomplished_time'] ?? null;
                                        $isOverdue = $step['is_overdue'] ?? false;
                                        $assignedStaff = $step['assigned_staff'] ?? null;
                                        $notes = $step['notes'] ?? null;
                                    @endphp

                                    <div class="border-l-4 
                                        @if ($stepStatus === 'completed') border-success
                                        @elseif($stepStatus === 'pending' && !$isOverdue) border-warning
                                        @elseif($stepStatus === 'pending' && $isOverdue) border-error
                                        @elseif($stepStatus === 'overdue') border-error
                                        @else border-gray-300 @endif pl-4 pb-4">

                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="badge 
                                                @if ($stepStatus === 'completed') badge-success
                                                @elseif($stepStatus === 'pending' && !$isOverdue) badge-warning
                                                @elseif($stepStatus === 'pending' && $isOverdue) badge-error
                                                @elseif($stepStatus === 'overdue') badge-error
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
                                            @elseif($stepStatus === 'pending' && !$isOverdue)
                                                <svg xmlns="http://www.w3.org/2000/svg"
                                                    class="w-4 h-4 text-warning animate-spin" fill="none"
                                                    viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                                </svg>
                                            @elseif($isOverdue || $stepStatus === 'overdue')
                                                <span class="badge badge-error badge-xs">OVERDUE</span>
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

                                            @if ($assignedStaff)
                                                <p><strong>Staff:</strong> 
                                                    <span class="badge badge-info badge-xs">{{ $assignedStaff }}</span>
                                                </p>
                                            @endif

                                            @if ($allocatedTime)
                                                <p><strong>Allocated Time:</strong> {{ $allocatedTime }}</p>
                                            @endif

                                            @if ($accomplishedTime)
                                                <p><strong>Time Accomplished:</strong> 
                                                    <span class="badge badge-success badge-xs">{{ $accomplishedTime }}</span>
                                                </p>
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
                                    <span class="badge 
                                        @if ($review->status === 'pending') badge-warning
                                        @elseif($review->status === 'approved') badge-success
                                        @elseif($review->status === 'rejected') badge-error
                                        @elseif($review->status === 'downloaded') badge-info
                                        @elseif($review->status === 'overdue') badge-error
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
                            <!-- Default tracking display -->
                            <div class="space-y-4">
                                <div class="border-l-4 border-success pl-4 pb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="badge badge-success badge-sm">1</div>
                                        <span class="text-sm font-semibold">
                                            {{ $review->submitted_at->format('M d, H:i') }}
                                        </span>
                                        <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 text-success"
                                            fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M5 13l4 4L19 7" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <p><strong>Action:</strong> Document Submitted</p>
                                        <p><strong>Submitted:</strong> {{ $review->submitted_at->format('M d, Y H:i') }}</p>
                                        @if ($review->assigned_staff)
                                            <p><strong>Staff:</strong> 
                                                <span class="badge badge-info badge-xs">{{ $review->assigned_staff }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>

                                <div class="border-l-4 border-warning pl-4 pb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="badge badge-warning badge-sm">2</div>
                                        <span class="text-sm font-semibold">In Progress</span>
                                        <svg xmlns="http://www.w3.org/2000/svg"
                                            class="w-4 h-4 text-warning animate-spin" fill="none"
                                            viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                        </svg>
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <p><strong>Action:</strong> Under Review</p>
                                        <p><strong>Reviewer:</strong> {{ $review->reviewer->name }}
                                            <span class="badge badge-xs">{{ $review->reviewer->type }}</span>
                                        </p>
                                        @if ($review->reviewer->department)
                                            <p><strong>Dept:</strong> {{ $review->reviewer->department->name }}</p>
                                        @endif
                                        @if ($review->assigned_staff)
                                            <p><strong>Staff:</strong> 
                                                <span class="badge badge-info badge-xs">{{ $review->assigned_staff }}</span>
                                            </p>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript for Form Controls -->
    <script>
        function toggleActionOptions(action) {
            // Hide all option divs
            document.getElementById('forward_options').style.display = 'none';
            document.getElementById('complete_options').style.display = 'none';
            document.getElementById('reject_options').style.display = 'none';
            
            // Show relevant option div
            if (action === 'forward') {
                document.getElementById('forward_options').style.display = 'block';
            } else if (action === 'complete') {
                document.getElementById('complete_options').style.display = 'block';
            } else if (action === 'reject') {
                document.getElementById('reject_options').style.display = 'block';
            }
        }
    </script>

    @php
        // Helper function to format time display
        function formatTime($value, $unit) {
            return $value . ' ' . ucfirst($unit);
        }

        function formatRemainingTime($minutes) {
            if ($minutes < 60) {
                return $minutes . ' minute' . ($minutes != 1 ? 's' : '');
            } elseif ($minutes < 1440) { // Less than a day
                $hours = floor($minutes / 60);
                return $hours . ' hour' . ($hours != 1 ? 's' : '');
            } else {
                $days = floor($minutes / 1440);
                return $days . ' day' . ($days != 1 ? 's' : '');
            }
        }

        function formatAccomplishedTime($startTime, $endTime) {
            $diff = $endTime->diff($startTime);
            
            if ($diff->d > 0) {
                return $diff->d . ' day' . ($diff->d > 1 ? 's' : '');
            } elseif ($diff->h > 0) {
                return $diff->h . ' hour' . ($diff->h > 1 ? 's' : '') . ' ' . $diff->i . ' min';
            } else {
                return $diff->i . ' minute' . ($diff->i != 1 ? 's' : '');
            }
        }
    @endphp
@endsection
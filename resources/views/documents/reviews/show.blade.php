@extends('layouts.app')
@use('Illuminate\Support\Facades\Storage')
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
                                    <h3 class="font-bold">Document is Complete!</h3>
                                    <div class="text-sm">Review process completed. The document can now be finished.</div>
                                </div>
                            </div>
                        @endif

                        <div class="grid grid-cols-1 gap-6 mb-6 md:grid-cols-2">
                            <div>
                                <h3 class="mb-2 text-lg font-semibold">Document Information</h3>
                                <p><strong>Type:</strong> {{ $review->document_type }}</p>
                                <p><strong>Client:</strong> {{ $review->client_name }}</p>
                                <p><strong>Difficulty:</strong> 
                                    @php
                                        $difficulty = $review->difficulty ?? 'normal';
                                        $badgeClass = match($difficulty) {
                                            'normal' => 'badge badge-success',
                                            'important' => 'badge badge-warning', 
                                            'urgent' => 'badge badge-error',
                                            'immediate' => 'badge badge-error',
                                            default => 'badge badge-neutral',
                                        };
                                    @endphp
                                    <span class="{{ $badgeClass }}">
                                        {{ ucfirst($difficulty) }}
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
                                        @elseif($review->status === 'completed') badge-primary
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
                                    @if($review->time_value && $review->time_unit)
                                        {{ formatTime($review->time_value, $review->time_unit) }}
                                    @elseif($review->process_time_minutes)
                                        {{ formatTime($review->process_time_minutes, 'minutes') }}
                                    @else
                                        Not specified
                                    @endif
                                </p>
                                
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
                                    @if (!in_array($key, ['action', 'reviewer_id', 'initial_notes']))
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
                                            <option value="forward">Forward</option>
                                            <option value="complete">Complete</option>
                                            <option value="approve">Approve</option>
                                            <option value="reject">Reject</option>
                                            <option value="cancel">Cancel</option>
                                        </select>
                                    </div>

                                    <!-- Assign Staff -->
                                    <div class="form-control">
                                        <label class="label">
                                            <span class="font-semibold label-text">Assign/Reassign Staff</span>
                                        </label>
                                        <select name="assigned_staff" class="select select-bordered">
                                            <option value="">Keep current assignment ({{ $review->assigned_staff ?? 'None' }})</option>
                                            @foreach($assignedStaff as $staff)
                                                <option value="{{ $staff['full_name'] }}" 
                                                    {{ $review->assigned_staff === $staff['full_name'] ? 'selected' : '' }}>
                                                    {{ $staff['full_name'] }} - {{ $staff['position'] }}
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
                                                            <option value="{{ $staff['full_name'] }}">
                                                                {{ $staff['full_name'] }} - {{ $staff['position'] }}
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
                                                This will mark the document as approved.
                                            </p>
                                            
                                            <div class="mt-4 form-control">
                                                <label class="label">
                                                    <span class="font-semibold label-text">Final Staff Assignment</span>
                                                </label>
                                                <select name="final_assigned_staff" class="select select-bordered">
                                                    <option value="">Keep current assignment ({{ $review->assigned_staff ?? 'None' }})</option>
                                                    @foreach($assignedStaff as $staff)
                                                        <option value="{{ $staff['full_name'] }}" 
                                                            {{ $review->assigned_staff === $staff['full_name'] ? 'selected' : '' }}>
                                                            {{ $staff['full_name'] }} - {{ $staff['position'] }}
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
                                        $remainingTime = $step['remaining_time_minutes'] ?? null;
                                        $isPendingStep = $stepStatus === 'pending';
                                    @endphp

                                    <div class="border-l-4 
                                        @if ($stepStatus === 'completed' || $stepStatus === 'forwarded' || $stepStatus === 'approved') border-success
                                        @elseif($stepStatus === 'pending' && !$isOverdue) border-warning
                                        @elseif($stepStatus === 'pending' && $isOverdue) border-error
                                        @elseif($stepStatus === 'overdue') border-error
                                        @else border-success @endif pl-4 pb-4">

                                        <div class="flex items-center gap-2 mb-2">
                                            <div class="badge 
                                                @if ($stepStatus === 'completed' || $stepStatus === 'forwarded' || $stepStatus === 'approved') badge-success
    
                                                @elseif($stepStatus === 'pending' && !$isOverdue) badge-warning
                                                @elseif($stepStatus === 'pending' && $isOverdue) badge-error
                                                @elseif($stepStatus === 'overdue') badge-error
                                                @else badge-success @endif badge-sm">
                                                {{ $stepNumber }}
                                            </div>

                                            <span class="text-sm font-semibold">
                                                {{ \Carbon\Carbon::parse($stepTimestamp)->format('M d, H:i') }}
                                            </span>

                                            @if ($stepStatus === 'completed' || $stepStatus === 'forwarded' || $stepStatus === 'approved')
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
                                                <span class="badge badge-error badge-xs animate-pulse">OVERDUE</span>
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
                                                    <p><strong>Dept:</strong> 
                                                        <span class="font-semibold">{{ $toDepartment }}</span>
                                                    </p>
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

                                            <!-- Show countdown/overdue for pending steps -->
                                            @if ($isPendingStep)
                                                @if ($isOverdue && $remainingTime)
                                                    <div class="p-2 mt-2 border border-red-300 rounded bg-red-50">
                                                        <p class="text-sm font-bold text-red-700">
                                                            <i class="mr-1">⚠️</i>
                                                            OVERDUE by {{ formatRemainingTime(round(abs($remainingTime))) }}
                                                        </p>
                                                        <p class="text-xs text-red-600">
                                                            Department: <strong>{{ $toDepartment ?? $fromDepartment }}</strong>
                                                        </p>
                                                    </div>
                                                @elseif (!$isOverdue && $remainingTime && $remainingTime > 0)
                                                    <div class="p-2 mt-2 border border-yellow-300 rounded bg-yellow-50">
                                                        <p class="text-sm font-bold text-yellow-700">
                                                            <i class="mr-1">⏱️</i>
                                                            Time Remaining: 
                                                            <span class="countdown-timer" 
                                                                  data-remaining-minutes="{{ round($remainingTime) }}"
                                                                  data-step="{{ $stepNumber }}">
                                                                {{ formatRemainingTime(round($remainingTime)) }}
                                                            </span>
                                                        </p>
                                                        <p class="text-xs text-yellow-600">
                                                            Department: <strong>{{ $toDepartment ?? $fromDepartment }}</strong>
                                                        </p>
                                                    </div>
                                                @endif
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

                                <!-- Overall countdown for current status -->
                                @if ($review->status === 'pending')
                                    @if (!$review->is_overdue && $review->remaining_time_minutes > 0)
                                        <div class="mt-2 text-xs">
                                            <strong>Overall Time Remaining:</strong>
                                            <span class="text-warning font-bold" id="countdown-timer" 
                                                  data-remaining-minutes="{{ $review->remaining_time_minutes }}">
                                                {{ formatRemainingTime($review->remaining_time_minutes) }}
                                            </span>
                                        </div>
                                    @elseif ($review->is_overdue)
                                        <div class="mt-2 text-xs">
                                            <span class="text-error font-bold animate-pulse">
                                                ⚠️ OVERDUE by {{ formatRemainingTime(round(abs($review->remaining_time_minutes))) }}
                                            </span>
                                        </div>
                                    @endif
                                @endif

                                @if ($review->current_step ?? false)
                                    <div class="mt-1 text-xs text-base-content/70">
                                        Step {{ $review->current_step['step'] ?? 'N/A' }}:
                                        {{ ucwords(str_replace('_', ' ', $review->current_step['action'] ?? 'unknown')) }}
                                    </div>
                                @endif
                            </div>
                        @else
                            <!-- Default tracking display with countdown -->
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

                                <div class="border-l-4 
                                    @if ($review->is_overdue) border-error
                                    @else border-warning @endif pl-4 pb-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="badge 
                                            @if ($review->is_overdue) badge-error
                                            @else badge-warning @endif badge-sm">2</div>
                                        <span class="text-sm font-semibold">In Progress</span>
                                        @if ($review->is_overdue)
                                            <span class="badge badge-error badge-xs animate-pulse">OVERDUE</span>
                                        @else
                                            <svg xmlns="http://www.w3.org/2000/svg"
                                                class="w-4 h-4 text-warning animate-spin" fill="none"
                                                viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                            </svg>
                                        @endif
                                    </div>
                                    <div class="space-y-1 text-sm">
                                        <p><strong>Action:</strong> Under Review</p>
                                        <p><strong>Reviewer:</strong> {{ $review->reviewer->name }}
                                            <span class="badge badge-xs">{{ $review->reviewer->type }}</span>
                                        </p>
                                        @if ($review->reviewer->department)
                                            <p><strong>Dept:</strong> 
                                                <span class="font-semibold">{{ $review->reviewer->department->name }}</span>
                                            </p>
                                        @endif
                                        @if ($review->assigned_staff)
                                            <p><strong>Staff:</strong> 
                                                <span class="badge badge-info badge-xs">{{ $review->assigned_staff }}</span>
                                            </p>
                                        @endif

                                        <!-- Current department countdown/overdue status -->
                                        @if ($review->status === 'pending')
                                            @if ($review->is_overdue)
                                                <div class="p-2 mt-2 border border-red-300 rounded bg-red-50">
                                                    <p class="text-sm font-bold text-red-700">
                                                        <i class="mr-1">⚠️</i>
                                                        OVERDUE by {{ formatRemainingTime(abs($review->remaining_time_minutes)) }}
                                                    </p>
                                                    <p class="text-xs text-red-600">
                                                        This department is taking too long!
                                                    </p>
                                                </div>
                                            @else
                                                <div class="p-2 mt-2 border border-yellow-300 rounded bg-yellow-50">
                                                    <p class="text-sm font-bold text-yellow-700">
                                                        <i class="mr-1">⏱️</i>
                                                        Time Remaining: 
                                                        <span id="countdown-timer" 
                                                              data-remaining-minutes="{{ $review->remaining_time_minutes }}">
                                                            {{ formatRemainingTime($review->remaining_time_minutes) }}
                                                        </span>
                                                    </p>
                                                </div>
                                            @endif
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

        document.addEventListener('DOMContentLoaded', function() {
            const countdownElements = document.querySelectorAll('[id="countdown-timer"], .countdown-timer');
            
            if (countdownElements.length > 0) {
                const reviewId = {{ $review->id }};
                let countdownInterval;
                let localCountdowns = new Map(); // Store local countdown states
                
                function fetchRemainingTime() {
                    fetch(`/documents/reviews/${reviewId}/remaining-time`, {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            countdownElements.forEach(element => {
                                const remainingMinutes = Math.max(0, Math.round(data.remaining_minutes));
                                localCountdowns.set(element, remainingMinutes);
                                updateCountdownDisplay(element, remainingMinutes, data.is_overdue);
                            });
                        } else {
                            console.error('Error fetching time:', data.message);
                        }
                    })
                    .catch(error => {
                        console.error('Ajax error:', error);
                    });
                }
                
                function updateCountdownDisplay(element, remainingMinutes, isOverdue) {
                    // Ensure we're working with rounded integers
                    remainingMinutes = Math.round(Math.abs(remainingMinutes));
                    
                    if (isOverdue || remainingMinutes <= 0) {
                        element.textContent = '0m';
                        element.className = element.className.replace(/text-\w+/, 'text-error') + ' font-bold animate-pulse';
                        // Optionally reload page after a delay
                        setTimeout(() => location.reload(), 2000);
                        return;
                    }
                    
                    const hours = Math.floor(remainingMinutes / 60);
                    const minutes = remainingMinutes % 60;
                    
                    let displayText;
                    if (hours > 0) {
                        displayText = `${hours}h ${minutes}m`;
                    } else {
                        displayText = `${minutes}m`;
                    }
                    
                    element.textContent = displayText;
                    
                    // Change color as time gets critical
                    if (remainingMinutes <= 10) {
                        element.className = element.className.replace(/text-\w+/, 'text-error') + ' font-bold animate-pulse';
                    } else if (remainingMinutes <= 30) {
                        element.className = element.className.replace(/text-\w+/, 'text-error') + ' font-bold';
                    } else if (remainingMinutes <= 60) {
                        element.className = element.className.replace(/text-\w+/, 'text-warning') + ' font-semibold';
                    }
                }
                
                // Local countdown function (runs every minute between server updates)
                function localCountdownTick() {
                    countdownElements.forEach(element => {
                        const currentMinutes = localCountdowns.get(element);
                        if (currentMinutes > 0) {
                            const newMinutes = currentMinutes - 1;
                            localCountdowns.set(element, newMinutes);
                            updateCountdownDisplay(element, newMinutes, newMinutes <= 0);
                        }
                    });
                }
                
                // Initial fetch
                fetchRemainingTime();
                
                // Update from server every 5 minutes to stay in sync
                const serverSyncInterval = setInterval(fetchRemainingTime, 300000); // 5 minutes
                
                // Local countdown every minute
                const localCountdownInterval = setInterval(localCountdownTick, 60000); // 1 minute
                
                // Clear intervals when page is hidden/unloaded
                document.addEventListener('visibilitychange', function() {
                    if (document.hidden) {
                        clearInterval(serverSyncInterval);
                        clearInterval(localCountdownInterval);
                    } else {
                        // Resume when page becomes visible again
                        fetchRemainingTime();
                        clearInterval(serverSyncInterval);
                        clearInterval(localCountdownInterval);
                        const newServerSyncInterval = setInterval(fetchRemainingTime, 300000);
                        const newLocalCountdownInterval = setInterval(localCountdownTick, 60000);
                    }
                });
                
                window.addEventListener('beforeunload', function() {
                    clearInterval(serverSyncInterval);
                    clearInterval(localCountdownInterval);
                });
            }
        });
    </script>
@endsection
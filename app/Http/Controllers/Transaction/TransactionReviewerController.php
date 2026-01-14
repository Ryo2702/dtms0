<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Services\Transaction\WorkflowEngineService;
use Illuminate\Http\Request;

class TransactionReviewerController extends Controller
{
    protected WorkflowEngineService $workflowEngine;

    public function __construct(WorkflowEngineService $workflowEngine)
    {
        $this->workflowEngine = $workflowEngine;
    }

    /**
     * List pending reviews for the authenticated user
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $tab = $request->get('tab', 'pending');

        // Pending reviews - only transactions with pending status
        $pendingReviews = TransactionReviewer::with([
            'transaction.workflow', 
            'transaction.creator', 
            'transaction.department',
            'transaction.originDepartment',
            'department', 
            'receivedBy',
            'previousReviewer',
            'previousReviewer.department'
        ])
            ->forReviewer($userId)
            ->where('status', 'pending')
            ->orderBy('due_date', 'asc')
            ->orderBy('reviewed_at', 'desc')
            ->get();

        // Reviewed by me - transactions I've already reviewed (approved/rejected)
        $reviewedByMe = TransactionReviewer::with([
            'transaction.workflow', 
            'transaction.creator', 
            'transaction.department',
            'transaction.originDepartment',
            'department', 
            'receivedBy',
            'previousReviewer',
            'previousReviewer.department'
        ])
            ->forReviewer($userId)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('reviewed_at', 'desc')
            ->limit(20)
            ->get();

        // Resubmissions - only pending resubmissions
        $resubmissions = TransactionReviewer::with([
            'transaction.workflow', 
            'transaction.creator', 
            'transaction.department',
            'transaction.originDepartment',
            'department', 
            'receivedBy',
            'previousReviewer',
            'previousReviewer.department'
        ])
            ->forReviewer($userId)
            ->where('status', 'pending')
            ->where('iteration_number', '>', 1)
            ->orderBy('due_date', 'asc')
            ->orderBy('reviewed_at', 'desc')
            ->get();

        // Stats - count only pending for accurate stats
        $pendingCount = $pendingReviews->where('status', 'pending')->count();
        $stats = [
            'pending' => $pendingCount,
            'due_today' => $pendingReviews->where('status', 'pending')->filter(fn($r) => $r->due_date && $r->due_date->isToday())->count(),
            'overdue' => $pendingReviews->where('status', 'pending')->filter(fn($r) => $r->isOverdue())->count(),
            'resubmissions' => $resubmissions->where('status', 'pending')->count(),
            'reviewed' => $reviewedByMe->count(),
        ];

        return view('transactions.reviews.index', compact(
            'pendingReviews', 
            'reviewedByMe', 
            'resubmissions',
            'stats',
            'tab'
        ));
    }

    /**
     * List overdue reviews (for admin/head monitoring)
     */
    public function overdue(Request $request)
    {
        $overdueReviews = TransactionReviewer::with(['transaction', 'reviewer', 'department'])
            ->overdue()
            ->orderBy('due_date', 'asc')
            ->paginate(10);

        return view('transactions.reviews.overdue', compact('overdueReviews'));
    }

    /**
     * Show review details for a specific transaction reviewer entry
     */
    public function show(TransactionReviewer $reviewer)
    {
        $reviewer->load([
            'transaction.workflow', 
            'transaction.originDepartment',
            'transaction.department',
            'reviewer', 
            'department', 
            'previousReviewer'
        ]);

        // Get workflow progress
        $workflowProgress = $this->workflowEngine->getWorkflowProgress($reviewer->transaction);

        return view('transactions.reviews.show', compact('reviewer', 'workflowProgress'));
    }

    /**
     * Show the review action page where user can approve/reject
     */
    public function review(Request $request, TransactionReviewer $reviewer)
    {
        // Ensure the current user is the assigned reviewer
        if ($reviewer->reviewer_id !== $request->user()->id) {
            abort(403, 'You are not authorized to review this transaction.');
        }

        // Ensure the review is still pending
        if ($reviewer->status !== 'pending') {
            return redirect()->route('transactions.reviews.show', $reviewer)
                ->with('error', 'This review has already been processed.');
        }

        // Ensure the transaction has been received
        if ($reviewer->received_status !== 'received') {
            return redirect()->route('transactions.reviews.index')
                ->with('error', 'This transaction must be marked as received before it can be reviewed.');
        }

        $reviewer->load(['transaction.workflow', 'transaction.creator', 'transaction.department', 'department', 'previousReviewer']);

        // Get workflow progress
        $workflowProgress = $this->workflowEngine->getWorkflowProgress($reviewer->transaction);

        // Get next reviewer if this is not the last step
        $nextReviewer = null;
        $transaction = $reviewer->transaction;
        $steps = $transaction->workflow_snapshot['steps'] ?? $transaction->workflow->getWorkflowSteps();
        $currentStep = $transaction->current_workflow_step;
        $isLastStep = $currentStep >= count($steps);

        if (!$isLastStep && $currentStep < count($steps)) {
            $nextStep = $steps[$currentStep]; // Next step (0-indexed, current_workflow_step is 1-indexed)
            $nextDepartmentId = $nextStep['department_id'];
            $nextReviewer = \App\Models\User::where('department_id', $nextDepartmentId)
                ->where(function($q) {
                    $q->where('type', 'Head')->orWhere('type', 'Staff');
                })
                ->first();
        }

        return view('transactions.reviews.review', compact('reviewer', 'workflowProgress', 'nextReviewer', 'isLastStep'));
    }

    /**
     * Approve the transaction review
     */
    public function approve(Request $request, TransactionReviewer $reviewer)
    {
        // Ensure the current user is the assigned reviewer
        if ($reviewer->reviewer_id !== $request->user()->id) {
            abort(403, 'You are not authorized to review this transaction.');
        }

        // Ensure the review is still pending
        if ($reviewer->status !== 'pending') {
            return redirect()->route('transactions.reviews.index')
                ->with('error', 'This review has already been processed.');
        }

        // Ensure the transaction has been received
        if ($reviewer->received_status !== 'received') {
            return redirect()->route('transactions.reviews.index')
                ->with('error', 'This transaction must be marked as received before it can be reviewed.');
        }

        $validated = $request->validate([
            'remarks' => 'nullable|string|max:1000',
        ]);

        // Update the reviewer record
        $reviewer->update([
            'status' => 'approved',
            'reviewed_at' => now(),
        ]);

        // Execute the workflow action to move to next department
        try {
            $transaction = $reviewer->transaction;
            $workflow = $transaction->workflow;
            $steps = $transaction->workflow_snapshot['steps'] ?? $workflow->getWorkflowSteps();
            $currentStep = $transaction->current_workflow_step;
            $isLastStep = $currentStep >= count($steps);

            // Execute the workflow state transition
            $this->workflowEngine->executeAction(
                $transaction,
                'approve',
                $request->user(),
                $validated['remarks'] ?? null
            );

            // Refresh transaction to get updated state from workflow engine
            $transaction->refresh();

            // Check if this is the final step (workflow completed)
            if ($isLastStep) {
                // Workflow is completed - set receiving status to pending for origin department
                $transaction->update([
                    'receiving_status' => 'pending',
                    'department_id' => $transaction->origin_department_id, // Return to origin department
                ]);
                $transaction->refresh();
            } else {
                // Move to the next workflow step
                $transaction->increment('current_workflow_step');
                
                // Refresh the transaction to get the updated current_workflow_step
                $transaction->refresh();
                
                // Assign the next reviewer based on workflow configuration
                $this->assignNextReviewer($transaction, $reviewer, $reviewer->iteration_number);
            }

        } catch (\Exception $e) {
            return redirect()->route('transactions.reviews.index')
                ->with('error', 'Failed to progress workflow: ' . $e->getMessage());
        }

        $successMessage = $isLastStep 
            ? 'Transaction approved and completed. Awaiting confirmation from origin department.'
            : 'Transaction approved and forwarded to the next department.';

        return redirect()->route('transactions.reviews.index')
            ->with('success', $successMessage);
    }

    /**
     * Assign the next reviewer based on workflow configuration
     * 
     * @param Transaction $transaction The transaction to assign reviewer for
     * @param TransactionReviewer $currentReviewer The reviewer who just approved
     * @param int $iterationNumber The current iteration number (for resubmissions)
     */
    protected function assignNextReviewer(Transaction $transaction, TransactionReviewer $currentReviewer, int $iterationNumber = 1): void
    {
        $workflow = $transaction->workflow;
        $steps = $transaction->workflow_snapshot['steps'] ?? $workflow->getWorkflowSteps();
        $currentStep = $transaction->current_workflow_step; // 1-indexed after increment

        // The current step after increment points to the next department (1-indexed)
        // So we need to use (currentStep - 1) as 0-indexed array access
        $nextStepIndex = $currentStep - 1;

        // Check if the next step exists
        if (!isset($steps[$nextStepIndex])) {
            return; // No more steps, workflow should be completed
        }

        $nextStep = $steps[$nextStepIndex];
        $nextDepartmentId = $nextStep['department_id'];

        // Calculate due date based on step's process time configuration
        $processTimeValue = $nextStep['process_time_value'] ?? 3;
        $processTimeUnit = $nextStep['process_time_unit'] ?? 'days';
        $dueDate = $this->calculateDueDate($processTimeValue, $processTimeUnit);

        // Find the department head (or designated reviewer) for the next department
        $nextReviewerUser = \App\Models\User::where('department_id', $nextDepartmentId)
            ->where('type', 'Head')
            ->first();

        if ($nextReviewerUser) {
            // Create a new reviewer entry for the next department
            // Set received_status to 'received' since it's automatically forwarded from the previous step
            // Set previous_reviewer_id to track who approved it before
            TransactionReviewer::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $nextReviewerUser->id,
                'department_id' => $nextDepartmentId,
                'status' => 'pending',
                'received_status' => 'received',
                'received_by' => $nextReviewerUser->id,
                'received_at' => now(),
                'due_date' => $dueDate,
                'iteration_number' => $iterationNumber,
                'previous_reviewer_id' => $currentReviewer->reviewer_id,
            ]);

            // Update transaction's current department
            $transaction->update([
                'department_id' => $nextDepartmentId,
            ]);
        }
    }

    /**
     * Calculate due date based on process time configuration
     */
    protected function calculateDueDate(int $value, string $unit): \Carbon\Carbon
    {
        return match ($unit) {
            'hours' => now()->addHours($value),
            'weeks' => now()->addWeeks($value),
            default => now()->addDays($value),
        };
    }

    /**
     * Reject the transaction review
     */
    public function reject(Request $request, TransactionReviewer $reviewer)
    {
        // Ensure the current user is the assigned reviewer
        if ($reviewer->reviewer_id !== $request->user()->id) {
            abort(403, 'You are not authorized to review this transaction.');
        }

        // Ensure the review is still pending
        if ($reviewer->status !== 'pending') {
            return redirect()->route('transactions.reviews.index')
                ->with('error', 'This review has already been processed.');
        }

        // Ensure the transaction has been received
        if ($reviewer->received_status !== 'received') {
            return redirect()->route('transactions.reviews.index')
                ->with('error', 'This transaction must be marked as received before it can be reviewed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'resubmission_deadline' => 'nullable|date|after:today',
        ]);

        // Update the reviewer record with rejection details
        $reviewer->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'resubmission_deadline' => $validated['resubmission_deadline'] ?? null,
        ]);

        try {
            $transaction = $reviewer->transaction;
            $workflow = $transaction->workflow;
            $steps = $transaction->workflow_snapshot['steps'] ?? $workflow->getWorkflowSteps();
            $currentStep = $transaction->current_workflow_step; // 1-indexed
            
            // Get the previous step (return destination)
            $returnOptions = $this->workflowEngine->getReturnOptions($transaction);
            $returnToDepartmentId = !empty($returnOptions) ? $returnOptions[0]['department_id'] : null;

            // Execute the workflow state transition (reject)
            $this->workflowEngine->executeAction(
                $transaction,
                'reject',
                $request->user(),
                $validated['rejection_reason'],
                $returnToDepartmentId
            );

            // If rejected at first step, return to creator
            if ($currentStep === 1) {
                // Mark transaction as returned to creator
                $transaction->update([
                    'current_state' => 'returned_to_creator',
                    'department_id' => $transaction->origin_department_id, // Return to originating department
                ]);

                // Log the return to creator
                \App\Models\TransactionLog::create([
                    'transaction_id' => $transaction->id,
                    'from_state' => $transaction->current_state,
                    'to_state' => 'returned_to_creator',
                    'action' => 'reject_first_step',
                    'action_by' => $request->user()->id,
                    'remarks' => 'Transaction rejected at first step and returned to creator: ' . $validated['rejection_reason'],
                ]);

                // Notify the creator
                // \App\Helpers\NotificationHelper::notifyTransactionRejected(
                //     $transaction,
                //     $transaction->creator,
                //     $validated['rejection_reason']
                // );
            } 
            // Only decrement if we're not at the first step
            elseif ($currentStep > 1) {
                $transaction->decrement('current_workflow_step');
                
                // Assign the transaction back to the previous department for resubmission
                $this->assignPreviousReviewer($transaction, $reviewer, $validated['resubmission_deadline'] ?? null);
            }

        } catch (\Exception $e) {
            // Log the error but continue - the reviewer status is already updated
            \Log::error('Workflow rejection failed: ' . $e->getMessage());
        }

        return redirect()->route('transactions.reviews.index')
            ->with('success', 'Transaction rejected. The submitter will be notified.');
    }

    /**
     * Assign the transaction back to the previous department for resubmission
     * 
     * @param Transaction $transaction The transaction being rejected
     * @param TransactionReviewer $currentReviewer The reviewer who rejected
     * @param string|null $resubmissionDeadline Optional deadline for resubmission
     */
    protected function assignPreviousReviewer(
        Transaction $transaction, 
        TransactionReviewer $currentReviewer,
        ?string $resubmissionDeadline = null
    ): void {
        $workflow = $transaction->workflow;
        $steps = $transaction->workflow_snapshot['steps'] ?? $workflow->getWorkflowSteps();
        $currentStep = $transaction->current_workflow_step; // 1-indexed, after decrement

        // After decrement, current_workflow_step points to the previous department
        // Use (currentStep - 1) for 0-indexed array access
        $prevStepIndex = $currentStep - 1;
        
        if (!isset($steps[$prevStepIndex])) {
            return; // Cannot go back further (at first step)
        }

        $prevStep = $steps[$prevStepIndex];
        $prevDepartmentId = $prevStep['department_id'];

        // Calculate due date for resubmission
        $dueDate = $resubmissionDeadline 
            ? \Carbon\Carbon::parse($resubmissionDeadline)
            : $this->calculateDueDate(
                $prevStep['process_time_value'] ?? 3,
                $prevStep['process_time_unit'] ?? 'days'
            );

        // Find the department head for the previous department
        $prevReviewerUser = \App\Models\User::where('department_id', $prevDepartmentId)
            ->where('type', 'Head')
            ->first();

        if ($prevReviewerUser) {
            // Create a new reviewer entry for resubmission with incremented iteration
            TransactionReviewer::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $prevReviewerUser->id,
                'department_id' => $prevDepartmentId,
                'status' => 'pending',
                'due_date' => $dueDate,
                'iteration_number' => ($currentReviewer->iteration_number ?? 1) + 1,
                'previous_reviewer_id' => $currentReviewer->reviewer_id,
            ]);

            // Update transaction's current department
            $transaction->update([
                'department_id' => $prevDepartmentId,
            ]);
        }
    }

    /**
     * Update due date for a pending review (admin action)
     */
    public function updateDueDate(Request $request, TransactionReviewer $reviewer)
    {
        $validated = $request->validate([
            'due_date' => 'required|date|after:now',
        ]);

        $reviewer->update([
            'due_date' => $validated['due_date'],
            'is_overdue' => false, // Reset overdue flag if extending
        ]);

        return back()->with('success', 'Due date updated successfully.');
    }

    /**
     * Reassign reviewer (admin action)
     */
    public function reassign(Request $request, TransactionReviewer $reviewer)
    {
        $validated = $request->validate([
            'reviewer_id' => 'required|exists:users,id',
        ]);

        $reviewer->update([
            'previous_reviewer_id' => $reviewer->reviewer_id,
            'reviewer_id' => $validated['reviewer_id'],
        ]);

        return back()->with('success', 'Reviewer reassigned successfully.');
    }

    /**
     * Get review history for a transaction
     */
    public function history(Transaction $transaction)
    {
        $reviewHistory = $transaction->reviewers()
            ->with(['reviewer', 'department', 'previousReviewer'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('transactions.reviews.history', compact('transaction', 'reviewHistory'));
    }

    /**
     * Mark transaction as received or not received
     */
    public function receive(Request $request, TransactionReviewer $reviewer)
    {
        // Check if user is authorized (must be head or staff)
        $user = $request->user();
        if (!$user->isHead() && $user->type !== 'Staff') {
            return back()->with('error', 'Only department heads or staff can receive transactions.');
        }

        // Check if user is from the same department as the reviewer
        if ($reviewer->reviewer->department_id !== $user->department_id) {
            return back()->with('error', 'You can only receive transactions assigned to your department.');
        }

        $validated = $request->validate([
            'received_status' => 'required|in:received,not_received',
        ]);

        $reviewer->update([
            'received_status' => $validated['received_status'],
            'received_by' => $user->id,
            'received_at' => now(),
        ]);

        $message = $validated['received_status'] === 'received' 
            ? 'Transaction marked as received successfully. Timer started.' 
            : 'Transaction marked as not received.';

        return back()->with('success', $message);
    }

    /**
     * Mark transaction as received/not received for the next reviewer
     */
    public function nextReviewerReceive(Request $request, TransactionReviewer $reviewer)
    {
        // Ensure the current user is the assigned reviewer for current step
        if ($reviewer->reviewer_id !== $request->user()->id) {
            abort(403, 'You are not authorized to receive this transaction for the next reviewer.');
        }

        // Ensure the review is still pending
        if ($reviewer->status !== 'pending') {
            return back()->with('error', 'This review has already been processed.');
        }

        // Ensure the transaction has been received
        if ($reviewer->received_status !== 'received') {
            return back()->with('error', 'You must receive this transaction before marking for next reviewer.');
        }

        $validated = $request->validate([
            'received_status' => 'required|in:received,not_received',
        ]);

        // Get the next reviewer
        $transaction = $reviewer->transaction;
        $steps = $transaction->workflow_snapshot['steps'] ?? $transaction->workflow->getWorkflowSteps();
        $currentStep = $transaction->current_workflow_step;
        
        if ($currentStep >= count($steps)) {
            return back()->with('error', 'This is the last step. No next reviewer available.');
        }

        // Create or update the next reviewer record
        $nextStep = $steps[$currentStep]; // Current step (0-indexed, current_workflow_step is 1-indexed)
        $nextDepartmentId = $nextStep['department_id'];
        $nextReviewerUser = \App\Models\User::where('department_id', $nextDepartmentId)
            ->where(function($q) {
                $q->where('type', 'Head')->orWhere('type', 'Staff');
            })
            ->first();

        if ($nextReviewerUser) {
            // Find existing next reviewer or create new one
            $nextReviewer = $transaction->reviewers()
                ->where('reviewer_id', $nextReviewerUser->id)
                ->where('status', 'pending')
                ->first();

            if ($nextReviewer) {
                $nextReviewer->update([
                    'received_status' => $validated['received_status'],
                    'received_by' => $request->user()->id,
                    'received_at' => now(),
                ]);
            }
        }

        $message = $validated['received_status'] === 'received' 
            ? 'Next reviewer marked as ready to receive.' 
            : 'Next reviewer marked as not ready.';

        return back()->with('success', $message);
    }

    /**
     * Resubmit a transaction after corrections (for resubmissions)
     * This marks the transaction as received and ready for the next review cycle
     */
    public function resubmit(Request $request, TransactionReviewer $reviewer)
    {
        // Check if user is authorized (must be head or staff)
        $user = $request->user();
        if (!$user->isHead() && $user->type !== 'Staff') {
            return back()->with('error', 'Only department heads or staff can resubmit transactions.');
        }

        // Check if user is from the same department as the reviewer
        if ($reviewer->reviewer->department_id !== $user->department_id) {
            return back()->with('error', 'You can only resubmit transactions assigned to your department.');
        }

        // Verify this is actually a resubmission (iteration_number > 1)
        if ($reviewer->iteration_number <= 1) {
            return back()->with('error', 'This action is only available for resubmissions.');
        }

        // Mark as received and ready for review
        $reviewer->update([
            'received_status' => 'received',
            'received_by' => $user->id,
            'received_at' => now(),
        ]);

        return back()->with('success', 'Transaction resubmitted successfully. Review timer started.');
    }
}

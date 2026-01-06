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

        // Pending reviews - transactions awaiting my review
        $pendingReviews = TransactionReviewer::with(['transaction.workflow', 'transaction.creator', 'department'])
            ->forReviewer($userId)
            ->pending()
            ->orderBy('due_date', 'asc')
            ->get();

        // Reviewed by me - transactions I've already reviewed (approved/rejected)
        $reviewedByMe = TransactionReviewer::with(['transaction.workflow', 'transaction.creator', 'department'])
            ->forReviewer($userId)
            ->whereIn('status', ['approved', 'rejected'])
            ->orderBy('reviewed_at', 'desc')
            ->limit(20)
            ->get();

        // Resubmissions - transactions that were rejected and resubmitted for re-review
        $resubmissions = TransactionReviewer::with(['transaction.workflow', 'transaction.creator', 'department'])
            ->forReviewer($userId)
            ->pending()
            ->where('iteration_number', '>', 1)
            ->orderBy('due_date', 'asc')
            ->get();

        // Stats
        $stats = [
            'pending' => $pendingReviews->count(),
            'due_today' => $pendingReviews->filter(fn($r) => $r->due_date && $r->due_date->isToday())->count(),
            'overdue' => $pendingReviews->filter(fn($r) => $r->isOverdue())->count(),
            'resubmissions' => $resubmissions->count(),
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
        $reviewer->load(['transaction.workflow', 'reviewer', 'department', 'previousReviewer']);

        return view('transactions.reviews.show', compact('reviewer'));
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

        $reviewer->load(['transaction.workflow', 'transaction.creator', 'transaction.department', 'department', 'previousReviewer']);

        return view('transactions.reviews.review', compact('reviewer'));
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
            $this->workflowEngine->executeAction(
                $transaction,
                'approve',
                $request->user(),
                $validated['remarks'] ?? null
            );

            // Update the current workflow step
            $transaction->increment('current_workflow_step');

            // Assign the next reviewer based on workflow
            $this->assignNextReviewer($transaction);

        } catch (\Exception $e) {
            return redirect()->route('transactions.reviews.index')
                ->with('error', 'Failed to progress workflow: ' . $e->getMessage());
        }

        return redirect()->route('transactions.reviews.index')
            ->with('success', 'Transaction approved and forwarded to the next department.');
    }

    /**
     * Assign the next reviewer based on workflow configuration
     */
    protected function assignNextReviewer(Transaction $transaction): void
    {
        $workflow = $transaction->workflow;
        $steps = $workflow->getWorkflowSteps();
        $currentStep = $transaction->current_workflow_step;

        // Check if there's a next step
        if ($currentStep > count($steps)) {
            return; // No more steps, transaction completed
        }

        // Get the next step's department
        $nextStepIndex = $currentStep - 1; // 0-indexed
        if (!isset($steps[$nextStepIndex])) {
            return;
        }

        $nextStep = $steps[$nextStepIndex];
        $nextDepartmentId = $nextStep['department_id'];

        // Find the department head (or designated reviewer) for the next department
        $nextReviewer = \App\Models\User::where('department_id', $nextDepartmentId)
            ->where('type', 'Head')
            ->first();

        if ($nextReviewer) {
            // Get the current reviewer's iteration number to maintain continuity
            $currentReviewerRecord = $transaction->reviewers()->latest()->first();
            $iterationNumber = $currentReviewerRecord ? $currentReviewerRecord->iteration_number : 1;

            // Create a new reviewer entry for the next department
            TransactionReviewer::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $nextReviewer->id,
                'department_id' => $nextDepartmentId,
                'status' => 'pending',
                'due_date' => now()->addDays(3), // Default 3 days to review
                'iteration_number' => $iterationNumber,
            ]);

            // Update transaction's current department
            $transaction->update([
                'department_id' => $nextDepartmentId,
            ]);
        }
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

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
            'resubmission_deadline' => 'nullable|date|after:today',
        ]);

        // Update the reviewer record
        $reviewer->update([
            'status' => 'rejected',
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'],
            'resubmission_deadline' => $validated['resubmission_deadline'] ?? null,
        ]);

        // Execute the workflow rejection action
        try {
            $transaction = $reviewer->transaction;
            
            // Get return department (previous step)
            $returnOptions = $this->workflowEngine->getReturnOptions($transaction);
            $returnToDepartmentId = !empty($returnOptions) ? $returnOptions[0]['department_id'] : null;

            $this->workflowEngine->executeAction(
                $transaction,
                'reject',
                $request->user(),
                $validated['rejection_reason'],
                $returnToDepartmentId
            );

            // Decrement the workflow step (going back)
            if ($transaction->current_workflow_step > 1) {
                $transaction->decrement('current_workflow_step');
            }

            // Assign the transaction back to the previous reviewer for resubmission
            $this->assignPreviousReviewer($transaction, $reviewer);

        } catch (\Exception $e) {
            // If workflow action fails, still proceed with the rejection
            // The reviewer status is already updated
        }

        return redirect()->route('transactions.reviews.index')
            ->with('success', 'Transaction rejected. The submitter will be notified.');
    }

    /**
     * Assign the transaction back to the previous department for resubmission
     */
    protected function assignPreviousReviewer(Transaction $transaction, TransactionReviewer $currentReviewer): void
    {
        $workflow = $transaction->workflow;
        $steps = $workflow->getWorkflowSteps();
        $currentStep = $transaction->current_workflow_step;

        // Get the previous step's department (which is now the current step after decrement)
        $prevStepIndex = $currentStep - 1; // 0-indexed
        if (!isset($steps[$prevStepIndex])) {
            return;
        }

        $prevStep = $steps[$prevStepIndex];
        $prevDepartmentId = $prevStep['department_id'];

        // Find the department head for the previous department
        $prevReviewer = \App\Models\User::where('department_id', $prevDepartmentId)
            ->where('type', 'Head')
            ->first();

        if ($prevReviewer) {
            // Create a new reviewer entry for resubmission with incremented iteration
            TransactionReviewer::create([
                'transaction_id' => $transaction->id,
                'reviewer_id' => $prevReviewer->id,
                'department_id' => $prevDepartmentId,
                'status' => 'pending',
                'due_date' => $currentReviewer->resubmission_deadline ?? now()->addDays(3),
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
}

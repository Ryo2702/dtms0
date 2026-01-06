<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionReviewer;
use Illuminate\Http\Request;

class TransactionReviewerController extends Controller
{
    /**
     * List pending reviews for the authenticated user
     */
    public function index(Request $request)
    {
        $reviews = TransactionReviewer::with(['transaction.workflow', 'department'])
            ->forReviewer($request->user()->id)
            ->pending()
            ->orderBy('due_date', 'asc')
            ->paginate(10);

        return view('transactions.reviews.index', compact('reviews'));
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

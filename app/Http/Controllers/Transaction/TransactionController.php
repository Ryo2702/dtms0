<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\TransactionRequest;
use App\Http\Requests\Transaction\ExecuteActionRequest;
use App\Models\Transaction;
use App\Models\Workflow;
use App\Models\Department;
use App\Models\AssignStaff;
use App\Services\Transaction\TrasactionService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class TransactionController extends Controller
{
    use AuthorizesRequests;

    public function __construct(
        protected TrasactionService $transactionService
    ) {}

    /**
     * Display available workflows for creating transactions
     */
    public function index(Request $request)
    {
        $user = $request->user();
        
        // Get active workflows - filter by user's department if origin_departments is set
        $workflows = Workflow::where('status', true)
            ->with(['documentTags' => function ($query) {
                $query->where('status', true);
            }])
            ->get()
            ->filter(function ($workflow) use ($user) {
                // If no origin departments set, workflow is available to all
                if (empty($workflow->origin_departments)) {
                    return true;
                }
                // Check if user's department is in origin departments
                return in_array($user->department_id, $workflow->origin_departments);
            });

        // Get departments for workflow route editing (Head users can edit route)
        $departments = Department::where('status', true)->orderBy('name')->get();
        
        // Check if user can edit workflow route (Head role only, not Admin)
        $canEditRoute = $user->hasRole('Head');

        // Get user's transactions
        $transactions = Transaction::where('created_by', $user->id)
            ->with(['workflow', 'assignStaff', 'department'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('transactions.index', compact('workflows', 'departments', 'canEditRoute'));
    }

    /**
     * Display user's own transactions
     */
    public function my(Request $request)
    {
        $user = $request->user();
        
        $tab = $request->get('tab', 'all');
        
        // Build base query for user's transactions
        $query = Transaction::where('created_by', $user->id)
            ->with(['workflow', 'assignStaff', 'department', 'currentReviewer', 'originDepartment']);

        // Filter by status tab
        $transactions = match($tab) {
            'in_progress' => $query->clone()->where('transaction_status', 'in_progress'),
            'completed' => $query->clone()->where('transaction_status', 'completed')->where('receiving_status', 'received'),
            'pending_receipt' => $query->clone()->where('transaction_status', 'completed')->where('receiving_status', 'pending'),
            'cancelled' => $query->clone()->where('transaction_status', 'cancelled'),
            default => $query->clone(),
        };

        $transactions = $transactions->orderBy('created_at', 'desc')->paginate(15);

        // Stats for tabs
        $stats = [
            'all' => Transaction::where('created_by', $user->id)->count(),
            'in_progress' => Transaction::where('created_by', $user->id)->where('transaction_status', 'in_progress')->count(),
            'pending_receipt' => Transaction::where('created_by', $user->id)
                ->where('transaction_status', 'completed')
                ->where('receiving_status', 'pending')
                ->count(),
            'completed' => Transaction::where('created_by', $user->id)
                ->where('transaction_status', 'completed')
                ->where('receiving_status', 'received')
                ->count(),
            'cancelled' => Transaction::where('created_by', $user->id)->where('transaction_status', 'cancelled')->count(),
        ];

        return view('transactions.my', compact('transactions', 'stats', 'tab'));
    }

    /**
     * Show form for creating a new transaction based on selected workflow
     */
    public function create(Request $request)
    {
        $workflowId = $request->get('workflow_id');
        
        if (!$workflowId) {
            return redirect()->route('transactions.index')
                ->with('error', 'Please select a workflow first.');
        }

        $workflow = Workflow::where('status', true)
            ->with('documentTags')
            ->findOrFail($workflowId);

        $user = $request->user();
        
        // Verify user can access this workflow
        if (!empty($workflow->origin_departments) && 
            !in_array($user->department_id, $workflow->origin_departments)) {
            return redirect()->route('transactions.index')
                ->with('error', 'You do not have access to this workflow.');
        }

        $assignStaff = AssignStaff::active()->get();
        $workflowConfig = $workflow->workflow_config;
        $workflowSteps = $workflow->getWorkflowSteps();

        return view('transactions.create', compact(
            'workflow',
            'assignStaff',
            'workflowConfig',
            'workflowSteps'
        ));
    }

    /**
     * Store a newly created transaction
     */
    public function store(TransactionRequest $request)
    {
        try {
            $validated = $request->validated();
            
            // If workflow_snapshot is provided with editable steps, use it
            // Otherwise, get the default from the workflow
            if (!isset($validated['workflow_snapshot'])) {
                $workflow = Workflow::findOrFail($validated['workflow_id']);
                $validated['workflow_snapshot'] = $workflow->workflow_config;
            }

            $transaction = $this->transactionService->createTransaction(
                $validated,
                $request->user()
            );

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', "Transaction {$transaction->transaction_code} created successfully!");

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to create transaction: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified transaction
     */
    public function show(Request $request, Transaction $transaction)
    {
        $transaction = $this->transactionService->getTransactionDetails($transaction);
        $workflowProgress = $this->transactionService->getWorkflowProgress($transaction);
        $availableActions = $this->transactionService->getAvailableActions(
            $transaction,
            request()->user()
        );

        // If AJAX request, return JSON with rendered HTML for modal
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('transactions.partials.show-content', compact(
                'transaction',
                'workflowProgress',
                'availableActions'
            ))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'transaction' => $transaction
            ]);
        }

        return view('transactions.show', compact(
            'transaction',
            'workflowProgress',
            'availableActions'
        ));
    }

    /**
     * Show form for editing the transaction
     */
    public function edit(Request $request, Transaction $transaction)
    {
        if ($transaction->isCompleted() || $transaction->isCancelled()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'error' => 'Cannot edit a completed or cancelled transaction.'
                ], 400);
            }
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('error', 'Cannot edit a completed or cancelled transaction.');
        }

        $transaction = $this->transactionService->getTransactionDetails($transaction);
        $assignStaff = AssignStaff::active()->get();
        $departments = Department::where('status', true)->orderBy('name')->get();

        $canEditWorkflow = (int) $transaction->current_workflow_step === 1;
        $workflowConfig = $transaction->workflow_snapshot ?? $transaction->workflow->workflow_config;
        $workflowSteps = $workflowConfig['steps'] ?? [];

        // If AJAX request, return JSON with rendered HTML for modal
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('transactions.partials.edit-form', compact(
                'transaction',
                'assignStaff',
                'departments',
                'canEditWorkflow',
                'workflowConfig',
                'workflowSteps'
            ))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
                'transaction' => $transaction
            ]);
        }

        return view('transactions.edit', compact(
            'transaction',
            'assignStaff',
            'departments',
            'canEditWorkflow',
            'workflowConfig',
            'workflowSteps'
        ));
    }

    /**
     * Update the specified transaction
     */
    public function update(TransactionRequest $request, Transaction $transaction)
    {
        try {
            // Skip update if no changes
            if (!$request->hasChanges()) {
                return redirect()
                    ->route('transactions.show', $transaction)
                    ->with('info', 'No changes detected.');
            }

            $transaction = $this->transactionService->updateTransaction(
                $transaction,
                $request->validated()
            );

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', 'Transaction updated successfully!');

        } catch (\Exception $e) {
            return back()
                ->withInput()
                ->with('error', 'Failed to update transaction: ' . $e->getMessage());
        }
    }

    /**
     * Execute a workflow action on the transaction
     */
    public function executeAction(ExecuteActionRequest $request, Transaction $transaction)
    {
        try {
            $this->transactionService->executeAction(
                $transaction,
                $request->input('action'),
                $request->user(),
                $request->input('remarks'),
                $request->input('return_to_department_id')
            );

            $actionLabels = [
                'approve' => 'approved',
                'reject' => 'returned',
                'resubmit' => 'resubmitted',
                'cancel' => 'cancelled',
            ];

            $actionLabel = $actionLabels[$request->input('action')] ?? 'processed';

            return redirect()
                ->route('transactions.show', $transaction)
                ->with('success', "Transaction has been {$actionLabel} successfully!");

        } catch (\Exception $e) {
            return back()->with('error', 'Failed to process action: ' . $e->getMessage());
        }
    }

    /**
     * Display transaction workflow progress/tracker
     */
    public function tracker(Transaction $transaction)
    {
        $transaction = $this->transactionService->getTransactionDetails($transaction);
        $workflowProgress = $this->transactionService->getWorkflowProgress($transaction);

        return view('transactions.tracker', compact('transaction', 'workflowProgress'));
    }

    /**
     * Display transaction history/logs
     */
    public function history(Transaction $transaction)
    {
        $transaction->load(['transactionLogs.actionBy', 'reviewers.reviewer']);

        return view('transactions.history', compact('transaction'));
    }

    /**
     * Get default workflow configuration for a transaction (from its workflow template)
     */
    public function getDefaultWorkflowConfig(Transaction $transaction)
    {
        $workflow = $transaction->workflow;
        
        return response()->json([
            'success' => true,
            'workflow_config' => $workflow->workflow_config,
        ]);
    }

    /**
     * Get workflow configuration for a specific workflow (AJAX)
     */
    public function getWorkflowConfig(Workflow $workflow)
    {
        return response()->json([
            'success' => true,
            'workflow' => [
                'id' => $workflow->id,
                'transaction_name' => $workflow->transaction_name,
                'description' => $workflow->description,
                'difficulty' => $workflow->difficulty,
                'workflow_config' => $workflow->workflow_config,
                'document_tags' => $workflow->documentTags,
            ],
        ]);
    }

    /**
     * Confirm that a completed transaction has been received
     */
    public function confirmReceived(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        // Check if user belongs to the origin department
        if ($user->department_id !== $transaction->origin_department_id) {
            abort(403, 'You are not authorized to confirm receipt of this transaction.');
        }

        // Check if transaction is completed and pending receiving
        if ($transaction->transaction_status !== 'completed' || $transaction->receiving_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This transaction cannot be confirmed at this time.');
        }

        $transaction->update([
            'receiving_status' => 'received',
            'received_at' => now(),
        ]);

        return redirect()->back()
            ->with('success', 'Transaction has been marked as received.');
    }

    /**
     * Mark a completed transaction as not received
     */
    public function markNotReceived(Request $request, Transaction $transaction)
    {
        $user = $request->user();

        // Check if user belongs to the origin department
        if ($user->department_id !== $transaction->origin_department_id) {
            abort(403, 'You are not authorized to update this transaction.');
        }

        // Check if transaction is completed and pending receiving
        if ($transaction->transaction_status !== 'completed' || $transaction->receiving_status !== 'pending') {
            return redirect()->back()
                ->with('error', 'This transaction cannot be updated at this time.');
        }

        $validated = $request->validate([
            'reason' => 'required|string|max:500',
        ]);

        $transaction->update([
            'receiving_status' => 'not_received',
        ]);

        // Optionally log the reason
        \App\Models\TransactionLog::create([
            'transaction_id' => $transaction->id,
            'from_state' => 'completed',
            'to_state' => 'not_received',
            'action' => 'mark_not_received',
            'action_by' => $user->id,
            'remarks' => $validated['reason'],
        ]);

        return redirect()->back()
            ->with('success', 'Transaction has been marked as not received.');
    }
}

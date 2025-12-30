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

        return view('transactions.index', compact('workflows', 'departments', 'canEditRoute'));
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

        $canEditWorkflow = $transaction->current_workflow_step === 1;
        $workflowConfig = $transaction->workflow_snapshot ?? $transaction->workflow->workflow_config;
        $workflowSteps = $workflowConfig['steps'] ?? [];

        // If AJAX request, return JSON with rendered HTML for modal
        if ($request->ajax() || $request->wantsJson()) {
            $html = view('transactions.partials.edit-form', compact(
                'transaction',
                'assignStaff',
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
}

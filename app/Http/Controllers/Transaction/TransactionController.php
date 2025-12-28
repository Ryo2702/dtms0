<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\TransactionRequest;
use App\Http\Requests\Transaction\ExecuteActionRequest;
use App\Models\Transaction;
use App\Models\Workflow;
use App\Models\Department;
use App\Models\DocumentTag;
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
     * Display a listing of transactions
     */
    public function index(Request $request)
    {
        $filters = $request->only([
            'status', 'urgency', 'department_id', 'workflow_id',
            'search', 'date_from', 'date_to'
        ]);

        $transactions = $this->transactionService->getTransactions($filters);
        $statistics = $this->transactionService->getStatistics();

        $workflows = Workflow::where('status', true)->get();
        $departments = Department::where('status', true)->get();

        return view('transactions.index', compact(
            'transactions',
            'statistics',
            'workflows',
            'departments',
            'filters'
        ));
    }

    /**
     * Show form for creating a new transaction
     */
    public function create(Request $request)
    {
        $workflows = Workflow::where('status', true)
            ->with('documentTags')
            ->get();

        $departments = Department::where('status', true)->get();
        $documentTags = DocumentTag::where('status', true)->get();
        $assignStaff = AssignStaff::active()->get();

        $selectedWorkflow = null;
        $workflowConfig = null;

        if ($request->has('workflow_id')) {
            $selectedWorkflow = Workflow::find($request->workflow_id);
            $workflowConfig = $selectedWorkflow?->workflow_config;
        }

        return view('transactions.create', compact(
            'workflows',
            'departments',
            'documentTags',
            'assignStaff',
            'selectedWorkflow',
            'workflowConfig'
        ));
    }

    /**
     * Store a newly created transaction
     */
    public function store(TransactionRequest $request)
    {
        try {
            $transaction = $this->transactionService->createTransaction(
                $request->validated(),
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
    public function show(Transaction $transaction)
    {
        $transaction = $this->transactionService->getTransactionDetails($transaction);
        $workflowProgress = $this->transactionService->getWorkflowProgress($transaction);
        $availableActions = $this->transactionService->getAvailableActions(
            $transaction,
            request()->user()
        );

        return view('transactions.show', compact(
            'transaction',
            'workflowProgress',
            'availableActions'
        ));
    }

    /**
     * Show form for editing the transaction
     */
    public function edit(Transaction $transaction)
    {
        if ($transaction->isCompleted() || $transaction->isCancelled()) {
            return redirect()
                ->route('transactions.show', $transaction)
                ->with('error', 'Cannot edit a completed or cancelled transaction.');
        }

        $transaction = $this->transactionService->getTransactionDetails($transaction);

        $departments = Department::where('status', true)->get();
        $documentTags = DocumentTag::where('status', true)->get();
        $assignStaff = AssignStaff::active()->get();

        $canEditWorkflow = $transaction->current_workflow_step === 1;
        $workflowConfig = $transaction->workflow_snapshot ?? $transaction->workflow->workflow_config;

        return view('transactions.edit', compact(
            'transaction',
            'departments',
            'documentTags',
            'assignStaff',
            'canEditWorkflow',
            'workflowConfig'
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

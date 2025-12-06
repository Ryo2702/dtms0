<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionReviewer;
use App\Models\TransactionWorkflow;
use App\Models\User;
use App\Services\Transaction\WorkflowRoutingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class TransactionWorkflowController extends Controller
{
    protected WorkflowRoutingService $workflowService;

    public function __construct(WorkflowRoutingService $workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function getWorkflowsByType(Request $request)
    {
        $validated = $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id'
        ]);

        try {
            $workflows = $this->workflowService->getWorkflowsByTransactionType(
                $validated['transaction_type_id']
            );

            return response()->json([
                'success' => true,
                'workflows' => $workflows,
                'message' => 'Workflows retrieved successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getActiveWorkflows(Request $request)
    {
        $validated = $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id'
        ]);

        try {
            $workflows = $this->workflowService->getActiveWorkflowsByType(
                ['transaction_type_id' => 'required|exists:transaction_types,id']
            );

            return response()->json([
                'success' => true,
                'workflows' => $workflows,
                'sequence_order' => $workflows->pluck('sequence_order')
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function createWorkflow(Request $request)
    {
        $user = Auth::user();

        if (!$user->isHead()) {
            return response()->json([
                'success' => false,
                'message' => 'Only department heads can create workflow'
            ], 403);
        }


        $validated = $request->validate([
            'transaction_type_id' => 'required|exixts:transaction_types,id',
            'department_id' => 'required|exists:departments,id',
            'sequence_order' => 'required|integer|min:1',
            'is_originating' => 'required|boolean'
        ]);


        try {
            $workflow = $this->workflowService->createWorkflowRoute(
                $validated,
                $user
            );

            return response()->json([
                'success' => true,
                'workflow' => $workflow,
                'message' => 'Workflow route created!'
            ], 201);
        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    public function routeTransaction(Request $request)
    {
        $user = Auth::user();

        if (!$user->isHead()) {
            return response()->json([
                'success' => false,
                'message' => 'Only heads can route transactions'
            ], 403);
        }

        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
            'reviewer_notes' => 'nullable|string',
            'action' => 'required|in:forward,approve,resubmit,return_to_original',
            'forward_to_user_id' => 'nullable|exists:users,id',
            'process_time_value' => 'nullable|integer|min:1',
            'process_time_unit' => 'nullable|in:minutes,hours,days'
        ]);

        try {
            DB::beginTransaction();

            $transaction = Transaction::findOrFail($validated['transaction_id']);

            $this->workflowService->verifyTransactionAccess($transaction, $user);

            $nextStep = $this->workflowService->getNextWorkflowStep(
                $transaction,
                $validated['action']
            );

            $reviewer = null;
            if ($validated['action'] === 'forward' && isset($validated['forward_to_user_id'])) {
                $reviewer = User::findOrFail($validated['forward_to_user_id']);

                // Verify reviewer is a head
                if (!$reviewer->isHead()) {
                    throw new \Exception('Target reviewer must be a department head');
                }
            }

            //Create iterative reviewer record
            $transactionReviewer = $this->workflowService->createIterativeReviewer(
                $transaction,
                $user,
                $reviewer,
                $validated
            );

            //Update transaction with current workflow step
            $transaction->update([
                'current_workflow_step' => $nextStep,
                'transaction_status' => 'in_progress',
            ]);

            // Send notification if forwarding to another head
            // if ($reviewer && $validated['action'] === 'forward') {
            //     NotificationHelper::send(
            //         $reviewer,
            //         'workflow_routing',
            //         'Transaction Routing Required',
            //         "Transaction {$transaction->transaction_code} requires your approval. From: {$user->name}",
            //         $transaction->id
            //     );

            DB::commit();

            return response()->json([
                'success' => true,
                'transaction' => $transaction->load('reviewer'),
                'message' => 'Transaction routed successfully'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getWorkflowChain(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id'
        ]);

        try {
            $chain = $this->workflowService->getTransactionWorkflowChain(
                $validated['transaction_id']
            );

            return response()->json([
                'success' => true,
                'chain' => $chain,
                'total_steps' => count($chain)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getPendingTransactions(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'transaction_type_id' => 'nullable|exists:transaction_types,id',
            'status' => 'nullable|in:pending,approved,re_submit,cancelled',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        try {
            $query = TransactionReviewer::where('reviewed_id', $user->id)->where('status', $validated['status'] ?? 'pending');

            if (isset($validated['transaction_type_id'])) {
                $query->whereHas('transaction', function ($q) use ($validated) {
                    $q->where('transaction_type_id', $validated['transaction_type_id']);
                });
            }

            $transactions = $query->with('transaction', 'reviewer')
                ->orderBy('due_date', 'asc')
                ->paginate($validated['per_page'] ?? 10);

            return response()->json([
                'success' => true,
                'transactions' => $transactions
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function configureWorkflowCycle(Request $request)
    {
        $user = Auth::user();

        if (!$user->isAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Only admins can configure workflows'
            ], 403);
        }

        $validated = $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'departments' => 'required|array|min:1',
            'departments.*.department_id' => 'required|exists:departments,id',
            'departments.*.sequence_order' => 'required|integer|min:1',
            'departments.*.is_originating' => 'required|boolean',
        ]);

        try {
            DB::beginTransaction();

            // Clear existing workflows for this transaction type
            TransactionWorkflow::where('transaction_type_id', $validated['transaction_type_id'])
                ->delete();

            // Create new workflow cycle
            $workflows = $this->workflowService->configureWorkflowCycle(
                $validated['transaction_type_id'],
                $validated['departments']
            );

            DB::commit();

            return response()->json([
                'success' => true,
                'workflows' => $workflows,
                'message' => 'Workflow cycle configured successfully'
            ], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function getWorkflowStats(Request $request)
    {
        $user = Auth::user();

        if (!$user->isHead()) {
            return response()->json([
                'success' => false,
                'message' => 'Only heads can view workflow stats'
            ], 403);
        }

        try {
            $stats = $this->workflowService->getHeadWorkflowStats($user);

            return response()->json([
                'success' => true,
                'stats' => $stats,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

     public function getNextReviewers(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,id',
        ]);

        try {
            $reviewers = $this->workflowService->getNextReviewersInCycle(
                $validated['transaction_id']
            );

            return response()->json([
                'success' => true,
                'reviewers' => $reviewers,
                'count' => count($reviewers),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}

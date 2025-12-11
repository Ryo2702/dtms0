<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\WorkflowRequests;
use App\Models\Department;
use App\Models\TransactionType;
use App\Models\TransactionWorkflow;
use App\Services\Transaction\WorkflowService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowController extends Controller
{
    use AuthorizesRequests;

    private WorkflowService $workflowService;
    public function __construct($workflowService)
    {
        $this->workflowService = $workflowService;
    }

    public function index($transactionTypeId = null) {
        $transactionTypes = TransactionType::all();
        $query = TransactionWorkflow::query();

        if ($transactionTypeId) {
            $query->byTransactionType($transactionTypeId);
        }

        $workflows = $query->with(['transactionType', 'department', 'nextStepOnApproval', 'nextStepOnRejection']);

        return view('workflows.index', compact('transactionTypes', 'workflows', 'transactionTypeId'));
    }

    public function create() {
        $transactionTypes = TransactionType::active()->get();
        $departments = Department::where('status', 1)->get();
        $workflows = TransactionWorkflow::all();

        return view('workflows.create', compact('transactionTypes', 'departments', 'workflows'));
    }

    public function store(WorkflowRequests $request) {
        try {
            DB::beginTransaction();

            $validated = $request->validated();

            if ($validated['is_originating'] ?? false) {
                TransactionWorkflow::byTransactionType($validated['transaction_type_id'])->update(['is_originating' => false]);
            }

            $workflow = TransactionWorkflow::create($validated);

            $errors = $this->workflowService->validateWorkflowConfiguration($workflow);
            if (!empty($errors)) {
                throw new \Exception(implode(', ', $errors));
            }

            DB::commit();

            return back()->with('success', 'Workflow step created Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', $e->getMessage())->withInput();
        }
    }


    public function chain($transactionTypeId) {
        $chain = $this->workflowService->getWorkflowChain($transactionTypeId);
        $transactionType = TransactionType::findOrFail($transactionTypeId);

        return view('', compact('chain', 'transactionType'));
    }

    public function getNextStep($transactionTypeId) {
        $workflows = TransactionWorkflow::byTransactionType($transactionTypeId)
            ->orderedBySequence()
            ->get(['id', 'sequence_order', 'department_id'])
            ->load('department')
            ->map(function ($workflow) {
                return [
                    'id' => $workflow->id,
                    'label' => "Step {$workflow->sequence_order} - {$workflow->department->name}",
                    'sequence_order' => $workflow->sequence_order       
                ];
            });

            return response()->json(
                $workflows
            );

    }

    public function validate(Request $request) {
        $workflow = $request->input('workflow');

        $errors = [];

        if (!$workflow['allow_cycles'] && $workflow['next_step_on_rejection_id']) {
            $errors[] = 'Cannot set rejection path without enabling cycles';
        }

        if ($workflow['allow_cycles'] && !$workflow['max_cycle_count']) {
            $errors[] = 'Max cycle count is required when cycles are enabled';
        }

        return response()->json([
            'valid' => empty($errors),
            'errors' => $errors
        ]);
    }

    public function bulkImport(Request $request) {
        $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'workflows' => 'required|json'
        ]);

        try {
            DB::beginTransaction();

            $transactionTypeId = $request->input('transaction_type_id');
            $workflows = json_decode($request->input('workflows'), true);

            //Delete existing workflows
            TransactionWorkflow::byTransactionType($transactionTypeId)->delete();

            foreach ($workflows as $workflow) {
                TransactionWorkflow::create([
                    'transaction_type_id' => $transactionTypeId,
                    'department_id' => $workflow['department_id'],
                    'sequence_order' => $workflow['sequence_order'],
                    'is_originating' => $workflow['is_originating'] ?? false,
                    'process_time_value' => $workflow['process_time_value'],
                    'process_time_unit' => $workflow['process_time_unit'],
                    'allow_cycles' => $workflow['allow_cycles'],
                    'max_cycle_count' => $workflow['max_cycle_count'] ?? 1
                ]);
            }

            DB::commit();

            return back()->with('success', 'Workflow Imported Successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage());
        }
    }
}
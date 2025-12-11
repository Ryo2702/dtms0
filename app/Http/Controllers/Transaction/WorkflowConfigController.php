<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\WorkflowConfigRequest;
use App\Models\Department;
use App\Models\TransactionType;
use App\Services\Transaction\WorkflowConfigService;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class WorkflowConfigController extends Controller
{
    use AuthorizesRequests;

    protected WorkflowConfigService $configService;

    public function __construct(WorkflowConfigService $configService)
    {
        $this->configService = $configService;
    }

    /**
     * Show workflow configuration for a transaction type
     */
    public function index()
    {
        $transactionTypes = TransactionType::with('transactions')->get();
        
        return view('workflows.index', compact('transactionTypes'));
    }

    /**
     * Show workflow builder for a transaction type
     */
    public function edit(TransactionType $transactionType)
    {
        $departments = Department::where('status', 1)->get();
        $currentConfig = $transactionType->workflow_config ?? ['steps' => [], 'transitions' => []];

        return view('workflows.edit', compact('transactionType', 'departments', 'currentConfig'));
    }

    /**
     * Store/Update workflow configuration
     */
    public function update(WorkflowConfigRequest $request, TransactionType $transactionType)
    {
        try {
            DB::beginTransaction();

            // Build the workflow configuration
            $config = $this->configService->buildWorkflowConfig($request->validated()['steps']);

            // Validate the configuration
            $errors = $this->configService->validateConfig($config);
            if (!empty($errors)) {
                return back()
                    ->withErrors(['workflow' => $errors])
                    ->withInput();
            }

            // Save the configuration
            $transactionType->update([
                'workflow_config' => $config,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.workflows.index')
                ->with('success', 'Workflow configuration saved successfully!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->with('error', $e->getMessage())
                ->withInput();
        }
    }

    /**
     * Preview transition map
     */
    public function preview(Request $request)
    {
        $steps = $request->input('steps', []);
        
        if (empty($steps)) {
            return response()->json(['error' => 'No steps provided'], 400);
        }

        $config = $this->configService->buildWorkflowConfig($steps);
        
        return response()->json($config);
    }

    /**
     * Get workflow steps as JSON for API
     */
    public function getSteps(TransactionType $transactionType)
    {
        return response()->json([
            'steps' => $transactionType->getWorkflowSteps(),
            'transitions' => $transactionType->getTransitions(),
        ]);
    }
}
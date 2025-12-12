<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Http\Requests\Transaction\WorkflowConfigRequest;
use App\Models\Department;
use App\Models\TransactionType;
use App\Models\Workflow;
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
     * Show all workflows grouped by transaction type
     */
    public function index()
    {
        $transactionTypes = TransactionType::with(['workflows' => function($q) {
            $q->orderBy('is_default', 'desc')->orderBy('name');
        }])->get();
        
        return view('workflows.index', compact('transactionTypes'));
    }

    /**
     * Show form to create a new workflow
     */
    public function create(Request $request)
    {
        $transactionTypeId = $request->get('transaction_type_id');
        $transactionType = $transactionTypeId 
            ? TransactionType::findOrFail($transactionTypeId) 
            : null;
        
        $transactionTypes = TransactionType::where('status', true)->get();
        $departments = Department::where('status', 1)->get();
        $currentConfig = ['steps' => [], 'transitions' => [], 'difficulty' => 'simple'];

        return view('workflows.create', compact('transactionType', 'transactionTypes', 'departments', 'currentConfig'));
    }

    /**
     * Store a new workflow
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:simple,moderate,complex',
            'is_default' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.department_id' => 'required|exists:departments,id',
        ]);

        try {
            DB::beginTransaction();

            // Build the workflow configuration
            $config = $this->configService->buildWorkflowConfig($request->input('steps'));
            $config['difficulty'] = $request->input('difficulty');

            // Validate the configuration
            $errors = $this->configService->validateConfig($config);
            if (!empty($errors)) {
                return back()->withErrors(['workflow' => $errors])->withInput();
            }

            // If this is marked as default, unset other defaults for this type
            if ($request->boolean('is_default')) {
                Workflow::where('transaction_type_id', $request->input('transaction_type_id'))
                    ->update(['is_default' => false]);
            }

            // Create the workflow
            $workflow = Workflow::create([
                'transaction_type_id' => $request->input('transaction_type_id'),
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'difficulty' => $request->input('difficulty'),
                'workflow_config' => $config,
                'is_default' => $request->boolean('is_default'),
                'status' => true,
            ]);

            DB::commit();

            return redirect()
                ->route('admin.workflows.index')
                ->with('success', "Workflow '{$workflow->name}' created successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Show workflow builder for editing
     */
    public function edit(Workflow $workflow)
    {
        $workflow->load('transactionType');
        $departments = Department::where('status', 1)->get();
        $currentConfig = $workflow->workflow_config ?? ['steps' => [], 'transitions' => [], 'difficulty' => 'normal'];

        return view('workflows.edit', compact('workflow', 'departments', 'currentConfig'));
    }

    /**
     * Update workflow configuration
     */
    public function update(Request $request, Workflow $workflow)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:simple,moderate,complex',
            'is_default' => 'boolean',
            'steps' => 'required|array|min:1',
            'steps.*.department_id' => 'required|exists:departments,id',
        ]);

        try {
            DB::beginTransaction();

            // Build the workflow configuration
            $config = $this->configService->buildWorkflowConfig($request->input('steps'));
            $config['difficulty'] = $request->input('difficulty');

            // Validate the configuration
            $errors = $this->configService->validateConfig($config);
            if (!empty($errors)) {
                return back()->withErrors(['workflow' => $errors])->withInput();
            }

            // If this is marked as default, unset other defaults for this type
            if ($request->boolean('is_default')) {
                Workflow::where('transaction_type_id', $workflow->transaction_type_id)
                    ->where('id', '!=', $workflow->id)
                    ->update(['is_default' => false]);
            }

            // Update the workflow
            $workflow->update([
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'difficulty' => $request->input('difficulty'),
                'workflow_config' => $config,
                'is_default' => $request->boolean('is_default'),
            ]);

            DB::commit();

            return redirect()
                ->route('admin.workflows.index')
                ->with('success', "Workflow '{$workflow->name}' updated successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
        }
    }

    /**
     * Delete a workflow
     */
    public function destroy(Workflow $workflow)
    {
        try {
            $name = $workflow->name;
            $workflow->delete();

            return redirect()
                ->route('admin.workflows.index')
                ->with('success', "Workflow '{$name}' deleted successfully!");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Toggle workflow status (active/inactive)
     */
    public function toggleStatus(Workflow $workflow)
    {
        $workflow->update(['status' => !$workflow->status]);

        return response()->json([
            'success' => true,
            'status' => $workflow->status,
            'message' => $workflow->status ? 'Workflow activated' : 'Workflow deactivated'
        ]);
    }

    /**
     * Set workflow as default for its transaction type
     */
    public function setDefault(Workflow $workflow)
    {
        DB::transaction(function () use ($workflow) {
            // Unset other defaults
            Workflow::where('transaction_type_id', $workflow->transaction_type_id)
                ->update(['is_default' => false]);
            
            // Set this as default
            $workflow->update(['is_default' => true]);
        });

        return response()->json([
            'success' => true,
            'message' => "'{$workflow->name}' is now the default workflow"
        ]);
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
     * Duplicate a workflow
     */
    public function duplicate(Request $request, Workflow $workflow)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'transaction_type_id' => 'nullable|exists:transaction_types,id',
        ]);

        try {
            $newWorkflow = Workflow::create([
                'transaction_type_id' => $request->input('transaction_type_id', $workflow->transaction_type_id),
                'name' => $request->input('name'),
                'description' => $workflow->description,
                'difficulty' => $workflow->difficulty,
                'workflow_config' => $workflow->workflow_config,
                'is_default' => false,
                'status' => true,
            ]);

            return response()->json([
                'success' => true,
                'message' => "Workflow duplicated as '{$newWorkflow->name}'!",
                'workflow' => $newWorkflow
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
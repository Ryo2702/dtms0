<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentTag;
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
        $transactionTypes = TransactionType::with(['workflows' => function ($q) {
            $q->with('documentTags.department');
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
        $documentTags = DocumentTag::where('status', true)->with('department')->get();
        $currentConfig = ['steps' => [], 'transitions' => [], 'difficulty' => 'simple'];

        return view('workflows.create', compact('transactionType', 'transactionTypes', 'departments', 'documentTags', 'currentConfig'));
    }

    /**
     * Store a new workflow
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_type_id' => 'required|exists:transaction_types,id',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:simple,complex,highly_technical',
            'steps' => 'required|array|min:1',
            'steps.*.department_id' => 'required|exists:departments,id',
            'document_tags' => 'nullable|array',
            'document_tags.*.id' => 'exists:document_tags,id',
            'document_tags.*.is_required' => 'boolean'
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

            // Create the workflow
            $workflow = Workflow::create([
                'transaction_type_id' => $request->input('transaction_type_id'),
                'description' => $request->input('description'),
                'difficulty' => $request->input('difficulty'),
                'workflow_config' => $config,
                'status' => true,
            ]);

            if ($request->has('document_tags') && !empty($request->input('document_tags'))) {
                // Filter to only include tags that have an 'id' key (checked checkboxes)
                $tags = collect($request->input('document_tags'))
                    ->filter(fn($tag) => isset($tag['id']) && !empty($tag['id']))
                    ->values()
                    ->toArray();
                
                if (!empty($tags)) {
                    $workflow->syncDocumentTags($tags);
                }
            }

            DB::commit();

            return redirect()
                ->route('admin.workflows.index')
                ->with('success', "Workflow created successfully!");
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
        $documentTags = DocumentTag::where('status', true)->with('department')->get();
        $currentConfig = $workflow->workflow_config ?? ['steps' => [], 'transitions' => [], 'difficulty' => 'simple'];

        $selectedTags = $workflow->documentTags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'is_required' => $tag->pivot->is_required,
            ];
        });
        return view('workflows.edit', compact('workflow', 'departments', 'currentConfig', 'documentTags', 'selectedTags'));
    }

    /**
     * Update workflow configuration
     */
    public function update(Request $request, Workflow $workflow)
    {
        $request->validate([
            'description' => 'nullable|string',
            'difficulty' => 'required|in:simple,complex,highly_technical',
            'steps' => 'required|array|min:1',
            'steps.*.department_id' => 'required|exists:departments,id',
            'document_tags' => 'nullable|array',
            'document_tags.*.id' => 'exists:document_tags,id',
            'document_tags.*.is_required' => 'boolean'
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


            $workflow->update([
                'description' => $request->input('description'),
                'difficulty' => $request->input('difficulty'),
                'workflow_config' => $config,
            ]);

            if ($request->has('document_tags')) {
                // Filter to only include tags that have an 'id' key (checked checkboxes)
                $tags = collect($request->input('document_tags', []))
                    ->filter(fn($tag) => isset($tag['id']) && !empty($tag['id']))
                    ->values()
                    ->toArray();
                
                $workflow->syncDocumentTags($tags);
            } else {
                $workflow->documentTags()->detach();
            }
            
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
            $workflow->delete();

            return redirect()
                ->route('admin.workflows.index')
                ->with('success', "Workflow deleted successfully!");
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


    public function getDocumentTags(Workflow $workflow)  {
        $tags = $workflow->documentTags()
            ->with('department')
            ->get()
            ->map(function ($tag){
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'department' => $tag->department->name,
                    'department_id' => $tag->department->id,
                    'is_required' => $tag->pivot->is_required
                ]; 
            });
        return response()->json($tags);
    }


    public function getTagDepartments(Workflow $workflow) {
        $departments = $workflow->getTagDepartments();

        return response()->json($departments);
    }
}

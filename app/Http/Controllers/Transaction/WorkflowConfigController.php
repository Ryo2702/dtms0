<?php

namespace App\Http\Controllers\Transaction;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentTag;
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
     * Show all workflows with TS# and transaction name
     */
    public function index()
    {
        $workflows = Workflow::with('documentTags.departments')
            ->orderBy('created_at', 'desc')
            ->get();
        $availableDocumentTags = DocumentTag::where('status', true)->get();

        return view('workflows.index', compact('workflows','availableDocumentTags'));
    }

    /**
     * Show form to create a new workflow
     */
    public function create(Request $request)
    {
        $departments = Department::where('status', 1)->get();
        $documentTags = DocumentTag::where('status', true)->with('departments')->get();
        $currentConfig = ['steps' => [], 'transitions' => [], 'difficulty' => 'simple'];

        return view('workflows.create', compact('departments', 'documentTags', 'currentConfig'));
    }

    /**
     * Store a new workflow
     */
    public function store(Request $request)
    {
        $request->validate([
            'transaction_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:simple,complex,highly_technical',
            'steps' => 'required|array|min:1',
            'steps.*.department_id' => 'required|exists:departments,id',
            'steps.*.document_tags' => 'nullable|array',
            'steps.*.document_tags.*' => 'exists:document_tags,id',
        ]);

        try {
            DB::beginTransaction();

            // Build the workflow configuration with document tags per step
            $steps = $request->input('steps');
            $config = $this->configService->buildWorkflowConfig($steps);
            $config['difficulty'] = $request->input('difficulty');

            // Validate the configuration
            $errors = $this->configService->validateConfig($config);
            if (!empty($errors)) {
                return back()->withErrors(['workflow' => $errors])->withInput();
            }

            // Process origin departments
            $originDepartments = array_values(array_unique(array_map('intval', $request->input('origin_departments', []))));

            // Create the workflow
            $workflow = Workflow::create([
                'transaction_name' => $request->input('transaction_name'),
                'description' => $request->input('description'),
                'difficulty' => $request->input('difficulty'),
                'workflow_config' => $config,
                'origin_departments' => $originDepartments,
                'status' => true,
            ]);

            // Collect all document tags from all steps
            $allDocumentTags = [];
            foreach ($steps as $stepIndex => $step) {
                if (isset($step['document_tags']) && is_array($step['document_tags'])) {
                    foreach ($step['document_tags'] as $tagId) {
                        if (!isset($allDocumentTags[$tagId])) {
                            $allDocumentTags[$tagId] = [
                                'id' => $tagId,
                                'is_required' => true, // Tags selected in steps are considered required
                                'steps' => []
                            ];
                        }
                        $allDocumentTags[$tagId]['steps'][] = $stepIndex + 1; // Store which step uses this tag
                    }
                }
            }

            if (!empty($allDocumentTags)) {
                $workflow->syncDocumentTags(array_values($allDocumentTags));
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
        $departments = Department::where('status', 1)->get();
        $documentTags = DocumentTag::where('status', true)->with('departments')->get();
        $currentConfig = $workflow->workflow_config ?? ['steps' => [], 'transitions' => [], 'difficulty' => 'simple'];

        $selectedTags = $workflow->documentTags->map(function ($tag) {
            return [
                'id' => $tag->id,
                'is_required' => $tag->pivot->is_required,
            ];
        });

        $originDepartmentIds = $workflow->getOriginDepartmentIds();

        return view('workflows.edit', compact('workflow', 'departments', 'currentConfig', 'documentTags', 'selectedTags', 'originDepartmentIds'));
    }

    /**
     * Update workflow configuration
     */
    public function update(Request $request, Workflow $workflow)
    {
        $request->validate([
            'transaction_name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'difficulty' => 'required|in:simple,complex,highly_technical',
            'steps' => 'required|array|min:1',
            'steps.*.department_id' => 'required|exists:departments,id',
            'steps.*.document_tags' => 'nullable|array',
            'steps.*.document_tags.*' => 'exists:document_tags,id',
            'origin_departments' => 'nullable|array',
            'origin_departments.*' => 'exists:departments,id',
        ]);

        try {
            DB::beginTransaction();

            // Build the workflow configuration with document tags per step
            $steps = $request->input('steps');
            $config = $this->configService->buildWorkflowConfig($steps);
            $config['difficulty'] = $request->input('difficulty');

            // Validate the configuration
            $errors = $this->configService->validateConfig($config);
            if (!empty($errors)) {
                return back()->withErrors(['workflow' => $errors])->withInput();
            }

            // Process origin departments
            $originDepartments = array_values(array_unique(array_map('intval', $request->input('origin_departments', []))));

            $workflow->update([
                'transaction_name' => $request->input('transaction_name'),
                'description' => $request->input('description'),
                'difficulty' => $request->input('difficulty'),
                'workflow_config' => $config,
                'origin_departments' => $originDepartments,
            ]);

            // Collect all document tags from all steps
            $allDocumentTags = [];
            foreach ($steps as $stepIndex => $step) {
                if (isset($step['document_tags']) && is_array($step['document_tags'])) {
                    foreach ($step['document_tags'] as $tagId) {
                        if (!isset($allDocumentTags[$tagId])) {
                            $allDocumentTags[$tagId] = [
                                'id' => $tagId,
                                'is_required' => true, // Tags selected in steps are considered required
                                'steps' => []
                            ];
                        }
                        $allDocumentTags[$tagId]['steps'][] = $stepIndex + 1; // Store which step uses this tag
                    }
                }
            }

            if (!empty($allDocumentTags)) {
                $workflow->syncDocumentTags(array_values($allDocumentTags));
            } else {
                $workflow->documentTags()->detach();
            }

            DB::commit();

            return redirect()
                ->route('admin.workflows.index')
                ->with('success', "Workflow '{$workflow->transaction_name}' updated successfully!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', $e->getMessage())->withInput();
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


    public function getDocumentTags(Workflow $workflow)
    {
        $tags = $workflow->documentTags()
            ->with('departments')
            ->get()
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'departments' => $tag->departments->pluck('name')->toArray(),
                    'department_ids' => $tag->departments->pluck('id')->toArray(),
                    'is_required' => $tag->pivot->is_required
                ];
            });
        return response()->json($tags);
    }


    public function getTagDepartments(Workflow $workflow)
    {
        $departments = $workflow->getTagDepartments();

        return response()->json($departments);
    }
}

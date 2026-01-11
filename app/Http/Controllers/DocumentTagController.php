<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DocumentTag;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class DocumentTagController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of document tags
     */
    public function index(Request $request)
    {
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        $departmentFilter = $request->get('department_id');

        $allowedSorts = ['name', 'slug', 'status', 'created_at'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        $query = DocumentTag::with('departments');

        if ($departmentFilter) {
            $query->whereHas('departments', function ($q) use ($departmentFilter) {
                $q->where('departments.id', $departmentFilter);
            });
        }

        $documentTags = $query->orderBy($sort, $direction)
            ->paginate(10)
            ->appends($request->query());

        $departments = Department::where('status', 1)->get();

        return view('document-tags.index', compact('documentTags', 'departments'));
    }

    /**
     * Show form to create a new document tag (for modal/ajax)
     */
    public function create()
    {
        $departments = Department::where('status', 1)->get();
        
        // Return JSON for AJAX/modal request
        if (request()->wantsJson() || request()->ajax()) {
            return response()->json([
                'departments' => $departments
            ]);
        }
        
        // Fallback to view if needed
        return view('admin.document-tags.create', compact('departments'));
    }

    /**
     * Store a new document tag
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:document_tags,slug',
            'description' => 'nullable|string',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
        ]);

        try {
            // Generate slug from name if not provided
            $slug = $request->input('slug') ?: Str::slug($request->input('name'));
            
            // Ensure unique slug
            $originalSlug = $slug;
            $count = 1;
            while (DocumentTag::where('slug', $slug)->exists()) {
                $slug = $originalSlug . '-' . $count;
                $count++;
            }

            $documentTag = DocumentTag::create([
                'name' => $request->input('name'),
                'slug' => $slug,
                'description' => $request->input('description'),
                'status' => $request->has('status') ? true : false,
            ]);

            // Sync departments (many-to-many)
            if ($request->has('department_ids')) {
                $departmentIds = array_filter($request->input('department_ids', []));
                $documentTag->departments()->sync($departmentIds);
            }

            // Return JSON for AJAX/modal request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document tag created successfully.',
                    'tag' => $documentTag->load('departments')
                ]);
            }

            return redirect()
                ->route('admin.document-tags.index')
                ->with('success', 'Document tag created successfully.');
        } catch (\Exception $e) {
            Log::error('Document tag creation failed: ' . $e->getMessage(), [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            // Return JSON error for AJAX/modal request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document tag creation failed.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Document tag creation failed: ' . $e->getMessage());
        }
    }

    /**
     * Show the document tag details (for modal/ajax)
     */
    public function show(DocumentTag $documentTag)
    {
        $documentTag->load(['departments', 'workflows']);

        return response()->json([
            'id' => $documentTag->id,
            'name' => $documentTag->name,
            'slug' => $documentTag->slug,
            'description' => $documentTag->description,
            'status' => $documentTag->status,
            'departments' => $documentTag->departments,
            'workflows' => $documentTag->workflows,
            'workflows_count' => $documentTag->workflows->count(),
            'created_at' => $documentTag->created_at,
            'updated_at' => $documentTag->updated_at,
        ]);
    }

    /**
     * Get document tag for editing (ajax)
     */
    public function edit(DocumentTag $documentTag)
    {
        
        $documentTag->load('departments');
        
        return response()->json([
            'id' => $documentTag->id,
            'name' => $documentTag->name,
            'slug' => $documentTag->slug,
            'description' => $documentTag->description,
            'department_ids' => $documentTag->departments->pluck('id')->toArray(),
            'status' => $documentTag->status,
        ]);
    }

    /**
     * Update a document tag
     */
    public function update(Request $request, DocumentTag $documentTag)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_ids' => 'nullable|array',
            'department_ids.*' => 'exists:departments,id',
            'status' => 'boolean',
        ]);

        try {
            $data = [
                'name' => $request->input('name'),
                'description' => $request->input('description'),
                'status' => $request->boolean('status', true),
            ];

            // Only update slug if name changed
            if ($request->input('name') !== $documentTag->name) {
                $slug = Str::slug($request->input('name'));
                $originalSlug = $slug;
                $count = 1;
                while (DocumentTag::where('slug', $slug)->where('id', '!=', $documentTag->id)->exists()) {
                    $slug = $originalSlug . '-' . $count;
                    $count++;
                }
                $data['slug'] = $slug;
            }

            $documentTag->update($data);

            // Sync departments (many-to-many)
            $departmentIds = array_filter($request->input('department_ids', []));
            $documentTag->departments()->sync($departmentIds);

            // Return JSON for AJAX/modal request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Document tag updated successfully.',
                    'data' => $documentTag->load('departments')
                ]);
            }

            return redirect()
                ->route('admin.document-tags.index')
                ->with('success', 'Document tag updated successfully.');
        } catch (\Exception $e) {
            Log::error('Document tag update failed: ' . $e->getMessage());
            
            // Return JSON error for AJAX/modal request
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Document tag update failed.',
                    'error' => $e->getMessage()
                ], 500);
            }

            return back()
                ->withInput()
                ->with('error', 'Document tag update failed.');
        }
    }

    /**
     * Toggle document tag status
     */
    public function toggleStatus(DocumentTag $documentTag)
    {
        $documentTag->update(['status' => !$documentTag->status]);

        return response()->json([
            'success' => true,
            'status' => $documentTag->status,
            'message' => $documentTag->status ? 'Tag activated' : 'Tag deactivated'
        ]);
    }

    /**
     * Get document tags by department (for ajax/api)
     */
    public function getByDepartment(Department $department)
    {
        $tags = $department->documentTags()
            ->where('status', true)
            ->get(['id', 'name', 'slug', 'description']);

        return response()->json($tags);
    }

    /**
     * Bulk assign tags to workflow (ajax)
     */
    public function bulkAssignToWorkflow(Request $request)
    {
        $request->validate([
            'workflow_id' => 'required|exists:workflows,id',
            'tags' => 'required|array',
            'tags.*.id' => 'required|exists:document_tags,id',
            'tags.*.is_required' => 'boolean',
        ]);

        try {
            $workflow = \App\Models\Workflow::findOrFail($request->input('workflow_id'));
            $workflow->syncDocumentTags($request->input('tags'));

            return response()->json([
                'success' => true,
                'message' => 'Tags assigned to workflow successfully.'
            ]);
        } catch (\Exception $e) {
            Log::error('Bulk tag assignment failed: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign tags to workflow.'
            ], 500);
        }
    }
}
<?php

namespace App\Http\Controllers;

use App\Models\DocumentType;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class DocumentTypeController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        if ($user->type === 'Admin') {
            // Admin can see all document types
            $documentTypes = DocumentType::with('department')->orderBy('department_id')->orderBy('name')->get();
            $departments = Department::active()->get();
        } else {
            // Head can only see their department's document types
            $documentTypes = DocumentType::with('department')
                ->where('department_id', $user->department_id)
                ->orderBy('name')
                ->get();
            $departments = Department::where('id', $user->department_id)->get();
        }

        return view('document-types.index', compact('documentTypes', 'departments'));
    }

    public function create()
    {
        $user = Auth::user();
        
        if ($user->type === 'Admin') {
            $departments = Department::active()->get();
        } else {
            $departments = Department::where('id', $user->department_id)->get();
        }

        return view('document-types.create', compact('departments'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => [
                'required',
                'exists:departments,id',
                // If user is Head, they can only create for their department
                $user->type === 'Head' ? Rule::in([$user->department_id]) : ''
            ],
            'default_process_time' => 'required|integer|min:1',
            'default_time_unit' => 'required|in:minutes,hours,days,weeks',
            'default_difficulty' => 'required|in:normal,important,urgent,immediate',
            'required_fields' => 'nullable|array',
            'required_fields.*' => 'string|max:255',
            'instructions' => 'nullable|string',
            'status' => 'boolean'
        ]);

        // Check for duplicate name in the same department
        $exists = DocumentType::where('name', $validated['name'])
            ->where('department_id', $validated['department_id'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A document type with this name already exists in the selected department.'])->withInput();
        }

        // Filter out empty required fields
        if (isset($validated['required_fields'])) {
            $validated['required_fields'] = array_filter($validated['required_fields'], function($field) {
                return !empty(trim($field));
            });
        }

        $documentType = DocumentType::create($validated);

        return redirect()->route('document-types.index')
            ->with('success', 'Document type created successfully.');
    }

    public function show(DocumentType $documentType)
    {
        $user = Auth::user();
        
        // Check if user can view this document type
        if ($user->type === 'Head' && $documentType->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access to this document type.');
        }

        return view('document-types.show', compact('documentType'));
    }

    public function edit(DocumentType $documentType)
    {
        $user = Auth::user();
        
        // Check if user can edit this document type
        if ($user->type === 'Head' && $documentType->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access to this document type.');
        }

        if ($user->type === 'Admin') {
            $departments = Department::active()->get();
        } else {
            $departments = Department::where('id', $user->department_id)->get();
        }

        return view('document-types.edit', compact('documentType', 'departments'));
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $user = Auth::user();
        
        // Check if user can update this document type
        if ($user->type === 'Head' && $documentType->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access to this document type.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'department_id' => [
                'required',
                'exists:departments,id',
                // If user is Head, they can only update for their department
                $user->type === 'Head' ? Rule::in([$user->department_id]) : ''
            ],
            'default_process_time' => 'required|integer|min:1',
            'default_time_unit' => 'required|in:minutes,hours,days,weeks',
            'default_difficulty' => 'required|in:normal,important,urgent,immediate',
            'required_fields' => 'nullable|array',
            'required_fields.*' => 'string|max:255',
            'instructions' => 'nullable|string',
            'status' => 'boolean'
        ]);

        // Check for duplicate name in the same department (excluding current record)
        $exists = DocumentType::where('name', $validated['name'])
            ->where('department_id', $validated['department_id'])
            ->where('id', '!=', $documentType->id)
            ->exists();

        if ($exists) {
            return back()->withErrors(['name' => 'A document type with this name already exists in the selected department.'])->withInput();
        }

        // Filter out empty required fields
        if (isset($validated['required_fields'])) {
            $validated['required_fields'] = array_filter($validated['required_fields'], function($field) {
                return !empty(trim($field));
            });
        }

        $documentType->update($validated);

        return redirect()->route('document-types.index')
            ->with('success', 'Document type updated successfully.');
    }

    public function destroy(DocumentType $documentType)
    {
        $user = Auth::user();
        
        // Check if user can delete this document type
        if ($user->type === 'Head' && $documentType->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access to this document type.');
        }

        $documentType->delete();

        return redirect()->route('document-types.index')
            ->with('success', 'Document type deleted successfully.');
    }

    public function toggleStatus(DocumentType $documentType)
    {
        $user = Auth::user();
        
        // Check if user can toggle this document type
        if ($user->type === 'Head' && $documentType->department_id !== $user->department_id) {
            abort(403, 'Unauthorized access to this document type.');
        }

        $documentType->update(['status' => !$documentType->status]);

        $status = $documentType->status ? 'activated' : 'deactivated';
        
        return redirect()->route('document-types.index')
            ->with('success', "Document type {$status} successfully.");
    }

    // API endpoint to get document types by department
    public function getByDepartment($departmentId)
    {
        $documentTypes = DocumentType::active()
            ->byDepartment($departmentId)
            ->select('id', 'name', 'default_process_time', 'default_time_unit', 'default_difficulty')
            ->orderBy('name')
            ->get();

        return response()->json($documentTypes);
    }
}

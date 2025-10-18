<?php

namespace App\Http\Controllers\Document;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\DocumentType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DocumentTypeController extends Controller
{
      public function index()
    {
        $user = auth()->user();

        if ($user->type !== 'Head') {
            abort(403, 'Unauthorized access');
        }

        $documentTypes = DocumentType::active()
            ->orderBy('title')
            ->paginate(10);

        $departments = Department::orderBy('name')->get();


        return view('document-types.index', compact('documentTypes', 'departments'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();
        if ($user->type !== 'Head') {
            abort(403, 'Unauthorized access');
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255|unique:document_types,title',
            'description' => 'nullable|string|max:100'
        ]);

        $validated['department_id'] = auth()->user()->department_id;

        DocumentType::create($validated);

        return redirect()->back()->with('success', 'Document type created successfully!');
    }

    public function update(Request $request, DocumentType $documentType)
    {
        $validated = $request->validate([
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('document_types')->ignore($documentType->id)
            ],
            'description' => 'nullable|string|max:100',
        ]);

        $documentType->update($validated);

        return redirect()->back()->with('success', 'Document Type Updated');
    }
}

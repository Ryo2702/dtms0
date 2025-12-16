<?php

namespace App\Http\Controllers\Admin\Departments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\DepartmentRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        $sort = request()->get('sort', 'name');
        $direction = request()->get('direction', 'asc');

        // Validate sort field
        $allowedSorts = ['name', 'code', 'status'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'name';
        }

        $departments = Department::where('name', 'not like', '%Admin%')
            ->orderBy($sort, $direction)
            ->paginate(8)
            ->appends(['sort' => $sort, 'direction' => $direction]);;
        return view('admin.departments.index', compact('departments'));
    }

    /**
     * @param DepartmentRequest|\Illuminate\Http\Request $request
     */
    public function store(DepartmentRequest $request)
    {
        try {

            $code = $this->generateCodeTitle($request->input('title'));

            $data = [
                'name' => $request->input('title'),
                'code' => $code,
                'description' => $request->input('description'),
                'status' => $request->has('status') ? 1 : 0
            ];

            if ($request->hasFile('logo')) {
                $logoPath = $request->file('logo')->store('departments/logos', 'public');
                $data['logo'] = $logoPath;
            }

            Department::create($data);

            return redirect()
                ->route('admin.departments.index')
                ->with('success', 'Department created successfully.');
        } catch (\Exception $e) {
            Log::error('Department creation failed: ' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Department creation failed.');
        }
    }

    public function edit($id)
    {
        $department = Department::findOrFail($id);

        return response()->json([
            'id' => $department->id,
            'name' => $department->name,
            'code' => $department->code,
            'description' => $department->description,
            'status' => $department->status,
            'logo' => $department->logo,
            'logo_url' => $department->getLogoUrl(),
            'active_users_count' => $department->getActiveUsersCount(),
            'total_users_count' => $department->getTotalUsersCount(),
            'created_at' => $department->created_at,
            'update_at' => $department->updated_at
        ]);
    }
    /**
     * @param DepartmentRequest|\Illuminate\Http\Request $request
     */

    public function update(DepartmentRequest $request, $id)
    {
        try {
            $department = Department::findOrFail($id);

            //if only generate new code based on the title(name)
            $code = $request->input('title') !== $department->name
                ? $this->generateCodeTitle($request->input('title'))
                : $department->code;


            $data = [
                'name' => $request->input('title'),
                'code' => $code,
                'description' => $request->input('description') ?? $department->description,
                'status' => $request->has('status') ? 1 : 0
            ];

            if ($request->hasFile('logo')) {
                if ($department->logo && Storage::disk('public')->exists($department->logo)) {
                    Storage::disk('public')->delete($department->logo);
                };

                $logoPath = $request->file('logo')->store('departments/logos', 'public');
                $data['logo'] = $logoPath;
            }

            $department->update($data);

            return redirect()
                ->route('admin.departments.index')
                ->with('success', 'Department updated successfully');
        } catch (\Exception $e) {
            Log::error('Department update failed' . $e->getMessage());
            return back()
                ->withInput()
                ->with('error', 'Department update failed');
        }
    }

    private function generateCodeTitle(string $title): string
    {
        $words = explode(' ', $title);
        $code = '';

        foreach ($words as $word) {
            if (!empty($word)) {
                $code .= strtoupper(substr($word, 0, 1));
            }
        }

        return $code ?: 'DEPT';
    }

    public function users($id)
    {
        $department = Department::findOrFail($id);
        $users = $department->users()->paginate(10);
        $availableUsers = User::whereNull('department_id')
            ->where('name', 'not like', '%Admin%')
            ->orWhere('department_id', '!=', $id)
            ->get();


        return response()->json([
            'department' => [
                'id' => $department->id,
                'name'  => $department->name,
                'code' => $department->code
            ],
            'users' => $users,
            'available_users' => $availableUsers
        ]);
    }


    public function assignUser(Request $request, $id)
    {
        $request->validate(
            [
                'user_id' => 'required|exists:users,id'
            ]
        );

        try {
            $department = Department::findOrFail($id);
            $user = User::findOrFail($request->user_id);

            if ($user->department_id && $user->department_id != $id) {
                return back()->with('error', 'User is already assigned to another department.');
            }

            $user->department_id = $id;
            $user->save();

            return back()->with('success', 'User assigned successfully.');
        } catch (\Exception $e) {
        }
    }
    public function removeUser(Request $request, $id)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        try {
            $department = Department::findOrFail($id);
            $user = User::findOrFail($request->user_id);

            if ($user->department_id != $id) {
                return back()->with('error', 'User is not in this department.');
            }

            $user->department_id = null;
            $user->save();

            return back()->with('success', 'User removed successfully.');
        } catch (\Exception $e) {
            Log::error('User removal failed: ' . $e->getMessage());
            return back()->with('error', 'Failed to remove user.');
        }
    }

    public function documentTags($id)
    {
        $department = Department::findOrFail($id);
        $tags = $department->documentTags()
            ->with('workflows')
            ->get()
            ->map(function ($tag) {
                return [
                    'id' => $tag->id,
                    'name' => $tag->name,
                    'slug' => $tag->slug,
                    'description' => $tag->description,
                    'status' => $tag->status,
                    'workflows_count' => $tag->workflows->count(),
                    'created_at' => $tag->created_at
                ];
            });

        return response()->json([
            'department' => [
                'id' => $department->id,
                'name' => $department->code,
                'code' => $department->code
            ],
            'document_tags' => $tags
        ]);
    }

    public function workflowWithTags($id)
    {
        $department = Department::findOrFail($id);
        $workflows = $department->getWorkflowsWithTags()
            ->with(['transactionType', 'documentTags'])
            ->get()
            ->map(function ($workflow) use ($department) {
                return [
                    'id' => $workflow->id,
                    'transaction_type' => $workflow->transactionType->transaction_name,
                    'description' => $workflow->description,
                    'difficulty' => $workflow->difficulty,
                    'status' => $workflow->status,
                    'tags_from_department' =>  $workflow->documentTags
                        ->where('department_id', $department->id)
                        ->values()
                        ->map(function ($tag) {
                            return [
                                'id' => $tag->id,
                                'name' => $tag->name,
                                'is_required' => $tag->pivot->is_required
                            ];
                        }),
                ];
            });

        return response()->json([
            'department' => [
                'id' => $department->id,
                'name' => $department->name
            ],
            'workflows' => $workflows
        ]);
    }

    public function activeDocumentTags($id)
    {
        $department = Department::findOrFail($id);
        $tags = $department->getActiveDocumentTags()
            ->get(['id', 'name', 'slug']);
            
        return response()->json($tags);
    }
}

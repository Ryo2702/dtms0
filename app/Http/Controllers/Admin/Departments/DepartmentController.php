<?php

namespace App\Http\Controllers\Admin\Departments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Department\DepartmentRequest;
use App\Models\Department;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
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
}

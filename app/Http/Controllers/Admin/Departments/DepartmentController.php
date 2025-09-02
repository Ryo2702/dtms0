<?php

namespace App\Http\Controllers\Admin\Departments;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Department\DepartmentRequest;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DepartmentController extends Controller
{
    use AuthorizesRequests;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Department::with(['head', 'staff'])->latest();

        // Filter by status
        $status = $request->get('status', 'active');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'inactive') {
            $query->where('status', 0);
        }

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('code', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        $departments = $query->paginate(10)->withQueryString();

        // Get counts for dashboard
        $totalDepartments = Department::count();
        $activeDepartments = Department::active()->count();
        $inactiveDepartments = Department::where('status', 0)->count();
        $departmentsWithHeads = Department::active()->whereHas('head')->count();

        return view('admin.departments.index', compact(
            'departments',
            'totalDepartments',
            'activeDepartments',
            'inactiveDepartments',
            'departmentsWithHeads'
        ));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $this->authorize('create', Department::class);

        return view('admin.departments.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(DepartmentRequest $request)
    {
        try {
            $this->authorize('create', Department::class);

            $data = $request->validated();

            // head validation moved to DepartmentRequest

            DB::transaction(function () use ($request, $data) {
                // Handle logo upload
                if ($request->hasFile('logo')) {
                    $logo = $request->file('logo');
                    $filename = time() . '_' . $logo->getClientOriginalName();
                    $data['logo'] = $logo->storeAs('departments', $filename, 'public');
                }

                $data['status'] = $data['status'] ?? 1;

                // Create department
                $department = Department::create($data);

                // Create department head (only if head fields provided)
                if ($request->filled('head_email')) {
                    $head = User::create([
                        'name'          => $request->input('head_name'),
                        'email'         => $request->input('head_email'),
                        'password'      => Hash::make($request->input('head_password')),
                        'department_id' => $department->id,
                        'type'          => 'Head',
                        'status'        => 1,
                    ]);

                    // Ensure role exists and assign
                    \Spatie\Permission\Models\Role::firstOrCreate(
                        ['name' => 'Head', 'guard_name' => 'web']
                    );
                    $head->assignRole($head->type);
                }
            });

            return redirect()->route('admin.departments.index')
                ->with('success', "Department and Head created successfully!");
        } catch (\Exception $e) {
            Log::error('Department creation with head failed:', [
                'error' => $e->getMessage(),
                'data' => $request->all()
            ]);

            return back()->with('error', 'Failed to create department and head. Please try again.')
                ->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Department $department)
    {

        $department->load(['head', 'staff', 'admin']);

        // Get department statistics
        $stats = [
            'total_users' => $department->getTotalUsersCount(),
            'active_users' => $department->getActiveUsersCount(),
            'staff_count' => $department->getStaffCount(),
            'active_staff' => $department->getActiveStaffCount(),
            'has_head' => $department->hasHead(),
            'has_admin' => $department->HasAdmin(),
        ];

        return view('admin.departments.show', compact('department', 'stats'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Department $department)
    {
        $this->authorize('update', $department);

        return view('admin.departments.edit', compact('department'));
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(DepartmentRequest $request, Department $department)
    {
        try {
            $this->authorize('update', $department);

            $data = $request->validated();

            // Handle logo upload
            if ($request->hasFile('logo')) {
                // Delete old logo if exists
                if ($department->logo && Storage::disk('public')->exists($department->logo)) {
                    Storage::disk('public')->delete($department->logo);
                }

                $logo = $request->file('logo');
                $filename = time() . '_' . $logo->getClientOriginalName();
                $data['logo'] = $logo->storeAs('departments', $filename, 'public');
            }

            $originalCode = $department->code;

            $department->update($data);

            // If code changed, regenerate municipal IDs for all users in this department
            if ($originalCode !== $department->code) {
                $this->regenerateMunicipalIds($department);
            }

            return redirect()->route('admin.departments.index')
                ->with('success', "Department '{$department->name}' updated successfully!");
        } catch (\Exception $e) {
            Log::error('Department update failed:', [
                'error' => $e->getMessage(),
                'department_id' => $department->id,
                'data' => $request->all()
            ]);

            return back()->with('error', 'Failed to update department. Please try again.')
                ->withInput();
        }
    }


    /**
     * Regenerate municipal IDs for all users in department
     */
    private function regenerateMunicipalIds(Department $department)
    {
        $users = $department->users()->get();

        foreach ($users as $user) {
            $user->municipal_id = $department->generateMunicipalId($user->type);
            $user->save();
        }

        Log::info("Regenerated municipal IDs for department {$department->name}", [
            'department_id' => $department->id,
            'users_updated' => $users->count()
        ]);
    }
}

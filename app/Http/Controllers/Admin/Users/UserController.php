<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Requests\Admin\User\UserRequest;
use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use App\Models\UserArchive;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index(Request $request)
    {
        $query = User::with('department')->latest();

        // Filter by status
        $status = $request->get('status', 'active');
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'inactive') {
            $query->inactive();
        }
        // If status is 'all', don't apply any status filter

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search by name, email, or municipal_id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('municipal_id', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(15)->withQueryString();

        // Get departments for filter dropdown
        $departments = Department::active()->orderBy('name')->get();

        // Get counts for dashboard
        $activeAdminCount = User::where('type', 'Admin')->active()->count();
        $activeHeadCount = User::where('type', 'Head')->active()->count();
        $activeStaffCount = User::where('type', 'Staff')->active()->count();
        $inactiveUsersCount = User::inactive()->count();

        return view('admin.users.index', compact(
            'users',
            'departments',
            'activeAdminCount',
            'activeHeadCount',
            'activeStaffCount',
            'inactiveUsersCount'
        ));
    }

    public function create()
    {
        $this->authorize('create', User::class);

        $departments = Department::active()->orderBy('name')->get();
        $types = ['Staff', 'Head', 'Admin'];

        $municipality = Auth::user()->municipality ?? (object)['id' => 1, 'name' => 'Default Municipality'];

        return view('admin.users.create', compact('departments', 'types', 'municipality'));
    }
    public function store(UserRequest $request)
    {
        $this->authorize('create', User::class);

        $data = $request->validated();


        $data['password'] = Hash::make($data['password']);
        $data['status'] = 1;


        // Check if department already has a head (if creating a head)
        if ($data['type'] === 'Head') {
            $department = Department::find($data['department_id']);
            if ($department && $department->hasHead()) {
                return back()->with('error', 'This department already has a head assigned.')
                    ->withInput();
            }
        }

        // Check admin limit (optional - you can set a limit)
        if ($data['type'] === 'Admin') {
            $adminCount = User::where('type', 'Admin')->active()->count();
            if ($adminCount >= 5) { // Set your admin limit
                return back()->with('error', 'Maximum number of admin users reached.')
                    ->withInput();
            }
        }

        $user = User::create($data);

        // Assign role if you're using Spatie Permission package
        if (method_exists($user, 'assignRole')) {
            $user->assignRole($data['type']);
        }

        return redirect()->route('admin.users.index')
            ->with('success', "User created successfully! Municipal ID: {$user->municipal_id}");
    }


    public function edit(User $user)
    {
        $this->authorize('update', $user);

        $departments = Department::active()->orderBy('name')->get();
        $types = ['Staff', 'Head', 'Admin'];

        return view('admin.users.edit', compact('user', 'departments', 'types'));
    }

    public function update(UserRequest $request, User $user)
    {
        $this->authorize('update', $user);

        $data = $request->validated();

        // Handle password update
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        /** @var User $authUser */
        $authUser = Auth::user();

        // Prevent non-admins from updating admin accounts
        if ($user->type === 'Admin' && !$authUser->isAdmin()) {
            abort(403, 'Unauthorized Action');
        }

        // Prevent deactivating admin accounts
        if ($user->type === 'Admin') {
            $data['status'] = 1;
        }

        // Check if changing to Head type and department already has head
        if (isset($data['type']) && $data['type'] === 'Head' && $user->type !== 'Head') {
            $department = Department::find($data['department_id'] ?? $user->department_id);
            if ($department && $department->hasHead()) {
                return back()->with('error', 'This department already has a head assigned.')
                    ->withInput();
            }
        }

        // Store original values for comparison
        $originalDepartment = $user->department_id;
        $originalType = $user->type;

        $user->update($data);

        // Municipal ID will be automatically regenerated by the model's boot method
        // if department or type changed

        // Sync roles if using Spatie Permission
        if (method_exists($user, 'syncRoles')) {
            $user->syncRoles([$user->type]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User updated successfully!');
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load('department');
        $activeAdminCount = User::where('type', 'Admin')->active()->count();

        return view('admin.users.show', compact('user', 'activeAdminCount'));
    }

    public function destroy(User $user)
    {
        // This method now redirects to deactivate
        return $this->deactivate($user);
    }

    public function deactivate(User $user, Request $request = null)
    {
        $this->authorize('delete', $user);

        // Prevent deactivating yourself
        if ($user->id === Auth::id()) {
            return back()->with('error', 'You cannot deactivate your own account.');
        }

        // Check if this is the only active admin account
        if ($user->type === 'Admin') {
            $activeAdminCount = User::where('type', 'Admin')->active()->count();
            if ($activeAdminCount <= 1) {
                return back()->with('error', 'Cannot deactivate the last active admin account. System must have at least one active administrator.');
            }
        }

        $reason = $request ? $request->input('reason', 'Deactivated by admin') : 'Deactivated by admin';
        $user->deactivate($reason);

        return back()->with('success', 'User account has been deactivated and archived successfully!');
    }
    public function reactivate(User $user)
    {
        $this->authorize('delete', $user);

        // Check if reactivating a Head and department already has one
        if ($user->type === 'Head') {
            $department = $user->department;
            if ($department && $department->hasHead()) {
                return back()->with('error', 'Cannot reactivate: This department already has an active head.');
            }
        }

        $user->activate();

        return back()->with('success', 'User account has been reactivated successfully!');
    }

    public function archives()
    {
        return view('admin.archives.archives');
    }

    public function userAccounts(Request $request)
    {

        $query = UserArchive::with(['user', 'department', 'deactivatedBy'])
            ->latest('deactivated_at');

        // Filter by department
        if ($request->filled('department_id')) {
            $query->where('department_id', $request->department_id);
        }

        // Filter by type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Search in archived records
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('municipal_id', 'like', "%{$search}%");
            });
        }

        $archives = $query->paginate(15)->withQueryString();

        $departments = Department::active()->orderBy('name')->get();
        $activeAdminCount = User::where('type', 'Admin')->active()->count();

        return view('admin.archives.user-accounts', compact('archives', 'departments', 'activeAdminCount'));
    }
    public function showArchive(UserArchive $archive)
    {
        $archive->load(['user', 'department', 'deactivatedBy']);
        $activeAdminCount = User::where('type', 'Admin')->active()->count();

        return view('admin.archives.archive-detail', compact('archive', 'activeAdminCount'));
    }
}

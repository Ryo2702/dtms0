<?php

namespace App\Http\Controllers\Admin\Users;

use App\Http\Controllers\Controller;
use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;

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
                    ->orWhere('employee_id', 'like', "%{$search}%");
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

        // For system admin: show staff grouped under each head
        $heads = User::where('type', 'Head')->with(['department.staff'])->get();

        return view('admin.users.index', compact(
            'users',
            'departments',
            'activeAdminCount',
            'activeHeadCount',
            'activeStaffCount',
            'inactiveUsersCount',
            'heads'
        ));
    }

    public function show(User $user)
    {
        $this->authorize('view', $user);

        $user->load('department');
        $activeAdminCount = User::where('type', 'Admin')->active()->count();

        return view('admin.users.show', compact('user', 'activeAdminCount'));
    }
}

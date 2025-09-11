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
        $query = User::with('department');

        $sortField = $request->get('sort', 'created_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['id', 'name', 'email', 'employee_id', 'created_at', 'type'];
        $allowedDirections = ['asc', 'desc'];

        if (!in_array($sortField, $allowedSorts)) {
            $sortField = 'created_at';
        }
        if (!in_array($sortDirection, $allowedDirections)) {
            $sortDirection = 'desc';
        }

        $query->orderBy($sortField, $sortDirection);

        // Filter by status
        $status = $request->get('status');
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

        // Search by name, email, or employee_id
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('employee_id', 'like', "%{$search}%");
            });
        }

        $users = $query->paginate(10)->withQueryString();

        $departments = Department::active()->orderBy('name')->get();

        // Get counts
        $activeHeadCount = User::where('type', 'Head')->active()->count();
        $activeStaffCount = User::where('type', 'Staff')->active()->count();
        $inactiveUsersCount = User::inactive()->count();

        $heads = User::where('type', 'Head')->with(['department.staff'])->get();

        // If it's an AJAX request, return only the table content
        if ($request->ajax()) {
            return view('admin.users.partials.table', compact(
                'users',
                'sortField',
                'sortDirection'
            ));
        }

        return view('admin.users.index', compact(
            'users',
            'departments',
            'activeHeadCount',
            'activeStaffCount',
            'inactiveUsersCount',
            'heads',
            'sortField',
            'sortDirection'
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

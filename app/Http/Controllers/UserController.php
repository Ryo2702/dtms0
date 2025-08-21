<?php

namespace App\Http\Controllers;

use App\Http\Requests\Admin\User\UserRequest;
use App\Models\User;
use App\Models\UserArchive;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    use AuthorizesRequests;

    public function index()
    {
        // Show only active users by default, or add filter option
        $users = User::latest()->paginate(10);
        $activeAdminCount = User::role('Admin')->active()->count();
        return view('admin.users.index', compact('users', 'activeAdminCount'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(UserRequest $request)
    {

        $this->authorize('create', User::class);
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        $user = User::create($data);
        $user->assignRole($data['type']);

        if ($request->role) {
            $user->assignRole($request->role);
        }

        return redirect()->route('admin.users.index')->with('success', 'User created successfully!');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(UserRequest $request, User $user)
    {
        $data = $request->validated();
        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        $user->update($data);
        $user->syncRoles([$data['type']]);

        return redirect()->route('admin.users.index')->with('success', 'User updated successfully!');
    }

    public function destroy(User $user)
    {
        // This method now redirects to deactivate
        return $this->deactivate($user);
    }

    public function deactivate(User $user, Request $request = null)
    {
        $this->authorize('delete', $user);

        // Check if this is the only active admin account
        if ($user->hasRole('Admin')) {
            $activeAdminCount = User::role('Admin')->active()->count();
            if ($activeAdminCount <= 1) {
                return back()->with('error', 'Cannot deactivate the last active admin account. System must have at least one active administrator.');
            }
        }

        $reason = 'Deactivated by admin';
        $user->deactivate($reason);

        return back()->with('success', 'User account has been deactivated and archived successfully!');
    }
    public function reactivate(User $user)
    {
        $this->authorize('delete', $user);

        $user->activate();

        return back()->with('success', 'User account has been reactivated successfully!');
    }

    public function archives()
    {
        return view('admin.archives.archives');
    }

    public function userAccounts()
    {
        $archives = UserArchive::with(['user', 'deactivatedBy'])
            ->latest('deactivated_at')
            ->paginate(10);
        $activeAdminCount = User::role('Admin')->active()->count();

        return view('admin.archives.user-accounts', compact('archives', 'activeAdminCount'));
    }
    public function showArchive(UserArchive $archive)
    {
        $archive->load(['user', 'deactivatedBy']);
        $activeAdminCount = User::role('Admin')->active()->count();
        return view('admin.archives.archive-detail', compact('archive', 'activeAdminCount'));
    }
}

<?php

namespace App\Http\Controllers\Head\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class StaffController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = Auth::user();

        // Simple pagination (fixed page size)
        $staff = User::byStaff()
            ->where('department_id', $user->department_id)
            ->paginate(15)
            ->withQueryString();

        return view('head.staff.index', compact('staff'));
    }

    public function create()
    {
        return view('head.staff.create');
    }

    public function store(Request $request)
    {
        /** @var User $CurrentUser */
        $CurrentUser = Auth::user();

        $validated = $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required'
        ]);

        $user = User::create([
            'name'          => $validated['name'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'department_id' => $CurrentUser->department_id,
            'type'          => 'Staff',
            'status'        => 1,
        ]);

        $user->assignRole('Staff');

        return redirect()->route('head.staff.index')->with('success', 'Created Successfully.');
    }

    public function show($id)
    {
        /** @var User $user */
        $user = Auth::user();

        $staff = User::byStaff()
            ->where('department_id', $user->department_id)
            ->findOrFail($id);

        return view('head.staff.show', compact('staff'));
    }

    // Add edit method required by Route::resource
    public function edit(User $staff)
    {
        /** @var User $user */
        $user = Auth::user();

        // ensure the head can only edit staff in their department
        $staff = User::byStaff()
            ->where('department_id', $user->department_id)
            ->findOrFail($staff->id);

        return view('head.staff.edit', compact('staff'));
    }

    // Optional: update method to handle form submission from edit
    public function update(Request $request, User $staff)
    {
        /** @var User $user */
        $user = Auth::user();

        $staff = User::byStaff()
            ->where('department_id', $user->department_id)
            ->findOrFail($staff->id);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', \Illuminate\Validation\Rule::unique('users', 'email')->ignore($staff->id)],
            'password' => 'nullable|string|min:8|confirmed',
            'status' => 'nullable|boolean',
        ]);

        $staff->name = $validated['name'];
        $staff->email = $validated['email'];
        if (!empty($validated['password'])) {
            $staff->password = Hash::make($validated['password']);
        }
        if (array_key_exists('status', $validated)) {
            $staff->status = (bool) $validated['status'];
        }
        $staff->save();

        return redirect()->route('head.staff.index')->with('success', 'Staff updated successfully.');
    }
}

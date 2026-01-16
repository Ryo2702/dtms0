<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AssignStaff;
use App\Models\Department;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;

class StaffController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->type !== 'Head') {
            abort(403, 'Unauthorized');
        }

        $staffs = AssignStaff::where('department_id', $user->department_id)
            ->orderBy('full_name')
            ->paginate(10);

        $departments = Department::orderBy('name')->get();

        return view('staff.index', compact('staffs', 'departments'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->type !== 'Head') {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'role' => 'required|string|max:255',
        ]);

        $validated['department_id'] = $user->department_id;

        AssignStaff::create($validated);

        return redirect()->back()->with('success', 'Success Staff Created');
    }

    public function update(Request $request, AssignStaff $staff) {
        
        $user = Auth::user();

        if ($user->type !== 'Head') {
            abort(403, 'Unauthorized');
        }

        if ($staff->department_id !== $user->department_id) {
            abort(403, 'Unauthorize person');
        }
        $validated = $request->validate([
            'full_name' => [
                'required', 
                'string', 
                Rule::unique('assign_staff', 'full_name')->ignore($staff->id)->where('department_id', $user->department_id)
            ],
            'position' => 'nullable|string|max:255',
            'role' => [
                'required', 
                'string', 
                'max:255', 
                Rule::unique('assign_staff','role')->ignore($staff->id)->where('department_id', $user->department_id)
            ]
        ]);

        $staff->update($validated);

        return redirect()->back()->with('success', 'Staff Updated');
    }

}

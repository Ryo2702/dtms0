<?php

namespace App\Http\Controllers\Staff;

use App\Http\Controllers\Controller;
use App\Models\AssignStaff;
use App\Models\Department;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->type !== 'Head') {
            abort(403, 'Unauthorized');
        }

        $staffs = AssignStaff::orderBy('full_name')
            ->paginate(10);

        $departments = Department::orderBy('name')->get();

        return view('staff.index', compact('staffs', 'departments'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($user->type !== 'Head') {
            abort(403, 'Unauthorized');
        }

        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'position' => 'nullable|string|max:255',
            'role' => 'required|string|max:255',
        ]);

        $validated['department_id'] = auth()->user()->department_id;

        AssignStaff::create($validated);

        return redirect()->back()->with('success', 'Success Staff Created');
    }


}

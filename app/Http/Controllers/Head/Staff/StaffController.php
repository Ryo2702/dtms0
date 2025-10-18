<?php

namespace App\Http\Controllers\Head\Staff;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class StaffController extends Controller
{
    public function index()
    {
        return view('head.staff.index');
    }

    public function create()
    {
        return view('head.staff.create');
    }

    public function store(Request $request)
    {
        return redirect()->route('head.staff.index')->with('success', 'Created Successfully.');
    }

    public function show($id)
    {
        return view('head.staff.show', compact('staff'));
    }

    public function edit(User $staff)
    {
        return view('head.staff.edit', compact('staff'));
    }

    public function update(Request $request, User $staff)
    {
        return redirect()->route('head.staff.index')->with('success', 'Staff updated successfully.');
    }
}

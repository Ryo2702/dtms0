@extends('layouts.app')

@section('content')
    <div class="p-6 max-w-lg mx-auto">
        <h1 class="text-2xl font-bold mb-4">Create User</h1>

        <form method="POST" action="{{ route('admin.users.store') }}" class="space-y-4">
            @csrf

            <input type="text" name="municipal_id" placeholder="Municipal ID" class="input input-bordered w-full" required>
            <input type="text" name="name" placeholder="Name" class="input input-bordered w-full" required>
            <input type="email" name="email" placeholder="Email" class="input input-bordered w-full" required>
            <input type="password" name="password" placeholder="Password" class="input input-bordered w-full" required>

            <input type="text" name="department" placeholder="Department" class="input input-bordered w-full">

            <select name="type" class="select select-bordered w-full" required>
                <option value="Staff">Staff</option>
                <option value="Head">Head</option>
                <option value="Admin">Admin</option>
            </select>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="status" value="1" checked class="checkbox">
                <span>Active</span>
            </label>

            <button class="btn btn-primary w-full">Save</button>
        </form>
    </div>
@endsection

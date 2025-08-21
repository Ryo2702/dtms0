@extends('layouts.app')

@section('content')
    <div class="p-6 max-w-lg mx-auto">
        <h1 class="text-2xl font-bold mb-4">Edit User</h1>

        <form method="POST" action="{{ route('admin.users.update', $user) }}" class="space-y-4">
            @csrf @method('PUT')

            <input type="text" name="municipal_id" value="{{ $user->municipal_id }}" class="input input-bordered w-full"
                required>
            <input type="text" name="name" value="{{ $user->name }}" class="input input-bordered w-full" required>
            <input type="email" name="email" value="{{ $user->email }}" class="input input-bordered w-full" required>
            <input type="password" name="password" placeholder="Leave blank to keep current"
                class="input input-bordered w-full">

            <input type="text" name="department" value="{{ $user->department }}" class="input input-bordered w-full">

            <select name="type" class="select select-bordered w-full" required>
                <option value="Staff" @selected($user->type == 'Staff')>Staff</option>
                <option value="Head" @selected($user->type == 'Head')>Head</option>
                <option value="Admin" @selected($user->type == 'Admin')>Admin</option>
            </select>

            <label class="flex items-center gap-2">
                <input type="checkbox" name="status" value="1" {{ $user->status ? 'checked' : '' }} class="checkbox">
                <span>Active</span>
            </label>

            <button class="btn btn-primary w-full">Update</button>
        </form>
    </div>
@endsection

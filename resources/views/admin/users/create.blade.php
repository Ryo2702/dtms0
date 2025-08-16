@extends('layouts.app')

@section('content')
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">Add New User</h2>

        @if ($errors->any())
            <div class="alert alert-error mb-4">
                <div>
                    <h3 class="font-bold">Please fix the following errors:</h3>
                    <ul class="list-disc list-inside">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <form action="{{ route('admin.users.store') }}" method="POST" class="space-y-4">
            @csrf

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Name <span class="text-red-500">*</span></span>
                </label>
                <input type="text" name="name" class="input input-bordered @error('name') input-error @enderror"
                    required value="{{ old('name') }}" placeholder="Enter full name">
                @error('name')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Email <span class="text-red-500">*</span></span>
                </label>
                <input type="email" name="email" class="input input-bordered @error('email') input-error @enderror"
                    required value="{{ old('email') }}" placeholder="user@example.com">
                @error('email')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Municipal ID <span class="text-red-500">*</span></span>
                </label>
                <input type="text" name="municipal_id"
                    class="input input-bordered @error('municipal_id') input-error @enderror" required
                    value="{{ old('municipal_id') }}" placeholder="Enter municipal ID">
                @error('municipal_id')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Department <span class="text-red-500">*</span></span>
                </label>
                <input type="text" name="department"
                    class="input input-bordered @error('department') input-error @enderror" required
                    value="{{ old('department') }}" placeholder="Enter department">
                @error('department')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Role <span class="text-red-500">*</span></span>
                </label>
                <select name="role" class="select select-bordered @error('role') select-error @enderror" required>
                    <option value="">Select Role</option>
                    @foreach ($roles as $roleId => $roleName)
                        <option value="{{ $roleId }}" {{ old('role') == $roleId ? 'selected' : '' }}>
                            {{ $roleName }}
                        </option>
                    @endforeach
                </select>
                @error('role')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Password <span class="text-red-500">*</span></span>
                </label>
                <input type="password" name="password" class="input input-bordered @error('password') input-error @enderror"
                    required placeholder="Minimum 6 characters">
                @error('password')
                    <label class="label">
                        <span class="label-text-alt text-error">{{ $message }}</span>
                    </label>
                @enderror
            </div>

            <div class="form-control">
                <label class="label">
                    <span class="label-text">Confirm Password <span class="text-red-500">*</span></span>
                </label>
                <input type="password" name="password_confirmation" class="input input-bordered" required
                    placeholder="Confirm your password">
            </div>

            <div class="form-control mt-6">
                <button type="submit" class="btn btn-primary">Save User</button>
                <a href="{{ route('admin.users.index') }}" class="btn btn-ghost">Cancel</a>
            </div>
        </form>
    </div>
@endsection

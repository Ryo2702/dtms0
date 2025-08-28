@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Edit User: {{ $user->name }}" :backRoute="route('admin.users.index')" backText="Back to Users" />

        <div class="bg-base-100 rounded-lg shadow-md p-6">
            <form action="{{ route('admin.users.update', $user) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Municipal ID -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Municipal ID</span>
                        </label>
                        <input type="text" id="municipal_id" name="municipal_id"
                            value="{{ $user->municipal_id ?? 'N/A' }}" readonly
                            class="input input-bordered bg-gray-100 text-gray-700 cursor-not-allowed" />
                        <label class="label">
                            <span class="label-text-alt">Municipal ID will be regenerated if department or type
                                changes</span>
                        </label>
                    </div>

                    <!-- Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $user->name) }}"
                            class="input input-bordered @error('name') input-error @enderror" placeholder="Enter user name"
                            required />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Email</span>
                        </label>
                        <input type="email" name="email" value="{{ old('email', $user->email) }}"
                            class="input input-bordered @error('email') input-error @enderror"
                            placeholder="Enter user email" required />
                        @error('email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Password (Leave blank to keep unchanged)</span>
                        </label>
                        <input type="password" name="password"
                            class="input input-bordered @error('password') input-error @enderror"
                            placeholder="Enter new password" />
                        @error('password')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department</span>
                        </label>
                        <select name="department_id"
                            class="select select-bordered @error('department_id') select-error @enderror">
                            <option value="" disabled>Select a department</option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id', $user->department_id) == $department->id ? 'selected' : '' }}>
                                    {{ $department->name }}</option>
                            @endforeach
                        </select>
                        @error('department_id')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Type -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Type</span>
                        </label>
                        <select name="type" class="select select-bordered @error('type') select-error @enderror"
                            required>
                            <option value="" disabled>Select a type</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}"
                                    {{ old('type', $user->type) == $type ? 'selected' : '' }}>{{ $type }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Status -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Status</span>
                        </label>
                        <select name="status" class="select select-bordered @error('status') select-error @enderror"
                            {{ $user->type === 'Admin' ? 'disabled' : '' }}>
                            <option value="1" {{ old('status', $user->status) == 1 ? 'selected' : '' }}>Active
                            </option>
                            <option value="0" {{ old('status', $user->status) == 0 ? 'selected' : '' }}>Inactive
                            </option>
                        </select>
                        @if ($user->type === 'Admin')
                            <p class="text-sm text-info mt-1">Admin accounts cannot be deactivated.</p>
                        @endif
                        @error('status')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn btn-primary">Update User</button>
                </div>
            </form>
        </div>
    </div>
@endsection

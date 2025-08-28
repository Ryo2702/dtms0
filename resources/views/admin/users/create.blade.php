@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Create User" :backRoute="route('admin.users.index')" backText="Back to Users" />

        <div class="bg-base-100 rounded-lg shadow-md p-6">
            <form action="{{ route('admin.users.store') }}" method="POST">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Name</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
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
                        <input type="email" name="email" value="{{ old('email') }}"
                            class="input input-bordered @error('email') input-error @enderror"
                            placeholder="Enter user email" required />
                        @error('email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Password</span>
                        </label>
                        <input type="password" name="password"
                            class="input input-bordered @error('password') input-error @enderror"
                            placeholder="Enter password" required />
                        @error('password')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Department -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department</span>
                        </label>
                        <select name="department_id" id="department_id"
                            class="select select-bordered @error('department_id') select-error @enderror" required>
                            <option value="" disabled {{ old('department_id') ? '' : 'selected' }}>Select a department
                            </option>
                            @foreach ($departments as $department)
                                <option value="{{ $department->id }}"
                                    {{ old('department_id') == $department->id ? 'selected' : '' }}>{{ $department->name }}
                                    ({{ $department->code }})
                                </option>
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
                        <select name="type" id="type"
                            class="select select-bordered @error('type') select-error @enderror" required>
                            <option value="" disabled {{ old('type') ? '' : 'selected' }}>Select a type</option>
                            @foreach ($types as $type)
                                <option value="{{ $type }}" {{ old('type') == $type ? 'selected' : '' }}>
                                    {{ $type }}</option>
                            @endforeach
                        </select>
                        @error('type')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn btn-primary">Create User</button>
                </div>
            </form>
        </div>
    </div>
@endsection

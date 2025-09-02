@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">Create Department with Head</h1>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-ghost">Back to Departments</a>
        </div>

        <div class="bg-base-100 rounded-lg shadow-md p-6">
            <form action="{{ route('admin.departments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Department Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department Name</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name') }}"
                            class="input input-bordered @error('name') input-error @enderror"
                            placeholder="Enter department name" required />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Department Code -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department Code</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code') }}"
                            class="input input-bordered @error('code') input-error @enderror"
                            placeholder="Enter department code" required />
                        @error('code')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Description -->
                    <div class="form-control md:col-span-2">
                        <label class="label">
                            <span class="label-text">Description</span>
                        </label>
                        <textarea name="description" class="textarea textarea-bordered @error('description') textarea-error @enderror"
                            placeholder="Enter department description">{{ old('description') }}</textarea>
                        @error('description')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Logo -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department Logo</span>
                        </label>
                        <input type="file" name="logo"
                            class="file-input file-input-bordered @error('logo') file-input-error @enderror"
                            accept="image/*" />
                        @error('logo')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <hr class="my-8">

                <h2 class="text-xl font-semibold mb-4">Department Head Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Head Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Head Name</span>
                        </label>
                        <input type="text" name="head_name" value="{{ old('head_name') }}"
                            class="input input-bordered @error('head_name') input-error @enderror"
                            placeholder="Enter head's name" required />
                        @error('head_name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Head Email -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Head Email</span>
                        </label>
                        <input type="email" name="head_email" value="{{ old('head_email') }}"
                            class="input input-bordered @error('head_email') input-error @enderror"
                            placeholder="Enter head's email" required />
                        @error('head_email')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Head Password -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Head Password</span>
                        </label>
                        <input type="password" name="head_password"
                            class="input input-bordered @error('head_password') input-error @enderror"
                            placeholder="Enter password" required />
                        @error('head_password')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Head Password Confirmation -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Confirm Head Password</span>
                        </label>
                        <input type="password" name="head_password_confirmation"
                            class="input input-bordered @error('head_password_confirmation') input-error @enderror"
                            placeholder="Confirm password" required />
                        @error('head_password_confirmation')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn btn-primary">Create Department &amp; Head</button>
                </div>
            </form>
        </div>
    </div>
@endsection

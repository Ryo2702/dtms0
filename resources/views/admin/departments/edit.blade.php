@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">Edit Department: {{ $department->name }}</h1>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-ghost">Back to Departments</a>
        </div>

        <div class="bg-base-100 rounded-lg shadow-md p-6">
            <form action="{{ route('admin.departments.update', $department) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Name -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department Name</span>
                        </label>
                        <input type="text" name="name" value="{{ old('name', $department->name) }}"
                            class="input input-bordered @error('name') input-error @enderror"
                            placeholder="Enter department name" required />
                        @error('name')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Code -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department Code</span>
                        </label>
                        <input type="text" name="code" value="{{ old('code', $department->code) }}"
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
                            placeholder="Enter department description">{{ old('description', $department->description) }}</textarea>
                        @error('description')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Logo -->
                    <div class="form-control">
                        <label class="label">
                            <span class="label-text">Department Logo</span>
                        </label>
                        @if ($department->logo)
                            <div class="mb-2">
                                <img src="{{ Storage::url($department->logo) }}" alt="Current Logo"
                                    class="w-24 h-24 object-cover rounded" />
                            </div>
                        @endif
                        <input type="file" name="logo"
                            class="file-input file-input-bordered @error('logo') file-input-error @enderror"
                            accept="image/*" />
                        @error('logo')
                            <p class="text-error text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                </div>

                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
@endsection

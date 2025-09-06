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
                    <x-form.file label="Department Logo" name="logo" :value="$department->logo" />

                    <x-form.input label="Department Name" name="name" :value="$department->name"
                        placeholder="Enter department name" required />

                    <x-form.input label="Department Code" name="code" :value="$department->code"
                        placeholder="Enter department code" required />

                    <x-form.textarea label="Description" name="description" :value="$department->description"
                        placeholder="Enter department description" class="md:col-span-2" />

                </div>
                <hr class="my-8">

                <h2 class="text-xl font-semibold mb-4">Department Head</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.input label="Head Name" name="head_name" :value="optional($department->head)->name" placeholder="Enter head's name"
                        required />
                    <x-form.input type="email" label="Head Email" name="head_email" :value="optional($department->head)->email"
                        placeholder="Enter head's email" required />
                    <x-form.input type="password" label="Head Password (leave blank to keep current)" name="head_password"
                        placeholder="Enter new password" />
                    <x-form.input type="password" label="Confirm Head Password" name="head_password_confirmation"
                        placeholder="Confirm new password" />
                </div>
                <!-- Submit Button -->
                <div class="mt-6 flex justify-end">
                    <button type="submit" class="btn btn-primary">Update Account</button>
                </div>
            </form>
        </div>
    </div>
@endsection

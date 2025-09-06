@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">

        <x-page-header title="Create Department with Head" :route="route('admin.departments.index')" buttonLabel="Back to Departments"
            icon="move-left" />

        <div class="bg-base-100 rounded-lg shadow-md p-6">
            <form action="{{ route('admin.departments.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.file class="file" label="Department Logo" name="logo"
                        default="{{ asset('images/default-logo.jpg') }}" />
                    <x-form.input label="Department Name" name="name" placeholder="Enter department name" required />
                    <x-form.input label="Department Code" name="code" placeholder="Enter department code" required />
                    <x-form.textarea label="Description" name="description" placeholder="Enter department description"
                        class="md:col-span-2" />
                </div>

                <hr class="my-8">

                <h2 class="text-xl font-semibold mb-4">Department Head Information</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <x-form.input label="Head Name" name="head_name" placeholder="Enter head's name" required />
                    <x-form.input type="email" label="Head Email" name="head_email" placeholder="Enter head's email"
                        required />
                    <x-form.input type="password" label="Head Password" name="head_password" placeholder="Enter password"
                        required />
                    <x-form.input type="password" label="Confirm Head Password" name="head_password_confirmation"
                        placeholder="Confirm password" required />

                    <!-- Submit Button -->
                    <div class="mt-6 flex justify-end text-center">
                        <button type="submit" class="btn btn-primary">Create Department &amp; Head</button>
                    </div>
            </form>
        </div>
    </div>
@endsection

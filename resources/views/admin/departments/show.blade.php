@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">{{ $department->name }}</h1>
            <a href="{{ route('admin.departments.index') }}" class="btn btn-ghost">Back to Departments</a>
        </div>

        <div class="bg-base-100 rounded-lg shadow-md p-6">
            <!-- Department Details -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <div>
                    <h2 class="text-lg font-semibold">Department Details</h2>
                    <div class="mt-4 space-y-2">
                        @if ($department->logo)
                            <p><strong>Logo:</strong></p>
                            <img src="{{ Storage::url($department->logo) }}" alt="{{ $department->name }} Logo"
                                class="w-30 h-30 object-cover rounded" />
                        @endif
                        <p><strong>Code:</strong> {{ $department->code }}</p>
                        <p><strong>Description:</strong> {{ $department->description ?? '—' }}</p>
                        <p><strong>Status:</strong>
                            <span class="badge {{ $department->status ? 'badge-success' : 'badge-error' }}">
                                {{ $department->status ? 'Active' : 'Inactive' }}
                            </span>
                        </p>

                    </div>
                </div>

                <div>
                    <h2 class="text-lg font-semibold">Statistics</h2>
                    <div class="mt-4 space-y-2">
                        <p><strong>Total Users:</strong> {{ $stats['total_users'] }}</p>
                        <p><strong>Active Users:</strong> {{ $stats['active_users'] }}</p>
                        <p><strong>Staff Count:</strong> {{ $stats['staff_count'] }}</p>
                        <p><strong>Has Head:</strong> {{ $stats['has_head'] ? 'Yes' : 'No' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

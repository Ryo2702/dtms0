@extends('layouts.app')

@section('content')
    <div class="card">
        <div class="card-header">
            <h2>User Details</h2>
        </div>
        <div class="card-body">
            <ul>
                <li><strong>Name:</strong> {{ $user->name }}</li>
                <li><strong>Email:</strong> {{ $user->email }}</li>
                <li><strong>Type:</strong> {{ $user->type }}</li>
                <li><strong>Status:</strong> {{ $user->is_active ? 'Active' : 'Inactive' }}</li>
                <li><strong>Department:</strong> {{ $user->department->name ?? 'N/A' }}</li>
                <li><strong>Municipal ID:</strong> {{ $user->employee_id }}</li>
            </ul>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary mt-3">Back to Users</a>
        </div>
    </div>

    <x-modal id="user-details-modal" title="User Details" size="lg">
        <div class="space-y-4">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700">Name</label>
                    <p class="mt-1 text-sm text-gray-900" id="modal-user-name">{{ $user->name ?? '' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Email</label>
                    <p class="mt-1 text-sm text-gray-900" id="modal-user-email">{{ $user->email ?? '' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Type</label>
                    <p class="mt-1 text-sm text-gray-900" id="modal-user-type">{{ $user->type ?? '' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Status</label>
                    <p class="mt-1" id="modal-user-status">
                        <span class="badge badge-success" id="status-active" style="display: none;">Active</span>
                        <span class="badge badge-ghost" id="status-inactive" style="display: none;">Inactive</span>
                    </p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Department</label>
                    <p class="mt-1 text-sm text-gray-900" id="modal-user-department">{{ $user->department->name ?? 'N/A' }}</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Municipal ID</label>
                    <p class="mt-1 text-sm text-gray-900 font-mono" id="modal-user-employee-id">{{ $user->employee_id ?? '' }}</p>
                </div>
            </div>
        </div>

        <x-slot name="actions">
            <button type="button" 
                    onclick="window['user-details-modal'].close()" 
                    class="btn btn-secondary">
                Close
            </button>
        </x-slot>
    </x-modal>

    <script>
    function showUserDetails(user) {
        // Populate modal with user data
        document.getElementById('modal-user-name').textContent = user.name || '';
        document.getElementById('modal-user-email').textContent = user.email || '';
        document.getElementById('modal-user-type').textContent = user.type || '';
        document.getElementById('modal-user-department').textContent = user.department?.name || 'N/A';
        document.getElementById('modal-user-employee-id').textContent = user.employee_id || '';
        
        // Handle status display
        const activeStatus = document.getElementById('status-active');
        const inactiveStatus = document.getElementById('status-inactive');
        
        if (user.is_active || user.status) {
            activeStatus.style.display = 'inline-block';
            inactiveStatus.style.display = 'none';
        } else {
            activeStatus.style.display = 'none';
            inactiveStatus.style.display = 'inline-block';
        }
        
        // Show modal
        window['user-details-modal'].showModal();
    }
    </script>
@endsection

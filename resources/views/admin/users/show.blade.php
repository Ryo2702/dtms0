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
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Staff Details</h2>
        <div class="card">
            <div class="card-body">
                <p><strong>Employee ID:</strong> {{ $staff->employee_id }}</p>
                <p><strong>Name:</strong> {{ $staff->name }}</p>
                <p><strong>Email:</strong> {{ $staff->email }}</p>
                <p><strong>Status:</strong> {!! $staff->formatted_status !!}</p>
                <p><strong>Last Seen:</strong> {{ $staff->last_seen }}</p>
                <p><strong>Department:</strong> {{ $staff->department_name }}</p>
            </div>
        </div>
        <a href="{{ route('head.staff.index') }}" class="btn btn-secondary mt-3">Back</a>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">Staff List</h1>
            <a href="{{ route('head.staff.create') }}" class="btn btn-primary">Add Staff</a>
        </div>

    </div>

  
@endsection

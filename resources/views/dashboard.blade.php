@extends('layouts.app')

@section('content')
    <div class="card bg-base-100 shadow-xl p-6">
        <h2 class="text-2xl font-bold">Welcome, {{ auth()->user()->municipal_id }}</h2>
        <p class="mt-2">Department: {{ auth()->user()->department }}</p>
        <p class="mt-2">Role: {{ auth()->user()->getRoleNames()->implode(', ') }}</p>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="container">
        <h2>Add Staff</h2>
        <form action="{{ route('head.staff.store') }}" method="POST">
            @csrf

            <x-form.input name="name" label="Name" required value="{{ old('name') }}" />
            <x-form.input name="email" label="Email" type="email" required value="{{ old('email') }}" />
            <x-form.input name="password" label="Password" type="password" required />
            <x-form.input name="password_confirmation" label="Confirm Password" type="password" required />

            <button type="submit" class="btn btn-success">Create</button>
            <a href="{{ route('head.staff.index') }}" class="btn btn-secondary">Back</a>
        </form>
    </div>
@endsection

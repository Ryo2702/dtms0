@extends('layouts.guest')

@section('content')
    <div
        class="min-h-screen flex items-center justify-center lg:justify-end px-4 bg-[url('/images/background.jpg')] bg-cover bg-center">
        <div class="w-full max-w-md h-full lg:h-screen flex items-center card glass bg-white/20 backdrop-blur-md shadow-xl">
            <div class="card-body">
                <h1 class="text-2xl font-bold text-center mb-4">Login</h1>

                @if (isset($errors) && $errors->any())
                    <div class="alert alert-error mb-4">
                        <ul class="list-disc pl-5">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('login.store') }}">
                    @csrf
                    <label class="form-control">
                        <div class="label"><span class="label-text">Employee ID</span></div>
                        <input type="text" name="employee_id" class="input input-bordered" required>
                    </label>

                    <label class="form-control mt-4">
                        <div class="label"><span class="label-text">Password</span></div>
                        <input type="password" name="password" class="input input-bordered" required>
                    </label>

                    <button type="submit" class="btn btn-primary mt-6 w-full">Login</button>
                </form>
            </div>
        </div>
    </div>

@endsection

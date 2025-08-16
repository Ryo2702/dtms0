@extends('layouts.guest')

@section('content')
    <div class="flex items-center justify-center min-h-screen">
        <div class="backdrop-blur-lg bg-white/60 border border-white/20 shadow-xl rounded-2xl p-10 text-center max-w-lg">

            <h1 class="text-5xl font-extrabold text-white mb-4">Doctrams</h1>

            <p class="text-lg mb-8">
                Welcome to Doctrams! <br>
                Document Tracking Management System of Bansud
            </p>

            <a href="{{ route('login') }}" class="btn btn-primary btn-lg w-full">
                Login
            </a>
        </div>
    </div>
@endsection

@extends('layouts.guest')

@section('content')
    <div class="flex items-center justify-center min-h-screen">
        <div class="backdrop-blur-lg shadow-xl rounded-2xl p-10 text-center max-w-lg bg-white/30">
            {{-- Added bg-white/30 for a subtle transparent white background, or remove for full transparency --}}
            <h1 class="text-5xl font-extrabold text-black mb-4">Doctrams</h1>

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

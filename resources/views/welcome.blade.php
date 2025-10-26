@extends('layouts.guest')

@section('content')
    <div class="min-h-screen flex items-center justify-end bg-cover bg-center">

        <div
            class="w-full max-w-md h-auto lg:h-screen flex flex-col items-center justify-center card glass bg-white/20 backdrop-blur-md shadow-xl p-8 mr-8">

            <div class="avatar mb-6">
                <div
                    class="w-32 h-32 rounded-full ring ring-primary ring-offset-base-100 ring-offset-4 shadow-2xl overflow-hidden bg-gradient-to-br from-primary to-secondary">
                    <img src="{{ asset('images/logo.jpg') }}" alt="DOCTRAMS Logo" class="object-cover w-full h-full" />
                </div>
            </div>

             <h2 class="text-xl font-semibold text-center text-primary mb-8 tracking-wide">
                Document Tracking Management System
            </h2>

            <div class="card-body items-center text-center w-full">
                <h1 class="text-4xl font-bold mb-4 text-base-content">
                    Hi, Welcome!
                </h1>

                <div class="card-actions w-full flex-col gap-3">
                    <a href="{{ route('login') }}"
                        class="btn btn-primary btn-lg w-full gap-2 shadow-lg hover:shadow-xl transition-all">
                        Login
                    </a>
                </div>

                <div class="divider text-xs pb-2">DOCTRAMS</div>

                <div class="divider"></div>
                <!-- Description -->
                <div class="text-xs text-base-content/60 text-center">
                    <p class="mb-2">By using this service, you understood and agree to the</p>
                    <div class="flex gap-2 justify-center flex-wrap">
                        <a href="#" class="link link-primary">Terms of Use</a>
                        <span>and</span>
                        <a href="#" class="link link-primary">Privacy Statement</a>
                    </div>
                </div>

                <div class="mt-4">
                    <div class="badge badge-outline">Municipality of Bansud</div>
                </div>
            </div>
        </div>
    </div>
@endsection
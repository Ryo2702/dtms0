@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Page Header --}}
        <x-page-header title="Dashboard" subtitle="Welcome to DTMS - Document Tracking Management System" :breadcrumbs="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Dashboard', 'url' => null]]" />

        {{-- Welcome Card --}}
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <x-user-avatar :user="auth()->user()" size="w-16 h-16" />
                        <div class="header">
                            <h1 class="card-title text-2xl text-primary">
                                Welcome back,
                                @if (auth()->user()->hasRole('Admin'))
                                    System Administrator!
                                @else
                                    {{ auth()->user()->name }}!
                                @endif
                            </h1>
                            <p class="text-sm text-base-content/70">{{ auth()->user()->employee_id }} Employee ID</p>
                            <p class="text-sm text-base-content/70">
                                @if (auth()->user()->hasRole('Admin'))
                                    System Administrator Role
                                @else
                                    {{ auth()->user()->getRoleNames()->implode(', ') }} Role
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-base-content/70">
                            Today's Date
                        </div>
                        <div class="text-lg font-semibold">
                            {{ now()->format('F d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

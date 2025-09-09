@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        <div class="p-3 lg:p-4 text-lg lg:text-xl font-bold">
            @php
                $user = Auth::user();
                $isAdmin = $user->type === 'Admin';
            @endphp
            <h1 class="ml-2 lg:ml-4 text-xxl lg:text-base">
                @if ($isAdmin)
                    <span class="hidden sm:inline">System Administrator</span>
                    <span class="sm:hidden">Admin</span>
                @else
                    <span class="hidden md:inline">{{ $user->department->name ?? 'Municipal System' }}</span>
                    <span class="md:hidden">{{ Str::limit($user->department->name ?? 'Municipal', 10) }}</span>
                @endif
            </h1>
        </div>


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

@extends('layouts.app')

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Page Header --}}
        <x-page-header title="Dashboard" subtitle="Welcome to DTMS - Document Tracking Management System" :breadcrumbs="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Dashboard', 'url' => null]]" />

        {{-- Welcome Card --}}
        <div class="card bg-base-100 shadow-xl mb-6">
            <div class="card-body">
                <div class="flex items-center justify-between">
                    <div>
                        <h2 class="card-title text-2xl text-primary">
                            Welcome back, {{ auth()->user()->name }}!
                        </h2>
                        <div class="mt-2 space-y-1">
                            <div class="flex items-center gap-2">
                                <span class="badge badge-secondary">{{ auth()->user()->municipal_id }}</span>
                                <span class="text-sm text-base-content/70">Municipal ID</span>
                            </div>
                            @if (auth()->user()->department)
                                <div class="flex items-center gap-2">
                                    <span
                                        class="badge badge-outline">{{ auth()->user()->department->name ?? 'No Department' }}</span>
                                    <span class="text-sm text-base-content/70">Department</span>
                                </div>
                            @endif
                            <div class="flex items-center gap-2">
                                <span class="badge badge-accent">{{ auth()->user()->getRoleNames()->implode(', ') }}</span>
                                <span class="text-sm text-base-content/70">Role</span>
                            </div>
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

        {{-- Role-based Dashboard Content --}}
        @if (auth()->user()->hasRole('Admin'))
            {{-- Admin Dashboard --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                {{-- Total Users --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title">Total Users</div>
                    <div class="stat-value text-primary">{{ \App\Models\User::count() }}</div>
                    <div class="stat-desc">Registered in system</div>
                </div>

                {{-- Active Users --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Active Users</div>
                    <div class="stat-value text-secondary">{{ \App\Models\User::where('status', 'active')->count() }}</div>
                    <div class="stat-desc">Currently active</div>
                </div>

                {{-- Total Departments --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title">Departments</div>
                    <div class="stat-value text-accent">{{ \App\Models\Department::count() }}</div>
                    <div class="stat-desc">Total departments</div>
                </div>

                {{-- Archived Users --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-warning">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title">Archives</div>
                    <div class="stat-value text-warning">{{ \App\Models\UserArchive::count() }}</div>
                    <div class="stat-desc">User archives</div>
                </div>
            </div>

            {{-- Quick Actions for Admin --}}
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-primary">Quick Actions</h3>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4">
                            <a href="{{ route('admin.users.index') }}" class="btn btn-primary btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                                </svg>
                                Manage Users
                            </a>
                            <a href="{{ route('admin.departments.index') }}" class="btn btn-secondary btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                                </svg>
                                Manage Departments
                            </a>
                            <a href="{{ route('admin.users.create') }}" class="btn btn-accent btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                    stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                </svg>
                                Create User
                            </a>
                            <a href="{{ route('admin.users.archives') }}" class="btn btn-warning btn-outline">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                                </svg>
                                View Archives
                            </a>
                        </div>
                    </div>
                </div>

                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title text-secondary">Recent Activity</h3>
                        <p class="text-base-content/70">Recent system activity will be displayed here.</p>
                        <div class="mt-4">
                            <div class="text-sm text-base-content/50">No recent activity</div>
                        </div>
                    </div>
                </div>
            </div>
        @elseif(auth()->user()->hasRole('Head'))
            {{-- Head Dashboard --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                {{-- Department Users --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title">Department Staff</div>
                    <div class="stat-value text-primary">
                        @if (auth()->user()->department_id)
                            {{ \App\Models\User::where('department_id', auth()->user()->department_id)->count() }}
                        @else
                            0
                        @endif
                    </div>
                    <div class="stat-desc">In your department</div>
                </div>

                {{-- My Documents --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title">My Documents</div>
                    <div class="stat-value text-secondary">0</div>
                    <div class="stat-desc">Pending processing</div>
                </div>

                {{-- Department Tasks --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-accent">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title">Department Tasks</div>
                    <div class="stat-value text-accent">0</div>
                    <div class="stat-desc">Active tasks</div>
                </div>
            </div>

            {{-- Head Quick Actions --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title text-primary">Department Management</h3>
                    <p class="text-base-content/70 mb-4">Manage your department staff and documents</p>
                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <button class="btn btn-primary btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5 0a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                            </svg>
                            View Staff
                        </button>
                        <button class="btn btn-secondary btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Manage Documents
                        </button>
                        <button class="btn btn-accent btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 00-2 2z" />
                            </svg>
                            View Reports
                        </button>
                    </div>
                </div>
            </div>
        @else
            {{-- Staff Dashboard --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                {{-- My Documents --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-primary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                            </path>
                        </svg>
                    </div>
                    <div class="stat-title">My Documents</div>
                    <div class="stat-value text-primary">0</div>
                    <div class="stat-desc">Documents submitted</div>
                </div>

                {{-- Pending Tasks --}}
                <div class="stat bg-base-100 rounded-lg shadow">
                    <div class="stat-figure text-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"
                            class="inline-block w-8 h-8 stroke-current">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                    <div class="stat-title">Pending Tasks</div>
                    <div class="stat-value text-secondary">0</div>
                    <div class="stat-desc">Awaiting action</div>
                </div>
            </div>

            {{-- Staff Quick Actions --}}
            <div class="card bg-base-100 shadow-xl">
                <div class="card-body">
                    <h3 class="card-title text-primary">Quick Actions</h3>
                    <p class="text-base-content/70 mb-4">Common tasks and document management</p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <button class="btn btn-primary btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                            Submit Document
                        </button>
                        <button class="btn btn-secondary btn-outline">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24"
                                stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            Track Documents
                        </button>
                    </div>
                </div>
            </div>
        @endif
    </div>
@endsection

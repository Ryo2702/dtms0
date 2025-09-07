@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="My Profile" subtitle="View and manage your profile information" />

        <div class="container">
            <!-- Profile Information Card -->
            <div class="lg:col-span-2">
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <div class="flex items-start gap-6">
                            <!-- Avatar -->
                            <div class="avatar">
                                <div class="w-24 h-24 rounded-full">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                        class="rounded-full object-cover" />
                                </div>
                            </div>
                            <!-- User Info -->
                            <div class="flex-1">
                                <h2 class="card-title text-2xl mb-2">{{ $user->name }}</h2>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="mail" class="w-4 h-4 text-gray-500"></i>
                                        <span>{{ $user->email }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="badge" class="w-4 h-4 text-gray-500"></i>
                                        <span>{{ $user->employee_id }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="building" class="w-4 h-4 text-gray-500"></i>
                                        <span>{{ $user->department?->name ?? 'No department assigned' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="user-check" class="w-4 h-4 text-gray-500"></i>
                                        <span
                                            class="badge {{ $user->type === 'Admin' ? 'badge-error' : ($user->type === 'Head' ? 'badge-warning' : 'badge-info') }}">
                                            {{ $user->type }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Button -->
                            <div class="card-actions">
                                <a href="{{ route('profile.edit') }}" class="btn btn-primary btn-sm">
                                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                                    Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="pt-10">
                        @csrf
                        <button
                            class="btn btn-logout w-full flex items-center justify-center text-sm lg:text-base p-2 lg:p-3">
                            <i data-lucide="log-out" class="h-4 w-4 lg:h-5 lg:w-5 mr-1 lg:mr-2"></i>
                            <span class="hidden sm:inline">Logout</span>
                            <span class="sm:hidden">Out</span>
                        </button>
                    </form>
                </div>

                @if (in_array($user->type, ['Staff', 'Head']))
                    <!-- Recent Activity -->
                    <div class="card bg-base-100 shadow-xl mt-6">
                        <div class="card-body">
                            <h3 class="card-title">
                                <i data-lucide="activity" class="w-5 h-5 mr-2"></i>
                                Recent Activity
                            </h3>

                            @php
                                $controller = app(\App\Http\Controllers\ProfileController::class);
                                $activities = $controller->getRecentActivity();
                            @endphp

                            @if ($activities->count() > 0)
                                <div class="space-y-3 mt-4">
                                    @foreach ($activities as $activity)
                                        <div class="flex items-center justify-between p-3 bg-base-200 rounded-lg">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-2 h-2 rounded-full {{ $activity['type'] === 'review_assigned' ? 'bg-info' : 'bg-success' }}">
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium">{{ $activity['description'] }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $activity['date']->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="badge badge-xs {{ $activity['status'] === 'pending' ? 'badge-warning' : ($activity['status'] === 'approved' ? 'badge-success' : 'badge-error') }}">
                                                    {{ ucfirst($activity['status']) }}
                                                </span>
                                                <a href="{{ $activity['url'] }}" class="btn btn-xs btn-outline">View</a>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-8 text-gray-500">
                                    <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-2 opacity-50"></i>
                                    <p>No recent activity</p>
                                </div>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

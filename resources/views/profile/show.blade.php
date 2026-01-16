@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="My Profile" subtitle="View and manage your profile information" />

        <div class="max-w-7xl mx-auto">
            <!-- Profile Information Card -->
            <div class="lg:col-span-2">
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start gap-6">
                            <!-- Avatar -->
                            <div class="flex-shrink-0">
                                <div class="w-24 h-24 rounded-full overflow-hidden border-4 border-blue-500 shadow-lg">
                                    <img src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                        class="w-full h-full object-cover" />
                                </div>
                            </div>
                            <!-- User Info -->
                            <div class="flex-1">
                                <h2 class="text-2xl font-bold text-gray-900 mb-2">{{ $user->name }}</h2>
                                <div class="space-y-2">
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="mail" class="w-4 h-4 text-gray-500"></i>
                                        <span class="text-gray-700">{{ $user->email }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="badge" class="w-4 h-4 text-gray-500"></i>
                                        <span class="text-gray-700">{{ $user->employee_id }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="building" class="w-4 h-4 text-gray-500"></i>
                                        <span class="text-gray-700">{{ $user->department?->name ?? 'No department assigned' }}</span>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <i data-lucide="user-check" class="w-4 h-4 text-gray-500"></i>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->type === 'Admin' ? 'bg-red-100 text-red-800' : ($user->type === 'Head' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                            {{ $user->type }}
                                        </span>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Button -->
                            <div class="flex-shrink-0">
                                <a href="{{ route('profile.edit') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                                    Edit Profile
                                </a>
                            </div>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}" class="border-t border-gray-200">
                        @csrf
                        <button
                            class="w-full flex items-center justify-center text-sm lg:text-base p-3 lg:p-4 bg-red-50 text-red-600 hover:bg-red-100 transition-colors font-medium">
                            <i data-lucide="log-out" class="h-4 w-4 lg:h-5 lg:w-5 mr-1 lg:mr-2"></i>
                            <span class="hidden sm:inline">Logout</span>
                            <span class="sm:hidden">Out</span>
                        </button>
                    </form>
                </div>

                @if (in_array($user->type, ['Staff', 'Head']))
                    <!-- Recent Activity -->
                    <div class="bg-white rounded-lg shadow-lg overflow-hidden mt-6">
                        <div class="p-6">
                            <h3 class="text-lg font-bold text-gray-900 flex items-center">
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
                                        <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg border border-gray-200">
                                            <div class="flex items-center gap-3">
                                                <div
                                                    class="w-2 h-2 rounded-full {{ $activity['type'] === 'review_assigned' ? 'bg-blue-500' : 'bg-green-500' }}">
                                                </div>
                                                <div>
                                                    <p class="text-sm font-medium text-gray-900">{{ $activity['description'] }}</p>
                                                    <p class="text-xs text-gray-500">
                                                        {{ $activity['date']->diffForHumans() }}</p>
                                                </div>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <span
                                                    class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium {{ $activity['status'] === 'pending' ? 'bg-yellow-100 text-yellow-800' : ($activity['status'] === 'approved' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800') }}">
                                                    {{ ucfirst($activity['status']) }}
                                                </span>
                                                <a href="{{ $activity['url'] }}" class="inline-flex items-center px-3 py-1 text-xs font-medium text-blue-600 hover:text-blue-800 border border-blue-600 rounded-lg hover:bg-blue-50 transition-colors">View</a>
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

@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Edit Profile" subtitle="Update your profile information and settings" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i data-lucide="user" class="w-5 h-5 mr-2"></i>
                            Basic Information
                        </h3>

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Avatar Upload -->
                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Profile Picture
                                </label>

                                <div class="flex items-center gap-4">
                                    <div class="flex-shrink-0">
                                        <div class="w-20 h-20 rounded-full overflow-hidden border-4 border-blue-500 shadow-lg">
                                            <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                                class="w-full h-full object-cover" />
                                        </div>
                                    </div>

                                    <div class="flex-1">
                                        <input type="file" name="avatar" id="avatar" accept="image/*"
                                            class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100 cursor-pointer" />
                                        <p class="mt-1 text-xs text-gray-500">JPG, PNG, GIF up to 2MB</p>
                                        @if ($user->avatar)
                                            <a href="{{ route('profile.remove-avatar') }}" class="inline-flex items-center mt-2 px-3 py-1 text-xs font-medium text-red-700 bg-red-50 rounded-lg hover:bg-red-100 transition-colors"
                                                onclick="return confirm('Are you sure you want to remove your avatar?')">
                                                Remove Avatar
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @error('avatar')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Full Name
                                </label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('name') border-red-500 @enderror" required />
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Email Address
                                </label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('email') border-red-500 @enderror" required />
                                @error('email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end gap-3">
                                <a href="{{ route('profile.show') }}" class="inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">Cancel</a>
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition-colors">
                                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 mb-4 flex items-center">
                            <i data-lucide="lock" class="w-5 h-5 mr-2"></i>
                            Change Password
                        </h3>

                        <form action="{{ route('profile.update-password') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Current Password -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Current Password
                                </label>
                                <input type="password" name="current_password"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('current_password') border-red-500 @enderror"
                                    required />
                                @error('current_password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    New Password
                                </label>
                                <input type="password" name="password"
                                    class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500 @error('password') border-red-500 @enderror" required />
                                @error('password')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Confirm New Password -->
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Confirm New Password
                                </label>
                                <input type="password" name="password_confirmation" class="block w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
                                    required />
                            </div>

                            <!-- Submit Button -->
                            <div class="flex justify-end">
                                <button type="submit" class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white text-sm font-medium rounded-lg hover:bg-yellow-700 transition-colors">
                                    <i data-lucide="key" class="w-4 h-4 mr-2"></i>
                                    Update Password
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Profile Information Display -->
            <div class="space-y-6">
                <!-- Account Details -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                            Account Details
                        </h3>

                        <div class="space-y-3 mt-4">
                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">Employee ID</div>
                                <div class="font-mono text-sm text-gray-900">{{ $user->employee_id }}</div>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">Department</div>
                                <div class="text-sm text-gray-900">{{ $user->department?->name ?? 'No department assigned' }}</div>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">User Type</div>
                                <div class="text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->type === 'Admin' ? 'bg-red-100 text-red-800' : ($user->type === 'Head' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800') }}">
                                        {{ $user->type }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-3 bg-gray-50 rounded-lg border border-gray-200">
                                <div class="text-xs text-gray-500 mb-1">Account Status</div>
                                <div class="text-sm">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $user->status ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ $user->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Activity -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i data-lucide="activity" class="w-5 h-5 mr-2"></i>
                            Account Activity
                        </h3>

                        <div class="space-y-2 mt-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Last Login:</span>
                                <span class="text-gray-900">{{ $user->last_activity ? $user->last_activity->diffForHumans() : 'Never' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Account Created:</span>
                                <span class="text-gray-900">{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Profile Updated:</span>
                                <span class="text-gray-900">{{ $user->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="bg-white rounded-lg shadow-lg overflow-hidden">
                    <div class="p-6">
                        <h3 class="text-lg font-bold text-gray-900 flex items-center">
                            <i data-lucide="zap" class="w-5 h-5 mr-2"></i>
                            Quick Actions
                        </h3>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('profile.show') }}" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
                                View Profile
                            </a>

                            <a href="{{ route('dashboard') }}" class="flex items-center justify-center w-full px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors">
                                <i data-lucide="home" class="w-4 h-4 mr-2"></i>
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Include Lucide Icons and File Preview Script -->
    @push('scripts')
        <script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
        <script>
            lucide.createIcons();

            // Preview uploaded avatar
            document.getElementById('avatar').addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(e) {
                        document.getElementById('avatar-preview').src = e.target.result;
                    };
                    reader.readAsDataURL(file);
                }
            });
        </script>
    @endpush
@endsection

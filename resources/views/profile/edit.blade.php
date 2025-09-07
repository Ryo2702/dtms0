@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Edit Profile" subtitle="Update your profile information and settings" />

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Profile Form -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Information -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i data-lucide="user" class="w-5 h-5 mr-2"></i>
                            Basic Information
                        </h3>

                        <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <!-- Avatar Upload -->
                            <div class="form-control mb-6">
                                <label class="label">
                                    <span class="label-text font-medium">Profile Picture</span>
                                </label>

                                <div class="flex items-center gap-4">
                                    <div class="avatar">
                                        <div class="w-20 h-20 rounded-full">
                                            <img id="avatar-preview" src="{{ $user->avatar_url }}" alt="{{ $user->name }}"
                                                class="rounded-full object-cover" />
                                        </div>
                                    </div>

                                    <div class="flex-1">
                                        <input type="file" name="avatar" id="avatar" accept="image/*"
                                            class="file-input file-input-bordered w-full max-w-xs" />
                                        <div class="label">
                                            <span class="label-text-alt">JPG, PNG, GIF up to 2MB</span>
                                        </div>
                                        @if ($user->avatar)
                                            <a href="{{ route('profile.remove-avatar') }}" class="btn btn-xs btn-error mt-1"
                                                onclick="return confirm('Are you sure you want to remove your avatar?')">
                                                Remove Avatar
                                            </a>
                                        @endif
                                    </div>
                                </div>
                                @error('avatar')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>

                            <!-- Name -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Full Name</span>
                                </label>
                                <input type="text" name="name" value="{{ old('name', $user->name) }}"
                                    class="input input-bordered w-full @error('name') input-error @enderror" required />
                                @error('name')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>

                            <!-- Email -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Email Address</span>
                                </label>
                                <input type="email" name="email" value="{{ old('email', $user->email) }}"
                                    class="input input-bordered w-full @error('email') input-error @enderror" required />
                                @error('email')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>

                            <!-- Submit Button -->
                            <div class="card-actions justify-end">
                                <a href="{{ route('profile.show') }}" class="btn btn-ghost">Cancel</a>
                                <button type="submit" class="btn btn-primary">
                                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Change Password -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title mb-4">
                            <i data-lucide="lock" class="w-5 h-5 mr-2"></i>
                            Change Password
                        </h3>

                        <form action="{{ route('profile.update-password') }}" method="POST">
                            @csrf
                            @method('PUT')

                            <!-- Current Password -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Current Password</span>
                                </label>
                                <input type="password" name="current_password"
                                    class="input input-bordered w-full @error('current_password') input-error @enderror"
                                    required />
                                @error('current_password')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>

                            <!-- New Password -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">New Password</span>
                                </label>
                                <input type="password" name="password"
                                    class="input input-bordered w-full @error('password') input-error @enderror" required />
                                @error('password')
                                    <div class="label">
                                        <span class="label-text-alt text-error">{{ $message }}</span>
                                    </div>
                                @enderror
                            </div>

                            <!-- Confirm New Password -->
                            <div class="form-control mb-4">
                                <label class="label">
                                    <span class="label-text font-medium">Confirm New Password</span>
                                </label>
                                <input type="password" name="password_confirmation" class="input input-bordered w-full"
                                    required />
                            </div>

                            <!-- Submit Button -->
                            <div class="card-actions justify-end">
                                <button type="submit" class="btn btn-warning">
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
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title">
                            <i data-lucide="info" class="w-5 h-5 mr-2"></i>
                            Account Details
                        </h3>

                        <div class="space-y-3 mt-4">
                            <div class="p-3 bg-base-200 rounded-lg">
                                <div class="text-xs text-gray-500 mb-1">Employee ID</div>
                                <div class="font-mono text-sm">{{ $user->employee_id }}</div>
                            </div>

                            <div class="p-3 bg-base-200 rounded-lg">
                                <div class="text-xs text-gray-500 mb-1">Department</div>
                                <div class="text-sm">{{ $user->department?->name ?? 'No department assigned' }}</div>
                            </div>

                            <div class="p-3 bg-base-200 rounded-lg">
                                <div class="text-xs text-gray-500 mb-1">User Type</div>
                                <div class="text-sm">
                                    <span
                                        class="badge {{ $user->type === 'Admin' ? 'badge-error' : ($user->type === 'Head' ? 'badge-warning' : 'badge-info') }}">
                                        {{ $user->type }}
                                    </span>
                                </div>
                            </div>

                            <div class="p-3 bg-base-200 rounded-lg">
                                <div class="text-xs text-gray-500 mb-1">Account Status</div>
                                <div class="text-sm">
                                    <span class="badge {{ $user->status ? 'badge-success' : 'badge-error' }}">
                                        {{ $user->status ? 'Active' : 'Inactive' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Account Activity -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title">
                            <i data-lucide="activity" class="w-5 h-5 mr-2"></i>
                            Account Activity
                        </h3>

                        <div class="space-y-2 mt-4 text-sm">
                            <div class="flex justify-between">
                                <span class="text-gray-500">Last Login:</span>
                                <span>{{ $user->last_activity ? $user->last_activity->diffForHumans() : 'Never' }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Account Created:</span>
                                <span>{{ $user->created_at->format('M d, Y') }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-gray-500">Profile Updated:</span>
                                <span>{{ $user->updated_at->diffForHumans() }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="card bg-base-100 shadow-xl">
                    <div class="card-body">
                        <h3 class="card-title">
                            <i data-lucide="zap" class="w-5 h-5 mr-2"></i>
                            Quick Actions
                        </h3>

                        <div class="space-y-2 mt-4">
                            <a href="{{ route('profile.show') }}" class="btn btn-block btn-outline btn-sm">
                                <i data-lucide="eye" class="w-4 h-4 mr-2"></i>
                                View Profile
                            </a>

                            <a href="{{ route('dashboard') }}" class="btn btn-block btn-outline btn-sm">
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

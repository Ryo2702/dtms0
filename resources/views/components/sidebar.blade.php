<!-- Sidebar -->
<div class="drawer-side">
    <label for="sidebar" class="drawer-overlay"></label>
    @auth
        <aside class="bg-dtms-primary text-white w-64 lg:w-64 md:w-56 sm:w-48 min-h-screen">
            <div class="p-3 lg:p-4 text-lg lg:text-xl font-bold border-b border-dtms-secondary flex items-center space-x-2">
                @php
                    $user = Auth::user();
                    $isAdmin = $user->type === 'Admin';
                    $logo =
                        !$isAdmin && $user->department && $user->department->logo
                            ? Storage::url($user->department->logo)
                            : null;
                    $currentRoute = request()->route()->getName();
                @endphp

                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo" class="w-10 h-10 lg:w-12 lg:h-12 rounded-full object-cover">
                @endif

                <span class="ml-2 lg:ml-4 text-sm lg:text-base">
                    @if ($isAdmin)
                        <span class="hidden sm:inline">System Administrator</span>
                        <span class="sm:hidden">Admin</span>
                    @else
                        <span class="hidden md:inline">{{ $user->department->name ?? 'Municipal System' }}</span>
                        <span class="md:hidden">{{ Str::limit($user->department->name ?? 'Municipal', 10) }}</span>
                    @endif
                </span>
            </div>

            <ul class="menu p-2 lg:p-4 text-sm lg:text-base space-y-1">
                <li class="mb-2">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'dashboard' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                            viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2H5a2 2 0 00-2-2z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 1v6m8-6v6" />
                        </svg>
                        <span class="ml-2 lg:ml-3">Dashboard</span>
                    </a>
                </li>

                @if ($user->type === 'Admin')
                    <li class="mb-2">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ Str::startsWith($currentRoute, 'admin.users') ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            <span class="ml-2 lg:ml-3">Admins</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('admin.departments.index') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ Str::startsWith($currentRoute, 'admin.departments') ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                            </svg>
                            <span class="ml-2 lg:ml-3">Department</span>
                        </a>
                    </li>
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.admin.track') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.admin.track' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="ml-2 lg:ml-3">Document Track</span>
                        </a>
                    </li>
                @endif

                @if ($user->type === 'Staff')
                    <li class="mb-2">
                        <a href="/staff-area"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ request()->is('staff-area*') ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="ml-2 lg:ml-3">Staff Area</span>
                        </a>
                    </li>

                    <!-- Documents Section for Staff -->
                    <li class="mb-2">
                        <a href="{{ route('documents.index') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.index' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="ml-2 lg:ml-3">Documents</span>
                        </a>
                    </li>

                    <!-- Reviews Section for Staff -->
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.index') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.index' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span class="flex-1 ml-2 lg:ml-3">Reviews</span>
                            @php
                                $pendingReviews = \App\Models\DocumentReview::where('assigned_to', auth()->id())
                                    ->where('status', 'pending')
                                    ->count();
                            @endphp
                            @if ($pendingReviews > 0)
                                <div class="badge badge-error badge-xs lg:badge-sm">{{ $pendingReviews }}</div>
                            @endif
                        </a>
                    </li>

                    <!-- Received Documents -->
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.received') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.received' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                            </svg>
                            <span class="flex-1 ml-2 lg:ml-3">Received</span>
                            @php
                                $receivedCount = \App\Models\DocumentReview::where(
                                    'current_department_id',
                                    auth()->user()->department_id,
                                )
                                    ->where('original_department_id', '!=', auth()->user()->department_id)
                                    ->where(function ($q) {
                                        if (auth()->user()->type === 'Head') {
                                            $q->where('assigned_to', auth()->id())->orWhere(
                                                'current_department_id',
                                                auth()->user()->department_id,
                                            );
                                        } else {
                                            $q->where('assigned_to', auth()->id());
                                        }
                                    })
                                    ->where('status', 'pending')
                                    ->count();
                            @endphp
                            @if ($receivedCount > 0)
                                <div class="badge badge-info badge-xs lg:badge-sm">{{ $receivedCount }}</div>
                            @endif
                        </a>
                    </li>

                    <!-- Sent Documents -->
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.sent') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.sent' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            <span class="flex-1 ml-2 lg:ml-3">Sent</span>
                            @php
                                $sentCount = \App\Models\DocumentReview::where(
                                    'original_department_id',
                                    auth()->user()->department_id,
                                )
                                    ->where('current_department_id', '!=', auth()->user()->department_id)
                                    ->where(function ($q) {
                                        if (auth()->user()->type === 'Head') {
                                            $q->where('created_by', auth()->id())->orWhere(
                                                'original_department_id',
                                                auth()->user()->department_id,
                                            );
                                        } else {
                                            $q->where('created_by', auth()->id());
                                        }
                                    })
                                    ->whereIn('status', ['pending', 'approved'])
                                    ->count();
                            @endphp
                            @if ($sentCount > 0)
                                <div class="badge badge-warning badge-xs lg:badge-sm">{{ $sentCount }}</div>
                            @endif
                        </a>
                    </li>

                    <!-- Completed Documents -->
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.completed') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.completed' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="flex-1 ml-2 lg:ml-3">Completed</span>
                            @php
                                $completedCount = \App\Models\DocumentReview::where(function ($q) {
                                    $q->where('created_by', auth()->id())->orWhere('assigned_to', auth()->id());
                                })
                                    ->where('status', 'approved')
                                    ->whereNotNull('downloaded_at')
                                    ->count();
                            @endphp
                            @if ($completedCount > 0)
                                <div class="badge badge-success badge-xs lg:badge-sm">{{ $completedCount }}</div>
                            @endif
                        </a>
                    </li>
                @endif

                @if ($user->type === 'Head')
                    <li class="mb-2">
                        <a href="/head-area"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ request()->is('head-area*') ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="ml-2 lg:ml-3">Head Area</span>
                        </a>
                    </li>

                    <li class="mb-2">
                        <a href="{{ route('head.staff.index') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ Str::startsWith($currentRoute, 'head.staff') ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                            </svg>
                            <span class="ml-2 lg:ml-3 hidden sm:inline">Staff Accounts</span>
                            <span class="ml-2 lg:ml-3 sm:hidden">Staff</span>
                        </a>
                    </li>

                    <!-- Documents Section for Head -->
                    <li class="mb-2">
                        <a href="{{ route('documents.index') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.index' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <span class="ml-2 lg:ml-3">Documents</span>
                        </a>
                    </li>

                    <!-- Reviews Section for Head -->
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.index') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.index' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                            </svg>
                            <span class="flex-1 ml-2 lg:ml-3">Reviews</span>
                            @php
                                $pendingReviews = \App\Models\DocumentReview::where('assigned_to', auth()->id())
                                    ->where('status', 'pending')
                                    ->count();
                            @endphp
                            @if ($pendingReviews > 0)
                                <div class="badge badge-error badge-xs lg:badge-sm">{{ $pendingReviews }}</div>
                            @endif
                        </a>
                    </li>

                    <!-- Received Documents for Head -->
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.received') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.received' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M7 16l-4-4m0 0l4-4m-4 4h18" />
                            </svg>
                            <span class="flex-1 ml-2 lg:ml-3">Received</span>
                            @php
                                $receivedCount = \App\Models\DocumentReview::where(
                                    'current_department_id',
                                    auth()->user()->department_id,
                                )
                                    ->where('original_department_id', '!=', auth()->user()->department_id)
                                    ->where(function ($q) {
                                        if (auth()->user()->type === 'Head') {
                                            $q->where('assigned_to', auth()->id())->orWhere(
                                                'current_department_id',
                                                auth()->user()->department_id,
                                            );
                                        } else {
                                            $q->where('assigned_to', auth()->id());
                                        }
                                    })
                                    ->where('status', 'pending')
                                    ->count();
                            @endphp
                            @if ($receivedCount > 0)
                                <div class="badge badge-info badge-xs lg:badge-sm">{{ $receivedCount }}</div>
                            @endif
                        </a>
                    </li>

                    <!-- Sent Documents for Head -->
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.sent') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.sent' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 8l4 4m0 0l-4 4m4-4H3" />
                            </svg>
                            <span class="flex-1 ml-2 lg:ml-3">Sent</span>
                            @php
                                $sentCount = \App\Models\DocumentReview::where(
                                    'original_department_id',
                                    auth()->user()->department_id,
                                )
                                    ->where('current_department_id', '!=', auth()->user()->department_id)
                                    ->where(function ($q) {
                                        if (auth()->user()->type === 'Head') {
                                            $q->where('created_by', auth()->id())->orWhere(
                                                'original_department_id',
                                                auth()->user()->department_id,
                                            );
                                        } else {
                                            $q->where('created_by', auth()->id());
                                        }
                                    })
                                    ->whereIn('status', ['pending', 'approved'])
                                    ->count();
                            @endphp
                            @if ($sentCount > 0)
                                <div class="badge badge-warning badge-xs lg:badge-sm">{{ $sentCount }}</div>
                            @endif
                        </a>
                    </li>

                    <!-- Completed Documents for Head -->
                    <li class="mb-2">
                        <a href="{{ route('documents.reviews.completed') }}"
                            class="flex items-center p-2 lg:p-3 rounded-lg transition-colors duration-200 {{ $currentRoute === 'documents.reviews.completed' ? 'bg-black/30 text-white border-l-4 border-white font-semibold' : 'hover:bg-dtms-secondary/50' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <span class="flex-1 ml-2 lg:ml-3">Completed</span>
                            @php
                                $completedCount = \App\Models\DocumentReview::where(function ($q) {
                                    $q->where('assigned_to', auth()->id())
                                        ->orWhere('created_by', auth()->id())
                                        ->orWhere('current_department_id', auth()->user()->department_id);
                                })
                                    ->where('status', 'approved')
                                    ->whereNotNull('downloaded_at')
                                    ->count();
                            @endphp
                            @if ($completedCount > 0)
                                <div class="badge badge-success badge-xs lg:badge-sm">{{ $completedCount }}</div>
                            @endif
                        </a>
                    </li>
                @endif

                @if (in_array($user->type, ['Staff', 'Head']))
                    <li class="mb-2 p-2 lg:p-3 bg-dtms-secondary/20 rounded-lg">
                        <div class="text-xs space-y-1">
                            <div class="flex justify-between">
                                <span class="hidden sm:inline">Pending:</span>
                                <span class="sm:hidden">P:</span>
                                <span class="font-bold">
                                    {{ \App\Models\DocumentReview::where('assigned_to', auth()->id())->where('status', 'pending')->count() }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="hidden sm:inline">Created:</span>
                                <span class="sm:hidden">C:</span>
                                <span class="font-bold">
                                    {{ \App\Models\DocumentReview::where('created_by', auth()->id())->count() }}
                                </span>
                            </div>
                            <div class="flex justify-between">
                                <span class="hidden sm:inline">Today:</span>
                                <span class="sm:hidden">T:</span>
                                <span class="font-bold">
                                    {{ \App\Models\DocumentReview::where('status', 'approved')->whereDate('reviewed_at', today())->count() }}
                                </span>
                            </div>
                        </div>
                    </li>
                @endif

                <li class="p-3 lg:p-4 border-t border-dtms-secondary mt-auto">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button
                            class="btn btn-logout w-full flex items-center justify-center text-sm lg:text-base p-2 lg:p-3">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 lg:h-5 lg:w-5 mr-1 lg:mr-2"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                            </svg>
                            <span class="hidden sm:inline">Logout</span>
                            <span class="sm:hidden">Out</span>
                        </button>
                    </form>
                </li>
            </ul>
        </aside>
    @endauth
</div>

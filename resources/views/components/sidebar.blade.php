@auth
    <div class="flex flex-col h-full bg-primary">
        <!-- Header -->
        <div class="flex items-center justify-between p-4 text-white border-b border-white/10">
            <div class="flex items-center space-x-3">
                <div>
                    <h1 class="text-lg font-bold">DOCTRAMS</h1>
                    <p class="text-xs text-white/70">Document Tracking Managament System</p>
                </div>
            </div>

            <!-- Mobile Close Button -->
            <button id="close-btn" class="btn btn-ghost btn-sm lg:hidden!">
                <i data-lucide="x" class="w-5 h-5 text-white"></i>
            </button>
        </div>

        @php
            $user = Auth::user();
        @endphp

        <!-- Navigation Menu -->
        <div class="flex-1 overflow-y-auto p-2">
            <ul class="menu menu-sm w-full">
                <!-- Dashboard -->
                <li class="mb-1">
                    <a href="{{ route('dashboard') }}"
                        class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ request()->route()->getName() === 'dashboard' ? 'bg-white/20' : '' }}">
                        <i data-lucide="home" class="w-5 h-5"></i>
                        <span>Dashboard</span>
                    </a>
                </li>

                <!-- Admin Section -->
                @if ($user->type === 'Admin')
                    <li class="menu-title text-white/70 text-xs font-semibold uppercase tracking-wider mt-4 mb-2">
                        <span>Administration</span>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'admin.users') ? 'bg-white/20' : '' }}">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span>Heads</span>
                        </a>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('admin.departments.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'admin.departments') ? 'bg-white/20' : '' }}">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                            <span>Departments</span>
                        </a>
                    </li>


                    <li class="mb-1">
                        <a href="{{ route('admin.audit-logs.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'admin.audit-logs') ? 'bg-white/20' : '' }}">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>

                    <li class="menu-title text-white/70 text-xs font-semibold uppercase tracking-wider mt-4 mb-2">
                        <span>Workflow</span>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('transaction-types.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'transaction-types') ? 'bg-white/20' : '' }}">
                            <i data-lucide="file-stack" class="w-5 h-5"></i>
                            <span>Transaction Types</span>
                        </a>
                    </li>

                @else

                    <li class="menu-title text-white/70 text-xs font-semibold uppercase tracking-wider mt-4 mb-2">
                        <span>Status</span>
                    </li>


                    <li class="menu-title text-white/70 text-xs font-semibold uppercase tracking-wider mt-4 mb-2">
                        <span>Department</span>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('staff.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'staff') ? 'bg-white/20' : '' }}">
                            <i data-lucide="users-round" class="w-5 h-5"></i>
                            <span>Staff Management</span>
                        </a>
                    </li>
                @endif

                <!-- Settings -->
                <li class="menu-title text-white/70 text-xs font-semibold uppercase tracking-wider mt-4 mb-2">
                    <span>Settings</span>
                </li>

                <li class="mb-1">
                    <a href="{{ route('profile.show') }}"
                        class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'profile') ? 'bg-white/20' : '' }}">
                        <i data-lucide="user-round" class="w-5 h-5"></i>
                        <span>Profile</span>
                    </a>
                </li>
            </ul>
        </div>

        <!-- User Info & Logout -->
        <div class="p-4 border-t border-white/10">
            <div class="flex items-center gap-3 mb-3 p-3 bg-white/10 rounded-lg">
                <div class="avatar">
                    <div class="w-10 rounded-full">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                            alt="{{ $user->name }}" />
                    </div>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-white truncate">{{ $user->name }}</div>
                    <div class="text-xs text-white/70 truncate">{{ $user->email }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button class="btn btn-outline btn-sm w-full text-white border-white/30 hover:bg-white hover:text-primary">
                    <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>

@endauth

<script>
    // document.addEventListener('DOMContentLoaded', function () {
    //     fetchNotifications();

    //     function fetchNotifications() {
    //         fetch('/api/notifications/counts', {
    //             method: 'GET',
    //             credentials: 'include',
    //             headers: {
    //                 'X-Requested-With': 'XMLHttpRequest',
    //                 'Accept': 'application/json'
    //             }
    //         })
    //             .then(response => response.json())
    //             .then(data => {
    //                 if (data.success) {
    //                     // Update all badges found in the DOM by matching id "badge-<key>"
    //                     document.querySelectorAll('[id^="badge-"]').forEach(badge => {
    //                         const key = badge.id.replace('badge-', '');
    //                         // try direct key, then title-cased key fallback, then 0
    //                         let raw = (data.unread_counts && (data.unread_counts[key] ?? data.unread_counts[key.charAt(0).toUpperCase() + key.slice(1)])) ?? 0;
    //                         const val = parseInt(raw, 10) || 0;
    //                         badge.textContent = val;
    //                         badge.style.display = val > 0 ? 'inline' : 'none';
    //                     });
    //                 }
    //             })
    //             .catch(error => {
    //                 console.error('Error fetching notification counts:', error);
    //             });
    //     }

    //     setInterval(fetchNotifications, 3000);
    // });
</script>
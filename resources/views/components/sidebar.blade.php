<!-- Sidebar -->
<div class="drawer-side">
    <label for="sidebar" class="drawer-overlay"></label>
    @auth
        <aside class="bg-dtms-primary text-white w-64 min-h-screen">
            <div class="p-4 text-xl font-bold border-b border-dtms-secondary flex items-center space-x-2">
                @php
                    $user = Auth::user();
                    $isAdmin = $user->hasRole('Admin');
                    // Use department logo if not admin, else no logo
                    $logo =
                        !$isAdmin && $user->department && $user->department->logo
                            ? Storage::url($user->department->logo)
                            : null;
                @endphp


                @if ($logo)
                    <img src="{{ $logo }}" alt="Logo" class="w-12 h-12 rounded-full object-cover">
                @endif

                <span class="ml-4">
                    @if ($isAdmin)
                        System Administrator
                    @else
                        {{ $user->department->name ?? 'Municipal System' }}
                    @endif
                </span>
            </div>

            <ul class="menu p-4 text-base">
                <li class="mb-3">
                    <a href="{{ route('dashboard') }}" class="hover:bg-dtms-secondary">Dashboard</a>
                </li>

                @role('Admin')
                    <li class="mb-3">
                        <a href="{{ route('admin.users.index') }}" class="hover:bg-dtms-secondary">Admins</a>
                    </li>
                    <li class="mb-3">
                        <a href="{{ route('admin.departments.index') }}" class="hover:bg-dtms-secondary">Department</a>
                    </li>
                @endrole

                @role('Staff')
                    <li><a href="/staff-area" class="hover:bg-dtms-secondary">Staff Area</a></li>
                @endrole

                @role('Head')
                    <li><a href="/head-area" class="hover:bg-dtms-secondary">Head Area</a></li>
                    <li class="mb-3">
                        <a href="{{ route('head.staff.index') }}" class="hover:bg-dtms-secondary">Staff Accounts</a>
                    </li>
                    <li class="mb-3">
                        <a href="{{ route('documents.index') }}" class="hover:bg-dtms-secondary">Documents</a>
                    </li>
                @endrole

                <li class="p-4 border-t border-dtms-secondary">
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button class="btn btn-logout w-full">Logout</button>
                    </form>
                </li>
            </ul>
        </aside>
    @endauth
</div>

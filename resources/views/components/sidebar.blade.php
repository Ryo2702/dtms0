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
            <button id="close-btn" class="p-2 rounded-lg hover:bg-white/10 lg:hidden">
                <i data-lucide="x" class="w-5 h-5 text-white"></i>
            </button>
        </div>

        @php
            $user = Auth::user();
        @endphp

        <!-- Navigation Menu -->
        <div class="flex-1 overflow-y-auto p-2">
            <ul class="w-full space-y-1">
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
                    <li class="px-3 pt-4 pb-2">
                        <span class="text-white/70 text-xs font-semibold uppercase tracking-wider">Administration</span>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('admin.departments.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'admin.departments') ? 'bg-white/20' : '' }}">
                            <i data-lucide="building-2" class="w-5 h-5"></i>
                            <span>Departments</span>
                        </a>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('admin.users.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'admin.users') ? 'bg-white/20' : '' }}">
                            <i data-lucide="users" class="w-5 h-5"></i>
                            <span>Heads</span>
                        </a>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('admin.audit-logs.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'admin.audit-logs') ? 'bg-white/20' : '' }}">
                            <i data-lucide="shield-check" class="w-5 h-5"></i>
                            <span>Audit Logs</span>
                        </a>
                    </li>

                    <li class="px-3 pt-4 pb-2">
                        <span class="text-white/70 text-xs font-semibold uppercase tracking-wider">Workflow Management</span>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('admin.workflows.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'transaction-types') ? 'bg-white/20' : '' }}">
                            <i data-lucide="file-type" class="w-5 h-5"></i>
                            <span>Transaction</span>
                        </a>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('admin.document-tags.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'transaction-types') ? 'bg-white/20' : '' }}">
                            <i data-lucide="file-type" class="w-5 h-5"></i>
                            <span>Documents</span>
                        </a>
                    </li>
                @else
                    <li class="px-3 pt-4 pb-2">
                        <span class="text-white/70 text-xs font-semibold uppercase tracking-wider">Status</span>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('transactions.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ request()->route()->getName() === 'transactions.index' || request()->route()->getName() === 'transactions.create' ? 'bg-white/20' : '' }}">
                            <i data-lucide="file-text" class="w-5 h-5"></i>
                            <span>Create Transactions</span>
                        </a>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('transactions.my') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ request()->route()->getName() === 'transactions.my' ? 'bg-white/20' : '' }}">
                            <i data-lucide="folder-open" class="w-5 h-5"></i>
                            <span>Transactions</span>
                            @php
                                $myTransactionsCount = \App\Models\Transaction::where('created_by', $user->id)
                                    ->where('transaction_status', 'in_progress')
                                    ->count();
                            @endphp
                            @if($myTransactionsCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-medium bg-blue-500 text-white rounded-full">{{ $myTransactionsCount }}</span>
                            @endif
                        </a>
                    </li>

                    <li class="mb-1">
                        <a href="{{ route('transactions.reviews.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::contains(request()->route()->getName(), 'reviews') ? 'bg-white/20' : '' }}">
                            <i data-lucide="clipboard-check" class="w-5 h-5"></i>
                            <span>Reviews</span>
                            @php
                                $pendingReviewsCount = \App\Models\TransactionReviewer::where('reviewer_id', $user->id)
                                    ->where('status', 'pending')
                                    ->count();
                            @endphp
                            @if($pendingReviewsCount > 0)
                                <span class="inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1.5 text-xs font-medium bg-yellow-500 text-white rounded-full">{{ $pendingReviewsCount }}</span>
                            @endif
                        </a>
                    </li>


                    <li class="px-3 pt-4 pb-2">
                        <span class="text-white/70 text-xs font-semibold uppercase tracking-wider">Department</span>
                    </li>


                    <li class="mb-1">
                        <a href="{{ route('staff.index') }}"
                            class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'staff') ? 'bg-white/20' : '' }}">
                            <i data-lucide="users-round" class="w-5 h-5"></i>
                            <span>Staff Management</span>
                        </a>
                    </li>
                @endif

                <!-- Reports Section -->
                <li class="px-3 pt-4 pb-2">
                    <span class="text-white/70 text-xs font-semibold uppercase tracking-wider">Reports & Analytics</span>
                </li>

                <li class="mb-1">
                    <a href="{{ route('reports.index') }}"
                        class="flex items-center gap-3 p-3 rounded-lg text-white hover:bg-white/10 {{ Str::startsWith(request()->route()->getName(), 'reports') ? 'bg-white/20' : '' }}">
                        <i data-lucide="bar-chart-3" class="w-5 h-5"></i>
                        <span>Reports</span>
                    </a>
                </li>

                <!-- Settings -->
                <li class="px-3 pt-4 pb-2">
                    <span class="text-white/70 text-xs font-semibold uppercase tracking-wider">Settings</span>
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
                <div class="flex-shrink-0">
                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=random"
                        alt="{{ $user->name }}"
                        class="w-10 h-10 rounded-full" />
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-white truncate">{{ $user->name }}</div>
                    <div class="text-xs text-white/70 truncate">{{ $user->email }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button class="w-full inline-flex items-center justify-center px-4 py-2 border border-white/30 text-white text-sm font-medium rounded-lg hover:bg-white hover:text-blue-600 transition-colors">
                    <i data-lucide="log-out" class="w-4 h-4 mr-2"></i>
                    Logout
                </button>
            </form>
        </div>
    </div>

@endauth

<script></script>

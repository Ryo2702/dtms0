<!-- Sidebar Content -->
@auth
    <div class="flex flex-col h-full overflow-hidden">
        <!-- Header -->
        <div class="flex items-center justify-between p-3 text-sm font-bold border-b sm:p-4 sm:text-base md:text-lg border-white/10 shrink-0">
            @php
                $currentRoute = request()->route()->getName();
            @endphp
            <div class="flex items-center space-x-2">
                <h1 class="truncate">DTMS</h1>
            </div>

            <!-- Mobile Close Button - Only show on mobile/tablet -->
            <button id="close-btn" class="p-1 btn btn-ghost btn-sm hover:bg-white/10 lg:!hidden">
                <i data-lucide="arrow-left" class="w-4 h-4 text-white sm:h-5 sm:w-5"></i>
            </button>
        </div>

        @php
            $user = Auth::user();
            $pendingReviews = $receivedCount = $sentCount = $completedCount = $rejectedCount = $canceledCount = 0;

            if ($user && in_array($user->type, ['Staff', 'Head'])) {
                // Pending reviews assigned to current user
                $pendingReviews = \App\Models\DocumentReview::where('assigned_to', $user->id)
                    ->where('status', 'pending')
                    ->count();

                // Received documents (documents from other departments to current department)
                $receivedQuery = \App\Models\DocumentReview::where('current_department_id', $user->department_id)
                    ->where('original_department_id', '!=', $user->department_id)
                    ->where('status', 'pending');

                if ($user->type === 'Head') {
                    $receivedQuery->where(function ($q) use ($user) {
                        $q->whereNull('assigned_to')->orWhere('assigned_to', $user->id);
                    });
                } else {
                    $receivedQuery->where('assigned_to', $user->id);
                }
                $receivedCount = $receivedQuery->count();

                // Sent documents
                $sentQuery = \App\Models\DocumentReview::where('original_department_id', $user->department_id)
                    ->where('current_department_id', '!=', $user->department_id)
                    ->whereIn('status', ['pending', 'approved']);

                if ($user->type === 'Head') {
                    $sentQuery->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)->orWhereExists(function ($subQ) use ($user) {
                            $subQ
                                ->select(\DB::raw(1))
                                ->from('users')
                                ->whereRaw('users.id = document_reviews.created_by')
                                ->where('users.department_id', $user->department_id);
                        });
                    });
                } else {
                    $sentQuery->where('created_by', $user->id);
                }
                $sentCount = $sentQuery->count();

                // Completed documents
                $completedQuery = \App\Models\DocumentReview::where('status', 'approved')->whereNotNull('downloaded_at');

                if ($user->type === 'Head') {
                    $completedQuery->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                            ->orWhere('assigned_to', $user->id)
                            ->orWhere('current_department_id', $user->department_id)
                            ->orWhere('original_department_id', $user->department_id);
                    });
                } else {
                    $completedQuery->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                    });
                }
                $completedCount = $completedQuery->count();

                // Count overdue completed documents
                $overdueCompletedQuery = clone $completedQuery;
                $overdueCompletedCount = $overdueCompletedQuery
                    ->whereNotNull('due_at')
                    ->whereColumn('downloaded_at', '>', 'due_at')
                    ->count();

                // Rejected documents
                $rejectedQuery = \App\Models\DocumentReview::where('status', 'rejected');
                if ($user->type === 'Head') {
                    $rejectedQuery->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                            ->orWhere('assigned_to', $user->id)
                            ->orWhere('current_department_id', $user->department_id)
                            ->orWhere('original_department_id', $user->department_id);
                    });
                } else {
                    $rejectedQuery->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                    });
                }
                $rejectedCount = $rejectedQuery->count();

                // Canceled documents
                $canceledQuery = \App\Models\DocumentReview::where('status', 'canceled');
                if ($user->type === 'Head') {
                    $canceledQuery->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                            ->orWhere('assigned_to', $user->id)
                            ->orWhere('current_department_id', $user->department_id)
                            ->orWhere('original_department_id', $user->department_id);
                    });
                } else {
                    $canceledQuery->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                    });
                }
                $canceledCount = $canceledQuery->count();
            }
        @endphp

        <!-- Navigation Menu -->
        <div class="flex-1 overflow-y-auto">
            <ul class="p-2 space-y-1 text-xs menu sm:p-3 sm:text-sm">

                <!-- Main Navigation -->
                <x-sidebar-label text="Main" />

                <x-sidebar-item :route="route('dashboard')" :active="$currentRoute === 'dashboard'" icon="home">
                    <span class="truncate">Dashboard</span>
                </x-sidebar-item>

                <!-- Admin Section -->
                @if ($user->type === 'Admin')
                    <x-sidebar-label text="Administration" />

                    <x-sidebar-item :route="route('admin.users.index')" :active="Str::startsWith($currentRoute, 'admin.users')" icon="users">
                        <span class="truncate">Admins</span>
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('admin.departments.index')" :active="Str::startsWith($currentRoute, 'admin.departments')" icon="building-2">
                        <span class="truncate">Departments</span>
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('admin.documents.track')" :active="$currentRoute === 'documents.reviews.admin.track'" icon="file-search">
                        <span class="truncate">Document Track</span>
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('admin.audit-logs.index')" :active="Str::startsWith($currentRoute, 'admin.audit-logs')" icon="shield-check">
                        <span class="truncate">Audit Logs</span>
                    </x-sidebar-item>
                @endif

                <!-- Document Management -->
                @if ($user->type === 'Staff' || $user->type === 'Head')
                    <x-sidebar-label text="Document Management" />

                    <x-sidebar-item :route="route('documents.index')" :active="$currentRoute === 'documents.index'" icon="file-text">
                        <span class="truncate">Documents</span>
                    </x-sidebar-item>

                    <!-- Document Workflow -->
                    <x-sidebar-label text="Document Workflow" />

                    <x-sidebar-item :route="route('documents.reviews.received')" :active="$currentRoute === 'documents.reviews.received'" icon="inbox" :badge="['class' => 'badge-info', 'count' => $receivedCount]" data-notification-type="received">
                        <span class="truncate">Received</span>
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('documents.reviews.sent')" :active="$currentRoute === 'documents.reviews.sent'" icon="send" :badge="['class' => 'badge-warning', 'count' => $sentCount]" data-notification-type="sent">
                        <span class="truncate">Sent</span>
                    </x-sidebar-item>

                    <!-- Document Status -->
                    <x-sidebar-label text="Document Status" />
                    <x-sidebar-item :route="route('documents.reviews.index')" :active="$currentRoute === 'documents.reviews.index'" icon="file-clock" :badge="['class' => 'badge-error', 'count' => $pendingReviews]" data-notification-type="pending">
                        <span class="truncate">Pending</span>
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('documents.reviews.completed')" :active="$currentRoute === 'documents.reviews.completed'" icon="check-circle" :badge="[
                        'class' => 'badge-success',
                        'count' => $completedCount,
                    ]" data-notification-type="completed">
                        <span class="truncate">Closed</span>
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('documents.reviews.index', ['status' => 'rejected'])" :active="$currentRoute === 'documents.reviews.index' && request('status') === 'rejected'" icon="x-circle" :badge="['class' => 'badge-error', 'count' => $rejectedCount]" data-notification-type="rejected">
                        <span class="truncate">Rejected</span>
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('documents.reviews.index', ['status' => 'canceled'])" :active="$currentRoute === 'documents.reviews.index' && request('status') === 'canceled'" icon="ban" :badge="['class' => 'badge-neutral', 'count' => $canceledCount]" data-notification-type="canceled">
                        <span class="truncate">Canceled</span>
                    </x-sidebar-item>
                @endif

                <!-- Department Management -->
                @if ($user->type === 'Head')
                    <x-sidebar-label text="Department Management" />

                    <x-sidebar-item :route="route('head.staff.index')" :active="Str::startsWith($currentRoute, 'head.staff')" icon="user-cog">
                        <span class="truncate">Staff Accounts</span>
                    </x-sidebar-item>
                @endif

                <!-- User Settings -->
                <x-sidebar-label text="Settings" />

                <x-sidebar-item :route="route('profile.show')" :active="Str::startsWith($currentRoute, 'profile')" icon="user">
                    <span class="truncate">Profile</span>
                </x-sidebar-item>
            </ul>
        </div>

        <!-- Logout Section - Fixed at bottom -->
        <div class="p-2 mt-auto border-t sm:p-3 border-white/10 shrink-0">
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button class="flex items-center justify-center w-full h-auto min-h-0 p-2 text-xs btn btn-logout sm:text-sm">
                    <i data-lucide="log-out" class="flex-shrink-0 w-3 h-3 mr-1 sm:h-4 sm:w-4 sm:mr-2"></i>
                    <span class="truncate">Logout</span>
                </button>
            </form>
        </div>
    </div>

    <!-- AJAX Notification Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Only run for Staff and Head users
            @if(in_array($user->type, ['Staff', 'Head']))
                function updateNotificationCounts() {
                    fetch('{{ route("notifications.counts") }}', {
                        method: 'GET',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Update each notification badge
                            updateBadge('pending', data.counts.pending, 'badge-error');
                            updateBadge('received', data.counts.received, 'badge-info');
                            updateBadge('sent', data.counts.sent, 'badge-warning');
                            updateBadge('completed', data.counts.completed, 'badge-success', data.counts.overdue_completed);
                            updateBadge('rejected', data.counts.rejected, 'badge-error');
                            updateBadge('canceled', data.counts.canceled, 'badge-neutral');
                        }
                    })
                    .catch(error => {
                        console.error('Error fetching notification counts:', error);
                    });
                }

                function updateBadge(type, count, badgeClass, overdueCount = 0) {
                    const menuItem = document.querySelector(`[data-notification-type="${type}"]`);
                    if (!menuItem) return;

                    const badgeContainer = menuItem.querySelector('.flex.items-center.gap-1') || 
                                         menuItem.querySelector('.badge')?.parentElement;
                    
                    if (badgeContainer) {
                        if (count > 0) {
                            let badgeHtml = `<div class="badge ${badgeClass} badge-xs lg:badge-sm">${count}</div>`;
                            
                            if (overdueCount > 0) {
                                badgeHtml += `<div class="badge badge-error badge-xs lg:badge-sm" title="Overdue">${overdueCount}</div>`;
                            }
                            
                            badgeContainer.innerHTML = badgeHtml;
                            badgeContainer.style.display = 'flex';
                        } else {
                            badgeContainer.style.display = 'none';
                        }
                    } else if (count > 0) {
                        // Create badge container if it doesn't exist
                        const linkElement = menuItem.querySelector('a');
                        const badgeDiv = document.createElement('div');
                        badgeDiv.className = 'flex items-center gap-1';
                        
                        let badgeHtml = `<div class="badge ${badgeClass} badge-xs lg:badge-sm">${count}</div>`;
                        if (overdueCount > 0) {
                            badgeHtml += `<div class="badge badge-error badge-xs lg:badge-sm" title="Overdue">${overdueCount}</div>`;
                        }
                        
                        badgeDiv.innerHTML = badgeHtml;
                        linkElement.appendChild(badgeDiv);
                    }
                }

                // Update counts on page load
                updateNotificationCounts();

                // Update counts every 30 seconds
                setInterval(updateNotificationCounts, 30000);

                // Update counts when page becomes visible (tab switching)
                document.addEventListener('visibilitychange', function() {
                    if (!document.hidden) {
                        updateNotificationCounts();
                    }
                });
            @endif
        });
    </script>
@endauth

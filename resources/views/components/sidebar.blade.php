<!-- Sidebar Content -->
@auth
    <div class="h-full flex flex-col overflow-hidden">
        <div
            class="p-3 sm:p-4 md:p-5 lg:p-4 text-base sm:text-lg md:text-xl lg:text-xl font-bold flex items-center justify-between border-b border-white/10">
            @php
                $currentRoute = request()->route()->getName();
            @endphp
            <div class="flex items-center space-x-2">
                <h1 class="truncate">DTMS</h1>
            </div>

            <!-- Mobile Close Button -->
            <button id="close-btn" class="btn btn-ghost btn-sm p-1 lg:hidden hover:bg-white/10">
                <i data-lucide="arrow-left" class="h-5 w-5 text-white"></i>
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
                    // Head can see all department documents or specifically assigned
                    $receivedQuery->where(function ($q) use ($user) {
                        $q->whereNull('assigned_to')->orWhere('assigned_to', $user->id);
                    });
                } else {
                    // Staff only see documents assigned to them
                    $receivedQuery->where('assigned_to', $user->id);
                }
                $receivedCount = $receivedQuery->count();

                // Sent documents (documents sent from current department to other departments)
                $sentQuery = \App\Models\DocumentReview::where('original_department_id', $user->department_id)
                    ->where('current_department_id', '!=', $user->department_id)
                    ->whereIn('status', ['pending', 'approved']);

                if ($user->type === 'Head') {
                    // Head can see all department sent documents
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
                    // Staff only see documents they created
                    $sentQuery->where('created_by', $user->id);
                }
                $sentCount = $sentQuery->count();

                // Completed documents
                $completedQuery = \App\Models\DocumentReview::where('status', 'approved')->whereNotNull(
                    'downloaded_at',
                );

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

        <ul
            class="menu p-2 sm:p-3 md:p-4 lg:p-4 text-xs sm:text-sm md:text-base lg:text-base space-y-1 flex-1 overflow-y-auto">

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
            @endif

            <!-- Document Management -->
            @if ($user->type === 'Staff' || $user->type === 'Head')
                <x-sidebar-label text="Document Management" />

                <x-sidebar-item :route="route('documents.index')" :active="$currentRoute === 'documents.index'" icon="file-text">
                    <span class="truncate">Documents</span>
                </x-sidebar-item>

                <!-- Document Workflow -->
                <x-sidebar-label text="Document Workflow" />

                <x-sidebar-item :route="route('documents.reviews.index')" :active="$currentRoute === 'documents.reviews.index'" icon="clipboard-list" :badge="['class' => 'badge-error', 'count' => $pendingReviews]">
                    <span class="truncate">Reviews</span>
                </x-sidebar-item>

                <x-sidebar-item :route="route('documents.reviews.received')" :active="$currentRoute === 'documents.reviews.received'" icon="inbox" :badge="['class' => 'badge-info', 'count' => $receivedCount]">
                    <span class="truncate">Received</span>
                </x-sidebar-item>

                <x-sidebar-item :route="route('documents.reviews.sent')" :active="$currentRoute === 'documents.reviews.sent'" icon="send" :badge="['class' => 'badge-warning', 'count' => $sentCount]">
                    <span class="truncate">Sent</span>
                </x-sidebar-item>

                <!-- Document Status -->
                <x-sidebar-label text="Document Status" />

                <x-sidebar-item :route="route('documents.reviews.completed')" :active="$currentRoute === 'documents.reviews.completed'" icon="check-circle" :badge="[
                    'class' => 'badge-success',
                    'count' => $completedCount,
                    'overdue_count' => $overdueCompletedCount,
                ]">
                    <span class="truncate">Closed</span>
                </x-sidebar-item>

                <x-sidebar-item :route="route('documents.reviews.index', ['status' => 'rejected'])" :active="$currentRoute === 'documents.reviews.index' && request('status') === 'rejected'" icon="x-circle" :badge="['class' => 'badge-error', 'count' => $rejectedCount]">
                    <span class="truncate">Rejected</span>
                </x-sidebar-item>

                <x-sidebar-item :route="route('documents.reviews.index', ['status' => 'canceled'])" :active="$currentRoute === 'documents.reviews.index' && request('status') === 'canceled'" icon="ban" :badge="['class' => 'badge-neutral', 'count' => $canceledCount]">
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

        <!-- Logout Section - Fixed at bottom -->
        <div class="p-2 sm:p-3 md:p-4 lg:p-4 mt-auto border-t border-white/10">
            <form method="POST" action="{{ route('logout') }}" class="w-full">
                @csrf
                <button
                    class="btn btn-logout w-full flex items-center justify-center text-xs sm:text-sm md:text-base lg:text-base p-2 sm:p-2.5 md:p-3 lg:p-3 min-h-0 h-auto">
                    <i data-lucide="log-out"
                        class="h-3 w-3 sm:h-4 sm:w-4 md:h-5 md:w-5 lg:h-5 lg:w-5 mr-1 sm:mr-1.5 md:mr-2 lg:mr-2 flex-shrink-0"></i>
                    <span class="truncate">Logout</span>
                </button>
            </form>
        </div>
    </div>
@endauth

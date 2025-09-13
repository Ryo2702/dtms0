<!-- Sidebar -->
<div class="drawer-side">
    <label for="sidebar" class="drawer-overlay"></label>
    @auth
        <aside class="bg-dtms-primary text-white w-64 lg:w-64 md:w-56 sm:w-48 min-h-screen">
            <div class="p-3 lg:p-4 text-lg lg:text-xl font-bold flex items-center space-x-2">
                @php
                    $currentRoute = request()->route()->getName();
                @endphp
                <div class="flex items-center">
                    <h1>Sidebar</h1>
                </div>


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

            <ul class="menu p-2 lg:p-4 text-sm lg:text-base space-y-1">
                <!-- Dashboard -->
                <x-sidebar-item :route="route('dashboard')" :active="$currentRoute === 'dashboard'" icon="home">
                    Dashboard
                </x-sidebar-item>

                @if ($user->type === 'Admin')
                    <x-sidebar-item :route="route('admin.users.index')" :active="Str::startsWith($currentRoute, 'admin.users')" icon="users">
                        Admins
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('admin.departments.index')" :active="Str::startsWith($currentRoute, 'admin.departments')" icon="building-2">
                        Departments
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('admin.documents.track')" :active="$currentRoute === 'documents.reviews.admin.track'" icon="file-search">
                        Document Track
                    </x-sidebar-item>
                @endif

                @if ($user->type === 'Staff' || $user->type === 'Head')
                    <x-sidebar-item :route="route('documents.index')" :active="$currentRoute === 'documents.index'" icon="file-text">
                        Documents
                    </x-sidebar-item>



                    <x-sidebar-item :route="route('documents.reviews.received')" :active="$currentRoute === 'documents.reviews.received'" icon="inbox" :badge="['class' => 'badge-info', 'count' => $receivedCount]">
                        Received
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('documents.reviews.sent')" :active="$currentRoute === 'documents.reviews.sent'" icon="send" :badge="['class' => 'badge-warning', 'count' => $sentCount]">
                        Sent
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('documents.reviews.completed')" :active="$currentRoute === 'documents.reviews.completed'" icon="check-circle" :badge="[
                        'class' => 'badge-success',
                        'count' => $completedCount,
                        'overdue_count' => $overdueCompletedCount,
                    ]">
                        Closed
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('documents.reviews.index', ['status' => 'rejected'])" :active="$currentRoute === 'documents.reviews.index' && request('status') === 'rejected'" icon="x-circle" :badge="['class' => 'badge-error', 'count' => $rejectedCount]">
                        Rejected
                    </x-sidebar-item>

                    <x-sidebar-item :route="route('documents.reviews.index', ['status' => 'canceled'])" :active="$currentRoute === 'documents.reviews.index' && request('status') === 'canceled'" icon="ban" :badge="['class' => 'badge-neutral', 'count' => $canceledCount]">
                        Canceled
                    </x-sidebar-item>
                @endif

                @if ($user->type === 'Head')
                    <x-sidebar-item :route="route('documents.reviews.index')" :active="$currentRoute === 'documents.reviews.index'" icon="clipboard-list" :badge="['class' => 'badge-error', 'count' => $pendingReviews]">
                        Reviews
                    </x-sidebar-item>
                    <x-sidebar-item :route="route('head.staff.index')" :active="Str::startsWith($currentRoute, 'head.staff')" icon="user-cog">
                        Staff Accounts
                    </x-sidebar-item>
                @endif

                <!-- Profile -->
                <x-sidebar-item :route="route('profile.show')" :active="Str::startsWith($currentRoute, 'profile')" icon="user">
                    Profile
                </x-sidebar-item>

                <div class="p-2 lg:p-4 mt-auto">
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
            </ul>
        </aside>
    @endauth
</div>

@extends('layouts.app')

@section('content')
    <div class="container px-4 py-6 mx-auto">
        {{-- Page Header --}}
        <x-page-header title="Dashboard" subtitle="Welcome to DTMS - Document Tracking Management System" :breadcrumbs="[['label' => 'Home', 'url' => route('dashboard')], ['label' => 'Dashboard', 'url' => null]]" />

        {{-- Welcome Card --}}
        <div class="mb-6 shadow-xl card bg-base-100">
            <div class="card-body">
                <div class="flex items-start justify-between">
                    <div class="flex items-center gap-4">
                        <x-user-avatar :user="auth()->user()" size="w-16 h-16" />
                        <div class="header">
                            <h1 class="text-2xl card-title text-primary">
                                Welcome back,
                                @if (auth()->user()->hasRole('Admin'))
                                    System Administrator!
                                @else
                                    {{ auth()->user()->name }}!
                                @endif
                            </h1>
                            <p class="text-sm text-base-content/70">{{ auth()->user()->employee_id }} Employee ID</p>
                            <p class="text-sm text-base-content/70">
                                @if (auth()->user()->hasRole('Admin'))
                                    System Administrator Role
                                @else
                                    {{ auth()->user()->getRoleNames()->implode(', ') }} Role
                                @endif
                            </p>
                        </div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm text-base-content/70">
                            Today's Date
                        </div>
                        <div class="text-lg font-semibold">
                            {{ now()->format('F d, Y') }}
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @php
            $user = auth()->user();
        @endphp

        {{-- Admin Dashboard --}}
        @if($user->hasRole('Admin'))
            @php
                // Admin Statistics
                $totalUsers = \App\Models\User::count();
                $totalDepartments = \App\Models\Department::count();
                $totalDocuments = \App\Models\DocumentReview::count();
                $pendingReviews = \App\Models\DocumentReview::where('status', 'pending')->count();
                $completedToday = \App\Models\DocumentReview::where('status', 'approved')
                    ->whereDate('reviewed_at', today())->count();
                $overdueDocs = \App\Models\DocumentReview::where('status', 'pending')
                    ->where('due_at', '<', now())->count();

                // Chart data for document trends (last 7 days)
                $chartData = [
                    'labels' => collect(range(6, 0))->map(fn($i) => now()->subDays($i)->format('M j'))->toArray(),
                    'datasets' => [
                        [
                            'label' => 'Documents Submitted',
                            'data' => collect(range(6, 0))->map(fn($i) => 
                                \App\Models\DocumentReview::whereDate('created_at', now()->subDays($i))->count()
                            )->toArray(),
                        ],
                        [
                            'label' => 'Documents Completed',
                            'data' => collect(range(6, 0))->map(fn($i) => 
                                \App\Models\DocumentReview::where('status', 'approved')
                                    ->whereDate('reviewed_at', now()->subDays($i))->count()
                            )->toArray(),
                        ]
                    ]
                ];

                // Department performance data for pie chart
                $departments = \App\Models\Department::withCount('documentReviews')->get();
                $departmentData = [
                    'labels' => $departments->pluck('name')->toArray(),
                    'datasets' => [
                        [
                            'label' => 'Documents by Department',
                            'data' => $departments->pluck('document_reviews_count')->toArray(),
                        ]
                    ]
                ];
            @endphp

            <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <x-stat-card 
                    title="Total Users" 
                    :value="$totalUsers"
                    iconName="users"
                    href="{{ route('admin.users.index') }}"
                />
                
                <x-stat-card 
                    title="Active Departments" 
                    :value="$totalDepartments"
                    iconName="building"
                    href="{{ route('admin.departments.index') }}"
                />
                
                <x-stat-card 
                    title="Total Documents" 
                    :value="$totalDocuments"
                    iconName="file-text"
                />
                
                <x-stat-card 
                    title="Pending Reviews" 
                    :value="$pendingReviews"
                    iconName="clock"
                    bgColor="bg-stat-accent"
                />
                
                <x-stat-card 
                    title="Completed Today" 
                    :value="$completedToday"
                    iconName="check-circle"
                    bgColor="bg-stat-secondary"
                />
                
                <x-stat-card 
                    title="Overdue Documents" 
                    :value="$overdueDocs"
                    iconName="alert-triangle"
                    bgColor="bg-stat-danger"
                />
            </div>

            
            <div class="grid grid-cols-1 gap-6 mb-8 lg:grid-cols-2">
                <x-stat-graph 
                    title="Document Activity Trends"
                    subtitle="Last 7 days overview"
                    :data="$chartData"
                    type="line"
                    height="350px"
                />
                
                <x-stat-graph 
                    title="Documents by Department"
                    subtitle="Distribution across departments"
                    :data="$departmentData"
                    type="doughnut"
                    height="350px"
                />
            </div>

        @else
            @php
                // Get user-specific statistics
                $userDept = $user->department_id;
                $pendingReviews = \App\Models\DocumentReview::where('assigned_to', $user->id)
                    ->where('status', 'pending')->count();
                
                $receivedDocs = \App\Models\DocumentReview::where('current_department_id', $userDept)
                    ->where('original_department_id', '!=', $userDept)
                    ->where('status', 'pending');
                if ($user->type === 'Head') {
                    $receivedDocs->where(function ($q) use ($user) {
                        $q->whereNull('assigned_to')->orWhere('assigned_to', $user->id);
                    });
                } else {
                    $receivedDocs->where('assigned_to', $user->id);
                }
                $receivedCount = $receivedDocs->count();
                
                $completedDocs = \App\Models\DocumentReview::where('status', 'approved')
                    ->whereNotNull('downloaded_at');
                if ($user->type === 'Head') {
                    $completedDocs->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                            ->orWhere('assigned_to', $user->id)
                            ->orWhere('current_department_id', $user->department_id);
                    });
                } else {
                    $completedDocs->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                    });
                }
                $completedCount = $completedDocs->count();
                
                $rejectedDocs = \App\Models\DocumentReview::where('status', 'rejected');
                if ($user->type === 'Head') {
                    $rejectedDocs->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                            ->orWhere('assigned_to', $user->id)
                            ->orWhere('current_department_id', $user->department_id);
                    });
                } else {
                    $rejectedDocs->where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                    });
                }
                $rejectedCount = $rejectedDocs->count();

                // Recent activities
                $recentActivities = collect();
                
                // Recent document reviews
                $recentReviews = \App\Models\DocumentReview::where(function ($q) use ($user) {
                        $q->where('created_by', $user->id)
                          ->orWhere('assigned_to', $user->id);
                    })
                    ->orderBy('updated_at', 'desc')
                    ->limit(10)
                    ->get();
                
                foreach ($recentReviews as $review) {
                    $activity = [
                        'title' => '',
                        'description' => '',
                        'type' => '',
                        'time_ago' => $review->updated_at->diffForHumans(),
                        'urgent' => $review->due_at && $review->due_at->isPast() && $review->status === 'pending',
                        'metadata' => [
                            'document_type' => $review->document_type,
                            'client_name' => $review->client_name,
                        ]
                    ];
                    
                    if ($review->status === 'pending' && $review->assigned_to === $user->id) {
                        $activity['title'] = 'Document Pending Review';
                        $activity['description'] = "Review required for {$review->document_type}";
                        $activity['type'] = 'document_submitted';
                    } elseif ($review->status === 'approved') {
                        $activity['title'] = 'Document Approved';
                        $activity['description'] = "Document {$review->document_type} has been approved";
                        $activity['type'] = 'document_approved';
                    } elseif ($review->status === 'rejected') {
                        $activity['title'] = 'Document Rejected';
                        $activity['description'] = "Document {$review->document_type} was rejected";
                        $activity['type'] = 'document_rejected';
                    } elseif ($review->downloaded_at) {
                        $activity['title'] = 'Document Downloaded';
                        $activity['description'] = "Document {$review->document_type} was downloaded";
                        $activity['type'] = 'document_downloaded';
                    }
                    
                    if ($activity['title']) {
                        $recentActivities->push($activity);
                    }
                }
                
                $recentActivities = $recentActivities->sortByDesc('time_ago')->take(10);
            @endphp

            <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-4">
                <x-stat-card 
                    title="Pending Reviews" 
                    :value="$pendingReviews"
                    iconName="clock"
                    bgColor="bg-stat-primary"
                    subtitle="Assigned to you"
                />
                
                <x-stat-card 
                    title="Received Documents" 
                    :value="$receivedCount"
                    iconName="inbox"
                    bgColor="bg-stat-info"
                    subtitle="From other departments"
                />
                
                <x-stat-card 
                    title="Completed" 
                    :value="$completedCount"
                    iconName="check-circle"
                    bgColor="bg-stat-secondary"
                    subtitle="Total processed"
                />
                
                <x-stat-card 
                    title="Rejected" 
                    :value="$rejectedCount"
                    iconName="x-circle"
                    bgColor="bg-stat-danger"
                    subtitle="Requires attention"
                />
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-1">
                <x-recent-activity 
                    title="Your Recent Activity"
                    :activities="$recentActivities"
                    :maxItems="10"
                    :showViewAll="true"
                    viewAllUrl="{{ route('documents.status.pending') }}"
                />
            </div>
        @endif
    </div>
@endsection

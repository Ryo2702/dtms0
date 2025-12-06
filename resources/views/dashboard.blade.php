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

                $totalStaff = \App\Models\AssignStaff::count();

         

                // Department performance data for pie chart
                // $departments = \App\Models\Department::withCount('documentReviews')->get();
                // $departmentData = [
                //     'labels' => $departments->pluck('name')->toArray(),
                //     'datasets' => [
                //         [
                //             'label' => 'Documents by Department',
                //             'data' => $departments->pluck('document_reviews_count')->toArray(),
                //         ]
                //     ]
                // ];
            @endphp

            <div class="grid grid-cols-1 gap-6 mb-8 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <x-stat-card 
                    title="Total Users" 
                    :value="$totalUsers"
                    iconName="users"
                    href="{{ route('admin.users.index') }}"
                />
                
                {{-- <x-stat-card 
                    title="Active Departments" 
                    :value="$totalDepartments"
                    iconName="building"
                    href="{{ route('admin.departments.index') }}"
                /> --}}
    
                <x-stat-card 
                    title="Total Staff" 
                    :value="$totalStaff"
                    iconName="users-round"
                />
                
             
            </div>

        @else
            @php
                // Get user-specific statistics
                // $userDept = $user->department_id;
                // $pendingReviews = \App\Models\DocumentReview::where('assigned_to', $user->id)
                //     ->where('status', 'pending')->count();
                
                // $receivedDocs = \App\Models\DocumentReview::where('current_department_id', $userDept)
                //     ->where('original_department_id', '!=', $userDept)
                //     ->where('status', 'pending');
                // if ($user->type === 'Head') {
                //     $receivedDocs->where(function ($q) use ($user) {
                //         $q->whereNull('assigned_to')->orWhere('assigned_to', $user->id);
                //     });
                // } else {
                //     $receivedDocs->where('assigned_to', $user->id);
                // }
                // $receivedCount = $receivedDocs->count();
                
                // $completedDocs = \App\Models\DocumentReview::where('status', 'approved')
                //     ->whereNotNull('downloaded_at');
                // if ($user->type === 'Head') {
                //     $completedDocs->where(function ($q) use ($user) {
                //         $q->where('created_by', $user->id)
                //             ->orWhere('assigned_to', $user->id)
                //             ->orWhere('current_department_id', $user->department_id);
                //     });
                // } else {
                //     $completedDocs->where(function ($q) use ($user) {
                //         $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                //     });
                // }
                // $completedCount = $completedDocs->count();
                
                // $rejectedDocs = \App\Models\DocumentReview::where('status', 'rejected');
                // if ($user->type === 'Head') {
                //     $rejectedDocs->where(function ($q) use ($user) {
                //         $q->where('created_by', $user->id)
                //             ->orWhere('assigned_to', $user->id)
                //             ->orWhere('current_department_id', $user->department_id);
                //     });
                // } else {
                //     $rejectedDocs->where(function ($q) use ($user) {
                //         $q->where('created_by', $user->id)->orWhere('assigned_to', $user->id);
                //     });
                // }
                // $rejectedCount = $rejectedDocs->count();

                //    // Recent activities with priority sorting
                // $recentActivities = collect();
                
                // // Define difficulty priority weights (higher = more urgent)
                // $difficultyPriority = [
                //     'immediate' => 4,
                //     'urgent' => 3,
                //     'important' => 2,
                //     'normal' => 1,
                // ];
                
                // // Recent document reviews
                // $recentReviews = \App\Models\DocumentReview::where(function ($q) use ($user) {
                //         $q->where('created_by', $user->id)
                //           ->orWhere('assigned_to', $user->id);
                //     })
                //     ->orderBy('updated_at', 'desc')
                //     ->limit(20) // Fetch more items for better filtering
                //     ->get();
                
                // foreach ($recentReviews as $review) {
                //     $activity = [
                //         'title' => '',
                //         'description' => '',
                //         'type' => '',
                //         'time_ago' => $review->updated_at->diffForHumans(),
                //         'urgent' => $review->due_at && $review->due_at->isPast() && $review->status === 'pending',
                //         'difficulty' => $review->difficulty ?? 'normal',
                //         'priority_score' => $difficultyPriority[$review->difficulty ?? 'normal'] ?? 1,
                //         'metadata' => [
                //             'document_type' => $review->document_type,
                //             'client_name' => $review->client_name,
                //             'difficulty' => $review->difficulty ?? 'normal',
                //         ]
                //     ];
                    
                //     // Boost priority score if overdue
                //     if ($activity['urgent']) {
                //         $activity['priority_score'] += 5;
                //     }
                    

                    
                //     if ($activity['title']) {
                //         $recentActivities->push($activity);
                //     }
                // }
 
                // $recentActivities = $recentActivities->sortBy([
                //     ['priority_score', 'desc'],
                //     ['time_ago', 'asc']
                // ])->values()->take(10);
            @endphp
{{-- 
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
            </div> --}}
        @endif
    </div>
@endsection

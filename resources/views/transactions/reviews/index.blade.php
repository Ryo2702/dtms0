@extends('layouts.app')

@section('title', 'My Reviews')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">My Reviews</h1>
                    <p class="text-gray-600 mt-1">Transactions received for your review</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.index') }}" class="btn btn-outline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Transactions
                    </a>
                    <a href="{{ route('transactions.reviews.overdue') }}" class="btn btn-error btn-outline">
                        <i data-lucide="alert-triangle" class="w-4 h-4 mr-2"></i>
                        Overdue Reviews
                    </a>
                </div>
            </div>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
            <div class="stat bg-white rounded-lg shadow">
                <div class="stat-figure text-primary">
                    <i data-lucide="clipboard-list" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Pending</div>
                <div class="stat-value text-primary">{{ $stats['pending'] }}</div>
            </div>
            <div class="stat bg-white rounded-lg shadow">
                <div class="stat-figure text-info">
                    <i data-lucide="refresh-cw" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Resubmissions</div>
                <div class="stat-value text-info">{{ $stats['resubmissions'] }}</div>
            </div>
            <div class="stat bg-white rounded-lg shadow">
                <div class="stat-figure text-warning">
                    <i data-lucide="clock" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Due Today</div>
                <div class="stat-value text-warning">{{ $stats['due_today'] }}</div>
            </div>
            <div class="stat bg-white rounded-lg shadow">
                <div class="stat-figure text-error">
                    <i data-lucide="alert-circle" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Overdue</div>
                <div class="stat-value text-error">{{ $stats['overdue'] }}</div>
            </div>
            <div class="stat bg-white rounded-lg shadow">
                <div class="stat-figure text-success">
                    <i data-lucide="check-circle" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Reviewed</div>
                <div class="stat-value text-success">{{ $stats['reviewed'] }}</div>
            </div>
        </div>

        {{-- Tabs --}}
        <div class="tabs tabs-boxed bg-white p-2 mb-6 rounded-lg shadow">
            <a href="{{ route('transactions.reviews.index', ['tab' => 'pending']) }}" 
               class="tab {{ $tab === 'pending' ? 'tab-active' : '' }}">
                <i data-lucide="inbox" class="w-4 h-4 mr-2"></i>
                Pending Reviews
                @if($stats['pending'] > 0)
                    <span class="badge badge-primary badge-sm ml-2">{{ $stats['pending'] }}</span>
                @endif
            </a>
            <a href="{{ route('transactions.reviews.index', ['tab' => 'resubmissions']) }}" 
               class="tab {{ $tab === 'resubmissions' ? 'tab-active' : '' }}">
                <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                Resubmissions
                @if($stats['resubmissions'] > 0)
                    <span class="badge badge-info badge-sm ml-2">{{ $stats['resubmissions'] }}</span>
                @endif
            </a>
            <a href="{{ route('transactions.reviews.index', ['tab' => 'reviewed']) }}" 
               class="tab {{ $tab === 'reviewed' ? 'tab-active' : '' }}">
                <i data-lucide="check-circle" class="w-4 h-4 mr-2"></i>
                Already Reviewed
            </a>
        </div>

        {{-- Content based on active tab --}}
        @if($tab === 'pending')
            {{-- Pending Reviews --}}
            <x-card>
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="inbox" class="w-5 h-5 text-primary"></i>
                        Transactions Awaiting Your Review
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">These transactions have been sent to your department for review</p>
                </div>
                
                @if($pendingReviews->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <i data-lucide="check-circle" class="w-12 h-12 mx-auto mb-4 text-success"></i>
                        <p class="text-lg font-medium">All caught up!</p>
                        <p class="text-sm">No pending reviews at the moment.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-primary text-white">
                                <tr>
                                    <th class="px-4 py-3">Transaction</th>
                                    <th class="px-4 py-3">Workflow</th>
                                    <th class="px-4 py-3">From</th>
                                    <th class="px-4 py-3">Due Date</th>
                                    <th class="px-4 py-3">Status</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($pendingReviews as $review)
                                    <tr class="hover:bg-gray-50 {{ $review->isOverdue() ? 'bg-red-50' : '' }}">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transactions.show', $review->transaction) }}" class="font-mono font-bold text-primary hover:underline">
                                                {{ $review->transaction->transaction_code }}
                                            </a>
                                            @if($review->iteration_number > 1)
                                                <span class="badge badge-info badge-sm ml-1">Resubmission #{{ $review->iteration_number }}</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ $review->transaction->workflow->transaction_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($review->transaction->creator->name ?? 'Unknown') }}&background=random" alt="" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium">{{ $review->transaction->creator->name ?? 'Unknown' }}</div>
                                                    <div class="text-xs text-gray-500">{{ $review->transaction->department->name ?? '' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($review->due_date)
                                                <div class="flex items-center gap-2 {{ $review->isOverdue() ? 'text-error' : ($review->due_date->isToday() ? 'text-warning' : '') }}">
                                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                                    {{ $review->due_date->format('M d, Y') }}
                                                    @if($review->isOverdue())
                                                        <span class="badge badge-error badge-sm">Overdue</span>
                                                    @elseif($review->due_date->isToday())
                                                        <span class="badge badge-warning badge-sm">Today</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">No deadline</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge badge-warning">Pending Review</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-1">
                                                <a href="{{ route('transactions.reviews.review', $review) }}" 
                                                   class="btn btn-sm btn-primary" title="Review">
                                                    <i data-lucide="clipboard-check" class="w-4 h-4 mr-1"></i>
                                                    Review
                                                </a>
                                                <a href="{{ route('transactions.show', $review->transaction) }}" 
                                                   class="btn btn-sm btn-ghost" title="View Transaction">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>

        @elseif($tab === 'resubmissions')
            {{-- Resubmissions --}}
            <x-card>
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="refresh-cw" class="w-5 h-5 text-info"></i>
                        Resubmitted Transactions
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">Transactions that were previously rejected and resubmitted for re-review</p>
                </div>
                
                @if($resubmissions->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <i data-lucide="inbox" class="w-12 h-12 mx-auto mb-4"></i>
                        <p class="text-lg font-medium">No resubmissions</p>
                        <p class="text-sm">No transactions have been resubmitted for re-review.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-info text-white">
                                <tr>
                                    <th class="px-4 py-3">Transaction</th>
                                    <th class="px-4 py-3">Workflow</th>
                                    <th class="px-4 py-3">From</th>
                                    <th class="px-4 py-3">Iteration</th>
                                    <th class="px-4 py-3">Due Date</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resubmissions as $review)
                                    <tr class="hover:bg-gray-50 {{ $review->isOverdue() ? 'bg-red-50' : '' }}">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transactions.show', $review->transaction) }}" class="font-mono font-bold text-primary hover:underline">
                                                {{ $review->transaction->transaction_code }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ $review->transaction->workflow->transaction_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($review->transaction->creator->name ?? 'Unknown') }}&background=random" alt="" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium">{{ $review->transaction->creator->name ?? 'Unknown' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <span class="badge badge-info">Resubmission #{{ $review->iteration_number }}</span>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($review->due_date)
                                                <div class="flex items-center gap-2 {{ $review->isOverdue() ? 'text-error' : ($review->due_date->isToday() ? 'text-warning' : '') }}">
                                                    <i data-lucide="calendar" class="w-4 h-4"></i>
                                                    {{ $review->due_date->format('M d, Y') }}
                                                    @if($review->isOverdue())
                                                        <span class="badge badge-error badge-sm">Overdue</span>
                                                    @elseif($review->due_date->isToday())
                                                        <span class="badge badge-warning badge-sm">Today</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400">No deadline</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex gap-1">
                                                <a href="{{ route('transactions.reviews.review', $review) }}" 
                                                   class="btn btn-sm btn-primary" title="Re-Review">
                                                    <i data-lucide="clipboard-check" class="w-4 h-4 mr-1"></i>
                                                    Re-Review
                                                </a>
                                                <a href="{{ route('transactions.show', $review->transaction) }}" 
                                                   class="btn btn-sm btn-ghost" title="View Transaction">
                                                    <i data-lucide="eye" class="w-4 h-4"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>

        @elseif($tab === 'reviewed')
            {{-- Already Reviewed --}}
            <x-card>
                <div class="p-4 border-b">
                    <h2 class="text-lg font-semibold flex items-center gap-2">
                        <i data-lucide="check-circle" class="w-5 h-5 text-success"></i>
                        Transactions You've Reviewed
                    </h2>
                    <p class="text-sm text-gray-500 mt-1">History of transactions you have approved or rejected</p>
                </div>
                
                @if($reviewedByMe->isEmpty())
                    <div class="p-8 text-center text-gray-500">
                        <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-4"></i>
                        <p class="text-lg font-medium">No review history</p>
                        <p class="text-sm">You haven't reviewed any transactions yet.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="table w-full">
                            <thead class="bg-success text-white">
                                <tr>
                                    <th class="px-4 py-3">Transaction</th>
                                    <th class="px-4 py-3">Workflow</th>
                                    <th class="px-4 py-3">From</th>
                                    <th class="px-4 py-3">Reviewed At</th>
                                    <th class="px-4 py-3">Decision</th>
                                    <th class="px-4 py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($reviewedByMe as $review)
                                    <tr class="hover:bg-gray-50">
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transactions.show', $review->transaction) }}" class="font-mono font-bold text-primary hover:underline">
                                                {{ $review->transaction->transaction_code }}
                                            </a>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="font-medium">{{ $review->transaction->workflow->transaction_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3">
                                            <div class="flex items-center gap-2">
                                                <div class="avatar">
                                                    <div class="w-8 rounded-full">
                                                        <img src="https://ui-avatars.com/api/?name={{ urlencode($review->transaction->creator->name ?? 'Unknown') }}&background=random" alt="" />
                                                    </div>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-medium">{{ $review->transaction->creator->name ?? 'Unknown' }}</div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($review->reviewed_at)
                                                <div class="text-sm">{{ $review->reviewed_at->format('M d, Y') }}</div>
                                                <div class="text-xs text-gray-500">{{ $review->reviewed_at->format('h:i A') }}</div>
                                            @else
                                                <span class="text-gray-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            @if($review->status === 'approved')
                                                <span class="badge badge-success">
                                                    <i data-lucide="check" class="w-3 h-3 mr-1"></i>
                                                    Approved
                                                </span>
                                            @elseif($review->status === 'rejected')
                                                <div>
                                                    <span class="badge badge-error">
                                                        <i data-lucide="x" class="w-3 h-3 mr-1"></i>
                                                        Rejected
                                                    </span>
                                                    @if($review->rejection_reason)
                                                        <div class="text-xs text-gray-500 mt-1 max-w-xs truncate" title="{{ $review->rejection_reason }}">
                                                            {{ Str::limit($review->rejection_reason, 50) }}
                                                        </div>
                                                    @endif
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('transactions.show', $review->transaction) }}" 
                                               class="btn btn-sm btn-ghost" title="View">
                                                <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </x-card>
        @endif
    </x-container>
@endsection

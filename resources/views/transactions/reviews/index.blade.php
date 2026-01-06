@extends('layouts.app')

@section('title', 'My Reviews')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">My Pending Reviews</h1>
                    <p class="text-gray-600 mt-1">Transactions awaiting your review</p>
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
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="stat bg-white rounded-lg shadow">
                <div class="stat-figure text-primary">
                    <i data-lucide="clipboard-list" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Pending Reviews</div>
                <div class="stat-value text-primary">{{ $reviews->total() }}</div>
            </div>
            <div class="stat bg-white rounded-lg shadow">
                <div class="stat-figure text-warning">
                    <i data-lucide="clock" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Due Today</div>
                <div class="stat-value text-warning">
                    {{ $reviews->filter(fn($r) => $r->due_date && $r->due_date->isToday())->count() }}
                </div>
            </div>
            <div class="stat bg-white rounded-lg shadow">
                <div class="stat-figure text-error">
                    <i data-lucide="alert-circle" class="w-8 h-8"></i>
                </div>
                <div class="stat-title">Overdue</div>
                <div class="stat-value text-error">
                    {{ $reviews->filter(fn($r) => $r->isOverdue())->count() }}
                </div>
            </div>
        </div>

        {{-- Reviews Table --}}
        <x-card>
            <x-data-table 
                :headers="['Transaction', 'Workflow', 'Department', 'Due Date', 'Status', 'Actions']"
                :paginator="$reviews"
                emptyMessage="No pending reviews found."
            >
                @foreach($reviews as $review)
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
                            {{ $review->department->name ?? 'N/A' }}
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
                            <x-status-badge 
                                :status="$review->status" 
                                :labels="['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']"
                                :variants="['pending' => 'badge-warning', 'approved' => 'badge-success', 'rejected' => 'badge-error']"
                            />
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex gap-1">
                                <a href="{{ route('transactions.show', $review->transaction) }}" 
                                   class="btn btn-sm btn-primary" title="Review">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                    Review
                                </a>
                                <a href="{{ route('transactions.reviews.show', $review) }}" 
                                   class="btn btn-sm btn-ghost" title="Details">
                                    <i data-lucide="info" class="w-4 h-4"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </x-card>
    </x-container>
@endsection

@extends('layouts.app')

@section('title', 'Transaction History')

@section('content')
    <x-container>
        {{-- Breadcrumb --}}
        <div class="mb-6">
            <div class="flex items-center gap-2 text-sm text-gray-500 mb-2">
                <a href="{{ route('transactions.index') }}" class="hover:text-primary">Transactions</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <a href="{{ route('transactions.show', $transaction) }}" class="hover:text-primary">{{ $transaction->transaction_code }}</a>
                <i data-lucide="chevron-right" class="w-4 h-4"></i>
                <span>History</span>
            </div>
            <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center gap-4">
                <div>
                    <h1 class="text-3xl font-bold">Transaction History</h1>
                    <p class="text-gray-600 mt-1">{{ $transaction->transaction_code }} - Complete activity log</p>
                </div>
                <div class="flex gap-2">
                    <a href="{{ route('transactions.show', $transaction) }}" class="btn btn-outline">
                        <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                        Back to Details
                    </a>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
            {{-- Summary --}}
            <div class="lg:col-span-1">
                <x-card title="Summary">
                    <dl class="space-y-3">
                        <div>
                            <dt class="text-sm text-gray-500">Transaction Code</dt>
                            <dd class="font-mono font-bold text-primary">{{ $transaction->transaction_code }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Status</dt>
                            <dd>
                                <x-status-badge 
                                    :status="$transaction->transaction_status" 
                                    :labels="[
                                        'draft' => 'Draft',
                                        'in_progress' => 'In Progress',
                                        'completed' => 'Completed',
                                        'cancelled' => 'Cancelled'
                                    ]"
                                    :variants="[
                                        'draft' => 'badge-ghost',
                                        'in_progress' => 'badge-info',
                                        'completed' => 'badge-success',
                                        'cancelled' => 'badge-error'
                                    ]"
                                />
                            </dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Total Actions</dt>
                            <dd class="font-medium">{{ $transaction->transactionLogs->count() }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Revisions</dt>
                            <dd class="font-medium">{{ $transaction->revision_number ?? 0 }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm text-gray-500">Created</dt>
                            <dd class="text-sm">{{ $transaction->created_at->format('M d, Y h:i A') }}</dd>
                        </div>
                        @if($transaction->completed_at)
                            <div>
                                <dt class="text-sm text-gray-500">Completed</dt>
                                <dd class="text-sm">{{ $transaction->completed_at->format('M d, Y h:i A') }}</dd>
                            </div>
                        @endif
                    </dl>
                </x-card>

                {{-- Quick Links --}}
                <x-card title="Quick Links" class="mt-6">
                    <div class="space-y-2">
                        <a href="{{ route('transactions.tracker', $transaction) }}" class="btn btn-ghost btn-block justify-start">
                            <i data-lucide="map-pin" class="w-4 h-4 mr-2"></i>
                            Track Progress
                        </a>
                        <a href="{{ route('transactions.review-history', $transaction) }}" class="btn btn-ghost btn-block justify-start">
                            <i data-lucide="users" class="w-4 h-4 mr-2"></i>
                            Review History
                        </a>
                    </div>
                </x-card>
            </div>

            {{-- Activity Log --}}
            <div class="lg:col-span-3">
                <x-card title="Activity Log">
                    @if($transaction->transactionLogs->count() > 0)
                        <div class="relative">
                            {{-- Timeline line --}}
                            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gray-200"></div>

                            <div class="space-y-4">
                                @foreach($transaction->transactionLogs->sortByDesc('created_at') as $log)
                                    @php
                                        $actionColors = [
                                            'created' => 'bg-blue-500',
                                            'submitted' => 'bg-indigo-500',
                                            'approve' => 'bg-success',
                                            'approved' => 'bg-success',
                                            'reject' => 'bg-error',
                                            'rejected' => 'bg-error',
                                            'returned' => 'bg-warning',
                                            'resubmit' => 'bg-warning',
                                            'resubmitted' => 'bg-warning',
                                            'cancel' => 'bg-gray-500',
                                            'cancelled' => 'bg-gray-500',
                                            'completed' => 'bg-success',
                                        ];
                                        $actionIcons = [
                                            'created' => 'plus',
                                            'submitted' => 'send',
                                            'approve' => 'check',
                                            'approved' => 'check',
                                            'reject' => 'x',
                                            'rejected' => 'x',
                                            'returned' => 'corner-up-left',
                                            'resubmit' => 'refresh-cw',
                                            'resubmitted' => 'refresh-cw',
                                            'cancel' => 'ban',
                                            'cancelled' => 'ban',
                                            'completed' => 'check-circle',
                                        ];
                                        $bgColor = $actionColors[strtolower($log->action)] ?? 'bg-gray-400';
                                        $icon = $actionIcons[strtolower($log->action)] ?? 'activity';
                                    @endphp
                                    
                                    <div class="relative flex items-start gap-4 pl-2">
                                        {{-- Icon --}}
                                        <div class="relative z-10 flex-shrink-0 w-10 h-10 rounded-full {{ $bgColor }} text-white flex items-center justify-center">
                                            <i data-lucide="{{ $icon }}" class="w-5 h-5"></i>
                                        </div>

                                        {{-- Content --}}
                                        <div class="flex-1 pb-4">
                                            <div class="p-4 bg-white border border-gray-200 rounded-lg shadow-sm">
                                                <div class="flex flex-col sm:flex-row justify-between items-start gap-2 mb-2">
                                                    <div>
                                                        <h4 class="font-semibold capitalize">{{ str_replace('_', ' ', $log->action) }}</h4>
                                                        <div class="flex items-center gap-2 text-sm text-gray-500">
                                                            @if($log->actionBy)
                                                                <span>{{ $log->actionBy->full_name }}</span>
                                                                <span>•</span>
                                                            @endif
                                                            <span>{{ $log->created_at->format('M d, Y h:i A') }}</span>
                                                        </div>
                                                    </div>
                                                    @if($log->from_step || $log->to_step)
                                                        <div class="flex items-center gap-2 text-sm">
                                                            @if($log->from_step)
                                                                <span class="badge badge-ghost">Step {{ $log->from_step }}</span>
                                                            @endif
                                                            @if($log->from_step && $log->to_step)
                                                                <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                            @endif
                                                            @if($log->to_step)
                                                                <span class="badge badge-primary">Step {{ $log->to_step }}</span>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </div>

                                                @if($log->from_department_id || $log->to_department_id)
                                                    <div class="flex items-center gap-2 text-sm text-gray-600 mb-2">
                                                        <i data-lucide="building-2" class="w-4 h-4"></i>
                                                        @if($log->fromDepartment)
                                                            <span>{{ $log->fromDepartment->name }}</span>
                                                        @endif
                                                        @if($log->from_department_id && $log->to_department_id)
                                                            <i data-lucide="arrow-right" class="w-3 h-3"></i>
                                                        @endif
                                                        @if($log->toDepartment)
                                                            <span>{{ $log->toDepartment->name }}</span>
                                                        @endif
                                                    </div>
                                                @endif

                                                @if($log->remarks)
                                                    <div class="mt-2 p-3 bg-gray-50 rounded text-sm">
                                                        <span class="text-gray-500 block mb-1">Remarks:</span>
                                                        <p>{{ $log->remarks }}</p>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-8 text-gray-500">
                            <i data-lucide="file-text" class="w-12 h-12 mx-auto mb-3 opacity-50"></i>
                            <p>No activity logs found.</p>
                        </div>
                    @endif
                </x-card>

                {{-- Reviewers History --}}
                @if($transaction->reviewers->count() > 0)
                    <x-card title="Reviewers" class="mt-6">
                        <div class="overflow-x-auto">
                            <table class="table table-zebra w-full">
                                <thead>
                                    <tr>
                                        <th>Reviewer</th>
                                        <th>Department</th>
                                        <th>Status</th>
                                        <th>Due Date</th>
                                        <th>Reviewed At</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($transaction->reviewers as $reviewer)
                                        <tr>
                                            <td>{{ $reviewer->reviewer->full_name ?? 'Unknown' }}</td>
                                            <td>{{ $reviewer->department->name ?? 'N/A' }}</td>
                                            <td>
                                                <x-status-badge 
                                                    :status="$reviewer->status" 
                                                    :labels="['pending' => 'Pending', 'approved' => 'Approved', 'rejected' => 'Rejected']"
                                                    :variants="['pending' => 'badge-warning', 'approved' => 'badge-success', 'rejected' => 'badge-error']"
                                                />
                                                @if($reviewer->is_overdue)
                                                    <span class="badge badge-error badge-sm ml-1">Overdue</span>
                                                @endif
                                            </td>
                                            <td>{{ $reviewer->due_date ? $reviewer->due_date->format('M d, Y') : 'N/A' }}</td>
                                            <td>{{ $reviewer->reviewed_at ? $reviewer->reviewed_at->format('M d, Y h:i A') : '-' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </x-card>
                @endif
            </div>
        </div>
    </x-container>
@endsection

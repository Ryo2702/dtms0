@extends('layouts.app')

@section('content')
<x-container>
    <x-page-header
        title="Audit Log Details"
        subtitle="View detailed information about this audit log entry"
    >
        <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline btn-sm">
            <i data-lucide="arrow-left" class="w-4 h-4 mr-1"></i>
            Back to Logs
        </a>
    </x-page-header>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Information -->
        <div class="lg:col-span-2">
            <x-card title="Log Information">
                <div class="space-y-4">
                    <!-- User Information -->
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">User</h4>
                        @if($auditLog->user)
                            <div class="flex items-center space-x-3">
                                <x-user-avatar :user="$auditLog->user" />
                                <div>
                                    <div class="font-medium text-gray-900">{{ $auditLog->user->name }}</div>
                                    <div class="text-sm text-gray-500">{{ $auditLog->user->email }}</div>
                                    <div class="text-xs text-gray-400">{{ $auditLog->user->type }} - {{ $auditLog->user->department->name ?? 'No Department' }}</div>
                                </div>
                            </div>
                        @else
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-gray-300 rounded-full flex items-center justify-center">
                                    <i data-lucide="user" class="w-5 h-5 text-gray-600"></i>
                                </div>
                                <div>
                                    <div class="font-medium text-gray-900">System</div>
                                    <div class="text-sm text-gray-500">Automated action</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Action Information -->
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Action</h4>
                        <div class="flex items-center space-x-2">
                            <x-status-badge 
                                :status="$auditLog->action"
                                :variants="[
                                    'login' => 'badge-success',
                                    'logout' => 'badge-info',
                                    'create' => 'badge-primary',
                                    'update' => 'badge-warning',
                                    'delete' => 'badge-error',
                                    'approve' => 'badge-success',
                                    'reject' => 'badge-error',
                                    'forward' => 'badge-info',
                                    'download' => 'badge-secondary',
                                ]"
                                :labels="[
                                    'login' => 'Login',
                                    'logout' => 'Logout',
                                    'create' => 'Create',
                                    'update' => 'Update',
                                    'delete' => 'Delete',
                                    'approve' => 'Approve',
                                    'reject' => 'Reject',
                                    'forward' => 'Forward',
                                    'download' => 'Download',
                                ]"
                            />
                            <span class="text-gray-600">{{ $auditLog->description }}</span>
                        </div>
                    </div>

                    <!-- Request Information -->
                    <div class="border-b border-gray-200 pb-4">
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Request Information</h4>
                        <div class="space-y-2 text-sm">
                            @if($auditLog->method)
                                <div class="flex">
                                    <span class="w-20 text-gray-500">Method:</span>
                                    <span class="font-mono bg-gray-100 px-2 py-1 rounded text-xs">{{ $auditLog->method }}</span>
                                </div>
                            @endif
                            @if($auditLog->url)
                                <div class="flex">
                                    <span class="w-20 text-gray-500">URL:</span>
                                    <span class="font-mono text-xs break-all">{{ $auditLog->url }}</span>
                                </div>
                            @endif
                            @if($auditLog->ip_address)
                                <div class="flex">
                                    <span class="w-20 text-gray-500">IP:</span>
                                    <span class="font-mono">{{ $auditLog->ip_address }}</span>
                                </div>
                            @endif
                        </div>
                    </div>

                    <!-- Model Information -->
                    @if($auditLog->model_type || $auditLog->model_id)
                        <div class="border-b border-gray-200 pb-4">
                            <h4 class="text-sm font-medium text-gray-900 mb-2">Affected Model</h4>
                            <div class="space-y-2 text-sm">
                                @if($auditLog->model_type)
                                    <div class="flex">
                                        <span class="w-20 text-gray-500">Type:</span>
                                        <span class="font-mono">{{ class_basename($auditLog->model_type) }}</span>
                                    </div>
                                @endif
                                @if($auditLog->model_id)
                                    <div class="flex">
                                        <span class="w-20 text-gray-500">ID:</span>
                                        <span class="font-mono">{{ $auditLog->model_id }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif

                    <!-- Timestamp -->
                    <div>
                        <h4 class="text-sm font-medium text-gray-900 mb-2">Timestamp</h4>
                        <div class="space-y-1 text-sm">
                            <div class="font-medium">{{ $auditLog->created_at->format('F j, Y \a\t g:i:s A') }}</div>
                            <div class="text-gray-500">{{ $auditLog->created_at->diffForHumans() }}</div>
                            <div class="text-xs text-gray-400 font-mono">{{ $auditLog->created_at->toISOString() }}</div>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Changes (if any) -->
            @if(!empty($auditLog->changes))
                <x-card title="Changes Made" class="mt-6">
                    <div class="space-y-4">
                        @foreach($auditLog->changes as $field => $change)
                            <div class="border border-gray-200 rounded-lg p-4">
                                <h5 class="font-medium text-gray-900 mb-2">{{ ucfirst(str_replace('_', ' ', $field)) }}</h5>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                    <div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">Before</div>
                                        <div class="bg-red-50 border border-red-200 rounded p-2 text-sm">
                                            <code class="text-red-800">{{ $change['old'] ?? 'null' }}</code>
                                        </div>
                                    </div>
                                    <div>
                                        <div class="text-xs text-gray-500 uppercase tracking-wide mb-1">After</div>
                                        <div class="bg-green-50 border border-green-200 rounded p-2 text-sm">
                                            <code class="text-green-800">{{ $change['new'] ?? 'null' }}</code>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <x-card title="Quick Actions" compact="true">
                <div class="space-y-2">
                    @if($auditLog->user)
                        <a href="{{ route('admin.audit-logs.index', ['user_id' => $auditLog->user_id]) }}" 
                           class="block text-sm text-blue-600 hover:text-blue-800">
                            <i data-lucide="user" class="w-4 h-4 inline mr-1"></i>
                            View all logs by this user
                        </a>
                    @endif
                    
                    <a href="{{ route('admin.audit-logs.index', ['action' => $auditLog->action]) }}" 
                       class="block text-sm text-blue-600 hover:text-blue-800">
                        <i data-lucide="activity" class="w-4 h-4 inline mr-1"></i>
                        View all {{ $auditLog->action }} actions
                    </a>
                    
                    <a href="{{ route('admin.audit-logs.index', ['date_from' => $auditLog->created_at->format('Y-m-d'), 'date_to' => $auditLog->created_at->format('Y-m-d')]) }}" 
                       class="block text-sm text-blue-600 hover:text-blue-800">
                        <i data-lucide="calendar" class="w-4 h-4 inline mr-1"></i>
                        View logs from this day
                    </a>
                </div>
            </x-card>

            <!-- User Agent (if available) -->
            @if($auditLog->user_agent)
                <x-card title="User Agent" compact="true">
                    <div class="text-xs font-mono text-gray-600 break-all">
                        {{ $auditLog->user_agent }}
                    </div>
                </x-card>
            @endif

            <!-- Raw Data -->
            <x-card title="Raw Data" compact="true">
                <details class="text-xs">
                    <summary class="cursor-pointer text-gray-600 hover:text-gray-800 mb-2">
                        View JSON data
                    </summary>
                    <pre class="bg-gray-50 p-2 rounded overflow-x-auto text-xs"><code>{{ json_encode($auditLog->toArray(), JSON_PRETTY_PRINT) }}</code></pre>
                </details>
            </x-card>
        </div>
    </div>
</x-container>
@endsection
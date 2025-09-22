@extends('layouts.app')

@section('content')
<x-container>
    <x-page-header
        title="Audit Log Test"
        subtitle="Test the audit logging functionality"
    />

    <x-card title="Test Audit Logging">
        <div class="space-y-4">
            <p class="text-gray-600">
                This page can be used to test the audit logging functionality. 
                Every action you perform will be logged in the audit system.
            </p>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Test Actions</h4>
                    <div class="space-y-2">
                        <form method="POST" action="{{ route('admin.audit-logs.test.action') }}">
                            @csrf
                            <input type="hidden" name="action" value="test_create">
                            <button type="submit" class="btn btn-primary btn-sm w-full">
                                Test Create Action
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.audit-logs.test.action') }}">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="action" value="test_update">
                            <button type="submit" class="btn btn-warning btn-sm w-full">
                                Test Update Action
                            </button>
                        </form>
                        
                        <form method="POST" action="{{ route('admin.audit-logs.test.action') }}">
                            @csrf
                            @method('DELETE')
                            <input type="hidden" name="action" value="test_delete">
                            <button type="submit" class="btn btn-error btn-sm w-full">
                                Test Delete Action
                            </button>
                        </form>
                    </div>
                </div>
                
                <div>
                    <h4 class="font-medium text-gray-900 mb-2">Recent Logs</h4>
                    <div class="bg-gray-50 p-4 rounded-lg max-h-64 overflow-y-auto">
                        @php
                            $recentLogs = \App\Models\AuditLog::with('user')
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp
                        
                        @forelse($recentLogs as $log)
                            <div class="mb-2 pb-2 border-b border-gray-200 last:border-0">
                                <div class="text-xs text-gray-500">
                                    {{ $log->created_at->diffForHumans() }}
                                </div>
                                <div class="text-sm">
                                    <span class="font-medium">{{ $log->user->name ?? 'System' }}</span>
                                    - {{ $log->description }}
                                </div>
                                <div class="text-xs text-gray-400">
                                    Action: {{ $log->action }} | IP: {{ $log->ip_address }}
                                </div>
                            </div>
                        @empty
                            <div class="text-gray-500 text-sm">No audit logs yet.</div>
                        @endforelse
                    </div>
                </div>
            </div>
            
            @if(session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            
            <div class="pt-4 border-t border-gray-200">
                <a href="{{ route('admin.audit-logs.index') }}" class="btn btn-outline">
                    <i data-lucide="external-link" class="w-4 h-4 mr-1"></i>
                    View Full Audit Log
                </a>
            </div>
        </div>
    </x-card>
</x-container>
@endsection
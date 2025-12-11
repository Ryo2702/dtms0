@extends('layouts.app')

@section('content')
<div class="p-4 sm:p-6">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Workflow Configuration</h1>
            <p class="text-gray-600 mt-2">Configure document routing workflows for each transaction type</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ session('success') }}</span>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            <span>{{ session('error') }}</span>
        </div>
    @endif

    {{-- Transaction Types Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($transactionTypes as $type)
            <div class="bg-base-100 rounded-lg shadow-md overflow-hidden">
                {{-- Card Header --}}
                <div class="p-4 border-b border-gray-200">
                    <div class="flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">{{ $type->document_name }}</h3>
                        @if($type->status)
                            <span class="badge badge-success badge-sm">Active</span>
                        @else
                            <span class="badge badge-ghost badge-sm">Inactive</span>
                        @endif
                    </div>
                    @if($type->description)
                        <p class="text-sm text-gray-500 mt-1 line-clamp-2">{{ $type->description }}</p>
                    @endif
                </div>

                {{-- Workflow Status --}}
                <div class="p-4">
                    @if($type->hasWorkflowConfigured())
                        @php
                            $steps = $type->getWorkflowSteps();
                        @endphp
                        <div class="mb-3">
                            <span class="text-xs font-medium text-gray-500 uppercase">Workflow Steps</span>
                            <div class="mt-2 flex flex-wrap gap-1">
                                @foreach($steps as $index => $step)
                                    <div class="flex items-center">
                                        <span class="badge badge-primary badge-sm">{{ $step['department_name'] }}</span>
                                        @if($index < count($steps) - 1)
                                            <svg class="w-4 h-4 mx-1 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                            </svg>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="text-xs text-gray-500">
                            <span class="font-medium">{{ count($steps) }}</span> steps configured
                        </div>
                    @else
                        <div class="flex items-center gap-2 text-warning">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span class="text-sm">No workflow configured</span>
                        </div>
                    @endif
                </div>

                {{-- Card Footer --}}
                <div class="p-4 bg-gray-50 border-t border-gray-200">
                    <a href="{{ route('admin.workflows.edit', $type) }}" class="btn btn-primary btn-sm w-full">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        {{ $type->hasWorkflowConfigured() ? 'Edit Workflow' : 'Configure Workflow' }}
                    </a>
                </div>
            </div>
        @empty
            <div class="col-span-full">
                <div class="bg-base-100 rounded-lg shadow-md p-8 text-center">
                    <svg class="w-12 h-12 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Transaction Types</h3>
                    <p class="text-gray-500 mb-4">Create transaction types first before configuring workflows.</p>
                    <a href="{{ route('admin.transaction-types.index') }}" class="btn btn-primary">
                        Manage Transaction Types
                    </a>
                </div>
            </div>
        @endforelse
    </div>
</div>
@endsection
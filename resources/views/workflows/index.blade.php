@extends('layouts.app')

@section('title', 'Workflow Management')

@section('content')
    <div class="container mx-auto px-4 py-6">
        {{-- Header --}}
        <div class="flex justify-between items-center mb-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900">Transaction</h1>
                <p class="text-gray-600 mt-1">Configure document routing workflows</p>
            </div>
            <a href="{{ route('admin.workflows.create') }}" class="btn btn-primary">
                <i data-lucide="plus" class="w-5 h-5 mr-2"></i>
                Create Workflow
            </a>
        </div>

        {{-- Workflows Table --}}
        <div class="card bg-base-100 shadow-md">
            <div class="card-body">
                @if ($workflows->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="table table-zebra w-full">
                            <thead>
                                <tr>
                                    <th>TS#</th>
                                    <th>Transaction Name</th>
                                    <th>Origin Departments</th>
                                    <th>Steps</th>
                                    <th>Document</th>
                                    <th>Estimated Time</th>
                                    <th>Difficulty</th>
                                    <th>Status</th>
                                    <th class="text-right">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($workflows as $workflow)
                                    @php
                                        $steps = $workflow->getWorkflowSteps();
                                        $totalTime = 0;
                                        $timeUnit = 'days';
                                        foreach ($steps as $step) {
                                            $value = $step['process_time_value'] ?? 0;
                                            $unit = $step['process_time_unit'] ?? 'minutes';
                                            if ($unit === 'minutes') {
                                                $totalTime += $value / 1440; // 1440 minutes in a day
                                            } elseif ($unit === 'hours') {
                                                $totalTime += $value / 24;
                                            } elseif ($unit === 'weeks') {
                                                $totalTime += $value * 7;
                                            } else {
                                                $totalTime += $value;
                                            }
                                        }
                                        $totalTime = round($totalTime, 1);
                                    @endphp
                                    <tr>
                                        {{-- TS# Column --}}
                                        <td>
                                            <span class="font-mono font-bold text-primary">{{ $workflow->id }}</span>
                                        </td>
                                        {{-- Transaction Name Column --}}
                                        <td>
                                            <div>
                                                <div class="font-medium">{{ $workflow->transaction_name }}</div>
                                                @if($workflow->description)
                                                    <div class="text-xs text-gray-500">{{ Str::limit($workflow->description, 50) }}</div>
                                                @endif
                                            </div>
                                        </td>
                                        {{-- Origin Departments Column --}}
                                        <td>
                                            @php
                                                $originDeptIds = $workflow->origin_departments ?? [];
                                                $originDepts = \App\Models\Department::whereIn('id', $originDeptIds)->get();
                                            @endphp
                                            @if($originDepts->count() > 0)
                                                <div class="flex flex-wrap gap-1 max-w-[200px]">
                                                    @foreach($originDepts->take(3) as $dept)
                                                        <span class="badge badge-sm badge-info" title="{{ $dept->name }}">
                                                            {{ Str::limit($dept->name, 12) }}
                                                        </span>
                                                    @endforeach
                                                    @if($originDepts->count() > 3)
                                                        <span class="badge badge-sm badge-ghost">+{{ $originDepts->count() - 3 }}</span>
                                                    @endif
                                                </div>
                                            @else
                                                <span class="text-gray-400 text-sm">All departments</span>
                                            @endif
                                        </td>
                                        {{-- Steps Column --}}
                                        <td>
                                                {{-- Arrow-based Step Flow Design --}}
                                                <div class="flex flex-wrap items-center gap-1">
                                                    @foreach ($steps as $index => $step)
                                                        <div class="group relative">
                                                            <div class="flex items-center">
                                                                <div
                                                                    class="flex items-center bg-base-200 rounded-lg px-2 py-1 border border-base-300 hover:bg-primary hover:text-primary-content transition-colors cursor-pointer">
                                                                    <span
                                                                        class="flex items-center justify-center w-5 h-5 rounded-full bg-primary text-primary-content text-xs font-bold mr-1.5">
                                                                        {{ $index + 1 }}
                                                                    </span>
                                                                    <span
                                                                        class="text-xs font-medium">{{ $step['department_name'] ?? 'Unknown' }}</span>
                                                                </div>
                                                                @if ($index < count($steps) - 1)
                                                                    <i data-lucide="arrow-right" class="w-4 h-4 text-primary mx-1 flex-shrink-0"></i>
                                                                @else
                                                                    <i data-lucide="check-circle" class="w-4 h-4 text-success ml-1 flex-shrink-0"></i>
                                                                @endif
                                                            </div>

                                                            {{-- Tooltip --}}
                                                            <div
                                                                class="absolute bottom-full left-1/2 transform -translate-x-1/2 mb-2 hidden group-hover:block z-50">
                                                                <div
                                                                    class="bg-neutral text-neutral-content text-xs rounded-lg px-3 py-2 shadow-lg min-w-[180px] max-w-[250px]">
                                                                    <div class="font-semibold mb-1">
                                                                        {{ $step['department_name'] ?? 'Unknown' }}</div>
                                                                    <div
                                                                        class="flex items-center gap-1 text-neutral-content/80">
                                                                        <i data-lucide="clock" class="w-3 h-3"></i>
                                                                        <span>{{ $step['process_time_value'] ?? 0 }}
                                                                            {{ $step['process_time_unit'] ?? 'days' }}</span>
                                                                    </div>
                                                                    @if (!empty($step['notes']))
                                                                        <div
                                                                            class="mt-1 pt-1 border-t border-neutral-content/20">
                                                                            <div class="text-neutral-content/70 italic">
                                                                                {{ $step['notes'] }}</div>
                                                                        </div>
                                                                    @endif
                                                                    <div
                                                                        class="absolute top-full left-1/2 transform -translate-x-1/2 -mt-1">
                                                                        <div
                                                                            class="border-8 border-transparent border-t-neutral">
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </td>
                                            @php
                                                // Get workflow's document tags directly
                                                $workflowTags = $workflow->documentTags()->where('status', true)->get();
                                            @endphp
                                            <td>
                                                @if ($workflowTags->count() > 0)
                                                    <div class="flex flex-wrap gap-1 max-w-[200px]">
                                                        @foreach ($workflowTags->take(3) as $tag)
                                                            <span class="badge badge-sm badge-ghost"
                                                                title="{{ $tag->description }}">
                                                                {{ Str::limit($tag->name, 15) }}
                                                            </span>
                                                        @endforeach
                                                        @if ($workflowTags->count() > 3)
                                                            <span class="badge badge-sm badge-ghost">
                                                                +{{ $workflowTags->count() - 3 }} more
                                                            </span>
                                                        @endif
                                                    </div>
                                                @else
                                                    <span class="text-gray-400 text-sm">No tags</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="flex items-center gap-1">
                                                    <i data-lucide="clock" class="w-4 h-4 text-gray-500"></i>
                                                    <span class="font-medium">{{ $totalTime }}
                                                        {{ $totalTime == 1 ? 'day' : 'days' }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge {{ $workflow->getDifficultBadgeClass() }}">
                                                    {{ ucfirst(str_replace('_', ' ', $workflow->difficulty)) }}
                                                </span>
                                            </td>
                                            <td>
                                                <label class="swap">
                                                    <input type="checkbox" {{ $workflow->status ? 'checked' : '' }}
                                                        onchange="toggleStatus('{{ $workflow->id }}')">
                                                    <span class="swap-on badge badge-success">Active</span>
                                                    <span class="swap-off badge badge-ghost">Inactive</span>
                                                </label>
                                            </td>
                                            <td class="text-right">
                                                <div class="flex gap-1 justify-end">
                                                    <a href="{{ route('admin.workflows.edit', $workflow) }}"
                                                        class="btn btn-xs btn-ghost" title="Edit">
                                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center py-12 text-gray-500">
                        <i data-lucide="git-branch" class="w-16 h-16 mx-auto mb-4 opacity-50"></i>
                        <h3 class="text-lg font-medium text-gray-900 mb-2">No Workflows</h3>
                        <p class="text-gray-500 mb-4">Create your first workflow to get started.</p>
                        <a href="{{ route('admin.workflows.create') }}" class="btn btn-primary">
                            Create First Workflow
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
    <script>
        function toggleStatus(workflowId) {
            fetch(`{{ url('admin/workflows') }}/${workflowId}/toggle-status`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(r => r.json()).then(data => {
                if (!data.success) alert(data.message || 'Error updating status');
            });
        }

        // Initialize Lucide icons
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
@endsection
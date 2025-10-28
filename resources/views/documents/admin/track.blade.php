@extends('layouts.app')

@section('title', 'Document Tracking System - Admin')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Document Tracking System" subtitle="Monitor all document journeys across departments" />

        <!-- Statistics Cards -->
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
            <x-stat-card bgColor="bg-stat-primary" title="Total Documents" :value="$stats['total_documents']" />

            <x-stat-card bgColor="bg-stat-accent" title="Pending" :value="$stats['pending_documents']" />

            <x-stat-card bgColor="bg-stat-secondary" title="Completed" :value="$stats['completed_documents']" />

            <x-stat-card bgColor="bg-stat-danger" title="Overdue" :value="$stats['overdue_documents']" />

            <x-stat-card bgColor="bg-stat-info" title="Avg. Processing" :value="$stats['avg_processing_time'] ? number_format($stats['avg_processing_time']) . 'm' : 'N/A'" />
        </div>

        <x-data-table :headers="[
            'department' => 'Department',
            'total_created' => 'Total Created',
            'pending_count' => 'Pending',
            'completed_count' => 'Completed',
            'rejected_count' => 'Rejected',
            'canceled_count' => 'Canceled',
        ]" :paginator="$departments" :sortableFields="['department', 'total_created', 'pending_count', 'approved_count', 'completed_count', 'rejected_count', 'canceled_count']"
            emptyMessage="No departments found.">

            @foreach ($departments as $department)
                <tr class="hover:bg-gray-50">
                    <!-- Department Name -->
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <div class="shrink-0">
                                <div class="h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                                    @if ($department->logo)
                                        <img src="{{ Storage::url($department->logo) }}" alt="{{ $department->name }} Logo"
                                            class="w-12 h-12 object-cover rounded" />
                                    @else
                                        <span>—</span>
                                    @endif
                                </div>
                            </div>
                            <div class="ml-4">
                                <div class="text-sm font-medium text-gray-900">{{ $department->name }}</div>
                                <div class="text-xs text-gray-500">{{ $department->code ?? 'N/A' }}</div>
                            </div>
                        </div>
                    </td>

                    <!-- Total Created -->
                    <td class="px-6 py-4">
                        <div class="flex items-center">
                            <span class="text-2xl font-bold">{{ $department->total_created }}</span>
                        </div>
                    </td>

                    <!-- Pending -->
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl font-bold">{{ $department->pending_count }}</span>
                            @if($department->pending_count > 0)
                                <x-status-badge status="pending" />
                            @endif
                        </div>
                    </td>

                    <!-- Completed -->
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl font-bold">{{ $department->completed_count }}</span>
                            @if($department->completed_count > 0)
                                <x-status-badge status="completed" />
                            @endif
                        </div>
                    </td>

                    <!-- Rejected -->
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl font-bold">{{ $department->rejected_count }}</span>
                            @if($department->rejected_count > 0)
                                <x-status-badge status="rejected" />
                            @endif
                        </div>
                    </td>

                    <!-- Canceled -->
                    <td class="px-6 py-4">
                        <div class="flex items-center space-x-2">
                            <span class="text-2xl font-bold">{{ $department->canceled_count }}</span>
                            @if($department->canceled_count > 0)
                                <x-status-badge status="canceled" />
                            @endif
                        </div>
                    </td>
                </tr>
            @endforeach
        </x-data-table>
    </div>
@endsection
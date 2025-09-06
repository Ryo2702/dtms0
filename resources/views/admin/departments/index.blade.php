@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Department Management" ::canCreate="['ability' => 'create', 'model' => \App\ Models\ User::class]" :route="route('admin.departments.create')" buttonLabel="Add Department"
            icon="plus" />


        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
            <x-stat-card bgColor="bg-stat-primary" title="Total Departments" :value="$totalDepartments ?? 0" />
            <x-stat-card bgColor="bg-stat-secondary" title="Active Departments" :value="$activeDepartments ?? 0" />
            <x-stat-card bgColor="bg-stat-accent" title="Departments With Heads" :value="$departmentsWithHeads ?? 0" />
            <x-stat-card bgColor="bg-stat-danger" title="Inactive Departments" :value="$inactiveDepartments ?? 0" />
        </div>

        {{-- Filters --}}
        <x-form.filter :action="route('admin.departments.index')" searchPlaceholder="Search by name, code, or description" :sortFields="['id' => 'ID', 'name' => 'Name', 'created_at' => 'Created At']"
            :statuses="['active' => 'Active', 'inactive' => 'Inactive']" containerId="filter-results" />


        <div id="filter-results">
            <x-data-table :headers="['ID', 'Logo', 'Code', 'Name', 'Head', 'Staff Count', 'Status', 'Actions']" :paginator="$departments" emptyMessage="No departments found.">
                @foreach ($departments as $department)
                    <tr>
                        <td class="px-4 py-3">{{ $department->id }}</td>
                        <td class="px-4 py-3">
                            @if ($department->logo)
                                <img src="{{ Storage::url($department->logo) }}" alt="{{ $department->name }} Logo"
                                    class="w-12 h-12 object-cover rounded" />
                            @else
                                <span>—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $department->code }}</td>
                        <td class="px-4 py-3 font-medium">{{ $department->name }}</td>
                        <td class="px-4 py-3">{{ $department->head?->name ?? '—' }}</td>
                        <td class="px-4 py-3">{{ $department->staff->count() }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$department->status" />
                        </td>
                        <td class="px-4 py-3">
                            <x-actions :model="$department" resource="departments" />
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>
@endsection

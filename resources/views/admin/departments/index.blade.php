@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="Department Management" ::canCreate="['ability' => 'create', 'model' => \App\ Models\ User::class]" :route="route('admin.departments.create')" buttonLabel="Add Department"
            icon="plus" />


        {{-- Filters --}}
        <x-form.filter :action="route('admin.departments.index')" searchPlaceholder="Search by name, code, or description" :sortFields="['id' => 'ID', 'name' => 'Name', 'created_at' => 'Created At']"
            :statuses="['active' => 'Active', 'inactive' => 'Inactive']" containerId="filter-results" />


        <div id="filter-results">
            <x-data-table :headers="['ID', 'Logo', 'Head', 'Code', 'Name', 'Status', 'Actions']" :paginator="$departments" emptyMessage="No departments found.">
                @foreach ($departments as $department)
                    <tr>
                        <td class="px-4 py-3">{{ $departments->firstItem() + $loop->index }}</td>
                        <td class="px-4 py-3">
                            @if ($department->logo)
                                <img src="{{ Storage::url($department->logo) }}" alt="{{ $department->name }} Logo"
                                    class="w-12 h-12 object-cover rounded" />
                            @else
                                <span>—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 font-medium">{{ $department->name }}</td>
                        <td class="px-4 py-3 font-mono text-sm">{{ $department->code }}</td>
                     <td class="px-4 py-3">{{ $department->head?->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <x-status-badge :status="$department->status" />
                        </td>
                        <td class="px-4 py-3">
                            <button onclick="showDepartmentDetails({{ $department->toJson() }})" 
                                        class="btn btn-sm btn-outline" 
                                        title="View">
                                    <i data-lucide="eye" class="w-4 h-4 mr-1"></i>
                                    View
                            </button> 
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>
    </div>

    {{-- Department Details Modal --}}
    <x-modal id="department-details-modal" title="Department Details" size="lg">
        <div class="space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <x-label text="Name" />
                    <p class="mt-1 text-sm text-gray-900 font-medium" id="modal-department-name"></p>
                </div>
                <div>
                    <x-label text="Code" />
                    <p class="mt-1 text-sm text-gray-900 font-mono bg-gray-50 px-2 py-1 rounded" id="modal-department-code"></p>
                </div>
                <div>
                    <x-label text="Department Head" />
                    <p class="mt-1 text-sm text-gray-900" id="modal-department-head"></p>
                </div>
                <div>
                    <x-label text="Status" />
                    <div class="mt-1" id="modal-department-status">
                        <span class="badge badge-success" id="status-active" style="display: none;">Active</span>
                        <span class="badge badge-ghost" id="status-inactive" style="display: none;">Inactive</span>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <x-label text="Logo" />
                    <div class="mt-1" id="modal-department-logo">
                        <img id="department-logo-img" src="" alt="Department Logo" class="w-20 h-20 object-cover rounded border" style="display: none;" />
                        <span id="no-logo-text" class="text-sm text-gray-500">No logo uploaded</span>
                    </div>
                </div>
                <div class="md:col-span-2">
                    <x-label text="Description" />
                    <p class="mt-1 text-sm text-gray-900" id="modal-department-description"></p>
                </div>
            </div>
            
            <div class="border-t pt-4">
                <x-label text="Department Information" />
                <div class="mt-2 grid grid-cols-1 md:grid-cols-2 gap-4 text-sm text-gray-600">
                    <div>
                        <span class="font-medium">Created:</span>
                        <span id="modal-department-created"></span>
                    </div>
                    <div>
                        <span class="font-medium">Last Updated:</span>
                        <span id="modal-department-updated"></span>
                    </div>
                </div>
            </div>
        </div>

        <x-slot name="actions">
            <button type="button" 
                    onclick="editDepartment()" 
                    class="btn btn-primary" 
                    id="edit-department-btn">
                <i data-lucide="edit" class="w-4 h-4 mr-2"></i>
                Edit Department
            </button>
            <button type="button" 
                    onclick="window['department-details-modal'].close()" 
                    class="btn btn-secondary">
                Close
            </button>
        </x-slot>
    </x-modal>

    <script>
    let currentDepartment = null;

    function showDepartmentDetails(department) {
        currentDepartment = department;
        
        document.getElementById('modal-department-name').textContent = department.name || '';
        document.getElementById('modal-department-code').textContent = department.code || '';
        document.getElementById('modal-department-head').textContent = department.head?.name || 'No head assigned';
        document.getElementById('modal-department-description').textContent = department.description || 'No description available';
        
        const logoImg = document.getElementById('department-logo-img');
        const noLogoText = document.getElementById('no-logo-text');
        
        if (department.logo) {
            logoImg.src = `/storage/${department.logo}`;
            logoImg.style.display = 'block';
            noLogoText.style.display = 'none';
        } else {
            logoImg.style.display = 'none';
            noLogoText.style.display = 'block';
        }
        
        const createdAt = department.created_at ? new Date(department.created_at).toLocaleDateString() : 'N/A';
        const updatedAt = department.updated_at ? new Date(department.updated_at).toLocaleString() : 'Never';
        
        document.getElementById('modal-department-created').textContent = createdAt;
        document.getElementById('modal-department-updated').textContent = updatedAt;
        
        // Handle status display
        const activeStatus = document.getElementById('status-active');
        const inactiveStatus = document.getElementById('status-inactive');
        
        if (department.status === 'active' || department.status === 1 || department.status === true) {
            activeStatus.style.display = 'inline-block';
            inactiveStatus.style.display = 'none';
        } else {
            activeStatus.style.display = 'none';
            inactiveStatus.style.display = 'inline-block';
        }
        
        // Show modal
        window['department-details-modal'].showModal();
    }

    function editDepartment() {
        if (currentDepartment) {
            window.location.href = `/admin/departments/${currentDepartment.id}/edit`;
        }
    }
    </script>
@endsection

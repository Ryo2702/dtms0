@extends('layouts.app')
@section('title', 'Staff Management')

@section('content')
    <x-container>
        <div class="mb-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-3xl font-bold">Staff Management</h1>
                    <p class="text-gray-600 mt-1">Create and manage Staff for your department</p>
                </div>

                <button type="button" class="btn btn-primary" onclick="addStaffModal.showModal()">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Add Staff
                </button>
            </div>


            <x-data-table :headers="['ID', 'Name of Staff', 'Position', 'Role', 'Status', 'Created', 'Actions']"
                :sortableFields="['full_name', 'position', 'role', 'created_at']" :paginator="$staffs">

                @foreach ($staffs as $staff)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $staffs->firstItem() + $loop->index }}
                        </td>
                        <td class="px-4 py-3 font-medium text-gray-900">
                            {{ $staff->full_name }}
                        </td>
                        <td class="px-4 py-3 truncate text-gray-600">
                            {{ $staff->position ?? 'No Position' }}
                        </td>
                        <td class="px-4 py-3 truncate">
                            {{ $staff->role }}
                        </td>
                        <td class="px-4 py-3">
                            @if ($staff->is_active)
                                <span class="badge badge-success">Active</span>
                            @else
                                <span class="badge badge-ghost">Inactive</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-gray-600 text-sm">
                            {{ $staff->created_at->format('M d, Y') }}
                        </td>
                        <td class="px-3 py-3">
                            <div class="flex gap-2">
                                @if ($staff->is_active)
                                    <button type="button" class="btn btn-sm btn-ghost"
                                        onclick="openEditModal({{ $staff->id }}, '{{ addslashes($staff->full_name) }}', '{{ addslashes($staff->position ?? '') }}', '{{ addslashes($staff->role) }}')"
                                        title="Edit">
                                        <i data-lucide="edit" class="w-4 h-4"></i>
                                    </button>
                                @endif
                            </div>
                        </td>
                    </tr>
                @endforeach
            </x-data-table>
        </div>

        {{-- Add Modal --}}
        <x-modal id="addStaffModal" title="Add New Staff" size="xl">
            <form action="{{ route('staff.store') }}" method="POST" id="addStaffForm">
                @csrf

                <div class="mb-4">
                    <x-form.input name="full_name" label="Full Name" placeholder="e.g., Charles, Jhon Doe" required
                        class="w-full" />
                </div>

                <div class="mb-4">
                    <x-form.input name="position" label="Position" placeholder="Enter Position" class="w-full" />
                </div>

                <div class="mb-4">
                    <x-form.select name="role" label="Role" required class="w-full">
                        <option value="">Select a Role</option>
                        <option value="Mayor">Mayor</option>
                        <option value="Vice Mayor">Vice Mayor</option>
                        <option value="City Councilor">City Councilor</option>
                        <option value="Punong Barangay">Punong Barangay</option>
                        <option value="Barangay Councilor">Barangay Councilor</option>
                        <option value="City Administrator">City Administrator</option>
                        <option value="Department Head">Department Head</option>
                        <option value="Staff">Staff</option>
                    </x-form.select>
                </div>
            </form>

            <x-slot name="actions">
                <button type="button" class="btn btn-ghost" onclick="addStaffModal.close()">
                    Cancel
                </button>
                <button type="submit" form="addStaffForm" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Create
                </button>
            </x-slot>
        </x-modal>

        {{-- Edit Modal --}}
        <x-modal id="editStaffModal" title="Edit Staff" size="xl">
            <form method="POST" id="editStaffForm">
                @csrf
                @method('PUT')

                <div class="mb-4">
                    <x-form.input name="full_name" id="edit_full_name" label="Full Name"
                        placeholder="e.g., Charles, Jhon Doe" required class="w-full" />
                </div>

                <div class="mb-4">
                    <x-form.input name="position" id="edit_position" label="Position" placeholder="Enter Position"
                        class="w-full" />
                </div>

                <div class="mb-4">
                    <x-form.select name="role" id="edit_role" label="Role" required class="w-full">
                        <option value="">Select a Role</option>
                        <option value="Mayor">Mayor</option>
                        <option value="Vice Mayor">Vice Mayor</option>
                        <option value="City Councilor">City Councilor</option>
                        <option value="Punong Barangay">Punong Barangay</option>
                        <option value="Barangay Councilor">Barangay Councilor</option>
                        <option value="City Administrator">City Administrator</option>
                        <option value="Department Head">Department Head</option>
                        <option value="Staff">Staff</option>
                    </x-form.select>
                </div>
            </form>

            <x-slot name="actions">
                <button type="button" class="btn btn-ghost" onclick="editStaffModal.close()">
                    Cancel
                </button>
                <button type="submit" form="editStaffForm" class="btn btn-primary">
                    <i data-lucide="save" class="w-4 h-4 mr-2"></i>
                    Update
                </button>
            </x-slot>
        </x-modal>
    </x-container>
@endsection


@push('scripts')
    <script>
        function openEditModal(id, fullName, position, role) {
            document.getElementById('edit_full_name').value = fullName;
            document.getElementById('edit_position').value = position;
            document.getElementById('edit_role').value = role;
            document.getElementById('editStaffForm').action = `/staff/${id}`;

            editStaffModal.showModal();

        }

        document.addEventListener('DOMContentLoaded', function () {
            @if($errors->any())
                @if(old('_method') === 'PUT')
               
                    @php
                        $editingStaffId = session('editing_staff_id') ?? request()->route('staff');
                    @endphp
                    openEditModal(
                        "{{ $editingStaffId }}", 
                        "{{ addslashes(old('full_name', '')) }}", 
                        "{{ addslashes(old('position', '')) }}", 
                        "{{ addslashes(old('role', '')) }}"
                    );
                @else
                    addStaffModal.showModal();
                @endif
            @endif
        });
    </script>
@endpush
@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <x-page-header title="User Accounts Archives" :backRoute="route('admin.users.archives')" backText="Back to Archives" />

        <div class="alert alert-info mb-6">
            <svg xmlns="http://www.w3.org/2000/svg" class="stroke-current shrink-0 h-6 w-6" fill="none" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
            </svg>
            <span>This section shows all deactivated user accounts. You can view details and reactivate accounts from
                here.</span>
        </div>

        <x-table-filters :route="route('admin.users.user-accounts')" searchPlaceholder="Search archived users..." :filters="[
            [
                'name' => 'department_id',
                'label' => 'Department',
                'options' => $departments->pluck('name', 'id')->prepend('All Departments', '')->toArray(),
                'default' => '',
            ],
            [
                'name' => 'type',
                'label' => 'User Type',
                'options' => ['' => 'All Types', 'Admin' => 'Admin', 'Head' => 'Head', 'Staff' => 'Staff'],
                'default' => '',
            ],
        ]" />

        <div class="bg-base-100 rounded-lg shadow-md overflow-hidden">
            <x-responsive-table :headers="[
                'Archive ID',
                'Municipal ID',
                'Name',
                'Email',
                'Type',
                'Deactivated At',
                'Deactivated By',
                'Reason',
                'Actions',
            ]">
                @forelse ($archives as $archive)
                    <tr class="hover:bg-base-50">
                        <td class="font-medium text-sm">{{ $archive->id }}</td>
                        <td class="font-mono text-sm">
                            <span class="municipal-id-badge">{{ $archive->municipal_id }}</span>
                        </td>
                        <td class="font-medium">{{ $archive->name }}</td>
                        <td class="text-sm text-gray-600">{{ $archive->email }}</td>
                        <td>
                            <div
                                class="badge 
                                {{ $archive->type === 'Admin' ? 'badge-error' : '' }}
                                {{ $archive->type === 'Head' ? 'badge-warning' : '' }}
                                {{ $archive->type === 'Staff' ? 'badge-info' : '' }}
                            ">
                                {{ $archive->type }}
                            </div>
                        </td>
                        <td class="text-sm">{{ $archive->deactivated_at->format('M d, Y H:i') }}</td>
                        <td class="text-sm">{{ $archive->deactivatedBy->name ?? 'System' }}</td>
                        <td class="text-sm max-w-xs truncate">{{ $archive->reason ?? 'No reason provided' }}</td>
                        <td>
                            <x-archive-actions :archive="$archive" :activeAdminCount="$activeAdminCount" />
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="9" class="text-center py-8 text-gray-500">
                            <div class="flex flex-col items-center">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-12 w-12 text-gray-300 mb-4" fill="none"
                                    viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4" />
                                </svg>
                                <p class="text-lg font-medium">No archived users found</p>
                                <p class="text-sm">All user accounts are currently active</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </x-responsive-table>
        </div>

        <div class="mt-6 flex justify-center">
            {{ $archives->links() }}
        </div>
    </div>
@endsection

@extends('layouts.app')

@section('content')
    <div class="p-4 sm:p-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-6 gap-4">
            <h1 class="text-2xl font-bold">User Management</h1>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none" viewBox="0 0 24 24"
                    stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Add User
            </a>
        </div>

        <x-table-filters />

        <div class="bg-base-100 rounded-lg shadow-md overflow-hidden">
            <x-responsive-table :headers="['ID', 'Municipal ID', 'Name', 'Email', 'Type', 'Status', 'Last Seen', 'Actions']">
                @foreach ($users as $user)
                    <tr class="hover:bg-base-50">
                        <td class="font-medium">{{ $user->id }}</td>
                        <td>{{ $user->municipal_id }}</td>
                        <td class="font-medium">{{ $user->name }}</td>
                        <td class="text-sm">{{ $user->email }}</td>
                        <td>
                            <div class="badge badge-outline">{{ $user->type }}</div>
                        </td>
                        <td>
                            <div class="badge {{ $user->status ? 'badge-success' : 'badge-error' }}">
                                {{ $user->status ? 'Active' : 'Inactive' }}
                            </div>
                        </td>
                        <td class="text-sm">{{ $user->last_seen }}</td>
                        <td>
                            <x-user-actions :user="$user" :activeAdminCount="$activeAdminCount" />
                        </td>
                    </tr>
                @endforeach
            </x-responsive-table>
        </div>

        <div class="mt-6 flex justify-center">
            {{ $users->links() }}
        </div>
    </div>
@endsection

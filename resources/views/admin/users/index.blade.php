<!-- resources/views/admin/users/index.blade.php -->
@extends('layouts.app')

@section('content')
    <div class="p-6">
        <h2 class="text-2xl font-bold mb-4">User Management</h2>

        <div class="mt-3 p-6">
            <a href="{{ route('admin.users.create') }}" class="btn btn-soft">Create</a>
        </div>

        <table class="table w-full">
            <thead>
                <tr>
                    <th>Name</th>
                    <th>Municipal ID</th>
                    <th>Department</th>
                    <th>Last Seen</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($users as $user)
                    <tr id="user-{{ $user->id }}">
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->municipal_id }}</td>
                        <td>{{ $user->department }}</td>
                        <td>
                            <span class="flex items-center gap-2">
                                <div class="w-2 h-2 rounded-full {{ $user->isOnline() ? 'bg-green-500' : 'bg-gray-400' }}">
                                </div>
                                <span class="{{ $user->isOnline() ? 'text-green-600 font-medium' : 'text-gray-500' }}">
                                    {{ $user->last_seen }}
                                </span>
                            </span>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endsection
<script>
    // Auto-refresh last seen every 30 seconds
    setInterval(() => {
        fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.text())
            .then(html => {
                const parser = new DOMParser();
                const doc = parser.parseFromString(html, 'text/html');
                const newTableBody = doc.querySelector('tbody');
                if (newTableBody) {
                    document.querySelector('tbody').innerHTML = newTableBody.innerHTML;
                }
            })
            .catch(error => console.log('Refresh error:', error));
    }, 3000); // 30 seconds
</script>

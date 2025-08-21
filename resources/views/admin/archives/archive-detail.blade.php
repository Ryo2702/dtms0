@extends('layouts.app')

@section('content')
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h1 class="text-2xl font-bold">Archive Details</h1>
            <a href="{{ route('admin.users.archives') }}" class="btn btn-secondary">Back to Archives</a>
        </div>

        <div class="bg-white shadow rounded-lg p-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <h3 class="text-lg font-semibold mb-4">User Information</h3>
                    <div class="space-y-2">
                        <p><strong>Municipal ID:</strong> {{ $archive->municipal_id }}</p>
                        <p><strong>Name:</strong> {{ $archive->name }}</p>
                        <p><strong>Email:</strong> {{ $archive->email }}</p>
                        <p><strong>Department:</strong> {{ $archive->department ?? 'Not specified' }}</p>
                        <p><strong>Type:</strong> {{ $archive->type }}</p>
                    </div>
                </div>

                <div>
                    <h3 class="text-lg font-semibold mb-4">Archive Information</h3>
                    <div class="space-y-2">
                        <p><strong>Deactivated At:</strong> {{ $archive->deactivated_at->format('F d, Y \a\t H:i:s') }}</p>
                        <p><strong>Deactivated By:</strong> {{ $archive->deactivatedBy->name ?? 'System' }}</p>
                        <p><strong>Reason:</strong> {{ $archive->reason ?? 'No reason provided' }}</p>
                        <p><strong>Current Status:</strong>
                            @if ($archive->user)
                                <span class="badge {{ $archive->user->status ? 'badge-success' : 'badge-error' }}">
                                    {{ $archive->user->status ? 'Active' : 'Inactive' }}
                                </span>
                            @else
                                <span class="badge badge-warning">User Record Not Found</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>

            @if ($archive->user && !$archive->user->status)
                <div class="mt-6 pt-6 border-t border-gray-200">
                    <h3 class="text-lg font-semibold mb-4">Actions</h3>
                    @php
                        $wouldBeLastAdmin = $archive->user->hasRole('Admin') && $activeAdminCount <= 0;
                    @endphp

                    @if ($wouldBeLastAdmin)
                        <div class="alert alert-warning mb-4">
                            <p>Cannot reactivate: System must have at least one active administrator.</p>
                        </div>
                        <button class="btn btn-success btn-disabled" disabled>Reactivate User Account</button>
                    @else
                        <form action="{{ route('admin.users.reactivate', $archive->user) }}" method="POST"
                            onsubmit="return confirm('Are you sure you want to reactivate this user? This will remove them from the archives.')">
                            @csrf
                            <button class="btn btn-success">Reactivate User Account</button>
                        </form>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

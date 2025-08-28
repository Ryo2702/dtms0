@props(['user', 'activeAdminCount' => 1])

@php
    $isLastAdmin = $user->type === 'Admin' && $activeAdminCount <= 1;
    $isSelf = auth()->id() === $user->id;
    $canDeactivate = $user->is_active && !$isLastAdmin && !$isSelf;
    $canReactivate = !$user->is_active;
@endphp

<div class="flex gap-2">
    {{-- View --}}
    <a href="{{ route('admin.users.show', $user) }}" class="btn btn-xs btn-outline">
        View
    </a>

    {{-- Edit --}}
    @can('update', $user)
        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-xs btn-outline">
            Edit
        </a>
    @endcan

    {{-- Deactivate --}}
    @can('delete', $user)
        @if ($canDeactivate)
            <form action="{{ route('admin.users.deactivate', $user) }}" method="POST"
                onsubmit="return confirm('Are you sure you want to deactivate this user?');" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-xs btn-error">
                    Deactivate
                </button>
            </form>
        @endif
    @endcan

    {{-- Reactivate --}}
    @can('delete', $user)
        @if ($canReactivate)
            <form action="{{ route('admin.users.reactivate', $user) }}" method="POST"
                onsubmit="return confirm('Reactivate this user?');" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-xs btn-success">
                    Reactivate
                </button>
            </form>
        @endif
    @endcan
</div>

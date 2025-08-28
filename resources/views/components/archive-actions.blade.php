@props(['archive', 'activeAdminCount' => 1])

<div class="dropdown dropdown-end">
    <div tabindex="0" role="button" class="btn btn-ghost btn-sm">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"
            class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                d="M12 6.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 12.75a.75.75 0 110-1.5.75.75 0 010 1.5zM12 18.75a.75.75 0 110-1.5.75.75 0 010 1.5z" />
        </svg>
    </div>
    <ul tabindex="0" class="dropdown-content z-[1] menu p-2 shadow bg-base-100 rounded-box w-52">
        <li>
            <a href="{{ route('admin.users.archive-detail', $archive) }}" class="text-info">
                View Details
            </a>
        </li>

        @if ($archive->user && !$archive->user->status)
            @php
                $wouldBeLastAdmin = $archive->user->hasRole('Admin') && $activeAdminCount <= 0;
            @endphp

            @if ($wouldBeLastAdmin)
                <li>
                    <span class="text-gray-400 cursor-not-allowed">
                        Cannot Reactivate (No Active Admin)
                    </span>
                </li>
            @else
                <li>
                    <form action="{{ route('admin.users.reactivate', $archive->user) }}" method="POST"
                        onsubmit="return confirm('Are you sure you want to reactivate this user?')" class="w-full">
                        @csrf
                        <button type="submit" class="text-success w-full text-left">
                            Reactivate User
                        </button>
                    </form>
                </li>
            @endif
        @endif
    </ul>
</div>

<div class="flex flex-wrap gap-2">
    <a href="{{ route('admin.departments.show', $department) }}" class="btn btn-xs sm:btn-sm btn-info">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 sm:mr-1" fill="none" viewBox="0 0 24 24"
            stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
        </svg>
        <span class="hidden sm:inline">View</span>
    </a>
    @can('update', $department)
        <a href="{{ route('admin.departments.edit', $department) }}" class="btn btn-xs sm:btn-sm btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 sm:mr-1" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
            </svg>
            <span class="hidden sm:inline">Edit</span>
        </a>
    @endcan
    @can('delete', $department)
        @if ($department->status)
            <form action="{{ route('admin.departments.deactivate', $department) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-xs sm:btn-sm btn-error"
                    onclick="return confirm('Are you sure you want to deactivate this department?')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 sm:mr-1" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                    </svg>
                    <span class="hidden sm:inline">Deactivate</span>
                </button>
            </form>
        @else
            <form action="{{ route('admin.departments.reactivate', $department) }}" method="POST" class="inline">
                @csrf
                @method('PATCH')
                <button type="submit" class="btn btn-xs sm:btn-sm btn-success"
                    onclick="return confirm('Are you sure you want to reactivate this department?')">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 sm:h-4 sm:w-4 sm:mr-1" fill="none"
                        viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span class="hidden sm:inline">Reactivate</span>
                </button>
            </form>
        @endif
    @endcan
</div>

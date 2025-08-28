{{-- resources/views/components/user-mobile-card.blade.php --}}
@props(['user', 'activeAdminCount'])

<div class="p-4 hover:bg-gray-50 transition-colors">
    <div class="flex items-start gap-4 mb-3">
        <div class="flex-shrink-0 flex flex-col items-center gap-2">
            <label class="cursor-pointer">
                <input type="checkbox" class="checkbox checkbox-sm user-checkbox" value="{{ $user->id }}"
                    data-user-type="{{ $user->type }}" data-user-active="{{ $user->is_active ? 'true' : 'false' }}">
            </label>
            <div
                class="w-16 h-16 bg-gradient-to-br from-dtms-primary to-dtms-secondary rounded-lg flex items-center justify-center text-white font-bold text-lg">
                {{ strtoupper(substr($user->name, 0, 2)) }}
            </div>
        </div>
        <div class="flex-1 min-w-0">
            <div class="flex items-center justify-between mb-1">
                <h3 class="font-semibold text-gray-900 truncate">{{ $user->name }}</h3>
                <div class="flex gap-1">
                    <x-user-type-badge :type="$user->type" />
                    <x-status-badge :active="$user->is_active" />
                </div>
            </div>
            <div class="flex items-center gap-2 mb-2">
                <span class="px-2 py-1 bg-dtms-accent text-white rounded text-xs font-mono">
                    {{ $user->municipal_id }}
                </span>
                <span class="text-sm text-gray-700 truncate">{{ $user->email }}</span>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-sm">
        <div>
            <span class="text-gray-600 font-medium">Department:</span>
            @if ($user->department)
                <div class="font-medium text-gray-900">{{ $user->department->name }}</div>
                <div class="text-xs text-gray-600">({{ $user->department->code }})</div>
            @else
                <span class="text-gray-500 italic">No Department</span>
            @endif
        </div>
        <div>
            <span class="text-gray-600 font-medium">Last Activity:</span>
            <div class="font-medium text-gray-900">
                {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Never' }}
                <span
                    class="ml-2 px-2 py-1 rounded text-xs 
                    {{ $user->updated_at && $user->updated_at->gt(now()->subMinutes(5)) ? 'bg-green-200 text-green-800' : 'bg-gray-200 text-gray-600' }}">
                    {{ $user->updated_at && $user->updated_at->gt(now()->subMinutes(5)) ? 'online' : 'offline' }}
                </span>
            </div>
        </div>
    </div>

    <div class="mt-3 pt-3 border-t border-gray-200">
        <x-user-actions :user="$user" :activeAdminCount="$activeAdminCount" />
    </div>
</div>

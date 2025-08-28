{{-- resources/views/components/user-table-row.blade.php --}}
@props(['user', 'activeAdminCount'])

<tr class="hover:bg-gray-50 transition-colors">
    <td class="px-4 xl:px-6 py-4">
        <label class="cursor-pointer">
            <input type="checkbox" class="checkbox checkbox-sm user-checkbox" value="{{ $user->id }}"
                data-user-type="{{ $user->type }}" data-user-active="{{ $user->is_active ? 'true' : 'false' }}">
        </label>
    </td>
    <td class="px-4 xl:px-6 py-4 font-medium text-sm text-gray-900">{{ $user->id }}</td>
    <td class="px-4 xl:px-6 py-4 font-mono text-sm">
        <span class="px-3 py-1 bg-dtms-accent text-gray-900 rounded-full text-xs font-semibold">
            {{ $user->municipal_id }}
        </span>
    </td>
    <td class="px-4 xl:px-6 py-4 font-medium text-gray-900">{{ $user->name }}</td>
    <td class="px-4 xl:px-6 py-4 text-sm text-gray-700">{{ $user->email }}</td>
    <td class="px-4 xl:px-6 py-4 text-sm">
        @if ($user->department)
            <div class="flex flex-col">
                <span class="font-medium text-gray-900">{{ $user->department->name }}</span>
                <span class="text-xs text-gray-600">({{ $user->department->code }})</span>
            </div>
        @else
            <span class="text-gray-500 italic">No Department</span>
        @endif
    </td>
    <td class="px-4 xl:px-6 py-4">
        <x-user-type-badge :type="$user->type" />
    </td>
    <td class="px-4 xl:px-6 py-4">
        <x-status-badge :active="$user->is_active" />
    </td>
    <td class="px-4 xl:px-6 py-4 text-sm text-gray-700">
        {{ $user->updated_at ? $user->updated_at->diffForHumans() : 'Never' }}
    </td>
    <td>
        <x-user-actions :user="$user" :activeAdminCount="$activeAdminCount" />
    </td>

</tr>

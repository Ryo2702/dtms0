@props([
    'logs' => collect(),
    'paginator' => null,
])

<x-data-table 
    :headers="[
        'User',
        'Action',
        'Description',
        'IP Address',
        'Date & Time'
    ]"
    :paginator="$paginator"
    :sortableFields="['user_id', 'action', 'created_at']"
    emptyMessage="No audit logs found."
>
    @forelse ($logs as $log)
        <tr class="hover:bg-gray-50">
            <td class="px-4 py-3 text-sm">
                <div class="flex items-center space-x-2">
                    @if($log->user)
                        <x-user-avatar :user="$log->user" size="sm" />
                        <div>
                            <div class="font-medium text-gray-900">{{ $log->user->name }}</div>
                            <div class="text-gray-500 text-xs">{{ $log->user->email }}</div>
                        </div>
                    @else
                        <div class="flex items-center space-x-2">
                            <div class="w-6 h-6 bg-gray-300 rounded-full flex items-center justify-center">
                                <i data-lucide="user" class="w-3 h-3 text-gray-600"></i>
                            </div>
                            <span class="text-gray-500 text-sm">System</span>
                        </div>
                    @endif
                </div>
            </td>
            
            <td class="px-4 py-3 text-sm">
                <x-status-badge 
                    :status="$log->action"
                    :variants="[
                        'login' => 'badge-success',
                        'logout' => 'badge-info',
                        'create' => 'badge-primary',
                        'update' => 'badge-warning',
                        'delete' => 'badge-error',
                        'approve' => 'badge-success',
                        'reject' => 'badge-error',
                        'forward' => 'badge-info',
                        'download' => 'badge-secondary',
                    ]"
                    :labels="[
                        'login' => 'Login',
                        'logout' => 'Logout',
                        'create' => 'Create',
                        'update' => 'Update',
                        'delete' => 'Delete',
                        'approve' => 'Approve',
                        'reject' => 'Reject',
                        'forward' => 'Forward',
                        'download' => 'Download',
                    ]"
                />
            </td>
            
            <td class="px-4 py-3 text-sm">
                <div class="max-w-xs">
                    <p class="text-gray-900">{{ $log->description }}</p>
                    @if($log->url)
                        <p class="text-gray-500 text-xs mt-1 font-mono">{{ $log->method }} {{ Str::limit($log->url, 50) }}</p>
                    @endif
                    
                    @if(!empty($log->changes))
                        <div class="mt-2">
                            <button 
                                class="text-xs text-blue-600 hover:text-blue-800"
                                onclick="toggleChanges('changes-{{ $log->id }}')"
                            >
                                View Changes
                            </button>
                            <div id="changes-{{ $log->id }}" class="hidden mt-1 p-2 bg-gray-50 rounded text-xs">
                                @foreach($log->changes as $field => $change)
                                    <div class="mb-1">
                                        <strong>{{ ucfirst($field) }}:</strong>
                                        <span class="text-red-600">{{ $change['old'] ?? 'null' }}</span>
                                        →
                                        <span class="text-green-600">{{ $change['new'] ?? 'null' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </td>
            
            <td class="px-4 py-3 text-sm text-gray-900 font-mono">
                {{ $log->ip_address }}
            </td>
            
            <td class="px-4 py-3 text-sm text-gray-900">
                <div>
                    <div class="font-medium">{{ $log->created_at->format('M j, Y') }}</div>
                    <div class="text-gray-500 text-xs">{{ $log->created_at->format('g:i A') }}</div>
                    <div class="text-gray-400 text-xs">{{ $log->created_at->diffForHumans() }}</div>
                </div>
            </td>
        </tr>
    @empty
        {{-- Empty state is handled by the data-table component --}}
    @endforelse
</x-data-table>

<script>
function toggleChanges(elementId) {
    const element = document.getElementById(elementId);
    element.classList.toggle('hidden');
}
</script>
@props([
    'totalLogs' => 0,
    'todayLogs' => 0,
    'weeklyLogs' => 0,
    'topActions' => collect(),
])

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <x-stat-card
        title="Total Logs"
        :value="number_format($totalLogs)"
        icon="file-text"
        bgColor="bg-blue-50"
        iconColor="text-blue-600"
        valueColor="text-blue-900"
    />

    <x-stat-card
        title="Today's Logs"
        :value="number_format($todayLogs)"
        icon="calendar"
        bgColor="bg-green-50"
        iconColor="text-green-600"
        valueColor="text-green-900"
    />

    <x-stat-card
        title="This Week"
        :value="number_format($weeklyLogs)"
        icon="trending-up"
        bgColor="bg-yellow-50"
        iconColor="text-yellow-600"
        valueColor="text-yellow-900"
    />

    <x-stat-card
        title="Most Common Action"
        :value="$topActions->first()['action'] ?? 'N/A'"
        :subtitle="$topActions->first() ? $topActions->first()['count'] . ' times' : ''"
        icon="activity"
        bgColor="bg-purple-50"
        iconColor="text-purple-600"
        valueColor="text-purple-900"
    />
</div>

@if($topActions->count() > 1)
    <x-card title="Action Breakdown" compact="true" class="mb-6">
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-5 gap-4">
            @foreach($topActions->take(10) as $actionData)
                <div class="text-center">
                    <x-status-badge 
                        :status="$actionData['action']"
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
                        class="mb-1"
                    />
                    <div class="text-sm font-semibold text-gray-900">{{ $actionData['count'] }}</div>
                    <div class="text-xs text-gray-500">
                        {{ round(($actionData['count'] / $totalLogs) * 100, 1) }}%
                    </div>
                </div>
            @endforeach
        </div>
    </x-card>
@endif
@props([
    'activities' => [],
    'title' => 'Recent Activity',
    'maxItems' => 10,
    'showViewAll' => true,
    'viewAllUrl' => null,
])

<div class="card shadow-lg bg-white-secondary">
    <div class="card-body p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="card-title text-lg text-primary">{{ $title }}</h3>
            @if ($showViewAll && $viewAllUrl)
                <a href="{{ $viewAllUrl }}" class="btn btn-ghost btn-sm text-primary hover:bg-primary hover:text-white">
                    View All
                    <x-dynamic-component component="lucide-arrow-right" class="w-4 h-4 ml-1" />
                </a>
            @endif
        </div>

        @if (count($activities) > 0)
            <div class="space-y-4 max-h-96 overflow-y-auto">
                @foreach ($activities->take($maxItems) as $activity)
                    @php
                        $borderClass = '';
                        if (isset($activity['difficulty'])) {
                            if ($activity['difficulty'] === 'urgent') {
                                $borderClass = 'border-l-4 border-red-900';
                            } elseif ($activity['difficulty'] === 'high') {
                                $borderClass = 'border-l-4 border-red-500';
                            } elseif ($activity['difficulty'] === 'medium') {
                                $borderClass = 'border-l-4 border-orange-500';
                            } elseif ($activity['difficulty'] === 'normal') {
                                $borderClass = 'border-l-4 border-green-500';
                            } elseif ($activity['difficulty'] === 'low') {
                                $borderClass = 'border-l-4 border-orange-500';
                            }
                        }
                    @endphp
                    <div class="flex items-start gap-3 p-3 rounded-lg hover:bg-primary/5 transition-colors {{ $borderClass }}">
                        <div class="shrink-0">
                            @if ($activity['type'] === 'document_submitted')
                                <div class="p-2 rounded-full" style="background-color: rgba(39, 84, 138, 0.1); color: #27548A;">
                                    <x-dynamic-component component="lucide-file-plus" class="w-4 h-4" />
                                </div>
                            @elseif ($activity['type'] === 'document_approved')
                                <div class="p-2 rounded-full" style="background-color: rgba(103, 192, 144, 0.2); color: #67C090;">
                                    <x-dynamic-component component="lucide-check-circle" class="w-4 h-4" />
                                </div>
                            @elseif ($activity['type'] === 'document_rejected')
                                <div class="p-2 rounded-full" style="background-color: rgba(255, 63, 51, 0.2); color: #FF3F33;">
                                    <x-dynamic-component component="lucide-x-circle" class="w-4 h-4" />
                                </div>
                            @elseif ($activity['type'] === 'document_forwarded')
                                <div class="p-2 rounded-full" style="background-color: rgba(221, 168, 83, 0.2); color: #DDA853;">
                                    <x-dynamic-component component="lucide-arrow-right-circle" class="w-4 h-4" />
                                </div>
                            @elseif ($activity['type'] === 'document_downloaded')
                                <div class="p-2 rounded-full" style="background-color: rgba(24, 59, 78, 0.2); color: #183B4E;">
                                    <x-dynamic-component component="lucide-download" class="w-4 h-4" />
                                </div>
                            @else
                                <div class="p-2 rounded-full bg-base-300 text-base-content">
                                    <x-dynamic-component component="lucide-activity" class="w-4 h-4" />
                                </div>
                            @endif
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="flex items-start justify-between">
                                <div class="flex-1">
                                    <p class="text-sm font-medium text-base-content">
                                        {{ $activity['title'] }}
                                    </p>
                                    <p class="text-xs text-base-content/70 mt-1">
                                        {{ $activity['description'] }}
                                    </p>
                                    @if (isset($activity['metadata']))
                                        <div class="flex flex-wrap gap-1 mt-2">
                                            @if (isset($activity['metadata']['document_type']))
                                                <span class="badge text-xs" style="background-color: #27548A; color: white;">
                                                    {{ $activity['metadata']['document_type'] }}
                                                </span>
                                            @endif
                                            @if (isset($activity['metadata']['client_name']))
                                                <span class="badge text-xs" style="background-color: #DDA853; color: white;">
                                                    {{ $activity['metadata']['client_name'] }}
                                                </span>
                                            @endif
                                            @if (isset($activity['metadata']['difficulty']))
                                                @php
                                                    $difficultyColors = [
                                                        'urgent' => 'background-color: #7c2d12; color: white;',
                                                        'high' => 'background-color: #ef4444; color: white;',
                                                        'medium' => 'background-color: #f59e0b; color: white;',
                                                        'normal' => 'background-color: #10b981; color: white;',
                                                        'low' => 'background-color: #10b921; color: white;',
                                                    ];
                                                    $difficultyStyle = $difficultyColors[$activity['metadata']['difficulty']] ?? '';
                                                @endphp
                                                <span class="badge text-xs" style="{{ $difficultyStyle }}">
                                                    {{ ucfirst($activity['metadata']['difficulty']) }}
                                                </span>
                                            @endif
                                        </div>
                                    @endif
                                </div>
                                <div class="shrink-0 text-right">
                                    <p class="text-xs text-base-content/60">
                                        {{ $activity['time_ago'] }}
                                    </p>
                                    @if (isset($activity['urgent']) && $activity['urgent'])
                                        <span class="badge text-xs mt-1" style="background-color: #FF3F33; color: white;">Overdue</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <div class="text-base-content/40 mb-2">
                    <x-dynamic-component component="lucide-inbox" class="w-12 h-12 mx-auto" />
                </div>
                <p class="text-base-content/60">No recent activity</p>
                <p class="text-sm text-base-content/40 mt-1">Your activities will appear here</p>
            </div>
        @endif
    </div>
</div>
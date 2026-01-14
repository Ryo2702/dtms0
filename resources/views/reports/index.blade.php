@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-900">Reports</h1>
            <p class="text-gray-600 mt-1">Manage and view all your reports</p>
        </div>
        <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
            <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
            New Report
        </a>
    </div>

    @if ($reports->count() > 0)
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach ($reports as $report)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow overflow-hidden">
                    <div class="p-6">
                        <div class="flex items-start justify-between mb-4">
                            <div class="flex-1">
                                <h3 class="text-lg font-semibold text-gray-900 truncate">{{ $report->name }}</h3>
                                <span class="inline-block mt-2 px-3 py-1 text-xs font-medium bg-blue-100 text-blue-800 rounded-full">
                                    {{ ucfirst($report->report_type) }}
                                </span>
                                @if ($report->is_public)
                                    <span class="inline-block ml-2 px-3 py-1 text-xs font-medium bg-green-100 text-green-800 rounded-full">
                                        Public
                                    </span>
                                @endif
                            </div>
                        </div>

                        @if ($report->description)
                            <p class="text-gray-600 text-sm mb-4 line-clamp-2">{{ $report->description }}</p>
                        @endif

                        <div class="space-y-2 mb-6 text-sm text-gray-600">
                            <div class="flex items-center">
                                <i data-lucide="user" class="w-4 h-4 mr-2"></i>
                                <span>{{ $report->creator->name }}</span>
                            </div>
                            <div class="flex items-center">
                                <i data-lucide="calendar" class="w-4 h-4 mr-2"></i>
                                <span>{{ $report->created_at->format('M d, Y') }}</span>
                            </div>
                            @if ($report->last_generated_at)
                                <div class="flex items-center">
                                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2"></i>
                                    <span>Generated: {{ $report->last_generated_at->format('M d, Y H:i') }}</span>
                                </div>
                            @endif
                        </div>

                        <div class="flex gap-2">
                            <a href="{{ route('reports.show', $report) }}" class="flex-1 px-4 py-2 text-center text-sm font-medium text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                View
                            </a>
                            @if ($report->created_by === Auth::id())
                                <a href="{{ route('reports.edit', $report) }}" class="flex-1 px-4 py-2 text-center text-sm font-medium text-blue-600 hover:bg-blue-50 rounded transition-colors">
                                    Edit
                                </a>
                                <form action="{{ route('reports.destroy', $report) }}" method="POST" class="flex-1" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="w-full px-4 py-2 text-sm font-medium text-red-600 hover:bg-red-50 rounded transition-colors">
                                        Delete
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $reports->links() }}
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-12 text-center">
            <i data-lucide="file-text" class="w-16 h-16 mx-auto text-gray-400 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No reports yet</h3>
            <p class="text-gray-600 mb-6">Create your first report to get started with analytics and insights.</p>
            <a href="{{ route('reports.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                Create Report
            </a>
        </div>
    @endif
</div>
@endsection

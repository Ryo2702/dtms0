@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ route('reports.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Reports
            </a>
            <h1 class="text-3xl font-bold text-gray-900">{{ $report->name }}</h1>
            <p class="text-gray-600 mt-1">{{ $report->description }}</p>
        </div>
        @if (Auth::id() === $report->created_by)
            <div class="flex gap-2">
                <a href="{{ route('reports.edit', $report) }}" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i data-lucide="edit" class="w-4 h-4 mr-2 inline"></i>Edit
                </a>
                <button onclick="generateReport()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2 inline"></i>Generate
                </button>
                @if ($latestResult)
                    <a href="{{ route('reports.export', $report) }}" class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                        <i data-lucide="download" class="w-4 h-4 mr-2 inline"></i>Export
                    </a>
                @endif
                <form action="{{ route('reports.destroy', $report) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition-colors">
                        <i data-lucide="trash-2" class="w-4 h-4 mr-2 inline"></i>Delete
                    </button>
                </form>
            </div>
        @endif
    </div>

    <!-- Report Info -->
    <div class="grid grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="text-sm text-gray-600">Type</div>
            <div class="text-lg font-semibold text-gray-900">{{ ucfirst($report->report_type) }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="text-sm text-gray-600">Created By</div>
            <div class="text-lg font-semibold text-gray-900">{{ $report->creator->name }}</div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="text-sm text-gray-600">Last Generated</div>
            <div class="text-lg font-semibold text-gray-900">
                {{ $report->last_generated_at ? $report->last_generated_at->format('M d, Y') : 'Never' }}
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-md p-4">
            <div class="text-sm text-gray-600">Visibility</div>
            <div class="text-lg font-semibold text-gray-900">
                <span class="px-2 py-1 text-sm rounded {{ $report->is_public ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-800' }}">
                    {{ $report->is_public ? 'Public' : 'Private' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Latest Report Result -->
    @if ($latestResult)
        <div class="bg-white rounded-lg shadow-md p-8 mb-8">
            <div class="flex items-center justify-between mb-6">
                <h2 class="text-2xl font-bold text-gray-900">Latest Report</h2>
                <span class="text-sm text-gray-600">Generated: {{ $latestResult->generated_at->format('M d, Y H:i') }}</span>
            </div>

            <!-- Summary -->
            @if ($latestResult->summary)
                <div class="grid grid-cols-2 gap-4 mb-6">
                    <div class="bg-blue-50 rounded-lg p-4">
                        <div class="text-sm text-gray-600">Total Records</div>
                        <div class="text-2xl font-bold text-blue-600">{{ $latestResult->total_records }}</div>
                    </div>
                    @if (isset($latestResult->summary['by_status']))
                        <div class="bg-purple-50 rounded-lg p-4">
                            <div class="text-sm text-gray-600">By Status</div>
                            <div class="text-sm font-medium text-gray-900">
                                @foreach ($latestResult->summary['by_status'] as $status => $count)
                                    <div>{{ ucfirst($status) }}: {{ $count }}</div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @endif

            <!-- Data Table -->
            @if (!empty($latestResult->data))
                <div class="overflow-x-auto">
                    <table class="min-w-full border-collapse">
                        <thead>
                            <tr class="bg-gray-100 border-b">
                                @foreach (array_keys((array) $latestResult->data[0]) as $column)
                                    <th class="px-4 py-2 text-left text-sm font-semibold text-gray-900">
                                        {{ ucfirst(str_replace('_', ' ', $column)) }}
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($latestResult->data as $row)
                                <tr class="border-b hover:bg-gray-50">
                                    @foreach ((array) $row as $value)
                                        <td class="px-4 py-2 text-sm text-gray-600">
                                            {{ is_array($value) ? json_encode($value) : $value }}
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <p class="text-gray-600 text-center py-8">No data to display</p>
            @endif
        </div>
    @else
        <div class="bg-white rounded-lg shadow-md p-12 text-center mb-8">
            <i data-lucide="bar-chart-3" class="w-16 h-16 mx-auto text-gray-400 mb-4"></i>
            <h3 class="text-lg font-semibold text-gray-900 mb-2">No Report Generated</h3>
            <p class="text-gray-600 mb-6">Click the Generate button to create the first report.</p>
            @if (Auth::id() === $report->created_by)
                <button onclick="generateReport()" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors">
                    <i data-lucide="refresh-cw" class="w-4 h-4 mr-2 inline"></i>Generate Report
                </button>
            @endif
        </div>
    @endif

    <!-- Report History -->
    @if ($results->count() > 0)
        <div class="bg-white rounded-lg shadow-md p-8">
            <h2 class="text-2xl font-bold text-gray-900 mb-6">Report History</h2>
            <div class="space-y-4">
                @foreach ($results as $result)
                    <div class="flex items-center justify-between p-4 border rounded-lg hover:bg-gray-50 transition-colors">
                        <div>
                            <div class="font-medium text-gray-900">
                                Generated: {{ $result->generated_at->format('M d, Y H:i:s') }}
                            </div>
                            <div class="text-sm text-gray-600">
                                {{ $result->total_records }} records
                            </div>
                        </div>
                        <form action="{{ route('reports.destroy', $report) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?');">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                Remove
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
            {{ $results->links() }}
        </div>
    @endif
</div>

<form id="generateForm" action="{{ route('reports.generate', $report) }}" method="POST" class="hidden">
    @csrf
</form>

<script>
function generateReport() {
    document.getElementById('generateForm').submit();
}
</script>
@endsection

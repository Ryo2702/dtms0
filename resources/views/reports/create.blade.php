@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-2xl mx-auto">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('reports.index') }}" class="inline-flex items-center text-blue-600 hover:text-blue-700 mb-4">
                <i data-lucide="arrow-left" class="w-4 h-4 mr-2"></i>
                Back to Reports
            </a>
            <h1 class="text-3xl font-bold text-gray-900">Create New Report</h1>
        </div>

        <form action="{{ route('reports.store') }}" method="POST" class="bg-white rounded-lg shadow-md p-8">
            @csrf

            <!-- Report Name -->
            <div class="mb-6">
                <label for="name" class="block text-sm font-medium text-gray-900 mb-2">Report Name *</label>
                <input type="text" id="name" name="name" value="{{ old('name') }}"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="e.g., Monthly Transaction Report" required>
                @error('name')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Description -->
            <div class="mb-6">
                <label for="description" class="block text-sm font-medium text-gray-900 mb-2">Description</label>
                <textarea id="description" name="description" rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    placeholder="Describe the purpose of this report">{{ old('description') }}</textarea>
                @error('description')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Report Type -->
            <div class="mb-6">
                <label for="report_type" class="block text-sm font-medium text-gray-900 mb-2">Report Type *</label>
                <select id="report_type" name="report_type"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                    onchange="updateTemplates()" required>
                    <option value="">Select a report type</option>
                    <option value="transaction" {{ old('report_type') === 'transaction' ? 'selected' : '' }}>Transaction Report</option>
                    <option value="workflow" {{ old('report_type') === 'workflow' ? 'selected' : '' }}>Workflow Report</option>
                    <option value="user" {{ old('report_type') === 'user' ? 'selected' : '' }}>User Report</option>
                    <option value="department" {{ old('report_type') === 'department' ? 'selected' : '' }}>Department Report</option>
                </select>
                @error('report_type')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Template -->
            <div class="mb-6">
                <label for="template_id" class="block text-sm font-medium text-gray-900 mb-2">Template (Optional)</label>
                <select id="template_id" name="template_id"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">None</option>
                    @foreach ($templates as $template)
                        <option value="{{ $template->id }}" {{ old('template_id') == $template->id ? 'selected' : '' }}>
                            {{ $template->name }}
                        </option>
                    @endforeach
                </select>
                @error('template_id')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-4 mb-6">
                <div>
                    <label for="date_range_start" class="block text-sm font-medium text-gray-900 mb-2">Start Date</label>
                    <input type="date" id="date_range_start" name="date_range_start" value="{{ old('date_range_start') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('date_range_start')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="date_range_end" class="block text-sm font-medium text-gray-900 mb-2">End Date</label>
                    <input type="date" id="date_range_end" name="date_range_end" value="{{ old('date_range_end') }}"
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('date_range_end')
                        <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <!-- Schedule Frequency -->
            <div class="mb-6">
                <label for="schedule_frequency" class="block text-sm font-medium text-gray-900 mb-2">Schedule Frequency</label>
                <select id="schedule_frequency" name="schedule_frequency"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    <option value="">No Schedule</option>
                    <option value="daily" {{ old('schedule_frequency') === 'daily' ? 'selected' : '' }}>Daily</option>
                    <option value="weekly" {{ old('schedule_frequency') === 'weekly' ? 'selected' : '' }}>Weekly</option>
                    <option value="monthly" {{ old('schedule_frequency') === 'monthly' ? 'selected' : '' }}>Monthly</option>
                </select>
                @error('schedule_frequency')
                    <p class="text-red-600 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Public Report -->
            <div class="mb-8">
                <label class="flex items-center">
                    <input type="checkbox" id="is_public" name="is_public" value="1"
                        {{ old('is_public') ? 'checked' : '' }}
                        class="w-4 h-4 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                    <span class="ml-3 text-sm font-medium text-gray-900">Make this report public</span>
                </label>
                <p class="text-sm text-gray-600 mt-1">Public reports can be viewed by other users in the system.</p>
            </div>

            <!-- Buttons -->
            <div class="flex gap-4">
                <button type="submit" class="flex-1 px-6 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-colors">
                    Create Report
                </button>
                <a href="{{ route('reports.index') }}" class="flex-1 px-6 py-2 border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors text-center">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

<script>
function updateTemplates() {
    // This can be expanded with AJAX to load templates based on report type
    console.log('Report type changed');
}
</script>
@endsection

@props([
    'action' => url()->current(),
    'searchPlaceholder' => 'Search...',
    'sortFields' => ['id' => 'ID', 'name' => 'Name', 'created_at' => 'Created At'],
    'statuses' => ['active' => 'Active', 'inactive' => 'Inactive'],
    'defaultSort' => 'id',
    'containerId' => 'filter-results',
])

<form id="filter-form" action="{{ $action }}" method="GET" class="mb-6 space-y-4">
    <div class="flex flex-wrap gap-4 items-end">
        <!-- Search Input -->
        <div class="flex-1 min-w-64 ">
            <label for="search" class="block text-sm font-medium mb-1">Search</label>
            <input type="text" name="search" id="search" value="{{ request('search') }}"
                class="input input-bordered w-full " placeholder="{{ $searchPlaceholder }}">
        </div>

        <!-- Status Filter Buttons -->
        <div>
            <label class="block text-sm font-medium mb-1">Status</label>
            <input type="hidden" name="status" id="status-input" value="{{ request('status') }}">
            <div class="btn-group">
                <button type="button" data-status=""
                    class="btn btn-sm status-btn {{ !request('status') ? 'btn-active' : 'btn-outline' }}">
                    All
                </button>
                @foreach ($statuses as $key => $label)
                    <button type="button" data-status="{{ $key }}"
                        class="btn btn-sm status-btn {{ request('status') == $key ? 'btn-active' : 'btn-outline' }}">
                        {{ $label }}
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Sort Field -->
        <div>
            <label for="sort" class="block text-sm font-medium mb-1">Sort By</label>
            <select name="sort" id="sort" class="select select-bordered">
                @foreach ($sortFields as $field => $label)
                    <option value="{{ $field }}" {{ request('sort', $defaultSort) == $field ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Submit Button -->
        <div>
            <button type="submit" class="btn btn-primary">
                <i data-lucide="search" class="w-4 h-4 mr-2"></i>
                Filter
            </button>
        </div>
    </div>

</form>

<!-- Loading indicator -->
<div id="loading-indicator" class="hidden text-center py-4">
    <span class="loading loading-spinner loading-md"></span>
    <span class="ml-2">Loading...</span>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('filter-form');
        const searchInput = document.getElementById('search');
        const sortSelect = document.getElementById('sort');
        const statusInput = document.getElementById('status-input');
        const statusButtons = document.querySelectorAll('.status-btn');
        const clearButton = document.getElementById('clear-filters');
        const resultsContainer = document.getElementById('{{ $containerId }}');
        const loadingIndicator = document.getElementById('loading-indicator');

        let searchTimeout;

        // Function to perform AJAX request
        function performFilter() {
            const formData = new FormData(form);
            const params = new URLSearchParams(formData);

            // Show loading indicator
            loadingIndicator.classList.remove('hidden');
            resultsContainer.style.opacity = '0.5';

            fetch(`{{ $action }}?${params.toString()}`, {
                    method: 'GET',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(response => response.text())
                .then(html => {
                    // Parse the response and extract the table content
                    const parser = new DOMParser();
                    const doc = parser.parseFromString(html, 'text/html');
                    const newResults = doc.getElementById('{{ $containerId }}');

                    if (newResults) {
                        resultsContainer.innerHTML = newResults.innerHTML;
                    }

                    // Update URL without reloading
                    const newUrl = `{{ $action }}?${params.toString()}`;
                    window.history.pushState({}, '', newUrl);

                    // Hide loading indicator
                    loadingIndicator.classList.add('hidden');
                    resultsContainer.style.opacity = '1';
                })
                .catch(error => {
                    console.error('Filter error:', error);
                    loadingIndicator.classList.add('hidden');
                    resultsContainer.style.opacity = '1';
                });
        }

        // Search input with debounce
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                performFilter();
            }, 500);
        });

        // Sort dropdown change
        sortSelect.addEventListener('change', function() {
            performFilter();
        });

        // Status button clicks
        statusButtons.forEach(button => {
            button.addEventListener('click', function() {
                const status = this.dataset.status;

                // Update hidden input
                statusInput.value = status;

                // Update button states
                statusButtons.forEach(btn => {
                    btn.classList.remove('btn-active');
                    btn.classList.add('btn-outline');
                });
                this.classList.add('btn-active');
                this.classList.remove('btn-outline');

                // Perform filter
                performFilter();
            });
        });

        // Clear filters
        if (clearButton) {
            clearButton.addEventListener('click', function() {
                // Reset form
                searchInput.value = '';
                sortSelect.value = '{{ $defaultSort }}';
                statusInput.value = '';

                // Reset status buttons
                statusButtons.forEach(btn => {
                    btn.classList.remove('btn-active');
                    btn.classList.add('btn-outline');
                });
                statusButtons[0].classList.add('btn-active'); // "All" button
                statusButtons[0].classList.remove('btn-outline');

                // Perform filter
                performFilter();
            });
        }

        // Form submit prevention
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            performFilter();
        });
    });
</script>

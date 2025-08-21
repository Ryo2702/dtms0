@props(['filters' => []])

<div class="bg-base-100 rounded-lg shadow-md p-4 mb-6">
    <div class="flex flex-col sm:flex-row gap-4 items-end">
        <div class="form-control w-full max-w-xs">
            <label class="label">
                <span class="label-text">Search</span>
            </label>
            <input type="text" placeholder="Search users..." class="input input-bordered w-full" id="searchInput" />
        </div>

        <div class="form-control w-full max-w-xs">
            <label class="label">
                <span class="label-text">Filter by Type</span>
            </label>
            <select class="select select-bordered w-full" id="typeFilter">
                <option value="">All Types</option>
                <option value="Admin">Admin</option>
                <option value="Staff">Staff</option>
                <option value="Head">Head</option>
            </select>
        </div>

        <div class="form-control w-full max-w-xs">
            <label class="label">
                <span class="label-text">Filter by Status</span>
            </label>
            <select class="select select-bordered w-full" id="statusFilter">
                <option value="">All Status</option>
                <option value="active">Active</option>
                <option value="inactive">Inactive</option>
            </select>
        </div>

        <button class="btn btn-outline btn-sm" onclick="clearFilters()">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24"
                stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Clear
        </button>
    </div>
</div>

<script>
    function clearFilters() {
        document.getElementById('searchInput').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('statusFilter').value = '';
        filterTable();
    }

    function filterTable() {
        const searchTerm = document.getElementById('searchInput').value.toLowerCase();
        const typeFilter = document.getElementById('typeFilter').value.toLowerCase();
        const statusFilter = document.getElementById('statusFilter').value.toLowerCase();

        const tableRows = document.querySelectorAll('tbody tr');

        tableRows.forEach(row => {
            const cells = row.querySelectorAll('td');
            if (cells.length === 0) return; // Skip empty rows

            const name = cells[2]?.textContent.toLowerCase() || '';
            const email = cells[3]?.textContent.toLowerCase() || '';
            const type = cells[4]?.textContent.toLowerCase() || '';
            const status = cells[5]?.textContent.toLowerCase() || '';

            const matchesSearch = name.includes(searchTerm) || email.includes(searchTerm);
            const matchesType = !typeFilter || type.includes(typeFilter);
            const matchesStatus = !statusFilter || status.includes(statusFilter);

            if (matchesSearch && matchesType && matchesStatus) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    }

    // Add event listeners
    document.addEventListener('DOMContentLoaded', function() {
        document.getElementById('searchInput')?.addEventListener('input', filterTable);
        document.getElementById('typeFilter')?.addEventListener('change', filterTable);
        document.getElementById('statusFilter')?.addEventListener('change', filterTable);
    });
</script>

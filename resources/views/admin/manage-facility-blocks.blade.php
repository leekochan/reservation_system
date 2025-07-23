@php
use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<div class="container mx-auto mt-[80px] px-4">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-3xl font-bold text-[#7B172E]">Manage Facility Blocks</h1>
            <p class="text-gray-600 mt-2">View and manage blocked time slots for facilities</p>
        </div>
        <div class="flex gap-3">
            <button onclick="openAddBlockModal()" 
                    class="bg-[#7B172E] text-white px-6 py-3 rounded-md hover:bg-[#5A1221] transition-colors font-semibold">
                Add New Block
            </button>
            <a href="{{ route('admin.calendar') }}" 
               class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors font-semibold">
                Back to Calendar
            </a>
        </div>
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-lg shadow p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Filter by Facility</label>
                <select id="facilityFilter" class="w-full border border-gray-300 rounded-md px-3 py-2">
                    <option value="">All Facilities</option>
                    @foreach($facilities as $facility)
                        <option value="{{ $facility->facility_id }}">{{ $facility->facility_name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">From Date</label>
                <input type="date" id="fromDate" class="w-full border border-gray-300 rounded-md px-3 py-2" 
                       value="{{ date('Y-m-d') }}">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">To Date</label>
                <input type="date" id="toDate" class="w-full border border-gray-300 rounded-md px-3 py-2"
                       value="{{ date('Y-m-d', strtotime('+1 month')) }}">
            </div>
        </div>
        <div class="mt-4">
            <button onclick="applyFilters()" class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700">
                Apply Filters
            </button>
        </div>
    </div>

    <!-- Blocks List -->
    <div class="bg-white rounded-lg shadow">
        <div class="p-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold">Active Facility Blocks</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Time</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Facility</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Purpose</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Notes</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody id="blocksTableBody" class="bg-white divide-y divide-gray-200">
                    @forelse($blocks as $block)
                        <tr data-block-id="{{ $block->block_id }}" data-facility-id="{{ $block->facility_id }}" data-date="{{ $block->date->format('Y-m-d') }}">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $block->date->format('M d, Y') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ Carbon::parse($block->start_time)->format('g:i A') }} - 
                                {{ Carbon::parse($block->end_time)->format('g:i A') }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $block->facility->facility_name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {{ $block->purpose }}
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-900">
                                {{ $block->notes ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <button onclick="deleteBlock({{ $block->block_id }})" 
                                        class="text-red-600 hover:text-red-900">
                                    Remove
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                No facility blocks found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Add Block Modal -->
<div id="addBlockModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
    <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold text-[#7B172E]">Add Facility Block</h3>
            <button onclick="closeAddBlockModal()" class="text-gray-500 hover:text-gray-700">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <form id="addBlockForm">
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                    <select id="add_facility_id" name="facility_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                        <option value="">Select Facility</option>
                        @foreach($facilities as $facility)
                            <option value="{{ $facility->facility_id }}">{{ $facility->facility_name }}</option>
                        @endforeach
                    </select>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                    <input type="date" id="add_date" name="date" required 
                           min="{{ date('Y-m-d') }}"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                </div>
                
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                        <input type="time" id="add_start_time" name="start_time" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                        <input type="time" id="add_end_time" name="end_time" required 
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                    </div>
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                    <input type="text" id="add_purpose" name="purpose" required placeholder="e.g., Special Program, Maintenance"
                           class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                </div>
                
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                    <textarea id="add_notes" name="notes" rows="3" placeholder="Additional details..."
                              class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]"></textarea>
                </div>
            </div>
            
            <div class="flex space-x-3 mt-6">
                <button type="submit" class="flex-1 bg-[#7B172E] text-white py-2 px-4 rounded-md hover:bg-[#5A1221] transition-colors">
                    Add Block
                </button>
                <button type="button" onclick="closeAddBlockModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition-colors">
                    Cancel
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openAddBlockModal() {
    document.getElementById('addBlockModal').classList.remove('hidden');
}

function closeAddBlockModal() {
    document.getElementById('addBlockModal').classList.add('hidden');
    document.getElementById('addBlockForm').reset();
}

function deleteBlock(blockId) {
    if (confirm('Are you sure you want to remove this facility block?')) {
        fetch(`/admin/facility-blocks/${blockId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remove the row from the table
                document.querySelector(`tr[data-block-id="${blockId}"]`).remove();
                alert('Facility block removed successfully!');
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while removing the facility block.');
        });
    }
}

function applyFilters() {
    const facilityId = document.getElementById('facilityFilter').value;
    const fromDate = document.getElementById('fromDate').value;
    const toDate = document.getElementById('toDate').value;
    
    // Hide all rows first
    const rows = document.querySelectorAll('#blocksTableBody tr[data-block-id]');
    rows.forEach(row => {
        const rowFacilityId = row.getAttribute('data-facility-id');
        const rowDate = row.getAttribute('data-date');
        
        let showRow = true;
        
        // Filter by facility
        if (facilityId && rowFacilityId !== facilityId) {
            showRow = false;
        }
        
        // Filter by date range
        if (fromDate && rowDate < fromDate) {
            showRow = false;
        }
        if (toDate && rowDate > toDate) {
            showRow = false;
        }
        
        row.style.display = showRow ? '' : 'none';
    });
}

// Handle add block form submission
document.getElementById('addBlockForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const data = Object.fromEntries(formData);
    
    fetch('/admin/facility-blocks', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Facility block added successfully!');
            closeAddBlockModal();
            // Reload the page to show the new block
            window.location.reload();
        } else {
            alert('Error: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('An error occurred while adding the facility block.');
    });
});

// Close modal when clicking outside
document.getElementById('addBlockModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeAddBlockModal();
    }
});
</script>

</body>
</html>

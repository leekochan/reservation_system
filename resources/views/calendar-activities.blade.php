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
@if(isset($isAdmin) && $isAdmin)
    @include('partials.admin-navbar')
@else
    @include('partials.navbar')
@endif

<div class="container mx-auto mt-[60px]">
    <!-- Top Layout -->
    <div class="flex flex-wrap gap-4 justify-between items-center mb-6">
        <!-- Left Section: Date -->
        <div class="flex flex-col items-center justify-center p-6 rounded-md ml-[100px]">
            <div class="flex items-end">
                <h1 class="text-[250px] font-extrabold text-[#7B172E] leading-none mr-4">
                    {{ $today->format('d') }}
                </h1>
                <div class="flex flex-col justify-center items-center">
                    <div class="text-8xl font-bold text-[#7B172E] mt-8">
                        {{ $currentDate->format('F') }}
                    </div>
                    <div class="text-4xl font-semibold text-[#7B172E]">
                        {{ $currentDate->format('Y') }}
                    </div>
                </div>
            </div>

            <!-- Admin Manage Button -->
            @if(isset($isAdmin) && $isAdmin)
                <div class="flex flex-row gap-4 mt-6">
                    <button onclick="openManageModal()"
                            class="bg-[#7B172E] text-white px-6 py-3 rounded-md hover:bg-[#5A1221] transition-colors font-semibold">
                        Manage Facility Blocks
                    </button>
                    <a href="{{ route('admin.facility-blocks.manage') }}"
                       class="bg-blue-600 text-white px-6 py-3 rounded-md hover:bg-blue-700 transition-colors font-semibold text-center">
                        View All Blocks
                    </a>
                </div>
            @endif
        </div>

        <!-- Calendar -->
        <div class="bg-white rounded shadow p-4 ml-auto mt-[50px] mr-[10px]">
            <!-- Navigation arrows -->
            <div class="flex justify-between items-center mb-2">
                <a href="?month={{ $currentDate->copy()->subMonth()->month }}&year={{ $currentDate->copy()->subMonth()->year }}{{ isset($isAdmin) && $isAdmin ? '&admin=true' : '' }}"
                   class="text-[#7B172E] hover:text-[#5A1221]">←</a>
                <div class="text-center text-lg font-medium">{{ $monthName }}, {{ $year }}</div>
                <a href="?month={{ $currentDate->copy()->addMonth()->month }}&year={{ $currentDate->copy()->addMonth()->year }}{{ isset($isAdmin) && $isAdmin ? '&admin=true' : '' }}"
                   class="text-[#7B172E] hover:text-[#5A1221]">→</a>
            </div>

            <div class="grid grid-cols-7 gap-2 text-sm text-center font-semibold">
                @foreach(['SUN','MON','TUE','WED','THU','FRI','SAT'] as $day)
                    <div>{{ $day }}</div>
                @endforeach

                @for($i = 0; $i < $firstDayOfMonth; $i++)
                    <div></div>
                @endfor

                @for($day = 1; $day <= $daysInMonth; $day++)
                    @php
                        $dateKey = $currentDate->copy()->day($day)->format('Y-m-d');
                        $hasReservations = isset($reservationsByDate[$dateKey]);
                        $isToday = $day == $today->day && $currentDate->month == $today->month && $currentDate->year == $today->year;
                    @endphp
                    <div class="p-1 relative {{ $isToday ? 'bg-maroon-800 text-white rounded-full' : '' }} {{ $hasReservations ? 'font-bold' : '' }}">
                        {{ $day }}
                        @if($hasReservations)
                            <div class="absolute bottom-0 left-1/2 transform -translate-x-1/2 w-1 h-1 bg-[#7B172E] rounded-full {{ $isToday ? 'bg-white' : '' }}"></div>
                        @endif
                    </div>
                @endfor
            </div>
        </div>
    </div>

    <!-- Dynamic Schedule Section -->
    <div class="mt-6 space-y-4">
        @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateKey = $currentDate->copy()->day($day)->format('Y-m-d');
                $dayReservations = isset($reservationsByDate[$dateKey]) ? $reservationsByDate[$dateKey] : collect();
                $dateObj = $currentDate->copy()->day($day);
                $colors = ['bg-yellow-700', 'bg-cyan-700', 'bg-teal-700', 'bg-blue-700', 'bg-green-700', 'bg-purple-700'];

                // Sort reservations by time
                $sortedReservations = $dayReservations->sortBy(function($reservation) {
                    // Handle admin blocks
                    if (isset($reservation->is_admin_block) && $reservation->is_admin_block) {
                        return $reservation->formatted_times['raw_start'];
                    }

                    // Handle regular reservations
                    $startTime = 'N/A';
                    if (isset($reservation->single) && $reservation->single) {
                        $startTime = $reservation->single->time_from ?? 'N/A';
                    } elseif (isset($reservation->consecutive) && $reservation->consecutive) {
                        $startTime = $reservation->consecutive->start_time_from ?? 'N/A';
                    } elseif (isset($reservation->multiple) && $reservation->multiple) {
                        $startTime = $reservation->multiple->start_time_from ?? 'N/A';
                    }
                    return $startTime !== 'N/A' ? $startTime : '23:59:59';
                });

                $columnCount = $sortedReservations->count() > 0 ? $sortedReservations->count() : 1;
            @endphp

            @if($sortedReservations->count() > 0)
                <div class="flex gap-2">
                    <div class="flex items-center justify-center flex-col w-20 flex-shrink-0 border-t border-l border-r border-b border-black">
                        <div class="text-center font-bold text-sm">{{ $dateObj->format('D') }}</div>
                        <div class="text-[#7B172E] font-extrabold text-2xl">{{ $dateObj->format('d') }}</div>
                    </div>

                    @foreach($sortedReservations as $index => $reservation)
                        @php
                            $colorClass = $colors[$index % count($colors)];

                            // Check if this is an admin block or regular reservation
                            if (isset($reservation->is_admin_block) && $reservation->is_admin_block) {
                                $colorClass = 'bg-red-600 text-white'; // Special color for admin blocks
                                $startTime = $reservation->formatted_times['start_time'];
                                $endTime = $reservation->formatted_times['end_time'];
                                $title = $reservation->purpose;
                                $facilityName = $reservation->facility->facility_name ?? 'No Facility';
                                $isAdminBlock = true;
                            } else {
                                // White background for regular reservations
                                $colorClass = 'bg-white text-gray-800 border-x border-black-500';
                                
                                // Get time information based on reservation type
                                $startTime = 'N/A';
                                $endTime = 'N/A';
                                $isAdminBlock = false;

                                if (isset($reservation->single) && $reservation->single) {
                                    $startTime = $reservation->single->time_from ?? 'N/A';
                                    $endTime = $reservation->single->time_to ?? 'N/A';
                                } elseif (isset($reservation->consecutive) && $reservation->consecutive) {
                                    $startTime = $reservation->consecutive->start_time_from ?? 'N/A';
                                    $endTime = $reservation->consecutive->start_time_to ?? 'N/A';
                                } elseif (isset($reservation->multiple) && $reservation->multiple) {
                                    $startTime = $reservation->multiple->start_time_from ?? 'N/A';
                                    $endTime = $reservation->multiple->start_time_to ?? 'N/A';
                                }

                                $title = $reservation->purpose ?? $reservation->title ?? 'Reservation';
                                $facilityName = $reservation->facility->facility_name ?? 'No Facility';
                            }
                        @endphp
                        <div class="{{ $colorClass }} p-4 border-t border-l border-r border-b border-black flex-shrink-0 flex flex-col justify-center" style="min-width: 200px; width: auto;">
                            @if($isAdminBlock)
                                <div class="font-bold text-md mb-1 flex items-center justify-start">
                                    <span class="text-xs bg-white text-red-600 px-2 py-1 rounded flex-shrink-0">ADMIN BLOCK</span>
                                </div>
                            @endif
                            <div class="text-sm mb-1 flex items-center">
                                <strong class="{{ $isAdminBlock ? 'text-white' : 'text-gray-600' }} flex-shrink-0 mr-1">Purpose:</strong> 
                                <span class="{{ $isAdminBlock ? 'text-white' : 'text-gray-800' }} whitespace-nowrap">{{ $title }}</span>
                            </div>
                            <div class="text-sm mb-1 flex items-center">
                                <strong class="{{ $isAdminBlock ? 'text-white' : 'text-gray-600' }} flex-shrink-0 mr-1">Facility:</strong> 
                                <span class="{{ $isAdminBlock ? 'text-white' : 'text-gray-800' }} whitespace-nowrap">{{ $facilityName }}</span>
                            </div>
                            <div class="text-sm mb-1 flex items-center">
                                <strong class="{{ $isAdminBlock ? 'text-white' : 'text-gray-600' }} flex-shrink-0 mr-1">Date:</strong> 
                                <span class="{{ $isAdminBlock ? 'text-white' : 'text-gray-800' }} whitespace-nowrap">{{ $dateKey }}</span>
                            </div>
                            <div class="text-sm mb-1 flex items-center">
                                <strong class="{{ $isAdminBlock ? 'text-white' : 'text-gray-600' }} flex-shrink-0 mr-1">Time:</strong>
                                @if($isAdminBlock)
                                    <span class="{{ $isAdminBlock ? 'text-white' : 'text-gray-800' }} whitespace-nowrap">{{ $startTime }} - {{ $endTime }}</span>
                                @elseif($startTime !== 'N/A' && $endTime !== 'N/A')
                                    <span class="{{ $isAdminBlock ? 'text-white' : 'text-gray-800' }} whitespace-nowrap">{{ Carbon::parse($startTime)->format('g:i A') }} -
                                    {{ Carbon::parse($endTime)->format('g:i A') }}</span>
                                @else
                                    <span class="{{ $isAdminBlock ? 'text-white' : 'text-gray-800' }} whitespace-nowrap">N/A</span>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="flex">
                    <div class="flex items-center justify-center flex-col w-20 flex-shrink-0 border-t border-l border-r border-b border-black">
                        <div class="text-center font-bold text-sm">{{ $dateObj->format('D') }}</div>
                        <div class="text-[#7B172E] font-extrabold text-2xl">{{ $dateObj->format('d') }}</div>
                    </div>
                    <div class="bg-gray-100 text-gray-500 p-4 border-t border-l border-r border-b border-black flex items-center flex-1">
                        <div class="text-sm">No reservations</div>
                    </div>
                </div>
            @endif
        @endfor
    </div>
</div>

<!-- Manage Facility Block Modal -->
@if(isset($isAdmin) && $isAdmin)
    <div id="manageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-4">
                <h3 class="text-lg font-semibold text-[#7B172E]">Block Facility Time</h3>
                <button onclick="closeManageModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="blockFacilityForm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Facility</label>
                        <select id="facility_id" name="facility_id" required class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                            <option value="">Select Facility</option>
                            <!-- We'll populate this dynamically -->
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Date</label>
                        <input type="date" id="date" name="date" required
                               min="{{ date('Y-m-d') }}"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Start Time</label>
                            <input type="time" id="start_time" name="start_time" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">End Time</label>
                            <input type="time" id="end_time" name="end_time" required
                                   class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Purpose</label>
                        <input type="text" id="purpose" name="purpose" required placeholder="e.g., Special Program, Maintenance"
                               class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Additional details..."
                                  class="w-full border border-gray-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]"></textarea>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button type="submit" class="flex-1 bg-[#7B172E] text-white py-2 px-4 rounded-md hover:bg-[#5A1221] transition-colors">
                        Block Time Slot
                    </button>
                    <button type="button" onclick="closeManageModal()" class="flex-1 bg-gray-300 text-gray-700 py-2 px-4 rounded-md hover:bg-gray-400 transition-colors">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<style>
    .bg-maroon-800 {
        background-color: #6A1B1A;
    }
</style>

<script>
    @if(isset($isAdmin) && $isAdmin)
    // Facilities data - we'll populate this from the backend
    let facilities = [];

    // Fetch facilities when page loads
    document.addEventListener('DOMContentLoaded', function() {
        fetchFacilities();
    });

    function fetchFacilities() {
        fetch('/api/facilities')
            .then(response => response.json())
            .then(data => {
                facilities = data.facilities || [];
                populateFacilitySelect();
            })
            .catch(error => console.error('Error fetching facilities:', error));
    }

    function populateFacilitySelect() {
        const select = document.getElementById('facility_id');
        select.innerHTML = '<option value="">Select Facility</option>';

        facilities.forEach(facility => {
            const option = document.createElement('option');
            option.value = facility.facility_id;
            option.textContent = facility.facility_name;
            select.appendChild(option);
        });
    }

    function openManageModal() {
        document.getElementById('manageModal').classList.remove('hidden');
    }

    function closeManageModal() {
        document.getElementById('manageModal').classList.add('hidden');
        document.getElementById('blockFacilityForm').reset();
    }

    // Handle form submission
    document.getElementById('blockFacilityForm').addEventListener('submit', function(e) {
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
                    alert('Facility time slot blocked successfully!');
                    closeManageModal();
                    // Reload the page to show the new block
                    window.location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while blocking the facility.');
            });
    });

    // Close modal when clicking outside
    document.getElementById('manageModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeManageModal();
        }
    });
    @endif
</script>

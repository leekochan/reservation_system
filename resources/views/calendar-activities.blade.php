@php
    use Carbon\Carbon;
@endphp

<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Calendar of Activities</title>
    <style>
        .calendar-day {
            transition: all 0.3s ease;
            cursor: pointer;
        }
        .calendar-day:hover {
            background-color: #f3f4f6;
            transform: scale(1.05);
        }
        .calendar-day.has-events {
            background-color: #fef3c7;
            font-weight: bold;
        }
        .calendar-day.today {
            background-color: #7B172E;
            color: white;
        }
        .calendar-day.selected {
            background-color: #1f2937;
            color: white;
        }
        .event-card {
            transition: all 0.3s ease;
            border-left: 4px solid;
        }
        .event-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }
        .event-accepted {
            border-left-color: #10b981;
        }
        .event-completed {
            border-left-color: #3b82f6;
        }
        .event-admin-block {
            border-left-color: #ef4444;
        }
        .filter-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        .status-badge {
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
        }
        .status-accepted {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-completed {
            background-color: #dbeafe;
            color: #1e40af;
        }
        .status-admin-block {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>

<body class="bg-gray-50 m-0 p-0 mt-[100px]">
@if(isset($isAdmin) && $isAdmin)
    @include('partials.admin-navbar')
@else
    @include('partials.navbar')
@endif

<div class="container mx-auto mt-[60px] px-10">
    <!-- Header Section -->
    <div class="flex flex-col lg:flex-row justify-between items-start lg:items-center mb-6">
        <!-- Date Display -->
        <div class="flex items-center mb-4 lg:mb-0">
            <div class="text-center">
                <h1 class="text-4xl lg:text-6xl font-extrabold text-[#7B172E] leading-none">
                    {{ $today->format('d') }}
                </h1>
                <div class="text-xl lg:text-2xl font-bold text-[#7B172E]">
                    {{ $currentDate->format('F Y') }}
                </div>
            </div>
        </div>

        <!-- Admin Controls -->
        @if(isset($isAdmin) && $isAdmin)
            <div class="flex flex-col sm:flex-row gap-2">
                <button onclick="openManageModal()"
                        class="bg-[#7B172E] text-white px-4 py-2 rounded-md hover:bg-[#5A1221] transition-all duration-200 shadow-md hover:shadow-lg font-medium text-sm">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
                    </svg>
                    Block Facility
                </button>
                <a href="{{ route('admin.facility-blocks.manage') }}"
                   class="bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition-all duration-200 shadow-md hover:shadow-lg font-medium text-sm text-center">
                    <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    View All Blocks
                </a>
            </div>
        @endif
    </div>

    <!-- Interactive Calendar -->
    <div class="bg-white rounded-xl shadow-lg p-4 mb-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-lg font-bold text-gray-800">Calendar Navigation</h2>
            <div class="flex items-center space-x-4">
                <a href="?month={{ $currentDate->copy()->subMonth()->month }}&year={{ $currentDate->copy()->subMonth()->year }}{{ isset($isAdmin) && $isAdmin ? '&admin=true' : '' }}"
                   class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
                    </svg>
                </a>
                <span class="text-lg font-semibold text-gray-800">{{ $monthName }}, {{ $year }}</span>
                <a href="?month={{ $currentDate->copy()->addMonth()->month }}&year={{ $currentDate->copy()->addMonth()->year }}{{ isset($isAdmin) && $isAdmin ? '&admin=true' : '' }}"
                   class="p-2 rounded-full hover:bg-gray-100 transition-colors">
                    <svg class="w-6 h-6 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>
        </div>

        <!-- Calendar Grid -->
        <div class="grid grid-cols-7 gap-1">
            <!-- Day Headers -->
            @foreach(['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $day)
                <div class="p-2 text-center font-semibold text-gray-600 text-xs">{{ $day }}</div>
            @endforeach

            <!-- Empty cells for first week -->
            @for($i = 0; $i < $firstDayOfMonth; $i++)
                <div class="p-2"></div>
            @endfor

            <!-- Calendar Days -->
            @for($day = 1; $day <= $daysInMonth; $day++)
                @php
                    $dateKey = $currentDate->copy()->day($day)->format('Y-m-d');
                    $hasReservations = isset($reservationsByDate[$dateKey]);
                    $isToday = $day == $today->day && $currentDate->month == $today->month && $currentDate->year == $today->year;
                    $dayDate = $currentDate->copy()->day($day);
                @endphp
                <div onclick="scrollToDate('{{ $dateKey }}')" 
                     class="calendar-day p-2 text-center cursor-pointer rounded-md border border-gray-200 hover:border-gray-300 {{ $isToday ? 'today' : '' }} {{ $hasReservations ? 'has-events' : '' }}">
                    <div class="text-xs font-medium">{{ $day }}</div>
                    @if($hasReservations)
                        <div class="mt-1">
                            <div class="w-1.5 h-1.5 bg-[#7B172E] rounded-full mx-auto"></div>
                        </div>
                    @endif
                </div>
            @endfor
        </div>
    </div>

    <!-- Filter Toggle Button -->
    <div class="mb-6">
        <button id="filterToggle" onclick="toggleFilter()" class="bg-gradient-to-r from-purple-600 to-blue-600 text-white px-6 py-3 rounded-lg hover:from-purple-700 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl font-semibold flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
            </svg>
            <span id="filterButtonText">Show Filters</span>
            <svg id="filterArrow" class="w-4 h-4 ml-2 transform transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
            </svg>
        </button>
    </div>

    <!-- Enhanced Filter Section -->
    <div id="filterSection" class="filter-card rounded-xl shadow-lg p-5 mb-6 text-white hidden">
        <h3 class="text-lg font-bold mb-4 flex items-center">
            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
            </svg>
            Filter Events
        </h3>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Facility Filter -->
            <div>
                <label class="block text-sm font-medium mb-2">Facility</label>
                <select id="facilityFilter" class="w-full border-0 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white text-gray-800 text-sm">
                    <option value="">All Facilities</option>
                </select>
            </div>

            <!-- Event Type Filter -->
            <div>
                <label class="block text-sm font-medium mb-2">Event Type</label>
                <select id="typeFilter" class="w-full border-0 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white text-gray-800 text-sm">
                    <option value="">All Types</option>
                    <option value="accepted">Accepted Reservations</option>
                    <option value="completed">Completed Events</option>
                    <option value="admin_block">Admin Blocks</option>
                </select>
            </div>

            <!-- Time Range Filter -->
            <div>
                <label class="block text-sm font-medium mb-2">Time Range</label>
                <select id="timeFilter" class="w-full border-0 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white text-gray-800 text-sm">
                    <option value="">All Times</option>
                    <option value="morning">Morning (6:00 AM - 12:00 PM)</option>
                    <option value="afternoon">Afternoon (12:00 PM - 6:00 PM)</option>
                    <option value="evening">Evening (6:00 PM - 11:00 PM)</option>
                </select>
            </div>

            <!-- Search Filter -->
            <div>
                <label class="block text-sm font-medium mb-2">Search</label>
                <input type="text" id="searchFilter" placeholder="Search events..." 
                       class="w-full border-0 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-white text-gray-800 text-sm">
            </div>
        </div>

        <!-- Filter Actions -->
        <div class="flex flex-wrap gap-2 mt-4">
            <button id="applyFilters" class="bg-white text-purple-600 px-4 py-2 rounded-lg hover:bg-gray-100 transition-all duration-200 font-medium text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.207A1 1 0 013 6.5V4z"></path>
                </svg>
                Apply Filters
            </button>
            <button id="clearFilters" class="bg-gray-600 text-white px-4 py-2 rounded-lg hover:bg-gray-700 transition-all duration-200 font-medium text-sm">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
                Clear All
            </button>
            <div id="filterStatus" class="flex items-center text-sm ml-2">
                <span id="filteredCount">Showing all events</span>
            </div>
        </div>
    </div>

    <!-- Events Section -->
    <div class="space-y-4" id="scheduleSection">
        @for($day = 1; $day <= $daysInMonth; $day++)
            @php
                $dateKey = $currentDate->copy()->day($day)->format('Y-m-d');
                $dayReservations = isset($reservationsByDate[$dateKey]) ? $reservationsByDate[$dateKey] : collect();
                $dateObj = $currentDate->copy()->day($day);
                $isToday = $dateKey === $today->format('Y-m-d');

                // Sort reservations by time
                $sortedReservations = $dayReservations->sortBy(function($reservation) {
                    if (isset($reservation->is_admin_block) && $reservation->is_admin_block) {
                        return $reservation->formatted_times['raw_start'] ?? '23:59:59';
                    }
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
            @endphp

            @if($sortedReservations->count() > 0)
                <div class="bg-white rounded-xl shadow-lg overflow-hidden" data-date="{{ $dateKey }}">
                    <!-- Day Header -->
                    <div class="bg-gradient-to-r from-[#7B172E] to-[#5A1221] text-white p-3">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3">
                                <div class="text-center">
                                    <div class="text-lg font-bold">{{ $dateObj->format('D') }}</div>
                                    <div class="text-2xl font-extrabold">{{ $dateObj->format('d') }}</div>
                                </div>
                                <div class="text-md font-semibold">{{ $dateObj->format('F Y') }}</div>
                            </div>
                            <div class="text-right">
                                <div class="text-xs opacity-90">{{ $sortedReservations->count() }} event{{ $sortedReservations->count() > 1 ? 's' : '' }}</div>
                                @if($isToday)
                                    <div class="text-xs bg-white text-[#7B172E] px-2 py-1 rounded-full mt-1">Today</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Events Grid -->
                    <div class="p-4">
                        <div class="grid grid-cols-1 lg:grid-cols-2 xl:grid-cols-3 gap-3">
                            @foreach($sortedReservations as $reservation)
                                @php
                                    // Determine event type and styling
                                    if (isset($reservation->is_admin_block) && $reservation->is_admin_block) {
                                        $eventType = 'admin_block';
                                        $statusClass = 'status-admin-block';
                                        $eventClass = 'event-admin-block';
                                        $statusText = 'ADMIN BLOCK';
                                        $startTime = $reservation->formatted_times['start_time'] ?? 'N/A';
                                        $endTime = $reservation->formatted_times['end_time'] ?? 'N/A';
                                        $title = $reservation->purpose ?? 'Admin Block';
                                        $facilityName = isset($reservation->facility) ? ($reservation->facility->facility_name ?? 'No Facility') : 'No Facility';
                                    } else {
                                        $eventType = $reservation->status;
                                        $statusClass = $reservation->status === 'completed' ? 'status-completed' : 'status-accepted';
                                        $eventClass = $reservation->status === 'completed' ? 'event-completed' : 'event-accepted';
                                        $statusText = strtoupper($reservation->status);
                                        
                                        $startTime = 'N/A';
                                        $endTime = 'N/A';
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
                                        
                                        $title = $reservation->purpose ?? 'Reservation';
                                        $facilityName = isset($reservation->facility) ? ($reservation->facility->facility_name ?? 'No Facility') : 'No Facility';
                                    }
                                @endphp
                                
                                <div class="event-card {{ $eventClass }} bg-white rounded-lg shadow-md p-3 border border-gray-200">
                                    <div class="flex items-start justify-between mb-2">
                                        <span class="status-badge {{ $statusClass }}">{{ $statusText }}</span>
                                        <div class="text-xs text-gray-500">{{ $dateKey }}</div>
                                    </div>
                                    
                                    <h4 class="font-bold text-gray-900 mb-2 line-clamp-2 text-sm">{{ $title }}</h4>
                                    
                                    <div class="space-y-1 text-xs">
                                        <div class="flex items-center">
                                            <svg class="w-3 h-3 text-gray-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                            </svg>
                                            <span class="text-gray-700">{{ $facilityName }}</span>
                                        </div>
                                        
                                        @if($startTime !== 'N/A' && $endTime !== 'N/A')
                                            <div class="flex items-center">
                                                <svg class="w-3 h-3 text-gray-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                                </svg>
                                                <span class="text-gray-700">
                                                    {{ Carbon::parse($startTime)->format('g:i A') }} - {{ Carbon::parse($endTime)->format('g:i A') }}
                                                </span>
                                            </div>
                                        @endif
                                        
                                        @if(isset($reservation->organization) && $reservation->organization)
                                            <div class="flex items-center">
                                                <svg class="w-3 h-3 text-gray-500 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                                                </svg>
                                                <span class="text-gray-700">{{ $reservation->organization }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @else
                <div class="bg-white rounded-xl shadow-lg p-4" data-date="{{ $dateKey }}">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3">
                            <div class="text-center">
                                <div class="text-lg font-bold text-gray-600">{{ $dateObj->format('D') }}</div>
                                <div class="text-2xl font-extrabold text-[#7B172E]">{{ $dateObj->format('d') }}</div>
                            </div>
                            <div class="text-md font-semibold text-gray-800">{{ $dateObj->format('F Y') }}</div>
                        </div>
                        @if($isToday)
                            <div class="text-xs bg-[#7B172E] text-white px-2 py-1 rounded-full">Today</div>
                        @endif
                    </div>
                    <div class="text-center py-4">
                        <svg class="w-12 h-12 text-gray-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        <p class="text-gray-500 text-sm">No events scheduled</p>
                    </div>
                </div>
            @endif
        @endfor
    </div>
</div>

<!-- Manage Facility Block Modal -->
@if(isset($isAdmin) && $isAdmin)
    <div id="manageModal" class="fixed inset-0 bg-black bg-opacity-50 hidden flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-full max-w-md mx-4">
            <div class="flex justify-between items-center mb-6">
                <h3 class="text-xl font-bold text-[#7B172E]">Block Facility Time</h3>
                <button onclick="closeManageModal()" class="text-gray-500 hover:text-gray-700">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            </div>

            <form id="blockFacilityForm">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Facility</label>
                        <select id="facility_id" name="facility_id" required class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                            <option value="">Select Facility</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Date</label>
                        <input type="date" id="date" name="date" required
                               min="{{ date('Y-m-d') }}"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Start Time</label>
                            <input type="time" id="start_time" name="start_time" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">End Time</label>
                            <input type="time" id="end_time" name="end_time" required
                                   class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Purpose</label>
                        <input type="text" id="purpose" name="purpose" required placeholder="e.g., Special Program, Maintenance"
                               class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Notes (Optional)</label>
                        <textarea id="notes" name="notes" rows="3" placeholder="Additional details..."
                                  class="w-full border border-gray-300 rounded-lg px-3 py-2 focus:outline-none focus:ring-2 focus:ring-[#7B172E]"></textarea>
                    </div>
                </div>

                <div class="flex space-x-3 mt-6">
                    <button type="submit" class="flex-1 bg-[#7B172E] text-white py-3 px-4 rounded-lg hover:bg-[#5A1221] transition-colors font-semibold">
                        Block Time Slot
                    </button>
                    <button type="button" onclick="closeManageModal()" class="flex-1 bg-gray-300 text-gray-700 py-3 px-4 rounded-lg hover:bg-gray-400 transition-colors font-semibold">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
@endif

<script>
    // Global variables
    let allFacilities = [];
    let allReservationData = [];
    let filteredReservationData = [];

    // Initialize when page loads
    document.addEventListener('DOMContentLoaded', function() {
        initializeCalendar();
        fetchAllFacilities();
        
        // Delay to ensure DOM is fully rendered
        setTimeout(() => {
            collectAllReservationData();
        }, 100);
    });

    function initializeCalendar() {
        // Initialize filtering
        document.getElementById('applyFilters').addEventListener('click', applyFilters);
        document.getElementById('clearFilters').addEventListener('click', clearFilters);
        document.getElementById('searchFilter').addEventListener('input', applyFilters);
        
        // Initialize calendar day clicks
        document.querySelectorAll('.calendar-day').forEach(day => {
            day.addEventListener('click', function() {
                // Remove previous selection
                document.querySelectorAll('.calendar-day.selected').forEach(d => d.classList.remove('selected'));
                // Add selection to clicked day
                this.classList.add('selected');
            });
        });
    }

    function fetchAllFacilities() {
        fetch('/api/facilities')
            .then(response => response.json())
            .then(data => {
                allFacilities = data.facilities || [];
                populateFacilitySelects();
            })
            .catch(error => console.error('Error fetching facilities:', error));
    }

    function populateFacilitySelects() {
        const filterSelect = document.getElementById('facilityFilter');
        const modalSelect = document.getElementById('facility_id');
        
        if (filterSelect) {
            filterSelect.innerHTML = '<option value="">All Facilities</option>';
            allFacilities.forEach(facility => {
                const option = document.createElement('option');
                option.value = facility.facility_id;
                option.textContent = facility.facility_name;
                filterSelect.appendChild(option);
            });
        }
        
        if (modalSelect) {
            modalSelect.innerHTML = '<option value="">Select Facility</option>';
            allFacilities.forEach(facility => {
                const option = document.createElement('option');
                option.value = facility.facility_id;
                option.textContent = facility.facility_name;
                modalSelect.appendChild(option);
            });
        }
    }

    function collectAllReservationData() {
        allReservationData = [];
        const scheduleItems = document.querySelectorAll('#scheduleSection > div');
        
        scheduleItems.forEach((item, dayIndex) => {
            const eventCards = item.querySelectorAll('.event-card');
            eventCards.forEach(card => {
                const statusBadge = card.querySelector('.status-badge');
                const title = card.querySelector('h4')?.textContent || '';
                const facility = card.querySelector('.text-gray-700')?.textContent || '';
                const timeElement = card.querySelectorAll('.text-gray-700')[1];
                const time = timeElement ? timeElement.textContent : 'N/A';
                const status = statusBadge ? statusBadge.textContent.toLowerCase() : '';
                
                allReservationData.push({
                    element: card,
                    dayElement: item,
                    title: title,
                    facility: facility,
                    time: time,
                    status: status,
                    dayIndex: dayIndex,
                    dateKey: item.dataset.date
                });
            });
        });
        
        filteredReservationData = [...allReservationData];
        updateFilterStatus();
    }

    function applyFilters() {
        const facilityFilter = document.getElementById('facilityFilter').value;
        const typeFilter = document.getElementById('typeFilter').value;
        const timeFilter = document.getElementById('timeFilter').value;
        const searchFilter = document.getElementById('searchFilter').value.toLowerCase();

        filteredReservationData = allReservationData.filter(reservation => {
            // Facility filter
            if (facilityFilter && !reservation.facility.toLowerCase().includes(facilityFilter.toLowerCase())) {
                return false;
            }

            // Type filter
            if (typeFilter) {
                if (typeFilter === 'admin_block' && !reservation.status.includes('admin')) return false;
                if (typeFilter === 'accepted' && !reservation.status.includes('accepted')) return false;
                if (typeFilter === 'completed' && !reservation.status.includes('completed')) return false;
            }

            // Time filter
            if (timeFilter && reservation.time !== 'N/A') {
                const timeMatch = checkTimeRange(reservation.time, timeFilter);
                if (!timeMatch) return false;
            }

            // Search filter
            if (searchFilter && !reservation.title.toLowerCase().includes(searchFilter)) {
                return false;
            }

            return true;
        });

        renderFilteredResults();
        updateFilterStatus();
    }

    function checkTimeRange(timeText, range) {
        if (timeText === 'N/A') return false;
        
        // Extract start time from the time text (format: "HH:MM AM/PM - HH:MM AM/PM")
        const timeMatch = timeText.match(/(\d{1,2}:\d{2}\s*(AM|PM))/i);
        if (!timeMatch) return false;
        
        const startTime = timeMatch[1];
        const hour = parseInt(startTime.split(':')[0]);
        const isPM = startTime.includes('PM');
        const hour24 = isPM && hour !== 12 ? hour + 12 : (hour === 12 && !isPM ? 0 : hour);

        switch(range) {
            case 'morning':
                return hour24 >= 6 && hour24 < 12;
            case 'afternoon':
                return hour24 >= 12 && hour24 < 18;
            case 'evening':
                return hour24 >= 18 && hour24 <= 23;
            default:
                return true;
        }
    }

    function renderFilteredResults() {
        // Hide all day containers first
        const allDayContainers = document.querySelectorAll('#scheduleSection > div');
        allDayContainers.forEach(container => {
            container.style.display = 'none';
        });

        // Group filtered reservations by day
        const reservationsByDay = {};
        filteredReservationData.forEach(reservation => {
            if (!reservationsByDay[reservation.dayIndex]) {
                reservationsByDay[reservation.dayIndex] = [];
            }
            reservationsByDay[reservation.dayIndex].push(reservation);
        });

        // Show only days with filtered reservations
        Object.keys(reservationsByDay).forEach(dayIndex => {
            const dayContainer = allDayContainers[parseInt(dayIndex)];
            if (dayContainer) {
                dayContainer.style.display = 'block';
            }
        });

        // If no filters are active, show all days
        const hasActiveFilters = document.getElementById('facilityFilter').value ||
                                document.getElementById('typeFilter').value ||
                                document.getElementById('timeFilter').value ||
                                document.getElementById('searchFilter').value;

        if (!hasActiveFilters) {
            allDayContainers.forEach(container => {
                container.style.display = 'block';
            });
        }
    }

    function clearFilters() {
        document.getElementById('facilityFilter').value = '';
        document.getElementById('typeFilter').value = '';
        document.getElementById('timeFilter').value = '';
        document.getElementById('searchFilter').value = '';
        
        filteredReservationData = [...allReservationData];
        renderFilteredResults();
        updateFilterStatus();
    }

    function updateFilterStatus() {
        const statusElement = document.getElementById('filteredCount');
        if (statusElement) {
            if (filteredReservationData.length === allReservationData.length) {
                statusElement.textContent = 'Showing all events';
            } else {
                statusElement.textContent = `Showing ${filteredReservationData.length} of ${allReservationData.length} events`;
            }
        }
    }

    function scrollToDate(dateKey) {
        const targetElement = document.querySelector(`[data-date="${dateKey}"]`);
        if (targetElement) {
            targetElement.scrollIntoView({ 
                behavior: 'smooth', 
                block: 'start' 
            });
            
            // Add highlight effect
            targetElement.style.transition = 'all 0.3s ease';
            targetElement.style.transform = 'scale(1.02)';
            targetElement.style.boxShadow = '0 20px 40px rgba(0,0,0,0.1)';
            
            setTimeout(() => {
                targetElement.style.transform = 'scale(1)';
                targetElement.style.boxShadow = '';
            }, 1000);
        }
    }

    function toggleFilter() {
        const filterSection = document.getElementById('filterSection');
        const filterButton = document.getElementById('filterButtonText');
        const filterArrow = document.getElementById('filterArrow');
        
        if (filterSection.classList.contains('hidden')) {
            // Show filter
            filterSection.classList.remove('hidden');
            filterButton.textContent = 'Hide Filters';
            filterArrow.style.transform = 'rotate(180deg)';
        } else {
            // Hide filter
            filterSection.classList.add('hidden');
            filterButton.textContent = 'Show Filters';
            filterArrow.style.transform = 'rotate(0deg)';
        }
    }

    @if(isset($isAdmin) && $isAdmin)
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

    // Scroll to Top functionality
    window.addEventListener('scroll', function() {
        const scrollToTopBtn = document.getElementById('scrollToTopBtn');
        if (window.pageYOffset > 300) {
            scrollToTopBtn.style.display = 'block';
            scrollToTopBtn.style.opacity = '1';
        } else {
            scrollToTopBtn.style.opacity = '0';
            setTimeout(() => {
                if (window.pageYOffset <= 300) {
                    scrollToTopBtn.style.display = 'none';
                }
            }, 300);
        }
    });

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }
</script>

<!-- Scroll to Top Button -->
<button id="scrollToTopBtn" onclick="scrollToTop()" 
        class="fixed bottom-6 right-6 bg-[#7B172E] hover:bg-[#5a0f20] text-white p-3 rounded-full shadow-lg transition-all duration-300 z-50" 
        style="display: none; opacity: 0;">
    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"></path>
    </svg>
</button>

</body>
</html>

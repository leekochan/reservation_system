<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.navbar')

<div id="dashboard" class="flex items-center justify-center min-h-[calc(100vh)] bg-cover bg-center bg-no-repeat backdrop-blur-sm"
     style="background-image: url('{{ asset('pictures/cebuUserBackground.jpg') }}'); background-blend-mode: overlay;"
>

    <div class="text-center">
        <div class="mb-6 flex justify-center">
            <img
                src="{{ asset('pictures/uplogo-removebg-preview.png') }}"
                alt="UP Cebu Logo"
                class="h-[160px] bg-white rounded-full w-auto object-contain"
            >
        </div>
        <h1 class="text-5xl md:text-5xl font-bold leading-tight" style="color : #7B172E;
  text-shadow:
    0 0 10px rgba(255,255,255,0.8),  /* Blurred spread */
    0 0 10px rgba(255,255,255,0.5);">
            Online Reservation Form Use of Facilities<br>
            and Other Equipment
        </h1>
    </div>
</div>

<!-- Replace the facilities grid section with this -->
<section id="facilities" class="bg-white py-16">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-10">Facilities</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($facilities as $facility)
                <div class="bg-gray-100 rounded-lg shadow p-4">
                    <img src="{{ asset('storage/' . $facility->picture) }}" alt="{{ $facility->facility_name }}" class="w-full h-40 object-cover rounded">
                    <p class="mt-4 text-start font-medium">{{ $facility->facility_name }}</p>
                </div>
            @endforeach
        </div>

        <!-- View All Button -->
        <div class="mt-8 flex justify-end">
            <a href="user/facilities" class="inline-flex items-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-800 font-medium rounded shadow transition">
                View all
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 ml-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>
        </div>
    </div>
</section>

@php
    use Carbon\Carbon;
    $today = Carbon::now();
@endphp

<section id="calendar" class="bg-white py-16">
    <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-2 gap-6 mt-[120px]">
        <!-- Left Section -->
        <div class="space-y-6">
            <h1 class="text-3xl font-bold">Calendar of Activities</h1>

            <!-- Real Calendar -->
            @php
                $month = now()->month;
                $year = now()->year;
                $today = date('j');
                $firstDay = Carbon::createFromDate($year, $month, 1);
                $startDayOfWeek = $firstDay->dayOfWeek; // 0 = Sunday, 6 = Saturday
                $daysInMonth = $firstDay->daysInMonth;
            @endphp

            <div class="bg-white rounded-lg shadow p-4">
                <h2 class="text-pink-700 font-bold text-xl mb-4">
                    {{ strtoupper($firstDay->format('F')) }}
                    <span class="text-sm text-gray-500">{{ $year }}</span>
                </h2>

                <!-- Weekday Headers -->
                <div class="grid grid-cols-7 text-center gap-1 text-sm font-medium text-gray-700 mb-2">
                    <div>SUN</div><div>MON</div><div>TUE</div><div>WED</div><div>THU</div><div>FRI</div><div>SAT</div>
                </div>

                <!-- Calendar Grid -->
                <div class="grid grid-cols-7 text-center gap-1 text-sm font-medium text-gray-700">
                    {{-- Add empty cells before the 1st day --}}
                    @for ($i = 0; $i < $startDayOfWeek; $i++)
                        <div class="py-2"></div>
                    @endfor

                    {{-- Actual calendar days --}}
                    @for ($day = 1; $day <= $daysInMonth; $day++)
                        @php
                            $isToday = $day == now()->day && $month == now()->month && $year == now()->year;
                        @endphp
                        <div class="py-2 {{ $isToday ? 'bg-red-600 text-white rounded-full font-bold' : '' }}">
                            {{ $day }}
                        </div>
                    @endfor
                </div>
            </div>

            <!-- Reservation Prompt -->
            <div class="bg-white p-4 rounded-lg shadow flex justify-between items-center">
                <p class="text-gray-700">Do you want to request reservation for use of facilities?</p>
                <a href="user/reservation" class="bg-pink-800 text-white px-4 py-2 rounded shadow hover:bg-pink-700">Reserve Now!</a>
            </div>
        </div>

        <!-- Right Section -->
        <div class="space-y-6">
            <div class="flex justify-between items-center">
                <h2 class="text-xl font-bold">EVENTS AND RESERVATIONS</h2>
                <div class="relative">
                    <button id="toggle-filters" class="flex items-center space-x-1 bg-pink-800 text-white px-3 py-2 rounded-lg text-sm hover:bg-pink-700 transition-all duration-200 shadow-md">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path>
                        </svg>
                        <span>Filter</span>
                        <svg id="filter-arrow" class="w-4 h-4 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- Filter Panel -->
            <div id="filter-panel" class="bg-white border border-gray-200 rounded-lg shadow-lg p-4 hidden">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Facility Filter -->
                    <div>
                        <label for="facility-filter" class="block text-sm font-medium text-gray-700 mb-2">Facility</label>
                        <select id="facility-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            <option value="all">All Facilities</option>
                            @foreach($allFacilities ?? [] as $facility)
                                <option value="{{ $facility->facility_id }}" {{ ($facilityFilter ?? 'all') == $facility->facility_id ? 'selected' : '' }}>
                                    {{ $facility->facility_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Reservation Type Filter -->
                    <div>
                        <label for="type-filter" class="block text-sm font-medium text-gray-700 mb-2">Reservation Type</label>
                        <select id="type-filter" class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-pink-500 focus:border-transparent">
                            <option value="all">All Types</option>
                            <option value="single" {{ ($typeFilter ?? 'all') == 'single' ? 'selected' : '' }}>Single</option>
                            <option value="consecutive" {{ ($typeFilter ?? 'all') == 'consecutive' ? 'selected' : '' }}>Consecutive</option>
                            <option value="multiple" {{ ($typeFilter ?? 'all') == 'multiple' ? 'selected' : '' }}>Multiple</option>
                        </select>
                    </div>
                </div>

                <div class="mt-3 flex gap-2">
                    <button id="apply-filters" class="bg-pink-800 text-white px-4 py-2 rounded text-sm hover:bg-pink-700">
                        Apply Filters
                    </button>
                    <button id="clear-filters" class="bg-gray-300 text-gray-700 px-4 py-2 rounded text-sm hover:bg-gray-400">
                        Clear All
                    </button>
                </div>
            </div>

            <div class="bg-white p-4 rounded-lg shadow space-y-4" id="events-container">
                <div id="events-content">
                    @php
                        $today = now()->toDateString();

                        // Separate today's events from other events
                        $todayEvents = collect($events ?? [])->filter(fn($e) => $e['date'] === $today);
                        $otherEvents = collect($events ?? [])->filter(fn($e) => $e['date'] !== $today)->sortBy('date');
                    @endphp

                    <div class="space-y-4">

                        {{-- Today’s Event (always on top) --}}
                        @forelse ($todayEvents as $event)
                            @php
                                $eventDate = Carbon::parse($event['date']);
                                $dayNum = $eventDate->format('d');
                                $dayOfWeek = strtoupper($eventDate->format('D'));
                            @endphp
                            <div class="flex items-start space-x-4">
                                <div class="flex flex-col items-center">
                                    <span class="font-bold text-red-600">Today</span>
                                    <span class="text-xs text-gray-500">{{ $dayOfWeek }}</span>
                                    <div class="w-1 h-full bg-black mt-1"></div>
                                </div>
                                <div class="bg-red-100 border-l-4 border-red-600 p-3 rounded w-full">
                                    <p class="font-semibold text-red-800">{{ $event['title'] }}</p>
                                    <p class="text-sm text-red-600">{{ $event['venue'] }}</p>
                                    <p class="text-sm text-red-600">{{ $event['time'] }}</p>
                                </div>
                            </div>
                        @empty
                            {{-- Show "No events today" if there are no events for today --}}
                            <div class="flex items-start space-x-4">
                                <div class="flex flex-col items-center">
                                    <span class="font-bold text-gray-400">Today</span>
                                    <span class="text-xs text-gray-400">{{ strtoupper(now()->format('D')) }}</span>
                                    <div class="w-1 h-full bg-gray-300 mt-1"></div>
                                </div>
                                <div class="bg-gray-50 p-3 rounded w-full">
                                    <p class="text-gray-500 italic">No events scheduled for today</p>
                                </div>
                            </div>
                        @endforelse

                        {{-- Other Events (sorted by date) --}}
                        @foreach ($otherEvents as $event)
                            @php
                                $eventDate = Carbon::parse($event['date']);
                                $dayNum = $eventDate->format('d');
                                $dayOfWeek = strtoupper($eventDate->format('D'));

                                // Determine if this is tomorrow, this week, etc. for better UX
                                $isThisWeek = $eventDate->isSameWeek(now());
                                $isTomorrow = $eventDate->isNextDay();
                            @endphp
                            <div class="flex items-start space-x-4">
                                <div class="flex flex-col items-center">
                                <span class="font-bold {{ $isTomorrow ? 'text-orange-600' : ($isThisWeek ? 'text-blue-600' : '') }}">
                                    @if($isTomorrow)
                                        Tomorrow
                                    @else
                                        {{ $dayNum }}
                                    @endif
                                </span>
                                    <span class="text-xs text-gray-500">{{ $dayOfWeek }}</span>
                                    <div class="w-1 h-full bg-black mt-1"></div>
                                </div>
                                <div class="bg-gray-100 p-3 rounded w-full {{ $isTomorrow ? 'border-l-4 border-orange-400' : ($isThisWeek ? 'border-l-4 border-blue-400' : '') }}">
                                    <p class="font-semibold">{{ $event['title'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $event['venue'] }}</p>
                                    <p class="text-sm text-gray-600">{{ $event['time'] }}</p>
                                </div>
                            </div>
                        @endforeach

                        {{-- Show message if no events at all --}}
                        @if(empty($events) || count($events) === 0)
                            <div class="text-center py-8">
                                <p class="text-gray-500 text-lg">No upcoming events or reservations</p>
                                <p class="text-gray-400 text-sm mt-2">Events will appear here when facilities are reserved</p>
                            </div>
                        @endif

                    </div>
                </div>
            </div>

            <div class="flex justify-center">
                <a href="/calendar_of_activities" class="bg-pink-800 text-white px-6 py-2 rounded shadow hover:bg-pink-700">View Calendar</a>
            </div>
        </div>
    </div>
</section>



<footer class="bg-[#7B172E] text-white py-8">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid-cols-1 md:grid-cols-3 gap-8 flex items-center">
            <!-- Logo and Left Column - Addresses -->
            <div class="flex items-start space-x-4 md:col-span-2">
                <img
                    src="{{ asset('pictures/uplogo.jpg') }}"
                    alt="UP Cebu Logo"
                    class="h-[100px] object-contain rounded-full backdrop-blur-sm"
                >
                <div class="space-y-4">
                    <h3 class="text-xl font-bold">University of the Philippines Cebu</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <p class="flex items-start">
                                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>Lahug: Gorordo Avenue, Cebu City 6000</span>
                            </p>
                            <p class="flex items-start">
                                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <span>SRP: South Road Properties, Cebu City 6000</span>
                            </p>
                        </div>
                        <div class="space-y-2 ml-[100px]">
                            <p class="flex items-start">
                                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                                <span>pio.upcebu@edu.ph</span>
                            </p>
                            <p class="flex items-start">
                                <svg class="h-5 w-5 mr-2 mt-0.5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                                <span>(032) 232 8187</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column - Reserve Button -->
            <div class="flex justify-end flex-1">
                <a href="/reservation" class="inline-block bg-white text-[#7B172E] px-8 py-3 rounded-full font-bold hover:bg-gray-100 transition-colors duration-300 whitespace-nowrap">
                    RESERVE NOW →
                </a>
            </div>
        </div>
    </div>
</footer>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const toggleFiltersBtn = document.getElementById('toggle-filters');
        const filterPanel = document.getElementById('filter-panel');
        const applyFiltersBtn = document.getElementById('apply-filters');
        const clearFiltersBtn = document.getElementById('clear-filters');
        const facilityFilter = document.getElementById('facility-filter');
        const typeFilter = document.getElementById('type-filter');
        const eventsContainer = document.getElementById('events-content');

        // Toggle filter panel
        toggleFiltersBtn.addEventListener('click', function() {
            const arrow = document.getElementById('filter-arrow');

            if (filterPanel.classList.contains('hidden')) {
                filterPanel.classList.remove('hidden');
                arrow.style.transform = 'rotate(180deg)';
            } else {
                filterPanel.classList.add('hidden');
                arrow.style.transform = 'rotate(0deg)';
            }
        });

        // Apply filters
        applyFiltersBtn.addEventListener('click', function() {
            applyFilters();
        });

        // Clear filters
        clearFiltersBtn.addEventListener('click', function() {
            facilityFilter.value = 'all';
            typeFilter.value = 'all';
            applyFilters();
        });

        // Auto-apply filters when dropdowns change
        facilityFilter.addEventListener('change', applyFilters);
        typeFilter.addEventListener('change', applyFilters);

        function applyFilters() {
            const facilityValue = facilityFilter.value;
            const typeValue = typeFilter.value;

            // Show loading state
            eventsContainer.innerHTML = `
            <div class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-pink-800"></div>
                <p class="mt-2 text-gray-500">Filtering events...</p>
            </div>
        `;

            // Make AJAX request
            fetch(`{{ route('user.filter.events') }}?facility_filter=${facilityValue}&type_filter=${typeValue}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        renderEvents(data.events);
                    } else {
                        showError('Failed to load events');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showError('An error occurred while filtering events');
                });
        }

        function renderEvents(events) {
            const today = new Date().toISOString().split('T')[0];
            const todayEvents = events.filter(event => event.date === today);
            const otherEvents = events.filter(event => event.date !== today);

            let html = '<div class="space-y-4">';

            // Today's events
            if (todayEvents.length > 0) {
                todayEvents.forEach(event => {
                    const eventDate = new Date(event.date);
                    const dayOfWeek = eventDate.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase();

                    html += `
                    <div class="flex items-start space-x-4">
                        <div class="flex flex-col items-center">
                            <span class="font-bold text-red-600">Today</span>
                            <span class="text-xs text-gray-500">${dayOfWeek}</span>
                            <div class="w-1 h-full bg-black mt-1"></div>
                        </div>
                        <div class="bg-red-100 border-l-4 border-red-600 p-3 rounded w-full">
                            <p class="font-semibold text-red-800">${event.title}</p>
                            <p class="text-sm text-red-600">${event.venue}</p>
                            <p class="text-sm text-red-600">${event.time}</p>
                            <span class="inline-block mt-1 px-2 py-1 text-xs bg-red-200 text-red-800 rounded">${event.reservation_type || 'Unknown'}</span>
                        </div>
                    </div>
                `;
                });
            } else {
                const todayDayOfWeek = new Date().toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase();
                html += `
                <div class="flex items-start space-x-4">
                    <div class="flex flex-col items-center">
                        <span class="font-bold text-gray-400">Today</span>
                        <span class="text-xs text-gray-400">${todayDayOfWeek}</span>
                        <div class="w-1 h-full bg-gray-300 mt-1"></div>
                    </div>
                    <div class="bg-gray-50 p-3 rounded w-full">
                        <p class="text-gray-500 italic">No events scheduled for today</p>
                    </div>
                </div>
            `;
            }

            // Other events
            otherEvents.forEach(event => {
                const eventDate = new Date(event.date);
                const today = new Date();
                const tomorrow = new Date(today);
                tomorrow.setDate(today.getDate() + 1);

                const dayNum = eventDate.getDate();
                const dayOfWeek = eventDate.toLocaleDateString('en-US', { weekday: 'short' }).toUpperCase();

                const isTomorrow = eventDate.toDateString() === tomorrow.toDateString();
                const isThisWeek = isInSameWeek(eventDate, today);

                const dayDisplayClass = isTomorrow ? 'text-orange-600' : (isThisWeek ? 'text-blue-600' : '');
                const dayDisplay = isTomorrow ? 'Tomorrow' : dayNum;
                const borderClass = isTomorrow ? 'border-l-4 border-orange-400' : (isThisWeek ? 'border-l-4 border-blue-400' : '');

                html += `
                <div class="flex items-start space-x-4">
                    <div class="flex flex-col items-center">
                        <span class="font-bold ${dayDisplayClass}">${dayDisplay}</span>
                        <span class="text-xs text-gray-500">${dayOfWeek}</span>
                        <div class="w-1 h-full bg-black mt-1"></div>
                    </div>
                    <div class="bg-gray-100 p-3 rounded w-full ${borderClass}">
                        <p class="font-semibold">${event.title}</p>
                        <p class="text-sm text-gray-600">${event.venue}</p>
                        <p class="text-sm text-gray-600">${event.time}</p>
                        <span class="inline-block mt-1 px-2 py-1 text-xs bg-gray-200 text-gray-800 rounded">${event.reservation_type || 'Unknown'}</span>
                    </div>
                </div>
            `;
            });

            // Show no events message if none found
            if (events.length === 0) {
                html += `
                <div class="text-center py-8">
                    <p class="text-gray-500 text-lg">No events found matching your filters</p>
                    <p class="text-gray-400 text-sm mt-2">Try adjusting your filter criteria</p>
                </div>
            `;
            }

            html += '</div>';
            eventsContainer.innerHTML = html;
        }

        function isInSameWeek(date1, date2) {
            const startOfWeek = (date) => {
                const result = new Date(date);
                const day = result.getDay();
                const diff = result.getDate() - day;
                return new Date(result.setDate(diff));
            };

            return startOfWeek(date1).getTime() === startOfWeek(date2).getTime();
        }

        function showError(message) {
            eventsContainer.innerHTML = `
            <div class="text-center py-8">
                <p class="text-red-500 text-lg">${message}</p>
                <button onclick="location.reload()" class="mt-2 text-sm text-blue-600 hover:text-blue-800">Refresh page</button>
            </div>
        `;
        }
    });
</script>

</body>
</html>

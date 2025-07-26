<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <style>
        .status-badge {
            @apply inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium;
        }
        .status-pending {
            @apply bg-yellow-100 text-yellow-800;
        }
        .status-accepted {
            @apply bg-green-100 text-green-800;
        }
        .status-declined {
            @apply bg-red-100 text-red-800;
        }
        .status-completed {
            @apply bg-blue-100 text-blue-800;
        }
    </style>
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<div class="bg-white p-10 px-16 mt-16 pb-0">
    <h1 class="font-bold text-3xl mb-4">RESERVATION</h1>
    @if($reservations->isEmpty())
        <p class="text-xl text-gray-500 ml-4 mt-4">No accepted reservations found.</p>
    @else
        <div class="grid w-full grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($reservations->take(3) as $reservation)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $reservation->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $reservation->organization }}</p>
                            </div>
                            <span class="status-badge bg-green-100 text-green-800 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">accepted</span>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span class="font-medium mr-2">Facility:</span>{{ $reservation->facility->facility_name }}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium mr-2">Schedule:</span>
                                @if($reservation->reservationDetail)
                                    @if($reservation->reservation_type === 'Single')
                                        {{ $reservation->reservationDetail->start_date }} ({{ $reservation->reservationDetail->time_from }} - {{ $reservation->reservationDetail->time_to }})
                                    @elseif($reservation->reservation_type === 'Consecutive')
                                        {{ $reservation->reservationDetail->start_date }} to {{ $reservation->reservationDetail->end_date ?? 'N/A' }}
                                    @elseif($reservation->reservation_type === 'Multiple')
                                        {{ $reservation->reservationDetail->start_date }} to {{ $reservation->reservationDetail->end_date ?? 'N/A' }}
                                    @endif
                                @else
                                    Schedule not available
                                @endif
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium mr-2">Equipment:</span>
                                @if($reservation->equipments && $reservation->equipments->count() > 0)
                                    @php
                                        $firstEquipment = $reservation->equipments->first();
                                        $quantity = $firstEquipment->pivot->quantity > 1 ? " ({$firstEquipment->pivot->quantity})" : '';
                                        $equipmentCount = $reservation->equipments->count();
                                    @endphp
                                    {{ $firstEquipment->equipment_name }}{{ $quantity }}
                                    @if($equipmentCount > 1)
                                        (+{{ $equipmentCount - 1 }} more)
                                    @endif
                                @else
                                    No equipment
                                @endif
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v1a1 1 0 01-1 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
                                </svg>
                                <span class="font-medium mr-2">Type:</span>{{ $reservation->reservation_type }}
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">
                                {{ $reservation->created_at->format('M d, Y') }}
                            </span>
                            <button onclick="openModal({{ $reservation->reservation_id }})" 
                                    class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                View Details
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6 text-right">
        <button class="w-auto py-2 px-4 bg-white shadow-xl hover:bg-gray-200 text-black rounded-md w-1/4">
            <a href="/admin/reservation">View all</a>
        </button>
    </div>
</div>


<div class="bg-white p-10 px-16 mt-16 pb-0">
    <h1 class="font-bold text-3xl mb-2 ml-4">PENDING</h1>
    @if($pendingRequests->isEmpty())
        <p class="text-xl text-gray-500 ml-4 mt-4">No pending reservations found.</p>
    @else
        <div class="grid w-full grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($pendingRequests->sortBy(['transaction_date'])->take(3) as $request)
                <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
                    <div class="p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h3 class="text-lg font-semibold text-gray-900">{{ $request->name }}</h3>
                                <p class="text-sm text-gray-600">{{ $request->organization }}</p>
                            </div>
                            <span class="status-badge bg-yellow-100 text-yellow-800 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium">pending</span>
                        </div>
                        
                        <div class="space-y-2 mb-4">
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                                </svg>
                                <span class="font-medium mr-2">Facility:</span>{{ $request->facility->facility_name }}
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                </svg>
                                <span class="font-medium mr-2">Schedule:</span>
                                @if($request->reservationDetail)
                                    @if($request->reservation_type === 'Single')
                                        {{ $request->reservationDetail->start_date }} ({{ $request->reservationDetail->time_from }} - {{ $request->reservationDetail->time_to }})
                                    @elseif($request->reservation_type === 'Consecutive')
                                        {{ $request->reservationDetail->start_date }} to {{ $request->reservationDetail->end_date ?? 'N/A' }}
                                    @elseif($request->reservation_type === 'Multiple')
                                        {{ $request->reservationDetail->start_date }} to {{ $request->reservationDetail->end_date ?? 'N/A' }}
                                    @endif
                                @else
                                    Schedule not available
                                @endif
                            </div>                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium mr-2">Equipment:</span>
                                @if($request->equipments && $request->equipments->count() > 0)
                                    @php
                                        $firstEquipment = $request->equipments->first();
                                        $quantity = $firstEquipment->pivot->quantity > 1 ? " ({$firstEquipment->pivot->quantity})" : '';
                                        $equipmentCount = $request->equipments->count();
                                    @endphp
                                    {{ $firstEquipment->equipment_name }}{{ $quantity }}
                                    @if($equipmentCount > 1)
                                        (+{{ $equipmentCount - 1 }} more)
                                    @endif
                                @else
                                    No equipment
                                @endif
                            </div>
                            
                            <div class="flex items-center text-sm text-gray-600">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v1a1 1 0 01-1 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
                                </svg>
                                <span class="font-medium mr-2">Type:</span>{{ $request->reservation_type }}
                            </div>
                        </div>
                        
                        <div class="flex justify-between items-center">
                            <span class="text-xs text-gray-500">
                                {{ $request->created_at->format('M d, Y') }}
                            </span>
                            <div class="flex gap-2">
                                <button onclick="openModal({{ $request->reservation_id }})" 
                                        class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                                    View Details
                                </button>
                                <button onclick="acceptReservation({{ $request->reservation_id }})" 
                                        class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition-colors">
                                    Accept
                                </button>
                                <button onclick="declineReservation({{ $request->reservation_id }})" 
                                        class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors">
                                    Decline
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6 text-right">
        <button class="w-auto py-2 px-4  bg-white shadow-xl hover:bg-gray-200 text-black rounded-md w-1/4">
            <a href="/admin/reservation">View all</a>
        </button>
    </div>
</div>

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
            <div class="mt-6 text-right">
                <button class="w-auto py-2 px-4 bg-white shadow-xl hover:bg-gray-200 text-black rounded-md w-1/4">
                    <a href="/admin/facilities">View all</a>
                </button>
            </div>
        </div>
    </div>
</section>

<section id="extra-equipments" class="bg-white py-16 min-h-screen">
    <div class="max-w-6xl mx-auto px-4">
        <h2 class="text-3xl font-bold mb-10">Extra Equipments</h2>

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="grid grid-cols-2 bg-gray-200 px-6 py-4 border-b border-gray-300">
                <div class="font-bold text-gray-800">Equipments</div>
                <div class="font-bold text-gray-800">Available Unit</div>
            </div>

            <div class="divide-y divide-gray-200">
                @foreach($equipments as $equipment)
                    <div class="grid grid-cols-2 px-6 py-3 hover:bg-gray-50">
                        <div class="text-gray-800">{{ $equipment->equipment_name }}</div>
                        <div class="text-gray-700">
                            @if($equipment->units === null)
                                Unlimited Units
                            @elseif($equipment->units == 0)
                                No units available
                            @else
                                {{ $equipment->units }} units
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>

        </div>
        <div class="flex justify-end mt-6">
            <button class="w-auto py-2 px-4 bg-white shadow-xl hover:bg-gray-200 text-black rounded-md w-1/4">
                    <a href="/admin/facilities">Manage equipment</a>
            </button>
        </div>
    </div>
</section>

<!-- Modal for detailed view -->
<div id="reservationModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
    <div class="relative top-20 mx-auto p-12 border w-11/12 md:w-3/4 lg:w-1/2 shadow-lg rounded-md bg-white">
        <!-- Modal Header -->
        <div class="flex justify-between items-center pb-3 border-b">
            <h3 class="text-lg font-bold text-gray-900">Reservation Details</h3>
            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
        
        <!-- Modal Content -->
        <div id="modalContent" class="mt-4">
            <!-- Content will be loaded here -->
        </div>
        
        <!-- Modal Footer -->
        <div class="flex justify-end pt-4 border-t mt-6">
            <button onclick="closeModal()" class="px-4 py-2 bg-gray-500 text-white rounded-md hover:bg-gray-600 mr-2">
                Close
            </button>
            <button id="acceptBtn" onclick="handleReservationAction('accept')" class="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                Accept
            </button>
            <button id="declineBtn" onclick="handleReservationAction('decline')" class="px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700 ml-2">
                Decline
            </button>
        </div>
    </div>
</div>

<script>
@verbatim
let currentReservationId = null;

function openModal(reservationId) {
    console.log('Opening modal for reservation:', reservationId);
    currentReservationId = reservationId;
    
    // Fetch reservation details using the same endpoint as reservations page
    fetch(`/admin/dashboard/reservation/${reservationId}/details`)
        .then(response => {
            console.log('Response received:', response);
            return response.json();
        })
        .then(data => {
            console.log('Data received:', data);
            if (data.error) {
                alert('Error loading reservation details');
                return;
            }
            
            showReservationModal(data);
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error loading reservation details');
        });
}

function showReservationModal(reservation) {
    // Show modal
    document.getElementById('reservationModal').classList.remove('hidden');
    
    // Update button states based on current status
    updateButtonStates(reservation.status);
    
    // Create equipment list with enhanced display
    let equipmentInfo = 'No equipment requested';
    if (reservation.equipments && reservation.equipments.length > 0) {
        equipmentInfo = reservation.equipments.map(eq => {
            const quantity = eq.quantity || 1;
            return `${eq.equipment_name} (Qty: ${quantity})`;
        }).join(', ');
    }
    
    // Create schedule information with enhanced design
    let scheduleInfo = '';
    if (reservation.reservation_detail) {
        const detail = reservation.reservation_detail;
        
        // Create equipment info for display under dates
        let equipmentForDates = '';
        if (reservation.equipments && reservation.equipments.length > 0) {
            equipmentForDates = reservation.equipments.map(eq => {
                const quantity = eq.quantity || eq.pivot?.quantity || 1;
                return `${eq.equipment_name} (Qty: ${quantity})`;
            }).join(', ');
        } else {
            equipmentForDates = 'No equipment';
        }
        
        if (reservation.reservation_type === 'Single') {
            scheduleInfo = `
                <div class="text-sm">
                    <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Scheduled Date
                    </h5>
                    <div class="bg-blue-50 p-4 rounded-lg border-l-4 border-blue-400 shadow-sm">
                        <div class="flex items-center justify-between mb-2">
                            <div class="flex items-center">
                                <svg class="w-4 h-4 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                </svg>
                                <span class="font-medium text-blue-600 text-base">${detail.start_date}</span>
                            </div>
                            <span class="text-xs bg-blue-100 text-blue-700 px-2 py-1 rounded">SINGLE</span>
                        </div>
                        <div class="flex items-center text-gray-600 mb-2">
                            <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                            <span class="text-sm">${detail.time_from} - ${detail.time_to}</span>
                        </div>
                        <div class="flex items-start text-gray-600 mt-3 pt-2 border-t border-blue-200">
                            <svg class="w-4 h-4 mr-2 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                            </svg>
                            <div>
                                <span class="text-sm font-medium">Equipment:</span>
                                <span class="text-sm ml-2">${equipmentForDates}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        } else if (reservation.reservation_type === 'Consecutive') {
            scheduleInfo = `
                <div class="text-sm">
                    <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Scheduled Date
                    </h5>
                    <div class="bg-green-50 p-4 rounded-lg border shadow-sm mb-3">
                        <div class="flex items-center mb-2">
                            <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-green-600">Consecutive Days Reservation</span>
                        </div>
                        <div class="space-y-3">
                            <div class="bg-green-50 p-4 rounded-lg border-l-4 border-green-400 shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-medium text-green-600">Start Date: ${detail.start_date}</span>
                                    </div>
                                    <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">START</span>
                                </div>
                                <div class="flex items-center text-gray-600 mb-2">
                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">${detail.start_time_from} - ${detail.start_time_to}</span>
                                </div>
                                <div class="flex items-start text-gray-600 mt-3 pt-2 border-t border-green-200">
                                    <svg class="w-4 h-4 mr-2 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <span class="text-sm font-medium">Equipment:</span>
                                        <span class="text-sm ml-2">${equipmentForDates}</span>
                                    </div>
                                </div>
                            </div>
                            ${detail.end_date ? `
                            <div class="bg-red-50 p-4 rounded-lg border-l-4 border-red-400 shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-medium text-red-600">End Date: ${detail.end_date}</span>
                                    </div>
                                    <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">END</span>
                                </div>
                                <div class="flex items-center text-gray-600 mb-2">
                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">${detail.end_time_from || detail.start_time_from} - ${detail.end_time_to || detail.start_time_to}</span>
                                </div>
                                <div class="flex items-start text-gray-600 mt-3 pt-2 border-t border-red-200">
                                    <svg class="w-4 h-4 mr-2 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <span class="text-sm font-medium">Equipment:</span>
                                        <span class="text-sm ml-2">${equipmentForDates}</span>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        } else if (reservation.reservation_type === 'Multiple') {
            scheduleInfo = `
                <div class="text-sm">
                    <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                        <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                        </svg>
                        Scheduled Date
                    </h5>
                    <div class="bg-purple-50 p-4 rounded-lg border shadow-sm mb-3">
                        <div class="flex items-center mb-2">
                            <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                            </svg>
                            <span class="text-sm font-medium text-purple-600">Multiple Days Reservation</span>
                        </div>
                        <div class="space-y-3">
                            <div class="bg-purple-50 p-4 rounded-lg border-l-4 border-purple-400 shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-medium text-purple-600">First Date: ${detail.start_date}</span>
                                    </div>
                                    <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">FIRST</span>
                                </div>
                                <div class="flex items-center text-gray-600 mb-2">
                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">${detail.start_time_from} - ${detail.start_time_to}</span>
                                </div>
                                <div class="flex items-start text-gray-600 mt-3 pt-2 border-t border-purple-200">
                                    <svg class="w-4 h-4 mr-2 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <span class="text-sm font-medium">Equipment:</span>
                                        <span class="text-sm ml-2">${equipmentForDates}</span>
                                    </div>
                                </div>
                            </div>
                            ${detail.end_date ? `
                            <div class="bg-indigo-50 p-4 rounded-lg border-l-4 border-indigo-400 shadow-sm">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="flex items-center">
                                        <svg class="w-4 h-4 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        <span class="font-medium text-indigo-600">Second Date: ${detail.end_date}</span>
                                    </div>
                                    <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">SECOND</span>
                                </div>
                                <div class="flex items-center text-gray-600 mb-2">
                                    <svg class="w-4 h-4 mr-2 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                                    </svg>
                                    <span class="text-sm">${detail.end_time_from || detail.start_time_from} - ${detail.end_time_to || detail.start_time_to}</span>
                                </div>
                                <div class="flex items-start text-gray-600 mt-3 pt-2 border-t border-indigo-200">
                                    <svg class="w-4 h-4 mr-2 text-gray-500 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                    </svg>
                                    <div>
                                        <span class="text-sm font-medium">Equipment:</span>
                                        <span class="text-sm ml-2">${equipmentForDates}</span>
                                    </div>
                                </div>
                            </div>
                            ` : ''}
                        </div>
                    </div>
                </div>
            `;
        }
    }
    
    // Helper function for status color classes
    function getStatusColorClasses(status) {
        switch(status.toLowerCase()) {
            case 'pending': return 'bg-yellow-100 text-yellow-800 border border-yellow-300';
            case 'accepted': return 'bg-green-100 text-green-800 border border-green-300';
            case 'declined': return 'bg-red-100 text-red-800 border border-red-300';
            case 'completed': return 'bg-blue-100 text-blue-800 border border-blue-300';
            case 'cancelled': return 'bg-gray-100 text-gray-800 border border-gray-300';
            default: return 'bg-gray-100 text-gray-800 border border-gray-300';
        }
    }
    
    // Helper function for time ago
    function getTimeAgo(dateString) {
        const date = new Date(dateString);
        const now = new Date();
        const diffTime = Math.abs(now - date);
        const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24));
        const diffHours = Math.floor(diffTime / (1000 * 60 * 60));
        const diffMinutes = Math.floor(diffTime / (1000 * 60));

        if (diffDays > 0) return `${diffDays} day${diffDays > 1 ? 's' : ''} ago`;
        if (diffHours > 0) return `${diffHours} hour${diffHours > 1 ? 's' : ''} ago`;
        if (diffMinutes > 0) return `${diffMinutes} minute${diffMinutes > 1 ? 's' : ''} ago`;
        return 'Just now';
    }
    
    // Populate modal content with enhanced design
    document.getElementById('modalContent').innerHTML = `
        <!-- Status and Transaction Info Header -->
        <div class="mb-6 p-4 bg-gradient-to-r from-gray-50 to-gray-100 rounded-lg border">
            <div class="flex justify-between items-center mb-3">
                <div class="flex items-center space-x-4">
                    <span class="px-4 py-2 text-sm font-bold rounded-md shadow-sm ${getStatusColorClasses(reservation.status)}">${reservation.status.toUpperCase()}</span>
                    <span class="text-sm text-gray-600 font-medium">📅 Transaction Date: ${reservation.transaction_date || new Date(reservation.created_at).toLocaleDateString()}</span>
                </div>
                <div class="text-right">
                    <p class="text-sm text-gray-500">⏰ ${getTimeAgo(reservation.created_at)}</p>
                </div>
            </div>
            <!-- Price in separate row -->
            <div class="pt-3 border-t border-gray-200">
                <p class="text-lg font-bold text-green-700">💰 Total Payment: ₱${parseFloat(reservation.total_payment).toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
            <!-- Personal Information Section -->
            <div class="bg-white p-5 rounded-lg border shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                    </svg>
                    Personal Information
                </h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Name:</span>
                        <span class="text-sm font-semibold text-gray-900">${reservation.name}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Email:</span>
                        <span class="text-sm text-blue-600 hover:underline">${reservation.email}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Organization:</span>
                        <span class="text-sm font-semibold text-gray-900">${reservation.organization}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Submitted:</span>
                        <span class="text-sm text-gray-700">${new Date(reservation.created_at).toLocaleString()}</span>
                    </div>
                </div>
            </div>
            
            <!-- Reservation Details Section -->
            <div class="bg-white p-5 rounded-lg border shadow-sm">
                <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                    <svg class="w-5 h-5 mr-2 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Reservation Details
                </h3>
                <div class="space-y-3">
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Type:</span>
                        <span class="text-sm font-semibold text-purple-700 bg-purple-100 px-2 py-1 rounded">${reservation.reservation_type}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Facility:</span>
                        <span class="text-sm font-semibold text-gray-900">${reservation.facility.facility_name}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Equipment:</span>
                        <span class="text-sm font-semibold text-gray-900">${equipmentInfo}</span>
                    </div>
                    <div class="flex items-start">
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Personal Equip:</span>
                        <span class="text-sm text-gray-900">${reservation.electric_equipment || 'None'}</span>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Purpose Section -->
        <div class="mb-6 bg-white p-5 rounded-lg border shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <svg class="w-5 h-5 mr-2 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                Purpose of Reservation
            </h3>
            <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded-md leading-relaxed">${reservation.purpose}</p>
        </div>

        ${reservation.instruction ? `
        <div class="mb-6 bg-white p-5 rounded-lg border shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <svg class="w-5 h-5 mr-2 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 15.5c-.77.833.192 2.5 1.732 2.5z"></path>
                </svg>
                Special Instructions
            </h3>
            <p class="text-sm text-gray-700 bg-gray-50 p-4 rounded-md leading-relaxed border-l-4 border-red-400">${reservation.instruction}</p>
        </div>
        ` : ''}

        <!-- Enhanced Schedule Details Section -->
        <div class="mb-6 bg-white p-5 rounded-lg border shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Schedule Details
            </h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                ${scheduleInfo}
            </div>
        </div> 

        ${reservation.signature ? `
        <div class="mb-6 bg-white p-5 rounded-lg border shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <svg class="w-5 h-5 mr-2 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"></path>
                </svg>
                Digital Signature
            </h3>
            <div class="bg-gray-50 p-4 rounded-lg">
                <img src="${reservation.signature}" alt="Digital Signature" class="max-w-xs border rounded shadow-sm">
            </div>
        </div>
        ` : ''}
    `;
}

function getEquipmentDetails(reservation) {
    const hasEquipments = reservation.equipments && reservation.equipments.length > 0;
    const hasPersonalEquipment = reservation.electric_equipment && reservation.electric_equipment.trim() !== '' && reservation.electric_equipment.toLowerCase() !== 'none';
    
    if (!hasEquipments && !hasPersonalEquipment) {
        return `
            <div class="text-center py-8">
                <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-4.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"></path>
                </svg>
                <p class="text-gray-500 font-medium">No Equipment Requested</p>
                <p class="text-sm text-gray-400 mt-1">This reservation does not include any additional equipment</p>
            </div>
        `;
    }

    let equipmentHTML = '';
    let totalItems = 0;
    let totalQuantity = 0;

    // Facility Equipment Section
    if (hasEquipments) {
        totalItems += reservation.equipments.length;
        totalQuantity += reservation.equipments.reduce((total, eq) => total + (eq.quantity || eq.pivot?.quantity || 1), 0);
        
        equipmentHTML += `
            <div class="mb-6">
                <h4 class="text-md font-semibold text-orange-700 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                    </svg>
                    Facility Equipment
                </h4>
                <div class="space-y-3">
        `;

        equipmentHTML += reservation.equipments.map((equipment, index) => {
            const quantity = equipment.quantity || equipment.pivot?.quantity || 1;
            const bgColor = index % 2 === 0 ? 'bg-white' : 'bg-orange-25';
            
            return `
                <div class="${bgColor} p-4 rounded-lg border border-orange-200 shadow-sm">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center">
                            <div class="w-10 h-10 bg-orange-500 rounded-full flex items-center justify-center mr-3">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                                </svg>
                            </div>
                            <div>
                                <h5 class="font-semibold text-gray-900 text-base">${equipment.equipment_name}</h5>
                                <p class="text-sm text-gray-600">Facility Equipment</p>
                            </div>
                        </div>
                        <div class="text-right">
                            <div class="flex items-center bg-orange-100 px-3 py-1 rounded-full">
                                <svg class="w-4 h-4 mr-1 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 20l4-16m2 16l4-16M6 9h14M4 15h14"></path>
                                </svg>
                                <span class="font-bold text-orange-700">${quantity}</span>
                                <span class="text-sm text-orange-600 ml-1">${quantity > 1 ? 'units' : 'unit'}</span>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        equipmentHTML += `</div></div>`;
    }

    // Personal Equipment Section
    if (hasPersonalEquipment) {
        totalItems += 1;
        equipmentHTML += `
            <div class="mb-4">
                <h4 class="text-md font-semibold text-blue-700 mb-3 flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                    Personal Equipment
                </h4>
                <div class="bg-white p-4 rounded-lg border border-blue-200 shadow-sm">
                    <div class="flex items-center">
                        <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                            </svg>
                        </div>
                        <div>
                            <h5 class="font-semibold text-gray-900 text-base">${reservation.electric_equipment}</h5>
                            <p class="text-sm text-gray-600">Personal/Electric Equipment</p>
                        </div>
                    </div>
                </div>
            </div>
        `;
    }

    return `
        <div class="space-y-3">
            <div class="flex items-center justify-between mb-4 bg-gradient-to-r from-orange-100 to-yellow-100 p-3 rounded-lg border border-orange-200">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path>
                    </svg>
                    <span class="font-medium text-orange-700">Total Equipment Types: ${totalItems}</span>
                </div>
                ${hasEquipments ? `<div class="text-sm text-gray-600">
                    Facility Equipment Quantity: ${totalQuantity} units
                </div>` : ''}
            </div>
            ${equipmentHTML}
        </div>
    `;
}

function getStatusColor(status) {
    switch(status.toLowerCase()) {
        case 'pending': return 'bg-yellow-100 text-yellow-800';
        case 'accepted': return 'bg-green-100 text-green-800';
        case 'declined': return 'bg-red-100 text-red-800';
        case 'completed': return 'bg-blue-100 text-blue-800';
        case 'cancelled': return 'bg-gray-100 text-gray-800';
        default: return 'bg-gray-100 text-gray-800';
    }
}

function updateButtonStates(status) {
    const acceptBtn = document.getElementById('acceptBtn');
    const declineBtn = document.getElementById('declineBtn');
    
    if (status === 'accepted') {
        // Hide both buttons for accepted reservations
        acceptBtn.style.display = 'none';
        declineBtn.style.display = 'none';
    } else if (status === 'declined') {
        declineBtn.disabled = true;
        declineBtn.classList.add('opacity-50', 'cursor-not-allowed');
        declineBtn.textContent = 'Already Declined';
        acceptBtn.disabled = false;
        acceptBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        acceptBtn.textContent = 'Accept';
        // Make sure buttons are visible for declined status
        acceptBtn.style.display = 'inline-block';
        declineBtn.style.display = 'inline-block';
    } else {
        // Both buttons enabled for pending status
        acceptBtn.disabled = false;
        acceptBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        acceptBtn.textContent = 'Accept';
        declineBtn.disabled = false;
        declineBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        declineBtn.textContent = 'Decline';
        // Make sure buttons are visible for pending status
        acceptBtn.style.display = 'inline-block';
        declineBtn.style.display = 'inline-block';
    }
}

function handleReservationAction(action) {
    if (!currentReservationId) return;
    
    const actionText = action === 'accept' ? 'accept' : 'decline';
    const confirmText = action === 'accept' ? 'accepting' : 'declining';
    
    if (!confirm(`Are you sure you want to ${actionText} this reservation?`)) {
        return;
    }
    
    // Disable buttons during request
    const acceptBtn = document.getElementById('acceptBtn');
    const declineBtn = document.getElementById('declineBtn');
    acceptBtn.disabled = true;
    declineBtn.disabled = true;
    acceptBtn.textContent = action === 'accept' ? 'Processing...' : acceptBtn.textContent;
    declineBtn.textContent = action === 'decline' ? 'Processing...' : declineBtn.textContent;
    
    fetch(`/admin/dashboard/reservation/${currentReservationId}/${action}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Show success message
            showNotification(`Reservation ${action}ed successfully!`, 'success');
            
            // Update button states
            updateButtonStates(data.status);
            
            // Update the status badge in the modal
            const statusBadge = document.querySelector('.px-3.py-1.text-xs.font-semibold.rounded-full');
            if (statusBadge) {
                statusBadge.className = `px-3 py-1 text-xs font-semibold rounded-full ${getStatusColor(data.status)}`;
                statusBadge.textContent = data.status.toUpperCase();
            }
            
            // Optionally refresh the page after a delay
            setTimeout(() => {
                location.reload();
            }, 2000);
        } else {
            showNotification(data.message || `Error ${confirmText} reservation`, 'error');
            // Re-enable buttons on error
            acceptBtn.disabled = false;
            declineBtn.disabled = false;
            acceptBtn.textContent = 'Accept';
            declineBtn.textContent = 'Decline';
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification(`Error ${confirmText} reservation`, 'error');
        // Re-enable buttons on error
        acceptBtn.disabled = false;
        declineBtn.disabled = false;
        acceptBtn.textContent = 'Accept';
        declineBtn.textContent = 'Decline';
    });
}

function showNotification(message, type) {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `fixed top-4 right-4 px-6 py-3 rounded-lg shadow-lg z-50 ${
        type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white'
    }`;
    notification.textContent = message;
    
    // Add to page
    document.body.appendChild(notification);
    
    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

function closeModal() {
    currentReservationId = null;
    document.getElementById('reservationModal').classList.add('hidden');
}

function acceptReservation(reservationId) {
    if (!confirm('Are you sure you want to accept this reservation?')) return;
    
    fetch(`/admin/dashboard/reservation/${reservationId}/accept`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Reservation accepted successfully!');
            location.reload();
        } else {
            alert('Error accepting reservation: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error accepting reservation');
    });
}

function declineReservation(reservationId) {
    if (!confirm('Are you sure you want to decline this reservation?')) return;
    
    fetch(`/admin/dashboard/reservation/${reservationId}/decline`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Reservation declined successfully!');
            location.reload();
        } else {
            alert('Error declining reservation: ' + data.message);
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Error declining reservation');
    });
}

// Close modal when clicking outside
document.getElementById('reservationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
@endverbatim
</script>

</body>
</html>

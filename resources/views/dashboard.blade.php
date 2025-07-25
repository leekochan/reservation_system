<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
</head>

<body class="bg-gray-50 m-0 p-0">
@include('partials.admin-navbar')

<div class="p-10 mt-16">
    <h1 class="font-bold text-3xl mb-2 ml-4">RESERVATION</h1>
    @if($reservations->isEmpty())
        <p class="text-xl text-gray-500 ml-4 mt-4">No accepted reservations found.</p>
    @else
        <div class="grid w-full grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($reservations as $reservation)
                <div class="block p-4 h-full">
                    <div
                        class="flex flex-col bg-white rounded-lg shadow-xl cursor-pointer group-hover:shadow-xl transition-shadow p-6 h-full relative">
                        <!-- Basic Information Only -->
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Name: <span class="font-semibold text-black">{{ $reservation->name }}</span>
                        </p>
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Organization: <span class="font-semibold text-black">{{ $reservation->organization }}</span>
                        </p>
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Reservation Type: <span class="font-semibold text-black">{{ $reservation->reservation_type }}</span>
                        </p>
                        
                        <!-- Facility and Equipment -->
                        <div class="mb-2 mt-2 border-t border-solid border-gray-300">
                            <p class="text-base text-gray-600 flex-grow mt-2">
                                Facility: <span class="font-semibold text-black">{{ $reservation->facility->facility_name }}</span>
                            </p>
                            <p class="text-base text-gray-600 flex-grow mt-2">
                                Equipment: <span class="font-semibold text-black">{{ $reservation->equipment->equipment_name ?? 'No equipment' }}</span>
                            </p>
                        </div>

                        <!-- Purpose -->
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Purpose: <span class="font-semibold text-black">{{ Str::limit($reservation->purpose, 50) }}</span>
                        </p>

                        <!-- Date and Time Information -->
                        @if($reservation->reservationDetail)
                            <div class="border-t border-solid border-gray-300 mt-2">
                                @if($reservation->reservation_type === 'Single')
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Date: <span class="font-semibold text-black">{{ $reservation->reservationDetail->start_date }}</span>
                                    </p>
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Time: <span class="font-semibold text-black">{{ $reservation->reservationDetail->time_from }} - {{ $reservation->reservationDetail->time_to }}</span>
                                    </p>
                                @elseif($reservation->reservation_type === 'Consecutive')
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Start Date: <span class="font-semibold text-black">{{ $reservation->reservationDetail->start_date }}</span>
                                    </p>
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Time: <span class="font-semibold text-black">{{ $reservation->reservationDetail->start_time_from }} - {{ $reservation->reservationDetail->start_time_to }}</span>
                                    </p>
                                @elseif($reservation->reservation_type === 'Multiple')
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        First Date: <span class="font-semibold text-black">{{ $reservation->reservationDetail->start_date }}</span>
                                    </p>
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Time: <span class="font-semibold text-black">{{ $reservation->reservationDetail->start_time_from }} - {{ $reservation->reservationDetail->start_time_to }}</span>
                                    </p>
                                @endif
                            </div>
                        @endif

                        <button onclick="openModal({{ $reservation->reservation_id }})" 
                                class="absolute bottom-4 right-4 bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded-md transition-colors">
                            View
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6 text-right">
        <button class="w-auto py-2 px-4 mr-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md w-1/4">
            <a href="/admin/reservation">View all >></a>
        </button>
    </div>
</div>


<div class="p-10 ">
    <h1 class="font-bold text-3xl mb-2 ml-4">PENDING</h1>
    @if($pendingRequests->isEmpty())
        <p class="text-xl text-gray-500 ml-4 mt-4">No pending reservations found.</p>
    @else
        <div class="grid w-full grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($pendingRequests->sortBy(['transaction_date']) as $request)
                <div class="block p-4 h-full">
                    <div
                        class="flex flex-col bg-white rounded-lg shadow-xl cursor-pointer group-hover:shadow-xl transition-shadow p-6 h-full relative">
                        <!-- Basic Information Only -->
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Name: <span class="font-semibold text-black">{{ $request->name }}</span>
                        </p>
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Organization: <span class="font-semibold text-black">{{ $request->organization }}</span>
                        </p>
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Reservation Type: <span class="font-semibold text-black">{{ $request->reservation_type }}</span>
                        </p>
                        
                        <!-- Facility and Equipment -->
                        <div class="mb-2 mt-2 border-t border-solid border-gray-300">
                            <p class="text-base text-gray-600 flex-grow mt-2">
                                Facility: <span class="font-semibold text-black">{{ $request->facility->facility_name }}</span>
                            </p>
                            <p class="text-base text-gray-600 flex-grow mt-2">
                                Equipment: <span class="font-semibold text-black">{{ $request->equipment->equipment_name ?? 'No equipment' }}</span>
                            </p>
                        </div>

                        <!-- Purpose -->
                        <p class="text-base text-gray-600 flex-grow mt-2">
                            Purpose: <span class="font-semibold text-black">{{ Str::limit($request->purpose, 50) }}</span>
                        </p>

                        <!-- Date and Time Information -->
                        @if($request->reservationDetail)
                            <div class="border-t border-solid border-gray-300 mt-2">
                                @if($request->reservation_type === 'Single')
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Date: <span class="font-semibold text-black">{{ $request->reservationDetail->start_date }}</span>
                                    </p>
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Time: <span class="font-semibold text-black">{{ $request->reservationDetail->time_from }} - {{ $request->reservationDetail->time_to }}</span>
                                    </p>
                                @elseif($request->reservation_type === 'Consecutive')
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Start Date: <span class="font-semibold text-black">{{ $request->reservationDetail->start_date }}</span>
                                    </p>
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Time: <span class="font-semibold text-black">{{ $request->reservationDetail->start_time_from }} - {{ $request->reservationDetail->start_time_to }}</span>
                                    </p>
                                @elseif($request->reservation_type === 'Multiple')
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        First Date: <span class="font-semibold text-black">{{ $request->reservationDetail->start_date }}</span>
                                    </p>
                                    <p class="text-base text-gray-600 flex-grow mt-2">
                                        Time: <span class="font-semibold text-black">{{ $request->reservationDetail->start_time_from }} - {{ $request->reservationDetail->start_time_to }}</span>
                                    </p>
                                @endif
                            </div>
                        @endif

                        <!-- View Button in bottom right corner -->
                        <button onclick="openModal({{ $request->reservation_id }})" 
                                class="absolute bottom-4 right-4 bg-blue-600 hover:bg-blue-700 text-white text-xs px-3 py-1 rounded-md transition-colors">
                            View
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
    <div class="mt-6 text-right">
        <button class="w-auto py-2 px-4 mr-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md w-1/4">
            <a href="/admin/reservation">View all >></a>
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
                <button class="w-auto py-2 px-4 mr-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md w-1/4">
                    <a href="/admin/facilities">View all >></a>
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
        <div class="px-6 py-4 bg-white text-right">
            <a href="/admin/equipments" class="w-auto py-2 px-4 bg-green-600 uppercase hover:bg-green-700 text-white rounded-md">
                Manage
            </a>
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
let currentReservationId = null;

function openModal(reservationId) {
    currentReservationId = reservationId;
    // Show modal
    document.getElementById('reservationModal').classList.remove('hidden');
    
    // Fetch reservation details
    fetch(`/admin/reservation/${reservationId}/details`)
        .then(response => response.json())
        .then(data => {
            // Update button states based on current status
            updateButtonStates(data.status);
            
            // Populate modal content
            document.getElementById('modalContent').innerHTML = `
                <!-- Status and Transaction Info -->
                <div class="mb-4 p-3 bg-gray-50 rounded-md">
                    <div class="flex justify-between items-center mb-2">
                        <div class="flex items-center space-x-3">
                            <span class="px-3 py-1 text-sm font-semibold rounded-sm ${getStatusColor(data.status)}">${data.status.toUpperCase()}</span>
                            <span class="text-sm text-gray-600">Transaction Date: ${data.transaction_date}</span>
                        </div>
                        <div class="text-right">
                            <p class="text-sm text-gray-500">${getTimeAgo(data.created_at)}</p>
                        </div>
                    </div>
                    <!-- Price in separate row -->
                    <div class="text-start pt-2 border-t border-gray-200">
                        <p class="text-md font-bold text-green-600">Total Payment: ₱${parseFloat(data.total_payment).toLocaleString('en-PH', {minimumFractionDigits: 2})}</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <!-- Personal Information -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3 border-b pb-2">Personal Information</h4>
                        <div class="space-y-2">
                            <p class="text-md font-semibold"><span class="font-medium text-gray-700">Name:</span> ${data.name}</p>
                            <p class="text-md font-semibold"><span class="font-medium text-gray-700">Email:</span> ${data.email}</p>
                            <p class="text-md font-semibold"><span class="font-medium text-gray-700">Organization:</span> ${data.organization}</p>
                            <p class="text-md font-semibold"><span class="font-medium text-gray-700">Contact:</span> ${data.contact_no}</p>
                        </div>
                    </div>
                    
                    <!-- Reservation Information -->
                    <div>
                        <h4 class="font-semibold text-gray-900 mb-3 border-b pb-2">Reservation Information</h4>
                        <div class="space-y-2">
                            <p class="text-md font-semibold"><span class="font-medium text-gray-700">Type:</span> ${data.reservation_type}</p>
                            <p class="text-md font-semibold"><span class="font-medium text-gray-700">Facility:</span> ${data.facility.facility_name}</p>
                            <p class="text-md font-semibold"><span class="font-medium text-gray-700">Equipment:</span> ${data.equipment ? data.equipment.equipment_name : 'No equipment'}</p>
                            <p class="text-md font-semibold"><span class="font-medium text-gray-700">Electric Equipment:</span> ${data.electric_equipment || 'None'}</p>
                        </div>
                    </div>
                </div>
                
                <!-- Purpose -->
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3 border-b pb-2">Purpose</h4>
                    <p class="text-md text-gray-600 bg-gray-50 p-3 rounded-md">${data.purpose}</p>
                </div>
                
                ${data.instruction ? `
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3 border-b pb-2">Special Instructions</h4>
                    <p class="text-md text-gray-600 bg-gray-50 p-3 rounded-md">${data.instruction}</p>
                </div>
                ` : ''}
                
                <!-- Schedule Details -->
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3 border-b pb-2">Complete Schedule Details</h4>
                    <div class="bg-gray-50 p-4 rounded-md">
                        ${getCompleteScheduleDetails(data)}
                    </div>
                </div>

                ${data.signature ? `
                <div class="mb-6">
                    <h4 class="font-semibold text-gray-900 mb-3 border-b pb-2">Digital Signature</h4>
                    <div class="bg-gray-50 p-3 rounded-md">
                        <img src="${data.signature}" alt="Digital Signature" class="max-w-xs border rounded">
                    </div>
                </div>
                ` : ''}
            `;
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('modalContent').innerHTML = '<p class="text-red-500">Error loading reservation details.</p>';
        });
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

function getCompleteScheduleDetails(data) {
    if (!data.reservation_detail) return '<p class="text-sm text-gray-600">No schedule details available.</p>';
    
    const detail = data.reservation_detail;
    
    if (data.reservation_type === 'Single') {
        return `
            <div class="text-md">
                <h5 class="font-medium text-gray-900 mb-2">Single Day Reservation</h5>
                <div class="bg-white p-3 rounded border">
                    <p class="font-medium text-blue-600 mb-1">📅 ${detail.start_date}</p>
                    <p class="text-gray-600">⏰ ${detail.time_from} - ${detail.time_to}</p>
                </div>
            </div>
        `;
    } else if (data.reservation_type === 'Consecutive') {
        return `
            <div class="text-sm">
                <h5 class="font-medium text-gray-900 mb-2">Consecutive Days Reservation</h5>
                <div class="space-y-2">
                    <div class="bg-white p-3 rounded border">
                        <p class="font-medium text-blue-600">📅 Start Date: ${detail.start_date}</p>
                        <p class="text-gray-600">⏰ ${detail.start_time_from} - ${detail.start_time_to}</p>
                    </div>
                    ${detail.intermediate_date ? `
                    <div class="bg-white p-3 rounded border">
                        <p class="font-medium text-green-600">📅 Intermediate Date: ${detail.intermediate_date}</p>
                        <p class="text-gray-600">⏰ ${detail.intermediate_time_from || detail.start_time_from} - ${detail.intermediate_time_to || detail.start_time_to}</p>
                    </div>
                    ` : ''}
                    ${detail.end_date ? `
                    <div class="bg-white p-3 rounded border">
                        <p class="font-medium text-red-600">📅 End Date: ${detail.end_date}</p>
                        <p class="text-gray-600">⏰ ${detail.end_time_from || detail.start_time_from} - ${detail.end_time_to || detail.start_time_to}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    } else if (data.reservation_type === 'Multiple') {
        return `
            <div class="text-sm">
                <h5 class="font-medium text-gray-900 mb-2">Multiple Days Reservation</h5>
                <div class="space-y-2">
                    <div class="bg-white p-3 rounded border">
                        <p class="font-medium text-blue-600">📅 First Date: ${detail.start_date}</p>
                        <p class="text-gray-600">⏰ ${detail.start_time_from} - ${detail.start_time_to}</p>
                    </div>
                    ${detail.intermediate_date ? `
                    <div class="bg-white p-3 rounded border">
                        <p class="font-medium text-green-600">📅 Intermediate Date: ${detail.intermediate_date}</p>
                        <p class="text-gray-600">⏰ ${detail.intermediate_time_from || detail.start_time_from} - ${detail.intermediate_time_to || detail.start_time_to}</p>
                    </div>
                    ` : ''}
                    ${detail.end_date ? `
                    <div class="bg-white p-3 rounded border">
                        <p class="font-medium text-purple-600">📅 Second Date: ${detail.end_date}</p>
                        <p class="text-gray-600">⏰ ${detail.end_time_from || detail.start_time_from} - ${detail.end_time_to || detail.start_time_to}</p>
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
}

function updateButtonStates(status) {
    const acceptBtn = document.getElementById('acceptBtn');
    const declineBtn = document.getElementById('declineBtn');
    
    if (status === 'accepted') {
        acceptBtn.disabled = true;
        acceptBtn.classList.add('opacity-50', 'cursor-not-allowed');
        acceptBtn.textContent = 'Already Accepted';
        declineBtn.disabled = false;
        declineBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        declineBtn.textContent = 'Decline';
    } else if (status === 'declined') {
        declineBtn.disabled = true;
        declineBtn.classList.add('opacity-50', 'cursor-not-allowed');
        declineBtn.textContent = 'Already Declined';
        acceptBtn.disabled = false;
        acceptBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        acceptBtn.textContent = 'Accept';
    } else {
        // Both buttons enabled for pending status
        acceptBtn.disabled = false;
        acceptBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        acceptBtn.textContent = 'Accept';
        declineBtn.disabled = false;
        declineBtn.classList.remove('opacity-50', 'cursor-not-allowed');
        declineBtn.textContent = 'Decline';
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
    
    fetch(`/admin/reservation/${currentReservationId}/${action}`, {
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

// Close modal when clicking outside
document.getElementById('reservationModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

</body>
</html>

<!DOCTYPE html>
<html lang="en">
<head>
    @include('partials.head')
    <title>Admin - Reservations Management</title>
    <style>
        .tab-button {
            @apply px-6 py-3 mx-1 rounded-lg transition-all duration-200 font-semibold;
        }
        .tab-button.active {
            @apply bg-blue-600 text-white shadow-lg;
        }
        .tab-button.inactive {
            @apply bg-gray-200 text-gray-700 hover:bg-gray-300;
        }
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

<body class="bg-gray-50">
@include('partials.admin-navbar')

<div class="container mx-auto px-16 py-6 mt-20">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Reservations Management</h1>
        <p class="text-gray-600">Manage all facility reservations and equipment requests</p>
    </div>

    <!-- Status Navigation Tabs -->
    <div class="mb-6 bg-white w-1/2 rounded-xl shadow-lg h-auto">
        <div class="flex justify-between">
            <button onclick="filterReservations('pending')" 
                    id="tab-pending" 
                    class="tab-button active h-full border-r-2 pl-8 pr-8 py-3 border-gray-300">
                Pending <span class="ml-2 bg-yellow-500 text-white px-2 py-1 rounded-full text-xs">{{ $pendingCount }}</span>
            </button>
            <button onclick="filterReservations('accepted')" 
                    id="tab-accepted" 
                    class="tab-button inactive h-full border-r-2 pl-1 pr-9 py-3 border-gray-300">
                Accepted <span class="ml-2 bg-green-500 text-white px-2 py-1 rounded-full text-xs">{{ $acceptedCount }}</span>
            </button>
            <button onclick="filterReservations('declined')" 
                    id="tab-declined" 
                    class="tab-button inactive h-full border-r-2 pl-1 pr-9 py-3 border-gray-300">
                Declined <span class="ml-2 bg-red-500 text-white px-2 py-1 rounded-full text-xs">{{ $declinedCount }}</span>
            </button>
            <button onclick="filterReservations('completed')" 
                    id="tab-completed" 
                    class="tab-button inactive h-full pl-1 pr-8 py-3">
                Completed <span class="ml-2 bg-blue-500 text-white px-2 py-1 rounded-full text-xs">{{ $completedCount }}</span>
            </button>
        </div>
    </div>

    <!-- Loading Indicator -->
    <div id="loading" class="hidden text-center py-8">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-blue-600"></div>
        <p class="text-gray-600 mt-2">Loading reservations...</p>
    </div>

    <!-- Reservations Container -->
    <div id="reservations-container" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        <!-- Cards will be inserted here by JavaScript -->
    </div>

    <!-- Empty State -->
    <div id="empty-state" class="hidden text-center py-12">
        <div class="text-gray-400 text-6xl mb-4">📋</div>
        <h3 class="text-lg font-medium text-gray-900 mb-2">No reservations found</h3>
        <p class="text-gray-600">There are no reservations with the selected status.</p>
    </div>
</div>

<!-- Reservation Detail Modal -->
<div id="reservation-modal" class="fixed inset-0 bg-black bg-opacity-60 hidden z-50 backdrop-blur-sm">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-5xl w-full max-h-screen overflow-y-auto border border-gray-200">
            <div class="p-8">
                <div class="flex justify-between items-center mb-6 pb-4 border-b-2 border-gray-100">
                    <h2 class="text-3xl font-bold text-gray-900 flex items-center">
                        <svg class="w-8 h-8 mr-3 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                        </svg>
                        Reservation Details
                    </h2>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 hover:bg-gray-100 p-2 rounded-full transition-all duration-200">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="modal-content">
                    <!-- Modal content will be loaded here -->
                </div>
            </div>
        </div>
    </div>
</div>

<script>
let allReservations = @json($allReservations);
let currentStatus = 'pending';

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    filterReservations('pending');
});

function filterReservations(status) {
    currentStatus = status;
    
    // Update tab appearance
    document.querySelectorAll('.tab-button').forEach(btn => {
        btn.classList.remove('active');
        btn.classList.add('inactive');
    });
    document.getElementById(`tab-${status}`).classList.add('active');
    document.getElementById(`tab-${status}`).classList.remove('inactive');
    
    // Filter and sort reservations by creation date (oldest first)
    const filteredReservations = allReservations
        .filter(reservation => reservation.status === status)
        .sort((a, b) => {
            // Always use created_at for accurate chronological sorting
            const dateA = new Date(a.created_at);
            const dateB = new Date(b.created_at);
            return dateA - dateB; // Ascending order (oldest first)
        });
    
    displayReservations(filteredReservations);
}

function displayReservations(reservations) {
    const container = document.getElementById('reservations-container');
    const emptyState = document.getElementById('empty-state');
    
    if (reservations.length === 0) {
        container.innerHTML = '';
        container.classList.add('hidden');
        emptyState.classList.remove('hidden');
        return;
    }
    
    container.classList.remove('hidden');
    emptyState.classList.add('hidden');
    
    container.innerHTML = reservations.map(reservation => createReservationCard(reservation)).join('');
}

function createReservationCard(reservation) {
    const statusClass = `status-${reservation.status}`;
    const facilityName = reservation.facility ? reservation.facility.facility_name : 'N/A';
    
    // Equipment display logic - show only first equipment if multiple
    let equipmentInfo = 'No equipment';
    if (reservation.equipments && reservation.equipments.length > 0) {
        const firstEquipment = reservation.equipments[0];
        const quantity = firstEquipment.quantity || 1;
        const equipmentName = quantity > 1 ? `${firstEquipment.equipment_name} (${quantity})` : firstEquipment.equipment_name;
        
        if (reservation.equipments.length > 1) {
            equipmentInfo = `${equipmentName} (+${reservation.equipments.length - 1} more)`;
        } else {
            equipmentInfo = equipmentName;
        }
    }
    
    const reservationDetail = reservation.reservation_detail;
    let scheduleInfo = 'Schedule not available';
    
    if (reservationDetail) {
        if (reservation.reservation_type === 'Single') {
            scheduleInfo = `${reservationDetail.start_date} (${reservationDetail.time_from || ''} - ${reservationDetail.time_to || ''})`;
        } else if (reservation.reservation_type === 'Consecutive') {
            scheduleInfo = `${reservationDetail.start_date} to ${reservationDetail.end_date || 'N/A'}`;
        } else if (reservation.reservation_type === 'Multiple') {
            scheduleInfo = `${reservationDetail.start_date} to ${reservationDetail.end_date || 'N/A'}`;
        }
    }
    
    return `
        <div class="bg-white rounded-lg shadow-md hover:shadow-lg transition-shadow duration-200">
            <div class="p-6">
                <div class="flex justify-between items-start mb-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">${reservation.name}</h3>
                        <p class="text-sm text-gray-600">${reservation.organization}</p>
                    </div>
                    <span class="status-badge ${statusClass}">${reservation.status}</span>
                </div>
                
                <div class="space-y-2 mb-4">
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path>
                        </svg>
                        <span class="font-medium mr-2">Facility:</span>${facilityName}
                    </div>
                    
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                        <span class="font-medium mr-2">Schedule:</span>${scheduleInfo}
                    </div>
                    
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                        <span class="font-medium mr-2">Equipment:</span>${equipmentInfo}
                    </div>
                    
                    <div class="flex items-center text-sm text-gray-600">
                        <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4V2a1 1 0 011-1h8a1 1 0 011 1v2h4a1 1 0 011 1v1a1 1 0 01-1 1v9a2 2 0 01-2 2H5a2 2 0 01-2-2V7a1 1 0 01-1-1V5a1 1 0 011-1h4z"></path>
                        </svg>
                        <span class="font-medium mr-2">Type:</span>${reservation.reservation_type}
                    </div>
                </div>
                
                <div class="flex justify-between items-center">
                    <div class="text-xs text-gray-500">
                        <div>${reservation.transaction_date || new Date(reservation.created_at).toLocaleDateString()}</div>
                        <div class="text-gray-400">${getTimeAgo(reservation.created_at)}</div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="viewDetails(${reservation.reservation_id})" 
                                class="bg-blue-600 text-white px-3 py-1 rounded text-sm hover:bg-blue-700 transition-colors">
                            View Details
                        </button>
                        ${reservation.status === 'pending' ? `
                            <button onclick="acceptReservation(${reservation.reservation_id})" 
                                    class="bg-green-600 text-white px-3 py-1 rounded text-sm hover:bg-green-700 transition-colors">
                                Accept
                            </button>
                            <button onclick="declineReservation(${reservation.reservation_id})" 
                                    class="bg-red-600 text-white px-3 py-1 rounded text-sm hover:bg-red-700 transition-colors">
                                Decline
                            </button>
                        ` : ''}
                    </div>
                </div>
            </div>
        </div>
    `;
}

function viewDetails(reservationId) {
    fetch(`/admin/reservations/${reservationId}/details`)
        .then(response => response.json())
        .then(data => {
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
    const modal = document.getElementById('reservation-modal');
    const modalContent = document.getElementById('modal-content');
    
    // Create equipment list
    let equipmentInfo = 'No equipment requested';
    if (reservation.equipments && reservation.equipments.length > 0) {
        equipmentInfo = reservation.equipments.map(eq => {
            const quantity = eq.quantity || 1;
            return `${eq.equipment_name} (Qty: ${quantity})`;
        }).join(', ');
    }
    
    // Create schedule information
    let scheduleInfo = '';
    if (reservation.reservation_detail) {
        const detail = reservation.reservation_detail;
        if (reservation.reservation_type === 'Single') {
            scheduleInfo = `
                <p><strong>Date:</strong> ${detail.start_date || 'N/A'}</p>
                <p><strong>Time:</strong> ${detail.time_from || 'N/A'} - ${detail.time_to || 'N/A'}</p>
            `;
        } else if (reservation.reservation_type === 'Consecutive') {
            scheduleInfo = `
                <p><strong>Start Date:</strong> ${detail.start_date || 'N/A'} (${detail.start_time_from || ''} - ${detail.start_time_to || ''})</p>
                <p><strong>End Date:</strong> ${detail.end_date || 'N/A'} (${detail.end_time_from || ''} - ${detail.end_time_to || ''})</p>
                ${detail.intermediate_date ? `<p><strong>Intermediate Date:</strong> ${detail.intermediate_date} (${detail.intermediate_time_from || ''} - ${detail.intermediate_time_to || ''})</p>` : ''}
            `;
        } else if (reservation.reservation_type === 'Multiple') {
            scheduleInfo = `
                <p><strong>Start Date:</strong> ${detail.start_date || 'N/A'} (${detail.start_time_from || ''} - ${detail.start_time_to || ''})</p>
                <p><strong>End Date:</strong> ${detail.end_date || 'N/A'} (${detail.end_time_from || ''} - ${detail.end_time_to || ''})</p>
                ${detail.intermediate_date ? `<p><strong>Intermediate Date:</strong> ${detail.intermediate_date} (${detail.intermediate_time_from || ''} - ${detail.intermediate_time_to || ''})</p>` : ''}
            `;
        }
    }
    
    modalContent.innerHTML = `
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

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-6">
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
                        <span class="text-sm font-medium text-gray-600 w-24 flex-shrink-0">Contact:</span>
                        <span class="text-sm font-semibold text-gray-900">${reservation.contact_no}</span>
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

        <!-- Schedule Date -->
        <div class="mb-6 bg-white p-5 rounded-lg border shadow-sm">
            <h3 class="text-lg font-bold text-gray-900 mb-4 pb-2 border-b border-gray-200 flex items-center">
                <svg class="w-5 h-5 mr-2 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                Scheduled Date
            </h3>
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 p-4 rounded-lg">
                ${getCompleteScheduleDetails(reservation)}
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
        
        ${reservation.status === 'pending' ? `
            <div class="mt-8 pt-6 border-t-2 border-gray-200 bg-gray-50 p-4 rounded-lg">
                <div class="flex gap-4 justify-end">
                    <button onclick="acceptReservation(${reservation.reservation_id})" 
                            class="bg-green-600 text-white px-8 py-3 rounded-lg hover:bg-green-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        Accept Reservation
                    </button>
                    <button onclick="declineReservation(${reservation.reservation_id})" 
                            class="bg-red-600 text-white px-8 py-3 rounded-lg hover:bg-red-700 transition-all duration-200 shadow-md hover:shadow-lg flex items-center font-semibold">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                        Decline Reservation
                    </button>
                </div>
            </div>
        ` : ''}
    `;
    
    modal.classList.remove('hidden');
}

function closeModal() {
    document.getElementById('reservation-modal').classList.add('hidden');
}

function acceptReservation(reservationId) {
    if (!confirm('Are you sure you want to accept this reservation?')) return;
    
    fetch(`/admin/reservations/${reservationId}/accept`, {
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
            // Update the reservation in our local data
            const reservation = allReservations.find(r => r.reservation_id === reservationId);
            if (reservation) {
                reservation.status = 'accepted';
            }
            // Refresh the current view
            filterReservations(currentStatus);
            closeModal();
            // Refresh page to update counts
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
    
    fetch(`/admin/reservations/${reservationId}/decline`, {
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
            // Update the reservation in our local data
            const reservation = allReservations.find(r => r.reservation_id === reservationId);
            if (reservation) {
                reservation.status = 'declined';
            }
            // Refresh the current view
            filterReservations(currentStatus);
            closeModal();
            // Refresh page to update counts
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

// Helper function to get status color classes
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

// Helper function to get time ago
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

// Helper function to get equipment for a specific date
function getEquipmentForDate(reservation, targetDate) {
    if (!reservation.equipments || reservation.equipments.length === 0) {
        return '';
    }
    
    const equipmentForDate = reservation.equipments.filter(eq => eq.reservation_date === targetDate);
    
    if (equipmentForDate.length === 0) {
        return '';
    }
    
    return `
        <div class="mt-2 pt-2 border-t border-gray-200">
            <div class="flex items-center mb-1">
                <svg class="w-4 h-4 mr-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                </svg>
                <span class="text-xs font-medium text-gray-600">Equipment:</span>
            </div>
            <div class="text-xs text-gray-700 bg-blue-50 p-2 rounded">
                ${equipmentForDate.map(eq => `🔧 ${eq.equipment_name} (Qty: ${eq.quantity})`).join('<br>')}
            </div>
        </div>
    `;
}

// Helper function to get complete schedule details
function getCompleteScheduleDetails(reservation) {
    if (!reservation.reservation_detail) return '<p class="text-sm text-gray-600">No schedule details available.</p>';
    
    const detail = reservation.reservation_detail;
    
    if (reservation.reservation_type === 'Single') {
        return `
            <div class="text-sm">
                <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                    <span class="bg-blue-500 text-white px-2 py-1 rounded-full text-xs mr-2">📅</span>
                    Single Day Reservation
                </h5>
                <div class="bg-white p-4 rounded-lg border shadow-sm">
                    <div class="flex items-center mb-2">
                        <span class="font-medium text-blue-600 text-base">📅 ${detail.start_date}</span>
                    </div>
                    <div class="flex items-center text-gray-600">
                        <span class="text-sm">⏰ ${detail.time_from} - ${detail.time_to}</span>
                    </div>
                    ${getEquipmentForDate(reservation, detail.start_date)}
                </div>
            </div>
        `;
    } else if (reservation.reservation_type === 'Consecutive') {
        return `
            <div class="text-sm">
                <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                    <span class="bg-green-500 text-white px-2 py-1 rounded-full text-xs mr-2">📅</span>
                    Consecutive Days Reservation
                </h5>
                <div class="space-y-3">
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-green-600">📅 Start Date: ${detail.start_date}</span>
                            <span class="text-xs bg-green-100 text-green-700 px-2 py-1 rounded">START</span>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">⏰ ${detail.start_time_from} - ${detail.start_time_to}</p>
                        ${getEquipmentForDate(reservation, detail.start_date)}
                    </div>
                    ${detail.intermediate_date ? `
                    <div class="bg-white p-4 rounded-lg border shadow-sm border-dashed border-yellow-300">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-yellow-600">📅 Intermediate: ${detail.intermediate_date}</span>
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">MIDDLE</span>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">⏰ ${detail.intermediate_time_from || detail.start_time_from} - ${detail.intermediate_time_to || detail.start_time_to}</p>
                        ${getEquipmentForDate(reservation, detail.intermediate_date)}
                    </div>
                    ` : ''}
                    ${detail.end_date ? `
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-red-600">📅 End Date: ${detail.end_date}</span>
                            <span class="text-xs bg-red-100 text-red-700 px-2 py-1 rounded">END</span>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">⏰ ${detail.end_time_from || detail.start_time_from} - ${detail.end_time_to || detail.start_time_to}</p>
                        ${getEquipmentForDate(reservation, detail.end_date)}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    } else if (reservation.reservation_type === 'Multiple') {
        return `
            <div class="text-sm">
                <h5 class="font-medium text-gray-900 mb-3 flex items-center">
                    <span class="bg-purple-500 text-white px-2 py-1 rounded-full text-xs mr-2">📅</span>
                    Multiple Days Reservation
                </h5>
                <div class="space-y-3">
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-purple-600">📅 First Date: ${detail.start_date}</span>
                            <span class="text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded">FIRST</span>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">⏰ ${detail.start_time_from} - ${detail.start_time_to}</p>
                        ${getEquipmentForDate(reservation, detail.start_date)}
                    </div>
                    ${detail.intermediate_date ? `
                    <div class="bg-white p-4 rounded-lg border shadow-sm border-dashed border-yellow-300">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-yellow-600">📅 Intermediate: ${detail.intermediate_date}</span>
                            <span class="text-xs bg-yellow-100 text-yellow-700 px-2 py-1 rounded">MIDDLE</span>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">⏰ ${detail.intermediate_time_from || detail.start_time_from} - ${detail.intermediate_time_to || detail.start_time_to}</p>
                        ${getEquipmentForDate(reservation, detail.intermediate_date)}
                    </div>
                    ` : ''}
                    ${detail.end_date ? `
                    <div class="bg-white p-4 rounded-lg border shadow-sm">
                        <div class="flex items-center justify-between">
                            <span class="font-medium text-indigo-600">📅 Second Date: ${detail.end_date}</span>
                            <span class="text-xs bg-indigo-100 text-indigo-700 px-2 py-1 rounded">SECOND</span>
                        </div>
                        <p class="text-gray-600 text-sm mt-1">⏰ ${detail.end_time_from || detail.start_time_from} - ${detail.end_time_to || detail.start_time_to}</p>
                        ${getEquipmentForDate(reservation, detail.end_date)}
                    </div>
                    ` : ''}
                </div>
            </div>
        `;
    }
}

// Close modal when clicking outside
document.getElementById('reservation-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeModal();
    }
});
</script>

</body>
</html>
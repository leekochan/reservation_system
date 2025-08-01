<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\ReservationRequest;

class AdminDashboardController extends Controller
{
    /**
     * Display the admin dashboard
     */
    public function adminDashboard()
    {
        // Get recent accepted reservations for dashboard
        $reservations = ReservationRequest::with([
            'facility',
            'equipment',
            'equipments',
            'reservationDetail'
        ])
            ->where('status', 'accepted')
            ->orderBy('transaction_date', 'desc')
            ->latest()
            ->take(6)
            ->get();

        // Get recent pending requests for dashboard
        $pendingRequests = ReservationRequest::with([
            'facility', 
            'equipment', 
            'equipments',
            'reservationDetail'
        ])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->latest()
            ->take(6)
            ->get();

        // Get recent facilities
        $facilities = Facility::latest()->take(6)->get();

        // Get all equipment
        $equipments = Equipment::all();

        // Get dashboard statistics
        $stats = [
            'total_reservations' => ReservationRequest::count(),
            'pending_requests' => ReservationRequest::where('status', 'pending')->count(),
            'accepted_reservations' => ReservationRequest::where('status', 'accepted')->count(),
            'total_facilities' => Facility::count(),
            'total_equipment' => Equipment::count(),
        ];

        return view('dashboard', compact('reservations', 'pendingRequests', 'facilities', 'equipments', 'stats'));
    }

    /**
     * Get reservation details for dashboard modal (simplified version)
     */
    public function getReservationDetails($id)
    {
        try {
            $reservation = ReservationRequest::with([
                'facility',
                'equipment',
                'equipments', 
                'reservationDetail'
            ])->findOrFail($id);

            return response()->json([
                'reservation_id' => $reservation->reservation_id,
                'name' => $reservation->name,
                'email' => $reservation->email,
                'organization' => $reservation->organization,
                'contact_no' => $reservation->contact_no,
                'purpose' => $reservation->purpose,
                'instruction' => $reservation->instruction,
                'electric_equipment' => $reservation->electric_equipment,
                'transaction_date' => $reservation->transaction_date,
                'reservation_type' => $reservation->reservation_type,
                'status' => $reservation->status,
                'total_payment' => $reservation->total_payment,
                'signature' => $reservation->signature,
                'created_at' => $reservation->created_at,
                'facility' => [
                    'facility_name' => $reservation->facility->facility_name ?? 'N/A'
                ],
                'equipment' => $reservation->equipment ? [
                    'equipment_name' => $reservation->equipment->equipment_name
                ] : null,
                'equipments' => $reservation->equipments->map(function($equipment) {
                    return [
                        'equipment_name' => $equipment->equipment_name,
                        'quantity' => $equipment->pivot->quantity ?? 1,
                        'reservation_date' => $equipment->pivot->reservation_date ?? null
                    ];
                }),
                'reservation_detail' => $reservation->reservationDetail ? [
                    'start_date' => $reservation->reservationDetail->start_date,
                    'end_date' => $reservation->reservationDetail->end_date ?? null,
                    'time_from' => $reservation->reservationDetail->time_from ?? null,
                    'time_to' => $reservation->reservationDetail->time_to ?? null,
                    'start_time_from' => $reservation->reservationDetail->start_time_from ?? null,
                    'start_time_to' => $reservation->reservationDetail->start_time_to ?? null,
                    'end_time_from' => $reservation->reservationDetail->end_time_from ?? null,
                    'end_time_to' => $reservation->reservationDetail->end_time_to ?? null,
                    'intermediate_date' => $reservation->reservationDetail->intermediate_date ?? null,
                    'intermediate_time_from' => $reservation->reservationDetail->intermediate_time_from ?? null,
                    'intermediate_time_to' => $reservation->reservationDetail->intermediate_time_to ?? null
                ] : null
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Reservation not found'], 404);
        }
    }

    /**
     * Quick accept reservation from dashboard
     */
    public function acceptReservation($id)
    {
        try {
            $reservation = ReservationRequest::findOrFail($id);
            $reservation->status = 'accepted';
            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Reservation accepted successfully',
                'status' => 'accepted'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error accepting reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Quick decline reservation from dashboard
     */
    public function declineReservation($id)
    {
        try {
            $reservation = ReservationRequest::findOrFail($id);
            $reservation->status = 'declined';
            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Reservation declined successfully',
                'status' => 'declined'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error declining reservation: ' . $e->getMessage()
            ], 500);
        }
    }
}

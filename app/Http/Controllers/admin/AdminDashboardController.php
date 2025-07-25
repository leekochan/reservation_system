<?php

namespace App\Http\Controllers\Admin; // Note: Capital 'A' in Admin is standard for PSR-4

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\ReservationRequest;

class AdminDashboardController extends Controller // Should extend base Controller
{
    public function adminDashboard()
    {
        $reservations = ReservationRequest::with([
            'facility',
            'equipment',
            'reservationDetail'
        ])
            ->where('status', 'accepted')
            ->orderBy('transaction_date', 'desc')
            ->latest()->take(6)->get(); // Execute the query with get()

        $pendingRequests = ReservationRequest::with(['facility', 'equipment', 'reservationDetail'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->latest()->take(6)->get();

        $facilities = Facility::latest()->take(6)->get();

        $equipments = Equipment::class::all();

        return view('dashboard', compact('reservations', 'pendingRequests', 'facilities', 'equipments')); // Changed to admin.dashboard
    }

    public function getReservationDetails($id)
    {
        try {
            $reservation = ReservationRequest::with([
                'facility',
                'equipment', 
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
                'message' => 'Error accepting reservation'
            ], 500);
        }
    }

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
                'message' => 'Error declining reservation'
            ], 500);
        }
    }
}

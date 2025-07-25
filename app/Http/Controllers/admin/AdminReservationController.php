<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Equipment;
use App\Models\Facility;
use App\Models\ReservationRequest;
use Illuminate\Http\Request;

class AdminReservationController extends Controller
{
    /**
     * Display the reservation management page
     */
    public function index()
    {
        // Get counts for each status
        $pendingCount = ReservationRequest::where('status', 'pending')->count();
        $acceptedCount = ReservationRequest::where('status', 'accepted')->count();
        $declinedCount = ReservationRequest::where('status', 'declined')->count();
        $completedCount = ReservationRequest::where('status', 'completed')->count();

        // Get all reservations (we'll filter on frontend)
        $allReservations = ReservationRequest::with([
            'facility',
            'equipment',
            'equipments',
            'reservationDetail'
        ])->orderBy('created_at', 'desc')->get();

        return view('reservation', compact(
            'allReservations',
            'pendingCount',
            'acceptedCount', 
            'declinedCount',
            'completedCount'
        ));
    }

    /**
     * Get reservation details via AJAX
     */
    public function show($id)
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
     * Accept a reservation
     */
    public function accept($id)
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
     * Decline a reservation
     */
    public function decline($id)
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

    /**
     * Mark a reservation as completed
     */
    public function complete($id)
    {
        try {
            $reservation = ReservationRequest::findOrFail($id);
            $reservation->status = 'completed';
            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Reservation marked as completed',
                'status' => 'completed'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error completing reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get reservations by status
     */
    public function getByStatus($status)
    {
        try {
            $validStatuses = ['pending', 'accepted', 'declined', 'completed'];
            
            if (!in_array($status, $validStatuses)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid status'
                ], 400);
            }

            $reservations = ReservationRequest::with([
                'facility',
                'equipment',
                'equipments',
                'reservationDetail'
            ])
            ->where('status', $status)
            ->orderBy('created_at', 'desc')
            ->get();

            return response()->json([
                'success' => true,
                'reservations' => $reservations,
                'count' => $reservations->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching reservations: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a reservation
     */
    public function cancel($id)
    {
        try {
            $reservation = ReservationRequest::findOrFail($id);
            $reservation->status = 'cancelled';
            $reservation->save();

            return response()->json([
                'success' => true,
                'message' => 'Reservation cancelled successfully',
                'status' => 'cancelled'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error cancelling reservation: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a reservation (permanent deletion)
     */
    public function destroy($id)
    {
        try {
            $reservation = ReservationRequest::findOrFail($id);
            $reservation->delete();

            return response()->json([
                'success' => true,
                'message' => 'Reservation deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting reservation: ' . $e->getMessage()
            ], 500);
        }
    }
}

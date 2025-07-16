<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ReservationRequest;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    /**
     * Display a listing of accepted reservations.
     */
    public function index()
    {
        $reservations = ReservationRequest::with([
            'facility',
            'equipment',
            'reservationDetail'
        ])
            ->where('status', 'accepted')
            ->orderBy('transaction_date', 'desc')
            ->paginate(10);

        return view('admin.reservations.index', compact('reservations'));
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create()
    {
        // You'll need to implement this if you want admin to create reservations
        return view('admin.reservations.create');
    }

    /**
     * Store a newly created reservation in storage.
     */
    public function store(Request $request)
    {
        // You'll need to implement this if you want admin to create reservations
    }

    /**
     * Display the specified reservation.
     */
    public function show($id)
    {
        $reservation = ReservationRequest::with([
            'facility',
            'equipment',
            'reservationDetail'
        ])
            ->findOrFail($id);

        return view('admin.reservations.show', compact('reservation'));
    }

    /**
     * Show the form for editing the specified reservation.
     */
    public function edit($id)
    {
        $reservation = ReservationRequest::findOrFail($id);
        return view('admin.reservations.edit', compact('reservation'));
    }

    /**
     * Update the specified reservation in storage.
     */
    public function update(Request $request, $id)
    {
        // Validation and update logic
    }

    /**
     * Remove the specified reservation from storage.
     */
    public function destroy($id)
    {
        $reservation = ReservationRequest::findOrFail($id);
        $reservation->delete();

        return redirect()->route('admin.reservations.index')
            ->with('success', 'Reservation deleted successfully');
    }
}

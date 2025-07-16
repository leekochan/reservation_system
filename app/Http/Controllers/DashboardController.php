<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReservationRequest;

class DashboardController extends Controller
{
    public function index()
    {
        $reservations = ReservationRequest::with([
            'facility',
            'equipment',
            'reservationDetail'
        ])
            ->where('status', 'accepted')
            ->orderBy('transaction_date', 'desc')
            ->take(5)
            ->get();

        $pendingRequests = ReservationRequest::with([
            'facility',
            'equipment',
            'reservationDetail'
        ])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('dashboard', compact('reservations', 'pendingRequests'));
    }
}

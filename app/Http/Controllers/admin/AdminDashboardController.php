<?php

namespace App\Http\Controllers\Admin; // Note: Capital 'A' in Admin is standard for PSR-4

use App\Http\Controllers\Controller;
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

        return view('dashboard', compact('reservations', 'pendingRequests', 'facilities')); // Changed to admin.dashboard
    }
}

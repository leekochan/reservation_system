<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\ReservationRequest;
use Illuminate\Http\Request;

class RequestController extends Controller
{
    public function pending()
    {
        $requests = ReservationRequest::with(['facility', 'equipment', 'reservationDetail'])
            ->where('status', 'pending')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('admin.requests.pending', compact('requests'));
    }

    public function approve($id)
    {
        $request = ReservationRequest::findOrFail($id);
        $request->update(['status' => 'accepted']);

        return back()->with('success', 'Reservation request approved successfully.');
    }

    public function reject($id)
    {
        $request = ReservationRequest::findOrFail($id);
        $request->update(['status' => 'declined']);

        return back()->with('success', 'Reservation request rejected.');
    }
}

<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Facility;
use App\Models\ReservationRequest;

class DashboardController extends Controller
{
    public function userDashboard()
    {
        // Get 6 latest facilities
        $facilities = Facility::latest()->take(6)->get();

        return view('user-dashboard', compact('facilities'));
    }
}

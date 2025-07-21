<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use App\Models\Equipment;
use App\Models\CalendarActivity;

class ReservationController extends Controller
{
    public function userReservation()
    {
        // Get all facilities from database
        $facilities = Facility::where('status', 'available')->get();

        // Get all equipment from database
        $equipments = Equipment::where('status', 'available')->get();

        // Get calendar activities (you may need to adjust this based on your needs)
        $calendarActivities = CalendarActivity::all();

        return view('user-reservation', compact('facilities', 'equipments', 'calendarActivities'));
    }
}

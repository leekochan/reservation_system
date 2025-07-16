<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function userDashboard()
    {
        // Get 6 latest facilities
        $facilities = Facility::latest()->take(6)->get();
        return view('user-dashboard', compact('facilities'));
    }

    public function userFacilities()
    {
        // Get all facilities
        $facilities = Facility::all();
        return view('user-facilities', compact('facilities'));
    }
}

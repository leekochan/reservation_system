<?php

namespace App\Http\Controllers;

use App\Models\Facility;
use Illuminate\Http\Request;

class FacilityController extends Controller
{
    public function userFacilities()
    {
        // Get all facilities
        $facilities = Facility::all();
        return view('user-facilities', compact('facilities'));
    }
}

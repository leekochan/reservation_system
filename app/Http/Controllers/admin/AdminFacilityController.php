<?php

namespace App\Http\Controllers\admin;

use App\Models\Facility;

class AdminFacilityController
{
    public function adminFacilities()
    {
        // Get all facilities
        $facilities = Facility::all();
        return view('facilities', compact('facilities'));
    }
}

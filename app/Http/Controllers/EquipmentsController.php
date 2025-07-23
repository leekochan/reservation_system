<?php

namespace App\Http\Controllers;

use App\Models\Equipment; // Make sure you have this model created

class EquipmentsController extends Controller
{
    public function equipment()
    {
        $equipments = Equipment::where('status', 'available')->get(); // Fetch available equipments
        return view('user-equipments', compact('equipments'));
    }
}

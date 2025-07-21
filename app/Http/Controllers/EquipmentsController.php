<?php

namespace App\Http\Controllers;

use App\Models\Equipment; // Make sure you have this model created

class EquipmentsController extends Controller
{
    public function equipment()
    {
        $equipments = Equipment::class->get()->all(); // Fetch all equipments
        return view('user-equipments');
    }
}

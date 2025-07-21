<?php

namespace App\Http\Controllers\admin;

use App\Models\Equipment;

class AdminEquipmentsController
{
    public function adminEquipments()
    {
        $equipments = Equipment::class::all();
        return view('equipments',  compact('equipments'));
    }
    public function adminManageEquipments()
    {
        $equipments = Equipment::class::all();
        return view('manage-equipments',  compact('equipments'));
    }

}

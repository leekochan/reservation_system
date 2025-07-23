<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Equipment;
use App\Models\Facility;

class BasicDataSeeder extends Seeder
{
    public function run(): void
    {
        // Create basic equipment if not exists
        if (Equipment::count() === 0) {
            Equipment::create([
                'equipment_name' => 'Projector',
                'units' => 5,
                'status' => 'available'
            ]);

            Equipment::create([
                'equipment_name' => 'Microphone',
                'units' => 10,
                'status' => 'available'
            ]);

            Equipment::create([
                'equipment_name' => 'Speaker',
                'units' => 8,
                'status' => 'available'
            ]);
        }

        // Create basic facilities if not exists
        if (Facility::count() === 0) {
            Facility::create([
                'facility_name' => 'Conference Room A',
                'facility_type' => 'meeting',
                'capacity' => 50,
                'status' => 'available'
            ]);

            Facility::create([
                'facility_name' => 'Auditorium',
                'facility_type' => 'event',
                'capacity' => 200,
                'status' => 'available'
            ]);

            Facility::create([
                'facility_name' => 'Training Room B',
                'facility_type' => 'training',
                'capacity' => 30,
                'status' => 'available'
            ]);
        }
    }
}

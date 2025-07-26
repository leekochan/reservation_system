<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReservationRequest;
use App\Models\Single;

echo "Creating a test reservation for facility 7...\n";

// Create a reservation request
$reservation = new ReservationRequest();
$reservation->facility_id = 7;
$reservation->equipment_id = 2; // Using an existing equipment ID
$reservation->status = 'accepted';
$reservation->name = 'Test User';
$reservation->email = 'test@example.com';
$reservation->organization = 'Test Org';
$reservation->contact_no = '1234567890';
$reservation->purpose = 'Testing time blocking';
$reservation->instruction = 'This is a test reservation';
$reservation->electric_equipment = 'no';
$reservation->reservation_type = 'Single';
$reservation->transaction_date = '2025-07-27';
$reservation->total_payment = '0';
$reservation->save();

// Create the single reservation detail
$single = new Single();
$single->reservation_id = $reservation->reservation_id;
$single->start_date = '2025-07-26';
$single->time_from = '10:00';
$single->time_to = '12:00';
$single->save();

echo "Created test reservation:\n";
echo "- Facility: 7\n";
echo "- Date: 2025-07-26\n";
echo "- Time: 10:00 - 12:00\n";
echo "- Status: accepted\n";
echo "\nNow testing time availability API...\n";

<?php

require_once 'vendor/autoload.php';

$app = require_once 'bootstrap/app.php';
$app->boot();

use App\Models\ReservationRequest;
use App\Models\Single;

// Create an accepted reservation for July 28, 2025 from 8:00 AM to 12:00 PM
$reservation = ReservationRequest::create([
    'facility_id' => 7,
    'user_name' => 'Test User',
    'user_email' => 'test@example.com',
    'user_id_number' => '12345',
    'user_contact' => '123-456-7890',
    'reservation_type' => 'single',
    'status' => 'accepted'
]);

Single::create([
    'reservation_request_id' => $reservation->id,
    'reservation_date' => '2025-07-28',
    'start_time' => '08:00:00',
    'end_time' => '12:00:00'
]);

echo "Created accepted reservation for July 28, 2025 from 8:00 AM to 12:00 PM\n";
echo "Reservation ID: " . $reservation->id . "\n";

?>

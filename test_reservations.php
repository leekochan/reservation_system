<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReservationRequest;
use App\Models\Single;
use App\Models\Consecutive;
use App\Models\Multiple;

echo "Checking accepted reservations for facility 7:\n";

$acceptedReservations = ReservationRequest::where('facility_id', 7)
    ->where('status', 'accepted')
    ->with('reservationDetail')
    ->get();

echo "Found " . $acceptedReservations->count() . " accepted reservations:\n";

foreach ($acceptedReservations as $reservation) {
    echo "Request ID: {$reservation->reservation_request_id}\n";
    echo "Status: {$reservation->status}\n";
    
    $detail = $reservation->reservationDetail;
    if ($detail) {
        echo "Type: " . get_class($detail) . "\n";
        
        if ($detail instanceof Single) {
            echo "Date: {$detail->start_date}, Time: {$detail->time_from} - {$detail->time_to}\n";
        } elseif ($detail instanceof Consecutive) {
            echo "Start: {$detail->start_date} ({$detail->start_time_from} - {$detail->start_time_to})\n";
            echo "End: {$detail->end_date} ({$detail->end_time_from} - {$detail->end_time_to})\n";
        } elseif ($detail instanceof Multiple) {
            echo "Start: {$detail->start_date} ({$detail->start_time_from} - {$detail->start_time_to})\n";
            if ($detail->intermediate_date) {
                echo "Intermediate: {$detail->intermediate_date} ({$detail->intermediate_time_from} - {$detail->intermediate_time_to})\n";
            }
            echo "End: {$detail->end_date} ({$detail->end_time_from} - {$detail->end_time_to})\n";
        }
    }
    echo "\n";
}

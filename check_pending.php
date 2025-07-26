<?php

require_once 'vendor/autoload.php';

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\ReservationRequest;

echo "Checking all pending reservations:\n";

$pendingRequests = ReservationRequest::where('status', 'pending')->get();

foreach ($pendingRequests as $request) {
    echo "Request ID: {$request->reservation_request_id}, Facility ID: {$request->facility_id}, Status: {$request->status}\n";
}

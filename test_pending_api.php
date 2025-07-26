<?php

require_once 'vendor/autoload.php';

use App\Models\ReservationRequest;
use App\Models\Single;
use App\Models\Consecutive;
use App\Models\Multiple;

// Bootstrap Laravel
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "Testing Pending Reservations API\n";
echo "=================================\n\n";

// Get a facility ID
$facilityId = 7;
$month = 7;
$year = 2025;

echo "Checking facility ID: {$facilityId}\n";
echo "Month: {$month}, Year: {$year}\n\n";

// Check for pending reservations
$pendingRequests = ReservationRequest::where('facility_id', $facilityId)
    ->where('status', 'pending')
    ->with('reservationDetail')
    ->get();

echo "Found " . $pendingRequests->count() . " pending requests for facility {$facilityId}:\n";

foreach ($pendingRequests as $request) {
    echo "- Request ID: {$request->reservation_request_id}\n";
    echo "  Status: {$request->status}\n";
    echo "  Detail Type: " . get_class($request->reservationDetail) . "\n";
    
    if ($request->reservationDetail instanceof Single) {
        echo "  Date: {$request->reservationDetail->start_date}\n";
    } elseif ($request->reservationDetail instanceof Consecutive) {
        echo "  Start Date: {$request->reservationDetail->start_date}\n";
        echo "  End Date: {$request->reservationDetail->end_date}\n";
    } elseif ($request->reservationDetail instanceof Multiple) {
        echo "  Start Date: {$request->reservationDetail->start_date}\n";
        echo "  Intermediate Date: {$request->reservationDetail->intermediate_date}\n";
        echo "  End Date: {$request->reservationDetail->end_date}\n";
    }
    echo "\n";
}

// Test the API endpoint simulation
echo "Testing API logic:\n";

$dates = [];
$daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
for ($day = 1; $day <= $daysInMonth; $day++) {
    $dates[] = sprintf('%04d-%02d-%02d', $year, $month, $day);
}

$pendingDates = [];
foreach ($dates as $date) {
    $hasPendingReservation = ReservationRequest::where('facility_id', $facilityId)
        ->where('status', 'pending')
        ->where(function($query) use ($date) {
            $query->whereHasMorph(
                'reservationDetail',
                [Single::class, Consecutive::class, Multiple::class],
                function($q, $type) use ($date) {
                    if ($type === Single::class) {
                        $q->where('start_date', $date);
                    } else {
                        $q->where('start_date', $date)
                            ->orWhere('intermediate_date', $date)
                            ->orWhere('end_date', $date);
                    }
                }
            );
        })
        ->exists();

    if ($hasPendingReservation) {
        $pendingDates[] = $date;
    }
}

echo "Pending dates found: " . implode(', ', $pendingDates) . "\n";

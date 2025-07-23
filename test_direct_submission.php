<?php

// Test script to directly submit reservation data
require_once 'vendor/autoload.php';

$url = 'http://127.0.0.1:8000/reservation';
$data = [
    '_token' => 'bypass', // We'll handle CSRF differently
    'name' => 'Test User',
    'email' => 'test@example.com',
    'organization' => 'Test Organization',
    'purpose' => 'Testing reservation system',
    'other_details' => 'This is a programmatic test of the reservation system',
    'personal_equipment' => 'no',
    'need_equipment' => 'no',
    'personal_equipment_details' => 'None',
    'reservation_type' => 'single',
    'facility_id' => '1',
    'dates' => [
        [
            'date' => '2025-07-25',
            'time_from' => '09:00',
            'time_to' => '10:00'
        ]
    ]
];

// Initialize cURL
$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_VERBOSE, true);

// Execute request
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);

curl_close($ch);

echo "HTTP Code: $httpCode\n";
echo "Error: $error\n";
echo "Response:\n$response\n";

?>

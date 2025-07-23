<?php

// Test script to simulate form submission data
$testData = [
    '_token' => 'test_token',
    'name' => 'Test User',
    'email' => 'test@example.com',
    'organization' => 'Test Organization',
    'purpose' => 'Testing the form',
    'other_details' => 'Additional test details',
    'personal_equipment' => 'no',
    'reservation_type' => 'single',
    'facility_id' => '1',
    'signature' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAA...',
    'need_equipment' => 'yes',
    'dates' => [
        [
            'date' => '2025-07-25',
            'time_from' => '09:00',
            'time_to' => '17:00'
        ]
    ],
    'equipment' => [
        [
            'equipment_id' => '1',
            'quantity' => '2',
            'date' => '2025-07-25'
        ]
    ]
];

echo "Expected form data structure:\n";
echo json_encode($testData, JSON_PRETTY_PRINT);
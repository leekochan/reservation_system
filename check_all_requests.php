<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=reservation_system', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking all reservation requests...\n";
    $stmt = $pdo->query("SELECT reservation_id, facility_id, status FROM reservation_requests ORDER BY reservation_id");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Request ID: " . $row['reservation_id'] . ", Facility: " . $row['facility_id'] . ", Status: " . $row['status'] . "\n";
    }
    
    echo "\nChecking singles records with their corresponding facility IDs...\n";
    $stmt = $pdo->query("
        SELECT s.single_id, s.reservation_id, s.start_date, s.time_from, s.time_to, 
               r.facility_id, r.status
        FROM singles s 
        LEFT JOIN reservation_requests r ON s.reservation_id = r.reservation_id 
        ORDER BY s.reservation_id
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "Single ID: " . $row['single_id'] . ", Reservation: " . $row['reservation_id'] . 
             ", Date: " . $row['start_date'] . ", Time: " . $row['time_from'] . "-" . $row['time_to'] . 
             ", Facility: " . ($row['facility_id'] ?? 'NULL') . ", Status: " . ($row['status'] ?? 'NULL') . "\n";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

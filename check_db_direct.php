<?php

// Simple database check
try {
    $pdo = new PDO('mysql:host=localhost;dbname=reservation_system', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Checking singles table structure:\n";
    $stmt = $pdo->query("DESCRIBE singles");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "\n";
    }
    
    echo "\nChecking existing records:\n";
    $stmt = $pdo->query("SELECT * FROM singles");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\nChecking all reservation requests for facility 4:\n";
    $stmt = $pdo->query("SELECT reservation_id, facility_id, status FROM reservation_requests WHERE facility_id = 4");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\nChecking consecutive for facility 4:\n";
    $stmt = $pdo->query("
        SELECT c.*, r.facility_id, r.status 
        FROM consecutive c 
        JOIN reservation_requests r ON c.reservation_id = r.reservation_id 
        WHERE r.facility_id = 4
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
    echo "\nChecking all reservations for facility 4:\n";
    $stmt = $pdo->query("
        SELECT 'single' as type, s.start_date, s.time_from, s.time_to, r.status
        FROM singles s 
        JOIN reservation_requests r ON s.reservation_id = r.reservation_id 
        WHERE r.facility_id = 4 AND r.status = 'accepted'
        UNION ALL
        SELECT 'consecutive' as type, c.start_date, c.start_time_from, c.start_time_to, r.status
        FROM consecutive c 
        JOIN reservation_requests r ON c.reservation_id = r.reservation_id 
        WHERE r.facility_id = 4 AND r.status = 'accepted'
        UNION ALL
        SELECT 'consecutive_end' as type, c.end_date, c.end_time_from, c.end_time_to, r.status
        FROM consecutive c 
        JOIN reservation_requests r ON c.reservation_id = r.reservation_id 
        WHERE r.facility_id = 4 AND r.status = 'accepted'
        ORDER BY start_date
    ");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        print_r($row);
    }
    
} catch(PDOException $e) {
    echo "Connection failed: " . $e->getMessage();
}

?>

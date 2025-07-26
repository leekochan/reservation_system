<?php

try {
    $pdo = new PDO('mysql:host=localhost;dbname=reservation_system', 'root', '');
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    echo "Removing overlapping reservation...\n";
    
    // Remove the single reservation (ID 13) that overlaps with consecutive reservation (ID 12)
    // First remove from singles table
    $stmt = $pdo->prepare("DELETE FROM singles WHERE reservation_id = ?");
    $stmt->execute([13]);
    echo "Deleted single record for reservation 13\n";
    
    // Then remove from reservation_requests table
    $stmt = $pdo->prepare("DELETE FROM reservation_requests WHERE reservation_id = ?");
    $stmt->execute([13]);
    echo "Deleted reservation request 13\n";
    
    echo "Overlap resolved! Only consecutive reservation 12 remains for July 28.\n";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}

?>

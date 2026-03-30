<?php
include('config.php');
if(isset($_GET['id'])) {
    $id = intval($_GET['id']);
    // Update checked_in to 1
    $sql = "UPDATE bookings SET checked_in = 1 WHERE booking_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);
    
    if($stmt->execute()) {
        echo "success";
    } else {
        echo "error";
    }
}
?>
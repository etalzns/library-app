<?php
require_once 'config.php';
session_start();

// ✅ Only admin can delete
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== "angelinaazka0@gmail.com") {
    echo "Access denied.";
    exit();
}

// Must have an ID
if (!isset($_GET['id'])) {
    header("Location: admin_bookings.php");
    exit();
}

$booking_id = intval($_GET['id']); // sanitize input

// Delete booking
$stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ?");
$stmt->bind_param("i", $booking_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        // Successfully deleted
        header("Location: admin_bookings.php?status=deleted");
        exit();
    } else {
        echo "No booking found with ID: $booking_id";
    }
} else {
    echo "Error deleting booking: " . $conn->error;
}
$stmt->close();
<?php
require_once 'config.php';
session_start();

// 1. Safety check: must be logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

$user_email = $_SESSION['user_email'];

if (isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);

    // 2. DELETE ONLY if the booking_id AND user_email match.
    // This prevents User A from deleting User B's booking via URL manipulation.
    $stmt = $conn->prepare("DELETE FROM bookings WHERE booking_id = ? AND user_email = ?");
    $stmt->bind_param("is", $booking_id, $user_email);

    if ($stmt->execute()) {
        // Redirect back to bookings page after successful deletion
        header("Location: bookings.php?status=deleted");
    } else {
        echo "Error deleting booking: " . $conn->error;
    }
    $stmt->close();
} else {
    header("Location: bookings.php");
}
exit;
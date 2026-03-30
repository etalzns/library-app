<?php
include('config.php');

if (!isset($_GET['booking_id'])) {
    die("Invalid QR");
}

$id = $_GET['booking_id'];

// Get booking info
$sql = "SELECT booking_date, start_time, end_time, checked_in 
        FROM bookings WHERE booking_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Booking not found");
}

$row = $result->fetch_assoc();

// Convert to timestamps
$currentTime = time();
$startTime = strtotime($row['booking_date'] . ' ' . $row['start_time']);
$endTime = strtotime($row['booking_date'] . ' ' . $row['end_time']);

// Allow 15 mins before start
$validStart = $startTime - (10 * 60);

// 🚫 Already checked in
if ((int)$row['checked_in'] === 1) {
    die("⚠️ Already checked in.");
}

// ❌ Too early
if ($currentTime < $validStart) {
    die("⏳ QR not active yet. Come back 15 mins before your slot.");
}

// ❌ Expired
if ($currentTime > $endTime) {
    die("❌ QR expired. Booking time is over.");
}

// ✅ VALID → CHECK IN
$update = "UPDATE bookings SET checked_in = 1 WHERE booking_id = ?";
$stmt2 = $conn->prepare($update);
$stmt2->bind_param("i", $id);
$stmt2->execute();

echo "success";
?>
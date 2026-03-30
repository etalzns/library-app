<?php
include('config.php');

$selected_date = $_GET['date'];
$selected_time = $_GET['time'];
$times = explode(" - ", $selected_time);
$check_start = date("H:i:s", strtotime($times[0]));

$rooms = ['Room A1','Room A2','Room A3','Room A4','Room A5','Room A6','Room A7','Room A8','Room 9','Room 10','Room 11','Room 13'];
$response = [];

foreach($rooms as $room_name) {
    $sql = "SELECT checked_in FROM bookings WHERE room_number = ? AND booking_date = ? AND start_time = ? AND status='confirmed'";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sss", $room_name, $selected_date, $check_start);
    $stmt->execute();
    $res = $stmt->get_result();
    $row = $res->fetch_assoc();

    $status = 'spot-available'; // Default Green

    if($row) {
        $startTime = strtotime($selected_date . ' ' . $check_start);
        $noShowDeadline = $startTime + (15 * 60);

        if((int)$row['checked_in'] === 1) {
            $status = 'spot-occupied'; // RED
        } elseif (time() > $noShowDeadline) {
            $status = 'spot-available'; // RELEASED (Turns green after 15m no-show)
        } else {
            $status = 'spot-booked'; // YELLOW
        }
    }

    $response[] = ["id" => str_replace(' ', '', $room_name), "status" => $status];
}

header('Content-Type: application/json');
echo json_encode($response);
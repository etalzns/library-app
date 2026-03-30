<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit();
}

$user_email = $_SESSION['user_email'];
$user_name  = $_SESSION['user_name'];

// Set timezone
date_default_timezone_set("Asia/Singapore");
$selected_date = date('Y-m-d');

// Current time in minutes for filtering slots
$currentHour = (int)date("H");
$currentMinute = (int)date("i");
$currentTimeMinutes = ($currentHour * 60) + $currentMinute;

// Define all available slots
$allSlots = [
    ["start"=>"08:30","end"=>"09:00"], ["start"=>"09:00","end"=>"10:00"],
    ["start"=>"10:00","end"=>"11:00"], ["start"=>"11:00","end"=>"12:00"],
    ["start"=>"12:00","end"=>"13:00"], ["start"=>"13:00","end"=>"14:00"],
    ["start"=>"14:00","end"=>"15:00"], ["start"=>"15:00","end"=>"16:00"],
    ["start"=>"16:00","end"=>"17:00"], ["start"=>"17:00","end"=>"17:30"]
];

// Filter slots based on current time
$slots = [];
foreach($allSlots as $slot){
    $endParts = explode(":", $slot['end']);
    $endMinutes = ((int)$endParts[0]*60) + (int)$endParts[1];
    if($endMinutes > $currentTimeMinutes){
        $slots[] = date("g:i A", strtotime($slot['start'])) . " - " . date("g:i A", strtotime($slot['end']));
    }
}

// Selected slot from POST or default
if(isset($_POST['time_slot'])){
    $posted_slot = $_POST['time_slot'];
    $selected_time = in_array($posted_slot,$slots) ? $posted_slot : ($slots[0] ?? "");
} else {
    $selected_time = $slots[0] ?? "";
}

$times = explode(" - ", $selected_time);
$check_start = isset($times[0]) ? date("H:i:s", strtotime($times[0])) : "";

// Fetch already booked rooms for this slot
$booked_room_names = [];
if($check_start){
    $check_sql = "SELECT room_number FROM bookings WHERE booking_date=? AND start_time=? AND status='confirmed'";
    $check_stmt = $conn->prepare($check_sql);
    $check_stmt->bind_param("ss",$selected_date,$check_start);
    $check_stmt->execute();
    $result = $check_stmt->get_result();
    while($row = $result->fetch_assoc()){ $booked_room_names[] = $row['room_number']; }
}

// Fetch all rooms from DB
$rooms_query = $conn->query("SELECT * FROM rooms ORDER BY 
    CASE WHEN room_name LIKE 'Room A%' THEN 1 ELSE 2 END, 
    LENGTH(room_name), room_name ASC");

$message = "";

// Handle form submission
if($_SERVER['REQUEST_METHOD']=="POST" && isset($_POST['final_booking'])){
    $selected_room = $_POST['selected_room'] ?? "";
    $purpose = $_POST['purpose'] ?? "";
    $end_time = date("H:i:s", strtotime($times[1]));

    if(empty($selected_room)){
        $message = "Please select a room from the list.";
    } elseif(empty($purpose)) {
        $message = "Please select your purpose of usage.";
    } else {
        $sql = "INSERT INTO bookings (user_email,user_name,room_number,booking_date,start_time,end_time,status,checked_in,purpose)
                VALUES (?,?,?,?,?,?, 'confirmed', 0, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssssss",$user_email,$user_name,$selected_room,$selected_date,$check_start,$end_time, $purpose);
        
        if($stmt->execute()){
            // Get last inserted booking ID
            $booking_id = $conn->insert_id;
            // Redirect to confirmation page with booking ID
            header("Location: admin_confirm.php?id=$booking_id");
            exit();
        } else {
            $message = "Failed to book. Please try again.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Book Room - Library@West</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
    *{box-sizing:border-box;font-family:'Segoe UI',sans-serif;}
    body{margin:0;background:#ddd;display:flex;justify-content:center;padding:20px;}
    .phone-container{width:360px;height:740px;background:#f8f9fa;position:relative;overflow-y:auto;box-shadow:0 0 20px rgba(0,0,0,0.1);display:flex;flex-direction:column;border-radius:20px;}
    .top-bar{background:#1a3a5f;color:#fff;padding:20px;display:flex;align-items:center;gap:15px;position:sticky;top:0;z-index:100;}
    .content{padding:15px;padding-bottom:80px;}
    
    .room-card{background:#fff;border-radius:10px;padding:12px 15px;margin-bottom:10px;display:flex;justify-content:space-between;align-items:center;box-shadow:0 2px 5px rgba(0,0,0,0.05);cursor:pointer;border:2px solid transparent;transition:0.2s;}
    .room-card.selected{border:2px solid #1a3a5f;background:#e3f2fd;}
    
    .room-card.is-deactivated { background: rgba(217, 48, 37, 0.08); border: 1px dashed #d93025; cursor: not-allowed; }
    .room-card.is-booked { background: #fff3cd; cursor: not-allowed; opacity: 0.8; }

    .room-info h5{margin:0;font-size:14px;color:#333;}
    .room-info p{margin:2px 0 0;font-size:11px;color:#777;}
    .deactivated-reason { font-size: 10px; color: #d93025; font-weight: bold; margin-top: 4px; display: block;}

    .badge{font-size:10px;padding:2px 8px;border-radius:4px;color:white;font-weight:bold;}
    .badge.available{background:#28a745;}
    .badge.full{background: #ffa000;}
    .badge.off{background: #d93025;}

    .input-label{font-size:13px;font-weight:bold;margin:15px 0 5px;display:block;color:#1a3a5f;}
    select{width:100%;padding:12px;border:1px solid #ddd;border-radius:8px;font-size:14px;background:#fff;margin-bottom:10px;}
    .confirm-btn{width:100%;background:#1a3a5f;color:#fff;border:none;padding:15px;border-radius:8px;font-weight:bold;margin-top:20px;cursor:pointer;}
</style>
</head>
<body>

<div class="phone-container">
    <div class="top-bar">
        <i class="fa-solid fa-arrow-left" style="cursor:pointer;" onclick="window.location.href='admin_dashboard.php'"></i>
        <span>Book Study Room</span>
    </div>

    <form  class="content" method="POST">
        <?php if($message!="") echo "<div style='background:#ffdada;color:#a30000;padding:10px;border-radius:5px;font-size:12px;margin-bottom:15px; text-align:center;'>$message</div>"; ?>
        
        <label class="input-label">Date</label>
        <p><strong><?php echo date('d M Y'); ?></strong></p>

        <label class="input-label">Select Time Slot</label>
        <select name="time_slot" onchange="this.form.submit()">
            <?php foreach($slots as $slot): ?>
                <option value="<?= $slot ?>" <?= ($selected_time==$slot)?"selected":"" ?>><?= $slot ?></option>
            <?php endforeach; ?>
        </select>

        <h4 style="margin: 20px 0 10px; color: #1a3a5f;">Select Room</h4>
        <input type="hidden" name="selected_room" id="room_input" required>

        <?php while($room = $rooms_query->fetch_assoc()):
            $room_name = $room['room_name'];
            $is_booked = in_array($room_name, $booked_room_names);
            $is_deactivated = ($room['status'] === 'unavailable');

            $card_class = "";
            $onclick = "selectRoom('$room_name', this)";

            if($is_deactivated) {
                $card_class = "is-deactivated";
                $onclick = "return false;";
            } elseif($is_booked) {
                $card_class = "is-booked";
                $onclick = "return false;";
            }
        ?>
            <div class="room-card <?= $card_class ?>" onclick="<?= $onclick ?>">
                <div class="room-info">
                    <h5><?= htmlspecialchars($room_name) ?></h5>
                    <p>Capacity: <?= (strpos($room_name, 'A8') !== false || strpos($room_name, '11') !== false || strpos($room_name, '13') !== false) ? '8 pax' : '4 pax'; ?></p>
                    <?php if($is_deactivated): ?>
                        <span class="deactivated-reason"><i class="fa-solid fa-triangle-exclamation"></i> Reason: <?= htmlspecialchars($room['deactivation_reason']) ?></span>
                    <?php endif; ?>
                </div>

                <?php if($is_deactivated): ?>
                    <span class="badge off">UNAVAILABLE</span>
                <?php elseif($is_booked): ?>
                    <span class="badge full">BOOKED</span>
                <?php else: ?>
                    <span class="badge available">AVAILABLE</span>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>

        <label class="input-label" style="margin-top:20px;">Purpose of Usage</label>
        <select name="purpose" required>
            <option value="">~ Select Purpose ~</option>
            <option value="Discussion/Project">Discussion / Project</option>
            <option value="Interview">Interview</option>
            <option value="Group Study">Group Study</option>
        </select>

        <button type="submit" name="final_booking" class="confirm-btn">Confirm Booking</button>
    </form>
</div>

<script>
function selectRoom(roomName, element){
    document.getElementById('room_input').value = roomName;
    document.querySelectorAll('.room-card').forEach(c=>c.classList.remove('selected'));
    element.classList.add('selected');
}
</script>

</body>
</html>
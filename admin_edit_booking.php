<?php
require_once 'config.php';
session_start();

// Safety check
if (!isset($_GET['id'])) {
    header("Location: bookings.php");
    exit();
}

$id = intval($_GET['id']);

// Get booking
$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$booking = $stmt->get_result()->fetch_assoc();

// Rooms
$rooms = [
    "Room A1","Room A2","Room A3","Room A4","Room A5","Room A6",
    "Room A7","Room A8","Room 9","Room 10","Room 11","Room 13"
];

// All time slots
$all_time_slots = [
    "08:30:00" => "08:30 AM - 09:00 AM",
    "09:00:00" => "09:00 AM - 10:00 AM",
    "10:00:00" => "10:00 AM - 11:00 AM",
    "11:00:00" => "11:00 AM - 12:00 PM",
    "12:00:00" => "12:00 PM - 01:00 PM",
    "13:00:00" => "01:00 PM - 02:00 PM",
    "14:00:00" => "02:00 PM - 03:00 PM",
    "15:00:00" => "03:00 PM - 04:00 PM",
    "16:00:00" => "04:00 PM - 05:00 PM",
    "17:00:00" => "05:00 PM - 05:30 PM"
];

// Filter slots from current time onwards
date_default_timezone_set("Asia/Singapore");
$current_time = date("H:i:s");
$time_slots = [];
foreach ($all_time_slots as $key => $label) {
    if ($key >= date("H:00:00")) { // starts from current hour
        $time_slots[$key] = $label;
    }
}

// Handle update
if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $new_room = $_POST['room_number'];
    $new_date = date('Y-m-d'); // force today
    $new_start = $_POST['start_time'];

    // Auto calculate end time
    $new_end = ($new_start == "17:00:00") ? "17:30:00" : date('H:i:s', strtotime($new_start . ' +1 hour'));

    // Update booking
    $update_stmt = $conn->prepare("
        UPDATE bookings 
        SET room_number = ?, booking_date = ?, start_time = ?, end_time = ? 
        WHERE booking_id = ?
    ");
    $update_stmt->bind_param("ssssi", $new_room, $new_date, $new_start, $new_end, $id);

    if ($update_stmt->execute()) {

        // --- SEND EMAIL IMMEDIATELY ---
        sendBookingEmail($conn, $id, $_SESSION['user_email']);

        // Redirect to confirmation page
        header("Location: confirm.php?id=$id");
        exit();
    }
}

// Function to send email (same as your previous one)
function sendBookingEmail($conn, $booking_id, $student_email) {
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
    $stmt->execute();
    $booking = $stmt->get_result()->fetch_assoc();
    if (!$booking) return;

    $current_id   = $booking['booking_id']; 
    $display_id   = "LIB-" . $current_id; 
    $room_number  = $booking['room_number']; 
    $booking_date = date("d F Y", strtotime($booking['booking_date'])); 
    $start = date("g:i A", strtotime($booking['start_time']));
    $end   = date("g:i A", strtotime($booking['end_time']));
    $booking_time = $start . " - " . $end;

    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($display_id);

    // PHPMailer
    require 'PHPMailer/src/Exception.php';
    require 'PHPMailer/src/PHPMailer.php';
    require 'PHPMailer/src/SMTP.php';
    $mail = new PHPMailer\PHPMailer\PHPMailer(true);

    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'angelinaazka0@gmail.com';
        $mail->Password   = 'eiwzxgnhdzrxegdz';
        $mail->SMTPSecure = PHPMailer\PHPMailer\PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('angelinaazka0@gmail.com', 'ITE Library Bot');
        $mail->addAddress($student_email);

        $qr_content = file_get_contents($qr_url);
        if ($qr_content) {
            $mail->addStringAttachment($qr_content, 'Your_Booking_QR.png');
        }

        $mail->isHTML(true);
        $mail->Subject = "Booking Confirmed - $room_number";
        $mail->Body    = "
            <div style='font-family: sans-serif; line-height: 1.6; color: #333;'>
                <h2 style='color: #1a3a5f;'>Your Booking is Confirmed!</h2>
                <p><strong>Room:</strong> $room_number</p>
                <p><strong>Date:</strong> $booking_date</p>
                <p><strong>Time:</strong> $booking_time</p>
                <p>Please show the <strong>attached QR code</strong> at the library entrance.</p>
            </div>
        ";

        $mail->send();
    } catch (Exception $e) {
        error_log("Mailer Error: " . $mail->ErrorInfo);
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Edit Booking</title>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<style>
body { background-color: #f4f6f8; padding: 20px; font-family: sans-serif; }
.edit-container { max-width: 400px; margin: 50px auto; background: white; padding: 30px; border-radius: 15px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
.btn-navy { background: #1a375f; color: white; width: 100%; font-weight: bold; padding: 10px; }
.btn-navy:hover { background: #122846; color: white; }
.form-label { color: #555; }
</style>
</head>
<body>

<div class="edit-container">
<h4 class="fw-bold mb-4 text-center">Edit Booking</h4>

<form method="POST">
<div class="mb-3">
<label class="form-label small fw-bold">Select Room</label>
<select name="room_number" class="form-select" required>
<?php foreach ($rooms as $r): ?>
<option value="<?php echo $r; ?>" <?php echo ($booking['room_number'] == $r) ? 'selected' : ''; ?>>
<?php echo $r; ?>
</option>
<?php endforeach; ?>
</select>
</div>

<div class="mb-3">
<label class="form-label small fw-bold">Booking Date</label>
<input type="text" class="form-control" value="<?php echo date('d M Y'); ?>" readonly>
</div>

<div class="mb-3">
<label class="form-label small fw-bold">Time Slot</label>
<select name="start_time" class="form-select" required>
<?php foreach ($time_slots as $value => $label): ?>
<option value="<?php echo $value; ?>" <?php echo ($booking['start_time'] == $value) ? 'selected' : ''; ?>>
<?php echo $label; ?>
</option>
<?php endforeach; ?>
</select>
</div>

<button type="submit" class="btn btn-navy mt-3">Save Changes</button>
<a href="admin_bookings.php" class="btn btn-link w-100 mt-2 text-muted text-decoration-none text-center">Cancel</a>

</form>
</div>
</body>
</html>
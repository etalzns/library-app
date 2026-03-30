<?php
require_once 'config.php';

// Load PHPMailer classes
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

session_start();

// 1. Get the student's email from the session
$student_email = isset($_SESSION['user_email']) ? $_SESSION['user_email'] : "";

// 2. Fetch Booking Details
if (isset($_GET['id'])) {
    $booking_id = intval($_GET['id']);
    $stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id = ?");
    $stmt->bind_param("i", $booking_id);
} else {
    $stmt = $conn->prepare("SELECT * FROM bookings ORDER BY booking_id DESC LIMIT 1");
}

$stmt->execute();
$result = $stmt->get_result();

if ($result && $result->num_rows > 0) {
    $booking = $result->fetch_assoc();
    $current_id   = $booking['booking_id']; 
    $display_id   = "LIB-" . $current_id; 
    $room_number  = $booking['room_number']; 
    $booking_date = date("d F Y", strtotime($booking['booking_date'])); 
    $start = date("g:i A", strtotime($booking['start_time']));
    $end   = date("g:i A", strtotime($booking['end_time']));
    $booking_time = $start . " - " . $end;

    $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=" . urlencode($display_id);

    // 3. AUTOMATIC EMAIL LOGIC
    if (!empty($student_email) && !isset($_SESSION['email_sent_' . $current_id])) {
        $mail = new PHPMailer(true);
        try {
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'angelinaazka0@gmail.com';
            $mail->Password   = 'eiwzxgnhdzrxegdz'; // Reminder: Secure this in config.php later!
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
            $mail->Port       = 587;

            $mail->setFrom('angelinaazka0@gmail.com', 'ITE Library Bot');
            $mail->addAddress($student_email);
            
            // Fetch QR and attach
            $qr_content = file_get_contents($qr_url);
            if($qr_content) {
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
            $_SESSION['email_sent_' . $current_id] = true;
        } catch (Exception $e) {
            error_log("Mailer Error: " . $mail->ErrorInfo);
        }
    }
} else {
    die("Booking record not found.");
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Confirmed</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --primary-blue: #1a3a5f; --success-green: #28a745; --edit-orange: #f39c12; }
        body { font-family: 'Segoe UI', sans-serif; background-color: #ddd; display: flex; justify-content: center; margin: 0; padding: 20px; }
        .phone-container { width: 360px; height: 740px; background: #f8f9fa; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(0,0,0,0.3); position: relative; }
        
        .header { background: var(--primary-blue); color: white; padding: 20px; display: flex; align-items: center; }
        .header a { color: white; text-decoration: none; }
        
        .status-header { text-align: center; padding: 20px 0; font-weight: bold; }
        .status-header i { color: var(--success-green); font-size: 2.5rem; display: block; margin-bottom: 10px; }
        
        .card { background: white; margin: 0 20px; padding: 25px; border-radius: 15px; text-align: center; box-shadow: 0 4px 15px rgba(0,0,0,0.05); }
        .qr-img { width: 160px; height: 160px; margin: 15px auto; display: block; border: 1px solid #eee; padding: 5px; border-radius: 10px;}
        
        .check-email-text { margin-bottom: 20px; font-size: 0.9rem; color: #1a3a5f; }

        .btn-group { display: flex; flex-direction: column; gap: 10px; margin-top: 10px; }
        .action-btn { 
            display: block; 
            text-decoration: none; 
            padding: 12px; 
            border-radius: 8px; 
            font-weight: bold; 
            font-size: 0.95rem;
            transition: 0.3s;
        }
        .btn-edit { background: white; color: var(--edit-orange); border: 2px solid var(--edit-orange); }
        .btn-edit:hover { background: var(--edit-orange); color: white; }
        
        .redirect-footer { margin-top: 20px; font-size: 0.8rem; color: #888; border-top: 1px solid #eee; padding-top: 10px; }
    </style>
</head>
<body>

<div class="phone-container">
    <div class="header">
        <a href="dashboard.php"><i class="fas fa-arrow-left"></i></a>
        <span style="margin-left:15px">Confirmation</span>
    </div>

    <div class="status-header">
        <i class="fas fa-check-circle"></i>
        Booking Confirmed
    </div>

    <div class="card">
        <div style="font-size:1.3rem; font-weight:bold; color: #1a3a5f;">
            <?php echo htmlspecialchars($room_number); ?>
        </div>

        <div style="color:#666; margin-top:5px; font-size: 1rem;">
            <?php echo $booking_date; ?><br>
            <strong><?php echo htmlspecialchars($booking_time); ?></strong>
        </div>

        <img src="<?php echo $qr_url; ?>" class="qr-img" alt="QR Code">

        <div class="check-email-text">
            <strong>Email sent to:</strong><br>
            <span style="font-size: 0.8rem; color: #777;"><?php echo htmlspecialchars($student_email); ?></span>
        </div>

        <div class="btn-group">
            <a href="edit_booking.php?id=<?php echo $current_id; ?>" class="action-btn btn-edit">
                <i class="fas fa-edit"></i> Make Changes
            </a>
        </div>

        <div class="redirect-footer">
            Redirecting to Dashboard in <span id="countdown">5</span>s...
        </div>
    </div>
</div>


<script>
const countdownElement = document.getElementById('countdown');
const countdownSeconds = 5;
const endTime = Date.now() + countdownSeconds * 1000;

const timer = setInterval(() => {
    const remaining = Math.ceil((endTime - Date.now()) / 1000);
    countdownElement.textContent = remaining > 0 ? remaining : 0;

    if (remaining <= 0) {
        clearInterval(timer);
        window.location.href = 'dashboard.php';
    }
}, 200); // check more often to prevent drift
</script>

</body>
</html>
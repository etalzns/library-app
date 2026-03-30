<?php
require_once 'config.php';
session_start();

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';

if (!isset($_GET['id'])) exit;

$booking_id = intval($_GET['id']);
$email = $_SESSION['user_email'];

$stmt = $conn->prepare("SELECT * FROM bookings WHERE booking_id=?");
$stmt->bind_param("i", $booking_id);
$stmt->execute();
$result = $stmt->get_result();
$booking = $result->fetch_assoc();

$qr = "https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=LIB-$booking_id";

$mail = new PHPMailer(true);

try {
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = 'your_email@gmail.com';
    $mail->Password = 'your_app_password';
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;

    $mail->setFrom('your_email@gmail.com', 'Library');
    $mail->addAddress($email);

    $mail->addStringAttachment(file_get_contents($qr), 'QR.png');

    $mail->isHTML(true);
    $mail->Subject = "Booking Confirmed";
    $mail->Body = "Your booking is confirmed.";

    $mail->send();

} catch (Exception $e) {}
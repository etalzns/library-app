<?php
require_once 'config.php';
session_start();

// 🔒 Protect page
if (!isset($_SESSION['user_email']) || !isset($_SESSION['user_name'])) {
    header("Location: login.php");
    exit;
}

// 👤 Session data
$user_name = $_SESSION['user_name'];
$user_email = $_SESSION['user_email'];

// 🧠 Simple admin check (change email if needed)
$is_admin = ($user_email === "admin@ite.edu.sg");

date_default_timezone_set("Asia/Singapore");
$current_date = date('Y-m-d');

// ✅ FIXED: Fetch ONLY this user's next booking
$sql = "SELECT room_number, booking_date, start_time 
        FROM bookings 
        WHERE user_email = ? AND booking_date >= ?
        ORDER BY booking_date ASC, start_time ASC 
        LIMIT 1";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ss", $user_email, $current_date);
$stmt->execute();
$result = $stmt->get_result();
$next_booking = $result->fetch_assoc();

// 🌅 Greeting logic
$hour = date("H");

if ($hour < 12) {
    $greeting_text = "Good Morning";
} elseif ($hour < 18) {
    $greeting_text = "Good Afternoon";
} else {
    $greeting_text = "Good Evening";
}

$full_greeting = $greeting_text . ", " . htmlspecialchars($user_name);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Homepage - SSRMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        * {
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            margin: 0;
            background-color: #f0f2f5;
            display: flex;
            justify-content: center;
        }

        .phone-container {
            width: 360px;
            height: 740px;
            background-color: #f8f9fa;
            position: relative;
            overflow-y: auto;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding-bottom: 70px;
        }

        .greeting {
            padding: 30px 20px;
            background: white;
            border-bottom: 1px solid #eee;
            text-align: center;
        }

        .greeting h2 {
            margin: 0;
            font-size: 22px;
            color: #1a3a5f;
            font-weight: 800;
        }

        .greeting p {
            margin: 10px 0 0;
            font-size: 16px;
            color: #666;
        }

        .action-grid {
            display: grid;
            gap: 20px;
            padding: 20px;
        }

        .card {
            border-radius: 15px;
            padding: 30px;
            text-align: center;
            color: white;
            cursor: pointer;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            min-height: 140px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            transition: transform 0.2s;
        }

        .card:active {
            transform: scale(0.98);
        }

        .blue { background-color: #215ba1; }
        .green { background-color: #3d9e0d; }
        .purple { background-color: #8e44ad; }

        .card i {
            font-size: 45px;
            margin-bottom: 12px;
        }

        .card h3 {
            font-size: 18px;
            margin: 5px 0;
        }

        .card p {
            font-size: 13px;
            margin: 0;
            opacity: 0.9;
        }

        .next-booking {
            background: white;
            margin: 0 20px;
            padding: 15px;
            border-radius: 10px;
            margin-top: 10px;
            text-align: center;
            font-size: 14px;
            color: #333;
        }

        .bottom-nav {
            position: absolute;
            bottom: 0;
            width: 100%;
            background: white;
            display: flex;
            justify-content: space-around;
            padding: 12px 0;
            border-top: 1px solid #ddd;
        }

        .nav-item {
            text-align: center;
            color: #1a3a5f;
            font-size: 12px;
            text-decoration: none;
            flex: 1;
        }

        .nav-item i {
            font-size: 20px;
            display: block;
            margin-bottom: 4px;
        }

        .nav-item:hover {
            color: #215ba1;
        }
    </style>
</head>

<body>

<div class="phone-container">

    <div class="greeting">
        <h2>Library@West</h2>
        <p><?php echo $full_greeting; ?></p>
    </div>


    <div class="action-grid">

        <a href="rules.php" style="text-decoration: none;">
            <div class="card blue">
                <i class="fa-solid fa-users"></i>
                <h3>Make Booking</h3>
                <p>For Group Discussions</p>
            </div>
        </a>

        <a href="bookings.php" style="text-decoration: none;">
            <div class="card green">
                <i class="fa-solid fa-calendar-days"></i>
                <h3>My Bookings</h3>
                <p>Check your current status</p>
            </div>
        </a>

        <!-- 🔥 ADMIN ONLY -->
        <?php if ($is_admin): ?>
            <a href="admin.php" style="text-decoration: none;">
                <div class="card purple">
                    <i class="fa-solid fa-user-shield"></i>
                    <h3>Admin Panel</h3>
                    <p>Manage all bookings</p>
                </div>
            </a>
        <?php endif; ?>

    </div>

    <div class="bottom-nav">
        <a href="dashboard.php" class="nav-item"><i class="fa-solid fa-house"></i>Home</a>
        <a href="rules.php" class="nav-item"><i class="fa-solid fa-book-open"></i>Book</a>
        <a href="bookings.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i>Bookings</a>
        <a href="logout.php" class="nav-item" style="color: #d93025;">
            <i class="fa-solid fa-right-from-bracket"></i>Exit
        </a>
    </div>

</div>

</body>
</html>
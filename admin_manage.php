<?php
session_start();
require_once 'config.php';

// 1. Initialize the variable with a default value
$full_greeting = "Welcome, Admin"; 

// 2. Your existing logic to refine the greeting
if (isset($_SESSION['user_name'])) {
    $user_name = $_SESSION['user_name'];
    date_default_timezone_set("Asia/Singapore");
    $hour = date("H");

    if ($hour < 12) {
        $greeting_text = "Good Morning";
    } elseif ($hour < 18) {
        $greeting_text = "Good Afternoon";
    } else {
        $greeting_text = "Good Evening";
    }

    $full_greeting = $greeting_text . ", " . htmlspecialchars($user_name);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - SSRMS</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * { box-sizing: border-box; font-family: 'Segoe UI', sans-serif; }

        :root {
            --admin-navy: #1a375f;
            --admin-teal: #006d77;
            --bg-light: #f4f6f8;
            --active-blue: #215ba1; /* Brighter blue for the bottom nav */
        }

        body {
            margin: 0; background-color: #f0f2f5;
            display: flex; justify-content: center;
        }

        .phone-container {
            width: 360px; height: 740px;
            background-color: var(--bg-light);
            position: relative; overflow-y: auto;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.1);
            padding-bottom: 70px;
        }

        .greeting {
            padding: 40px 20px; background: white;
            border-bottom: 1px solid #eee; text-align: center;
        }

        .greeting h2 { margin: 0; font-size: 22px; color: var(--admin-navy); font-weight: 800; }
        .greeting p { margin: 10px 0 0; font-size: 15px; color: #666; }

        .action-grid { display: grid; grid-template-columns: 1fr; gap: 15px; padding: 20px; }

        .card {
            border-radius: 16px; padding: 25px; text-decoration: none;
            color: white; display: flex; align-items: center; gap: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08); transition: all 0.2s ease;
        }

        .card:active { transform: scale(0.97); }
        .card.users { background: linear-gradient(135deg, #1a375f 0%, #2c5282 100%); }
        .card.rooms { background: linear-gradient(135deg, #006d77 0%, #00818a 100%); }
        .card i { font-size: 32px; width: 40px; text-align: center; }
        .card-text h3 { margin: 0; font-size: 18px; font-weight: 600; }
        .card-text p { margin: 4px 0 0; font-size: 13px; opacity: 0.85; }

        /* --- Updated Bottom Nav Colors --- */
        .bottom-nav {
            position: absolute; bottom: 0; width: 100%;
            background: white; display: flex;
            justify-content: space-around; padding: 12px 0;
            border-top: 1px solid #eee;
        }

        .nav-item {
            text-align: center;
            color: #7f8c8d; /* Grey for inactive items */
            font-size: 11px;
            text-decoration: none;
            flex: 1;
            transition: color 0.3s;
        }

        .nav-item i { font-size: 18px; display: block; margin-bottom: 4px; }

        /* The blue color you requested for the active button */
        .nav-item.active {
            color: var(--active-blue); 
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="phone-container">
        <div class="greeting">
            <h2>Manage Menu</h2>
            <p><?php echo $full_greeting; ?></p>
        </div>

        <div class="action-grid">
            <a href="admin_manage_users.php" class="card users">
                <i class="fa-solid fa-user-group"></i>
                <div class="card-text">
                    <h3>Manage Users</h3>
                    <p>Edit name, email, and permissions</p>
                </div>
            </a>

            <a href="admin_manage_rooms.php" class="card rooms">
                <i class="fa-solid fa-door-open"></i>
                <div class="card-text">
                    <h3>Manage Rooms</h3>
                    <p>Add, remove, or update facilities</p>
                </div>
            </a>
        </div>

        <div class="bottom-nav">
            <a href="admin_dashboard.php" class="nav-item"><i class="fa-solid fa-house"></i>Home</a>
            <a href="admin_rules.php" class="nav-item"><i class="fa-solid fa-book-open"></i>Book</a>
            <a href="admin_bookings.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i>Bookings</a>
            <a href="admin_manage.php" class="nav-item active"><i class="fa-solid fa-user-gear"></i>Manage</a>
            <a href="logout.php" class="nav-item" style="color: #d93025;"><i class="fa-solid fa-right-from-bracket"></i>Exit</a>
        </div>
    </div>
</body>
</html>
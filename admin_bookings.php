<?php
session_start();
require_once 'config.php';

// 🔒 Protect admin page
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit();
}

// OPTIONAL: Only allow admin email
if ($_SESSION['user_email'] !== "angelinaazka0@gmail.com") {
    echo "Access denied.";
    exit();
}

// Fetch bookings
$query = "SELECT booking_id, user_email, room_number, booking_date, start_time, end_time, checked_in, check_in_time 
          FROM bookings 
          ORDER BY booking_date ASC, start_time ASC";

$result = $conn->query($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <style>
        :root { --navy: #1a375f; --bg: #f4f6f8; }
        body { background-color: var(--bg); font-family: 'Segoe UI', sans-serif; padding-bottom: 80px; margin: 0; }
        .header { background-color: var(--navy); color: white; padding: 15px; position: sticky; top: 0; z-index: 1000; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        .booking-card { border: none; border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); margin-bottom: 15px; background: white; transition: transform 0.2s; }
        .expired-overlay { opacity: 0.6; background-color: #f8f9fa; }
        .bottom-nav { position: fixed; bottom: 0; left: 0; right: 0; background: white; display: flex; justify-content: space-around; padding: 10px 0; border-top: 1px solid #dee2e6; z-index: 1000; }
        .nav-item { color: var(--navy); text-decoration: none; font-size: 0.75rem; text-align: center; flex: 1; }
        .nav-item i { font-size: 1.4rem; display: block; margin-bottom: 2px; }
        .status-badge { font-size: 0.75rem; padding: 5px 10px; border-radius: 20px; }
        .action-btns a { margin-left: 8px; }
    </style>
</head>
<body>

<div class="header d-flex justify-content-between align-items-center">
    <div class="d-flex align-items-center">
        <a href="admin_dashboard.php" class="text-white me-3"><i class="bi bi-arrow-left fs-4"></i></a>
        <h5 class="mb-0">Admin Dashboard</h5>
    </div>
    <small class="opacity-75"><?php echo htmlspecialchars($_SESSION['user_email']); ?></small>
</div>

<div class="container py-4">

    <?php if ($result && $result->num_rows > 0): ?>
        <?php while ($row = $result->fetch_assoc()): 
            $startTime = strtotime($row['booking_date'] . ' ' . $row['start_time']);
            $endTime = strtotime($row['booking_date'] . ' ' . $row['end_time']);
            $currentTime = time();
            $noShowDeadline = $startTime + (15 * 60);
            $isPast = ($currentTime > $endTime);

            if ((int)$row['checked_in'] === 1) {
                $statusText = "In Use"; $statusColor = "danger";
            } elseif ($currentTime > $endTime) {
                $statusText = "Expired"; $statusColor = "dark";
            } elseif ($currentTime > $noShowDeadline && (int)$row['checked_in'] === 0) {
                $statusText = "No Show"; $statusColor = "warning text-dark";
            } else {
                $statusText = "Confirmed"; $statusColor = "success";
            }
        ?>
            <div class="card booking-card <?php echo ($isPast) ? 'expired-overlay' : ''; ?>">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($row['room_number']); ?></h5>
                            <p class="text-primary small mb-1 fw-bold">
                                <i class="bi bi-person me-1"></i> <?php echo htmlspecialchars($row['user_email']); ?>
                            </p>
                            <p class="text-muted small mb-1">
                                <i class="bi bi-calendar3 me-1"></i> <?php echo date('j M Y', $startTime); ?>
                            </p>
                            <p class="text-muted small mb-2">
                                <i class="bi bi-clock me-1"></i> <?php echo date('g:i A', $startTime); ?> – <?php echo date('g:i A', $endTime); ?>
                            </p>
                            <span class="badge bg-<?php echo $statusColor; ?> status-badge"><?php echo $statusText; ?></span>
                        </div>

                        <div class="action-btns d-flex">
                            <a href="admin_edit_booking.php?id=<?php echo $row['booking_id']; ?>" 
                               class="btn btn-outline-warning btn-sm border-0">
                                <i class="fa-solid fa-pen-to-square"></i>
                            </a>
                            <a href="admin_delete_booking.php?id=<?php echo $row['booking_id']; ?>" 
                               class="btn btn-outline-danger btn-sm border-0" 
                               onclick="return confirm('Delete this booking?')">
                                <i class="fa-solid fa-trash-can"></i>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <div class="text-center py-5">
            <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
            <p class="text-muted mt-2">No bookings found in the system.</p>
        </div>
    <?php endif; ?>
</div>

<div class="bottom-nav">
    <a href="admin_dashboard.php" class="nav-item"><i class="fa-solid fa-house"></i>Home</a>
    <a href="admin_rules.php" class="nav-item"><i class="fa-solid fa-book-open"></i>Book</a>
    <a href="admin_bookings.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i>Bookings</a>
    <a href="admin_manage.php" class="nav-item"><i class="fa-solid fa-user-gear"></i>Manage</a>
                <a href="logout.php" class="nav-item" style="color: #d93025;"><i class="fa-solid fa-right-from-bracket"></i>Exit</a>
</div>

</body>
</html>
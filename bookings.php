<?php
require_once 'config.php';
session_start();

// 1. Protect page - Redirect if not logged in
if (!isset($_SESSION['user_email'])) {
    header("Location: login.php");
    exit;
}

// 2. Get the logged-in user's email from the session
$user_email = $_SESSION['user_email'];

// 3. Updated Query: Added WHERE user_email = ?
// We also fetch 'checked_in_at' to support the arrival time feature
$query = "SELECT booking_id, room_number, booking_date, start_time, end_time, checked_in, check_in_time 
          FROM bookings 
          WHERE user_email = ? 
          ORDER BY booking_date ASC, start_time ASC";

$stmt = $conn->prepare($query);
// 4. Bind the session email to the query for security
$stmt->bind_param("s", $user_email); 
$stmt->execute();
$result = $stmt->get_result();
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bookings</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        :root { --navy: #1a375f; --bg: #f4f6f8; }
        body { background-color: var(--bg); font-family: sans-serif; padding-bottom: 80px; }
        .header { background-color: var(--navy); color: white; padding: 15px; position: sticky; top: 0; z-index: 1000;}
        .booking-card { border-radius: 12px; box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05); margin-bottom: 20px; background: white; }
        .nav-bottom { background: white; border-top: 1px solid #dee2e6; position: fixed; bottom: 0; width: 100%; display: flex; justify-content: space-around; padding: 10px 0; }
        .nav-link { color: var(--navy); text-decoration: none; font-size: 0.75rem; text-align: center; }
        .nav-link i { font-size: 1.4rem; display: block; }
        .expired-overlay { opacity: 0.6; background-color: #f8f9fa; }
    </style>
</head>

<body>
    <div class="header d-flex align-items-center">
        <a href="dashboard.php" class="text-white me-3"><i class="bi bi-arrow-left fs-4"></i></a>
        <h5 class="mb-0">My Bookings</h5>
    </div>

    <div class="container py-4">

        <?php if (count($bookings) > 0): ?>
            <?php foreach ($bookings as $row): 
                // Logic for status badges
                $startTime = strtotime($row['booking_date'] . ' ' . $row['start_time']);
                $endTime = strtotime($row['booking_date'] . ' ' . $row['end_time']);
                $currentTime = time();
                $noShowDeadline = $startTime + (10 * 60);
                $isEditable = true;

                if ((int)$row['checked_in'] === 1) {
                    $statusText = "In Use"; $statusColor = "danger"; $isEditable = false;
                } elseif ($currentTime > $endTime) {
                    $statusText = "Expired"; $statusColor = "dark"; $isEditable = false;
                } elseif ($currentTime > $noShowDeadline && (int)$row['checked_in'] === 0) {
                    $statusText = "No Show"; $statusColor = "warning text-dark"; $isEditable = false;
                } else {
                    $statusText = "Confirmed"; $statusColor = "success";
                }
            ?>
                <div class="card booking-card <?php echo (!$isEditable) ? 'expired-overlay' : ''; ?>">
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold mb-1"><?php echo htmlspecialchars($row['room_number']); ?></h5>
                                <p class="text-muted small mb-1">
                                    <i class="bi bi-calendar3 me-1"></i> <?php echo date('j M Y', strtotime($row['booking_date'])); ?>
                                </p>
                                <p class="text-muted small mb-2">
                                    <i class="bi bi-clock me-1"></i> <?php echo date('g:i A', $startTime); ?> – <?php echo date('g:i A', $endTime); ?>
                                </p>
                                <span class="badge bg-<?php echo $statusColor; ?>"><?php echo $statusText; ?></span>
                                
                                <?php if ($row['checked_in'] == 1 && $row['checked_in_at']): ?>
                                    <div class="mt-2 small text-success fw-bold">
                                        <i class="bi bi-geo-alt-fill"></i> Arrived at <?php echo date('g:i A', strtotime($row['checked_in_at'])); ?>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <?php if ($isEditable): ?>
                                <button onclick="confirmDelete(<?php echo $row['booking_id']; ?>)" class="btn btn-outline-danger btn-sm border-0">
                                    <i class="bi bi-trash3 fs-5"></i>
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-calendar-x fs-1"></i>
                <p class="mt-2">You haven't booked any rooms yet.</p>
                <a href="rules.php" class="btn btn-primary btn-sm mt-2">Book Now</a>
            </div>
        <?php endif; ?>
    </div>

    <nav class="nav-bottom">
        <a href="dashboard.php" class="nav-link"><i class="bi bi-house-door"></i>Home</a>
        <a href="rules.php" class="nav-link"><i class="bi bi-plus-circle"></i>Book</a>
        <a href="bookings.php" class="nav-link fw-bold"><i class="bi bi-calendar-check"></i>Bookings</a>
    </nav>

    <script>
        function confirmDelete(id) {
            if (confirm("Are you sure you want to cancel this booking?")) {
                window.location.href = 'delete_booking.php?id=' + id;
            }
        }
    </script>
</body>
</html>
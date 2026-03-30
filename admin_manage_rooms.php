<?php
session_start();
require_once 'config.php';

// Only admins can access
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Room Status Toggle
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['action_type'])) {
    $room_id = $_POST['room_id'];

    if ($_POST['action_type'] === 'deactivate') {
        $reason = trim($_POST['reason']);
        $stmt = $conn->prepare("UPDATE rooms SET status = 'unavailable', deactivation_reason = ? WHERE room_id = ?");
        $stmt->bind_param("si", $reason, $room_id);
    } else { // activate
        $stmt = $conn->prepare("UPDATE rooms SET status = 'available', deactivation_reason = NULL WHERE room_id = ?");
        $stmt->bind_param("i", $room_id);
    }

    $stmt->execute();
    header("Location: admin_manage_rooms.php");
    exit();
}

// Fetch all rooms
$all_rooms = $conn->query("SELECT * FROM rooms ORDER BY room_name ASC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Rooms</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --navy: #1a375f; --red: #d93025; --green: #1e7e34; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f8; margin: 0; padding-bottom: 80px; }
        .header { background: var(--navy); color: white; padding: 15px; display: flex; align-items: center; }
        .container { padding: 20px; max-width: 800px; margin: auto; }
        .room-card { background: white; margin-bottom: 15px; padding: 20px; border-radius: 12px; display: flex; justify-content: space-between; align-items: center; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .status-badge { font-size: 11px; padding: 4px 8px; border-radius: 20px; font-weight: bold; text-transform: uppercase; }
        .status-available { background: #e6f4ea; color: var(--green); }
        .status-unavailable { background: #fce8e6; color: var(--red); }
        .btn-deactivate { background: #fff; color: var(--red); border: 1px solid var(--red); padding: 8px 15px; border-radius: 6px; cursor: pointer; }
        .btn-activate { background: var(--green); color: #fff; border: none; padding: 8px 15px; border-radius: 6px; cursor: pointer; }

        /* Modal Style */
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 25px; border-radius: 15px; width: 320px; text-align: center; }

        .bottom-nav { position: fixed; bottom: 0; width: 100%; background: white; display: flex; justify-content: space-around; padding: 12px 0; border-top: 1px solid #eee; }
        .nav-item { text-align: center; color: #888; text-decoration: none; font-size: 11px; }
    </style>
</head>
<body>

<div class="header">
    <a href="admin_manage.php" style="color:white; margin-right:15px;"><i class="fa-solid fa-arrow-left"></i></a>
    <h3 style="margin:0;">Manage Rooms</h3>
</div>

<div class="container">
    <?php while($room = $all_rooms->fetch_assoc()): ?>
        <div class="room-card">
            <div>
                <h4 style="margin:0;"><?= htmlspecialchars($room['room_name']) ?></h4>
                <span class="status-badge <?= $room['status'] === 'available' ? 'status-available' : 'status-unavailable' ?>">
                    <?= htmlspecialchars($room['status']) ?>
                </span>
                <?php if($room['status'] === 'unavailable' && !empty($room['deactivation_reason'])): ?>
                    <p style="margin: 5px 0 0; font-size: 12px; color: var(--red); font-style: italic;">
                        Reason: <?= htmlspecialchars($room['deactivation_reason']) ?>
                    </p>
                <?php endif; ?>
            </div>

            <div>
                <?php if($room['status'] === 'available'): ?>
                    <button class="btn-deactivate" onclick="openDeactivateModal(<?= $room['room_id'] ?>, '<?= addslashes($room['room_name']) ?>')">Deactivate</button>
                <?php else: ?>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="room_id" value="<?= $room['room_id'] ?>">
                        <input type="hidden" name="action_type" value="activate">
                        <button type="submit" class="btn-activate">Activate</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
    <?php endwhile; ?>
</div>

<!-- Deactivate Modal -->
<div id="deactivateModal" class="modal">
    <div class="modal-content">
        <h4 id="modalRoomName" style="margin-top:0; color:var(--navy);"></h4>
        <p style="font-size: 13px; color: #666;">Provide a reason for deactivation:</p>
        <form method="POST">
            <input type="hidden" name="room_id" id="modalRoomId">
            <input type="hidden" name="action_type" value="deactivate">
            <textarea name="reason" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:15px;" rows="3" placeholder="e.g. Under Maintenance"></textarea>
            <button type="submit" style="background: var(--red); color:white; border:none; width:100%; padding:12px; border-radius:8px; font-weight:bold; cursor:pointer;">Confirm Deactivate</button>
            <button type="button" onclick="closeModal()" style="background:none; border:none; color:#777; margin-top:10px; cursor:pointer;">Cancel</button>
        </form>
    </div>
</div>

<!-- Bottom Navigation -->
<div class="bottom-nav">
    <a href="admin_dashboard.php" class="nav-item"><i class="fa-solid fa-house"></i>Home</a>
    <a href="admin_rules.php" class="nav-item"><i class="fa-solid fa-book-open"></i>Book</a>
    <a href="admin_bookings.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i>Bookings</a>
    <a href="admin_manage.php" class="nav-item"><i class="fa-solid fa-user-gear"></i>Manage</a>
    <a href="logout.php" class="nav-item" style="color: #d93025;"><i class="fa-solid fa-right-from-bracket"></i>Exit</a>
</div>

<script>
function openDeactivateModal(id, name) {
    document.getElementById('modalRoomId').value = id;
    document.getElementById('modalRoomName').innerText = "Deactivate: " + name;
    document.getElementById('deactivateModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('deactivateModal').style.display = 'none';
}
</script>

</body>
</html>
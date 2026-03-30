<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Handle Ban/Unban POST Logic
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['action_type'])) {
    $target_id = $_POST['user_id'];
    if ($_POST['action_type'] === 'ban') {
        $reason = trim($_POST['ban_reason']);
        $stmt = $conn->prepare("UPDATE users SET is_banned = 1, ban_reason = ? WHERE id = ?");
        $stmt->bind_param("si", $reason, $target_id);
    } else {
        $stmt = $conn->prepare("UPDATE users SET is_banned = 0, ban_reason = NULL WHERE id = ?");
        $stmt->bind_param("i", $target_id);
    }
    $stmt->execute();
    header("Location: admin_manage_users.php");
    exit();
}

// Fetch Users
$search = $_GET['search'] ?? '';
$search_param = "%$search%";

// Active Users
$sql_active = "SELECT * FROM users WHERE is_banned = 0 AND (user_name LIKE ? OR user_email LIKE ?) ORDER BY user_name ASC";
$stmt_a = $conn->prepare($sql_active);
$stmt_a->bind_param("ss", $search_param, $search_param);
$stmt_a->execute();
$active_users = $stmt_a->get_result();

// Banned Users
$sql_banned = "SELECT * FROM users WHERE is_banned = 1 ORDER BY user_name ASC";
$banned_users = $conn->query($sql_banned);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users Screen (Admin)</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root { --navy: #1a375f; --red: #d93025; --green: #1e7e34; }
        body { font-family: 'Segoe UI', sans-serif; background: #f4f6f8; margin: 0; padding-bottom: 80px; }
        .header { background: var(--navy); color: white; padding: 15px; display: flex; align-items: center; }
        
        .main-container { display: flex; gap: 20px; padding: 20px; }
        .column { flex: 1; background: #fff; border-radius: 15px; padding: 15px; box-shadow: 0 4px 10px rgba(0,0,0,0.05); min-height: 70vh; }
        .column-header { border-bottom: 2px solid #f0f0f0; margin-bottom: 15px; padding-bottom: 10px; display: flex; justify-content: space-between; align-items: center; }
        
        .user-item { display: flex; justify-content: space-between; align-items: center; padding: 12px; border-bottom: 1px solid #f9f9f9; }
        .user-info strong { display: block; font-size: 14px; color: var(--navy); }
        .user-info small { color: #666; font-size: 12px; }
        
        .btn-ban { background: #fff; color: var(--red); border: 1px solid var(--red); padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px; }
        .btn-unban { background: var(--green); color: #fff; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; font-size: 11px; }
        
        .modal { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 2000; justify-content: center; align-items: center; }
        .modal-content { background: white; padding: 25px; border-radius: 15px; width: 320px; text-align: center; }
        
        .bottom-nav { position: fixed; bottom: 0; width: 100%; background: white; display: flex; justify-content: space-around; padding: 12px 0; border-top: 1px solid #eee; }
        .nav-item { text-align: center; color: #888; text-decoration: none; font-size: 11px; }
    </style>
</head>
<body>

<div class="header">
    <a href="admin_manage.php" style="color:white; margin-right:15px;"><i class="fa-solid fa-arrow-left"></i></a>
    <h3 style="margin:0;">Manage Users</h3>
</div>

<div class="main-container">
    <div class="column">
        <div class="column-header">
            <h4 style="margin:0;">Active Users</h4>
            <form method="GET" style="width: 60%;">
                <input type="text" name="search" placeholder="Search..." value="<?= htmlspecialchars($search) ?>" style="width:100%; padding:5px 10px; border-radius:15px; border:1px solid #ddd; font-size:12px;">
            </form>
        </div>
        <?php while($u = $active_users->fetch_assoc()): ?>
            <div class="user-item">
                <div class="user-info">
                    <strong><?= htmlspecialchars($u['user_name']) ?></strong>
                    <small><?= htmlspecialchars($u['user_email']) ?></small>
                </div>
                <button class="btn-ban" onclick="triggerBanModal(<?= $u['id'] ?>, '<?= addslashes($u['user_name']) ?>')">Ban User</button>
            </div>
        <?php endwhile; ?>
    </div>

    <div class="column" style="background: #fffafa;">
        <div class="column-header">
            <h4 style="margin:0; color: var(--red);">Banned List</h4>
            <span style="font-size: 12px; background: var(--red); color: white; padding: 2px 8px; border-radius: 10px;"><?= $banned_users->num_rows ?> Users</span>
        </div>
        <?php while($u = $banned_users->fetch_assoc()): ?>
            <div class="user-item">
                <div class="user-info">
                    <strong><?= htmlspecialchars($u['user_name']) ?></strong>
                    <small style="color: var(--red);">Reason: <?= htmlspecialchars($u['ban_reason']) ?></small>
                </div>
                <form method="POST">
                    <input type="hidden" name="user_id" value="<?= $u['id'] ?>">
                    <input type="hidden" name="action_type" value="unban">
                    <button type="submit" class="btn-unban">Unban</button>
                </form>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div id="banModal" class="modal">
    <div class="modal-content">
        <h4 id="modalUserName" style="margin-top:0;"></h4>
        <form method="POST">
            <input type="hidden" name="user_id" id="modalUserId">
            <input type="hidden" name="action_type" value="ban">
            <p style="font-size: 13px; text-align: left; margin-bottom: 5px;">Reason for Ban:</p>
            <textarea name="ban_reason" required style="width:100%; padding:10px; border-radius:8px; border:1px solid #ddd; margin-bottom:15px;" rows="3" placeholder="e.g. Noise Complaint"></textarea>
            <button type="submit" style="background: var(--red); color:white; border:none; width:100%; padding:10px; border-radius:8px; font-weight:bold;">Confirm Ban</button>
            <button type="button" onclick="closeModal()" style="background:none; border:none; color:#777; margin-top:10px; cursor:pointer;">Cancel</button>
        </form>
    </div>
</div>

    <div class="bottom-nav">
            <a href="admin_dashboard.php" class="nav-item"><i class="fa-solid fa-house"></i>Home</a>
            <a href="admin_rules.php" class="nav-item"><i class="fa-solid fa-book-open"></i>Book</a>
            <a href="admin_bookings.php" class="nav-item"><i class="fa-solid fa-calendar-check"></i>Bookings</a>
            <a href="admin_manage.php" class="nav-item"><i class="fa-solid fa-user-gear"></i>Manage</a>
            <a href="logout.php" class="nav-item" style="color: #d93025;"><i class="fa-solid fa-right-from-bracket"></i>Exit</a>
        </div>

<script>
function triggerBanModal(id, name) {
    document.getElementById('modalUserId').value = id;
    document.getElementById('modalUserName').innerText = "Ban: " + name;
    document.getElementById('banModal').style.display = 'flex';
}
function closeModal() {
    document.getElementById('banModal').style.display = 'none';
}
</script>
</body>
</html>
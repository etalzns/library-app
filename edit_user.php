<?php
session_start();
require_once 'config.php';

// Admin check
if (!isset($_SESSION['user_email']) || $_SESSION['user_email'] !== "admin@ite.edu.sg") {
    exit("Unauthorized");
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f6f8; padding: 20px; }
        .edit-container { max-width: 400px; margin: auto; background: white; padding: 25px; border-radius: 15px; box-shadow: 0 4px 12px rgba(0,0,0,0.1); }
    </style>
</head>
<body>
    <div class="edit-container">
        <h4 class="mb-4">Edit User Profile</h4>
        <form action="update_user.php" method="POST">
            <input type="hidden" name="user_id" value="<?php echo $id; ?>">
            
            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">FULL NAME</label>
                <input type="text" name="new_name" class="form-control" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label text-muted small fw-bold">EMAIL ADDRESS</label>
                <input type="email" name="new_email" class="form-control" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>

            <button type="submit" class="btn btn-primary w-100">Update User</button>
            <a href="admin_manage_users.php" class="btn btn-light w-100 mt-2">Cancel</a>
        </form>
    </div>
</body>
</html>
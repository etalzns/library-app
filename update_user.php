<?php
session_start();
require_once 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user_id = $_POST['user_id'];
    $new_name = trim($_POST['new_name']);
    $new_email = trim($_POST['new_email']);

    // 1. Basic Validation
    if (empty($new_name) || empty($new_email)) {
        header("Location: admin_manage_users.php?error=empty_fields");
        exit();
    }

    // 2. Prepare the Update Query
    $sql = "UPDATE users SET name = ?, email = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssi", $new_name, $new_email, $user_id);

    // 3. Execute and Redirect
    if ($stmt->execute()) {
        // Success: Go back to management list
        header("Location: admin_manage_users.php?status=updated");
    } else {
        // Error: likely duplicate email
        echo "Error updating record: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}
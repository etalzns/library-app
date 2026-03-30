<?php
session_start();
require_once 'config.php'; 

$message = "";
$status_class = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name_input = trim($_POST['user_name']);
    $email_input = trim($_POST['user_email']);
    $password_input = trim($_POST['user_password'] ?? '');

    // Regex Patterns from your flowchart
    $student_pattern = "/^[a-zA-Z0-9._%+-]+@connect\.ite\.edu\.sg$/i";
    $staff_pattern   = "/^[a-zA-Z0-9._%+-]+@ite\.edu\.sg$/i";
    $cet_pattern     = "/^[a-zA-Z0-9._%+-]+@polite\.edu\.sg$/i";

    if (empty($name_input) || empty($email_input)) {
        $message = "Please fill in all fields.";
        $status_class = "error";
    } else {
        // 1. SEARCH DATABASE (Including is_banned and ban_reason)
        $stmt = $conn->prepare("SELECT id, user_name, user_email, role, is_banned, ban_reason FROM users WHERE user_email = ?");
        $stmt->bind_param("s", $email_input);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // 2. CHECK IF BANNED (Flowchart: "Display notification that User has been banned")
            if ($user['is_banned'] == 1) {
                $message = "<strong>Access Denied.</strong> You have been banned.<br>Reason: " . htmlspecialchars($user['ban_reason']);
                $status_class = "error";
            } 
            // 3. ADMIN PASSWORD CHECK
            elseif ($user['user_email'] === "angelinaazka0@gmail.com" && $password_input !== "2978admin0812") {
                $message = "Incorrect Admin Password.";
                $status_class = "error";
            } 
            // 4. SUCCESSFUL LOGIN
            else {
                $_SESSION['user_name'] = $user['user_name'];
                $_SESSION['user_email'] = $user['user_email'];
                $_SESSION['role'] = $user['role'];
                
                // Redirect based on role
                if ($user['role'] === 'admin') {
                    header("Location: admin_dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit();
            }
        } else {
            // 5. AUTO-REGISTER NEW USER (If email is valid ITE/Polite)
            if (preg_match($student_pattern, $email_input) || preg_match($staff_pattern, $email_input) || preg_match($cet_pattern, $email_input)) {
                
                $assigned_role = preg_match($staff_pattern, $email_input) ? 'staff' : 'student';

                $insert = $conn->prepare("INSERT INTO users (user_name, user_email, role) VALUES (?, ?, ?)");
                $insert->bind_param("sss", $name_input, $email_input, $assigned_role);
                
                if ($insert->execute()) {
                    $_SESSION['user_name'] = $name_input;
                    $_SESSION['user_email'] = $email_input;
                    $_SESSION['role'] = $assigned_role;
                    header("Location: dashboard.php");
                    exit();
                }
            } else {
                $message = "Access Denied. Please use a valid ITE or Polite email.";
                $status_class = "error";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ITE Library Room Booking - Login</title>
    <style>
        body { font-family: 'Segoe UI', sans-serif; background-color: #ddd; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-container { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 8px 24px rgba(0,0,0,0.1); width: 360px; }
        h2 { text-align: center; color: #1a3a5f; margin-bottom: 25px; }
        label { display: block; margin-bottom: 8px; font-weight: 600; color: #444; font-size: 0.9rem; }
        input { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #dadce0; border-radius: 8px; box-sizing: border-box; }
        button { width: 100%; padding: 15px; background-color: #1a3a5f; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; transition: 0.3s; }
        button:hover { background-color: #122a45; }
        .msg { padding: 12px; margin-bottom: 20px; border-radius: 8px; text-align: center; font-size: 14px; line-height: 1.4; }
        .error { background-color: #fce8e6; color: #d93025; border: 1px solid #f1b2ac; }
    </style>
</head>
<body>

<div class="login-container">
    <h2>Library@West Login</h2>

    <?php if (!empty($message)): ?>
        <div class="msg <?php echo $status_class; ?>">
            <?php echo $message; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <label for="user_name">Full Name</label>
        <input type="text" name="user_name" id="user_name" placeholder="Enter your name" required>

        <label for="user_email">Institutional Email</label>
        <input type="email" name="user_email" id="user_email" placeholder="xxx@connect.ite.edu.sg" required>

        <label for="user_password">Password (Admin only)</label>
        <input type="password" name="user_password" id="user_password" placeholder="Enter admin password">
        
        <button type="submit">Verify & Login</button>
    </form>
</div>

</body>
</html>
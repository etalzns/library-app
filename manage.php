<?php
session_start();
require_once('config.php');

// Check if admin is logged in (adjust according to your system)
if (!isset($_SESSION['user_email']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Messages
$message = "";

// Handle deletion of users or rooms
if (isset($_GET['delete_user'])) {
    $id = intval($_GET['delete_user']);
    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "User deleted successfully!";
}

if (isset($_GET['delete_room'])) {
    $id = intval($_GET['delete_room']);
    $stmt = $conn->prepare("DELETE FROM rooms WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $message = "Room deleted successfully!";
}

// Handle add/edit room
if (isset($_POST['save_room'])) {
    $room_name = $_POST['room_name'];
    $capacity = $_POST['capacity'];
    $floor = $_POST['floor'];

    if (!empty($_POST['room_id'])) {
        // Edit existing room
        $stmt = $conn->prepare("UPDATE rooms SET room_name=?, capacity=?, floor=? WHERE id=?");
        $stmt->bind_param("siii", $room_name, $capacity, $floor, $_POST['room_id']);
        $stmt->execute();
        $message = "Room updated successfully!";
    } else {
        // Add new room
        $stmt = $conn->prepare("INSERT INTO rooms (room_name, capacity, floor) VALUES (?, ?, ?)");
        $stmt->bind_param("sii", $room_name, $capacity, $floor);
        $stmt->execute();
        $message = "Room added successfully!";
    }
}

// Fetch users and rooms
$users = $conn->query("SELECT * FROM users ORDER BY id ASC");
$rooms = $conn->query("SELECT * FROM rooms ORDER BY id ASC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Management</title>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
<style>
body{font-family:sans-serif;margin:0;padding:0;background:#f0f2f5;}
.container{max-width:1000px;margin:20px auto;padding:20px;background:#fff;border-radius:10px;box-shadow:0 5px 15px rgba(0,0,0,0.1);}
h1{margin-bottom:20px;color:#1a436d;}
.tab-buttons{display:flex;margin-bottom:20px;}
.tab-buttons button{flex:1;padding:10px;background:#ddd;border:none;cursor:pointer;font-weight:bold;color:#1a436d;}
.tab-buttons button.active{background:#1a436d;color:#fff;}
.tab{display:none;}
table{width:100%;border-collapse:collapse;}
table th, table td{border:1px solid #ddd;padding:10px;text-align:left;}
table th{background:#1a436d;color:#fff;}
form input, form select{padding:8px;margin:5px 0;border:1px solid #ccc;border-radius:5px;width:100%;}
form button{padding:10px;background:#1a436d;color:#fff;border:none;border-radius:5px;cursor:pointer;margin-top:5px;}
.message{padding:10px;background:#d4edda;color:#155724;border-radius:5px;margin-bottom:10px;}
.action-btn{color:#fff;padding:5px 8px;border-radius:3px;text-decoration:none;margin-right:5px;}
.edit-btn{background:#ffc107;}
.delete-btn{background:#dc3545;}
</style>
<script>
function openTab(tabName){
    document.querySelectorAll('.tab').forEach(t=>t.style.display='none');
    document.getElementById(tabName).style.display='block';
    document.querySelectorAll('.tab-buttons button').forEach(b=>b.classList.remove('active'));
    document.querySelector('button[data-tab="'+tabName+'"]').classList.add('active');
}
function editRoom(id, name, capacity, floor){
    document.getElementById('room_id').value = id;
    document.getElementById('room_name').value = name;
    document.getElementById('capacity').value = capacity;
    document.getElementById('floor').value = floor;
}
</script>
</head>
<body>
<div class="container">
<h1>Admin Management</h1>

<?php if($message!="") echo "<div class='message'>$message</div>"; ?>

<div class="tab-buttons">
    <button class="active" data-tab="users" onclick="openTab('users')">Manage Users</button>
    <button data-tab="rooms" onclick="openTab('rooms')">Manage Rooms</button>
</div>

<!-- USERS TAB -->
<div class="tab" id="users" style="display:block;">
    <h2>Users</h2>
    <table>
        <tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Actions</th></tr>
        <?php while($u=$users->fetch_assoc()): ?>
        <tr>
            <td><?php echo $u['id']; ?></td>
            <td><?php echo htmlspecialchars($u['name']); ?></td>
            <td><?php echo htmlspecialchars($u['email']); ?></td>
            <td><?php echo $u['role']; ?></td>
            <td>
                <a class="action-btn edit-btn" href="edit_user.php?id=<?php echo $u['id'];?>"><i class="fas fa-edit"></i></a>
                <a class="action-btn delete-btn" href="?delete_user=<?php echo $u['id'];?>" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

<!-- ROOMS TAB -->
<div class="tab" id="rooms">
    <h2>Rooms</h2>
    <form method="POST">
        <input type="hidden" id="room_id" name="room_id">
        <label>Room Name</label>
        <input type="text" id="room_name" name="room_name" required>
        <label>Capacity</label>
        <input type="number" id="capacity" name="capacity" required>
        <label>Floor</label>
        <select id="floor" name="floor">
            <option value="1">1</option>
            <option value="2">2</option>
        </select>
        <button type="submit" name="save_room">Save Room</button>
    </form>
    <br>
    <table>
        <tr><th>ID</th><th>Name</th><th>Capacity</th><th>Floor</th><th>Actions</th></tr>
        <?php while($r=$rooms->fetch_assoc()): ?>
        <tr>
            <td><?php echo $r['id']; ?></td>
            <td><?php echo htmlspecialchars($r['room_name']); ?></td>
            <td><?php echo $r['capacity']; ?></td>
            <td><?php echo $r['floor']; ?></td>
            <td>
                <button class="action-btn edit-btn" onclick="editRoom('<?php echo $r['id'];?>','<?php echo $r['room_name'];?>','<?php echo $r['capacity'];?>','<?php echo $r['floor'];?>')"><i class="fas fa-edit"></i></button>
                <a class="action-btn delete-btn" href="?delete_room=<?php echo $r['id'];?>" onclick="return confirm('Delete this room?')"><i class="fas fa-trash"></i></a>
            </td>
        </tr>
        <?php endwhile; ?>
    </table>
</div>

</div>
</body>
</html>
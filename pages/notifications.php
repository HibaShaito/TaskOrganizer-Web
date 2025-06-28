<?php
session_start();
include "../includes/conn.php";

// Check if user is logged in
if (!isset($_SESSION['login_user'])) {
    header("Location: SignIn.php");
    exit();
}

$user_id = $_SESSION['login_user'];

// Fetch username from the database
$sql = "SELECT username FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$username = "User";
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $username = $user['username'];
}

// Fetch notifications from the database
$sql = "SELECT * FROM notifications WHERE user_id = $user_id ORDER BY notification_date DESC";
$notifications = mysqli_query($conn, $sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifications</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/notification.css">
    <script src="https://kit.fontawesome.com/89c74d5bb8.js" crossorigin="anonymous"></script>
    <script src="../js/nav.js" defer></script>
    <script>
        function clearNotification(button) {
            const notificationId = button.getAttribute('data-id');
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'clear_notifications.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    if (xhr.responseText.includes("Notification cleared")) {
                        button.parentElement.remove();
                    } else {
                        alert(xhr.responseText);
                    }
                }
            };
            xhr.send('notification_id=' + notificationId);
        }

        function clearAllNotifications() {
            const xhr = new XMLHttpRequest();
            xhr.open('POST', 'clear_notifications.php', true);
            xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
            xhr.onreadystatechange = function() {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    if (xhr.responseText.includes("All notifications cleared")) {
                        document.querySelectorAll('.notifications li').forEach(item => item.remove());
                    } else {
                        alert(xhr.responseText);
                    }
                }
            };
            xhr.send('clear_all=true');
        }
    </script>
</head>

<body>
    <header>
        <nav class="navbar">
            <div class="logo">
                <img src="../img/logo.ico" alt="logo life planner">
                <h1>Life Planner</h1>
            </div>
            <ul class="nav-links" id="navbar">
                <li><a href="../index.php">Home</a></li>
                <li><a href="../pages/dashboard.php">Dashboard</a></li>
                <li><a href="../pages/tasks.php">Tasks</a></li>
                <li><a href="../pages/goals.php">Goals</a></li>
                <li><a href="../pages/events.php">Events</a></li>
                <li><a href="../pages/profile.php">Profile</a></li>
                <li><a href="../pages/notifications.php">Notifications</a></li>
                <li><a href="../pages/logout.php">Log Out</a></li>
                <li><a href="../pages/SignUp.php">Sign Up</a></li>
                <a href="#" id="close"><i class="fa-solid fa-xmark" style="color: #ff0000"></i></a>
            </ul>
            <div id="mobile">
                <i id="bar" class="fa-solid fa-outdent" style="color: #b197fc"></i>
            </div>
        </nav>
    </header>
    <h2>Your Notifications</h2>
    <ul class="notifications">
        <?php while ($notification = mysqli_fetch_assoc($notifications)): ?>
            <li>
                <?php echo htmlspecialchars($notification['message']) . " - " . date("Y-m-d H:i A", strtotime($notification['notification_date'])); ?>
                <button onclick="clearNotification(this)" data-id="<?php echo $notification['notification_id']; ?>">Clear</button>
            </li>
        <?php endwhile; ?>
    </ul>
    <div class="clear-all">
        <button onclick="clearAllNotifications()">Clear All</button>
    </div>
</body>

</html>
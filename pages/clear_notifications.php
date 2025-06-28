<?php
session_start();
include "../includes/conn.php";

if (!isset($_SESSION['login_user'])) {
    header("Location: SignIn.php");
    exit();
}

$user_id = $_SESSION['login_user'];

if (isset($_POST['notification_id'])) {
    $notification_id = $_POST['notification_id'];
    $sql = "DELETE FROM notifications WHERE notification_id = $notification_id AND user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        echo "Notification cleared.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

if (isset($_POST['clear_all'])) {
    $sql = "DELETE FROM notifications WHERE user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        echo "All notifications cleared.";
    } else {
        echo "Error: " . mysqli_error($conn);
    }
}

mysqli_close($conn);

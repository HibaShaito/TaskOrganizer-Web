<?php
include "./../../includes/conn.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = test_input($_POST["email"]);
    $password = test_input($_POST["password"]);
    $confirm_password = test_input($_POST["confirm_password"]);

    if ($password !== $confirm_password) {
        die("Passwords do not match.");
    }

    // Check password strength
    if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
        die("Password must be at least 8 characters long and include at least one uppercase letter, one lowercase letter, and one number.");
    }

    $password_hash = password_hash($password, PASSWORD_BCRYPT);

    // Update the password in the database
    $sql = "UPDATE users SET password_hash='$password_hash' WHERE email='$email'";
    if (mysqli_query($conn, $sql)) {
        echo "Password has been reset successfully.";
    } else {
        echo "Failed to reset password.";
    }
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

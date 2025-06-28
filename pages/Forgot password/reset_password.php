<?php
if (isset($_GET['email'])) {
    $email = test_input($_GET['email']);
} else {
    die('Invalid request.');
}

function test_input($data)
{
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <link rel="stylesheet" href="../css/forgot_password.css">
</head>

<body>
    <div class="container">
        <h2>Reset Password</h2>
        <form action="update_password.php" method="post">
            <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
            <input type="password" name="password" required placeholder="Enter new password">
            <input type="password" name="confirm_password" required placeholder="Confirm new password">
            <button type="submit">Reset Password</button>
        </form>
    </div>
</body>

</html>
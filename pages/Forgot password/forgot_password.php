<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password</title>
    <link rel="stylesheet" href="../css/forgot_password.css">
</head>

<body>
    <div class="container">
        <h2>Forgot Password</h2>
        <form action="send_reset_link.php" method="post">
            <input type="email" name="email" required placeholder="Enter your email">
            <button type="submit">Send Reset Link</button>
        </form>
    </div>
</body>

</html>
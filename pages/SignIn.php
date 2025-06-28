<?php
session_start(); // Starting Session
$error = ''; // Variable to store error message

if (isset($_POST['submit'])) {
  // Check if email or password is empty
  if (empty($_POST['email']) || empty($_POST['password'])) {
    $error = "Email or Password is invalid";
  } else {
    // Include connection file after form submission
    include "../includes/conn.php";

    // Get email and password from POST data
    $email = $_POST['email'];
    $password = $_POST['password'];

    //  the SQL query 
    $sql = "SELECT * FROM users WHERE email='" . $email . "'";

    // Execute the query
    $result = mysqli_query($conn, $sql);
    $rows = mysqli_num_rows($result); // Count the rows
    if ($rows == 1) { // Check if email exists
      $res = mysqli_fetch_array($result); // Fetch data first
      if (password_verify($password, $res['password_hash'])) { // Verify the hashed password
        $_SESSION['login_user'] = $res['user_id']; // Set the session
        if ($res['role_id'] == 1) {
          $_SESSION['role'] = "admin"; // Assign role
          header("location:../Admin/admin_page.php"); // Redirect to admin page
        } elseif ($res['role_id'] == 2) {
          $_SESSION['role'] = "user"; // Assign role
          header("location:profile.php"); // Redirect to user profile
        }
      } else {
        // If no match, set an error message
        $error = "Invalid email or password";
      }
    } else {
      // If no match, set an error message
      $error = "Invalid email or password";
    }
  }
}
?>
<?php if (isset($_SESSION['message'])) {
  echo "<script>alert('" . $_SESSION['message'] . "');</script>";
  unset($_SESSION['message']);
} ?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login Page</title>
  <link rel="stylesheet" href="../css/SignIn.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="../js/SignIn.js" defer></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/crypto-js/4.1.1/crypto-js.js" defer></script>
</head>

<body>
  <div class="container wrapper">
    <div class="cloud">
      <div class="cloud-part1"></div>
      <div class="eye left-eye"></div>
      <div class="eye right-eye"></div>
      <div class="mouth"></div>
    </div>
    <span class="bg-animate"></span>
    <div id="form-container" class="form-box login">
      <h2>Login</h2>
      <form id="login-form" action="<?php echo $_SERVER['PHP_SELF'] ?>" method="post">
        <div class="input-box">
          <input type="text" id="email" name="email" required />
          <label for="email">Email:</label>
          <i class="bx bxs-user"></i>
        </div>
        <div class="input-box">
          <input type="password" id="password" name="password" required />
          <label for="password">Password:</label>
          <i class="bx bxs-lock-alt"></i>
          <i class="bx bx-hide toggle-password" id="toggle-password"></i>
        </div>
        <button type="submit" name="submit" class="btn">Login</button>
        <span><?php echo $error; ?></span>
        <div class="logreg-link">
          <p>
            Don't have an account?
            <a href="../pages/SignUp.php" class="register-link">Sign Up</a>
            <br>
            <a href="../pages/Forgot password/forgot_password.php" class="forgot-password-link">Forgot Password?</a>
          </p>
        </div>
      </form>
      <hr />
      <br />
      <div class="social-login">
        <button class="btn facebook">Sign In with Facebook</button>
        <button class="btn google">Sign In with Google</button>
      </div>
    </div>
  </div>
</body>

</html>
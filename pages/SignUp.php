<?php
// Define error variables and set them to empty values
$nameErr  = $emailErr = $passwordErr = $confirmPasswordErr = "";
$name = $email = $password = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

  if (empty($_POST["name"])) {
    $nameErr = "First name is required";
  } else {
    $name = test_input($_POST["name"]);
    if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
      $nameErr = "Only letters and whitespace allowed";
    }
  }

  if (empty($_POST["email"])) {
    $emailErr = "Email is required";
  } else {
    $email = test_input($_POST["email"]);
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format";
    }
  }

  if (empty($_POST["password"])) {
    $passwordErr = "Password is required";
  } else {
    $password = test_input($_POST["password"]);
    if (strlen($password) < 8) {
      $passwordErr = "Password must be at least 8 characters";
    } elseif (!preg_match("/[a-z]/", $password) || !preg_match("/[A-Z]/", $password) || !preg_match("/[0-9]/", $password)) {
      $passwordErr = "Password must include at least one uppercase letter, one lowercase letter, and one number";
    }
  }

  if (empty($_POST["confirm-password"])) {
    $confirmPasswordErr = "Confirm password is required";
  } else {
    $confirm_password = test_input($_POST["confirm-password"]);
    if ($password !== $confirm_password) {
      $confirmPasswordErr = "Passwords do not match";
    }
  }

  if (empty($nameErr) && empty($emailErr) && empty($passwordErr) && empty($confirmPasswordErr)) {
    include "../includes/conn.php";

    // Check if email already exists
    $email_check_sql = "SELECT * FROM users WHERE email = '$email'";
    $email_check_result = mysqli_query($conn, $email_check_sql);

    if (mysqli_num_rows($email_check_result) > 0) {
      // Email already exists, redirect to login page with alert
      $_SESSION['message'] = "Email already exists, please log in.";
      header("Location: ../pages/SignIn.php");
      exit();
    } else {
      $password_hash = password_hash($password, PASSWORD_BCRYPT);
      $sql = "INSERT INTO users (username, email, password_hash) 
                    VALUES ('$name', '$email', '$password_hash')";

      if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Signup successful! Please log in.";
        header("Location: ../pages/SignIn.php");
        exit();
      } else {
        echo "Error: " . $sql . "<br>" . mysqli_error($conn);
      }

      mysqli_close($conn);
    }
  }
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
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Signup Page with Cloud Animation</title>
  <link rel="stylesheet" href="../css/SignUp.css" />
  <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet" />
  <script src="../js/SignUp.js" defer></script>
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
    <div id="form-container" class="form-box signup">
      <h2>Signup</h2>
      <form id="signup-form" method="POST">
        <div class="input-box">
          <input type="text" id="name" name="name" required />
          <span class="error"><?php echo $nameErr; ?></span>
          <label for="name">Name:</label>
          <i class="bx bxs-edit-alt"></i>
        </div>
        <div class="input-box">
          <input type="text" id="email" name="email" required />
          <span class="error"><?php echo $emailErr; ?></span>
          <label for="email">Email:</label>
          <i class="bx bxs-user"></i>
        </div>
        <div class="input-box">
          <input type="password" id="password" name="password" required />
          <span class="error"><?php echo $passwordErr; ?></span>
          <label for="password">Password:</label>
          <i class="bx bxs-lock-alt"></i>
          <i class="bx bx-hide" id="togglePassword" onclick="togglePasswordVisibility('password', this)"></i>
        </div>

        <div class="input-box">
          <input type="password" id="confirm-password" name="confirm-password" required />
          <span class="error"><?php echo $confirmPasswordErr; ?></span>
          <label for="confirm-password">Confirm Password:</label>
          <i class="bx bxs-lock-alt"></i>
          <i class="bx bx-hide" id="toggleConfirmPassword" onclick="togglePasswordVisibility('confirm-password', this)"></i>
        </div>

        <button type="submit" class="btn">Signup</button>
        <div class="logreg-link">
          <p>Already have an account? <a href="../pages/SignIn.php" class="register-link">Sign In</a></p>
        </div>
      </form>
      <hr />
      <br />
      <div class="social-login">
        <button type="button" class="btn google" onclick="googleSignIn()">Sign Up With Google</button>
        <button type="button" class="btn facebook" onclick="facebookSignIn()">Sign Up With Facebook</button>
      </div>
    </div>
  </div>
</body>

</html>
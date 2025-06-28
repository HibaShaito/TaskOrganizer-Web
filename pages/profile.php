<?php
session_start();
include "../includes/conn.php";

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Check if user is logged in
if (!isset($_SESSION['login_user'])) {
    header("Location: SignIn.php");
    exit();
}

$user_id = $_SESSION['login_user'];

// Fetch user data from the database
$sql = "SELECT username, email, profile_picture, tasks_completed, goals_achieved, created_at FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
} else {
    echo "Error: Unable to fetch user details.";
    exit();
}

// Handle profile picture and username update
$picErr = "";
$usernameErr = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['username'])) {
        $username = test_input($_POST['username']);
        $sql = "UPDATE users SET username='$username' WHERE user_id=$user_id";
        if (mysqli_query($conn, $sql)) {
            // Refresh the page to see the updated username
            header("Location: profile.php");
            exit();
        } else {
            $usernameErr = "Sorry, there was an error updating your username.";
        }
    }

    if (isset($_FILES['profile_picture'])) {
        $errors = array();
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_type = $_FILES['profile_picture']['type'];
        $file_parts = explode('.', $file_name);
        $file_ext = strtolower(end($file_parts));
        $extensions = array("jpeg", "jpg", "png");

        if (in_array($file_ext, $extensions) === false) {
            $errors[] = 'Extension not allowed, please choose a JPEG or PNG file.';
        }

        if ($file_size > 2097152) {
            $errors[] = 'File size must be exactly 2 MB';
        }

        if (empty($errors) == true) {
            $upload_dir = "../img/uploads/";
            $pic = $upload_dir . $file_name;
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create the directory if it doesn't exist
            }
            if (move_uploaded_file($file_tmp, $pic)) {
                // Check if the file is successfully uploaded
                echo "File uploaded successfully: $pic <br>";

                // Store only the file name in the database
                $sql = "UPDATE users SET profile_picture='$file_name' WHERE user_id=$user_id";
                if (mysqli_query($conn, $sql)) {
                    echo "Database updated successfully! <br>";
                    // Refresh the page to see the updated profile picture
                    header("Location: profile.php");
                    exit();
                } else {
                    $picErr = "Sorry, there was an error updating your profile picture in the database.";
                }
            } else {
                $picErr = "Sorry, there was an error uploading your profile picture.";
            }
        } else {
            foreach ($errors as $error) {
                $picErr .= $error . "<br>";
            }
        }
    }
}

mysqli_close($conn);

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
    <title>Your Profile</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/profile.css">
    <!--Link to fontawesome cdn website to get some icons-->
    <script src="https://kit.fontawesome.com/89c74d5bb8.js" crossorigin="anonymous"></script>
    <script src="../js/nav.js" defer></script>
    <script>
        function previewImage(input) {
            var file = input.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                document.getElementById('profileImage').src = e.target.result;
            }

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>

<body>
    <!-- Header Section -->
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
                <!--adding close will be only visible in medium side devices navbar-->
                <a href="#" id="close"><i class="fa-solid fa-xmark" style="color: #ff0000"></i></a>
            </ul>
            <div id="mobile">
                <i id="bar" class="fa-solid fa-outdent" style="color: #b197fc"></i>
            </div>
        </nav>
    </header>
    <!-- Profile Section -->
    <section class="profile">
        <div class="profile-card">
            <div class="profile-picture">
                <img src="<?php echo htmlspecialchars('../img/uploads/' . $user['profile_picture']); ?>" alt="Profile Picture" id="profileImage">
            </div>
            <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="uploadButton">Choose File</label>
                <input type="file" id="uploadButton" name="profile_picture" onchange="previewImage(this)">
                <span class="error"><?php echo $picErr; ?></span>
                <button type="submit">Update Profile</button>
            </form>
            <form method="post" enctype="multipart/form-data" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user['username']); ?>" required autocomplete="username">
                <span class="error"><?php echo $usernameErr; ?></span><br><br>

                <button type="submit">Update Username</button>
            </form>
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($user['username']); ?></h2>
                <p>Signed Up: <?php echo date("F j, Y", strtotime($user['created_at'])); ?></p>
                <p>Tasks Completed: <?php echo $user['tasks_completed']; ?></p>
                <p>Goals Achieved: <?php echo $user['goals_achieved']; ?></p>
            </div>
        </div>
    </section>
</body>

</html>
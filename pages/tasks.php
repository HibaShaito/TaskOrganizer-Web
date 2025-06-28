<?php
session_start();
include "../includes/conn.php";

// Check if user is logged in
if (!isset($_SESSION['login_user'])) {
    header("Location: SignIn.php");
    exit();
}

$user_id = $_SESSION['login_user'];

// Define error variables and set them to empty values
$titleErr = $descriptionErr = $dueDateErr = "";
$title = $description = $due_date = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Validate title
    if (empty($_POST["title"])) {
        $titleErr = "Task title is required";
    } else {
        $title = test_input($_POST["title"]);
    }

    // Validate description
    if (empty($_POST["description"])) {
        $description = ""; // Description is optional
    } else {
        $description = test_input($_POST["description"]);
    }

    // Validate due date
    if (empty($_POST["due_date"])) {
        $dueDateErr = "Due date is required";
    } else {
        $due_date = test_input($_POST["due_date"]);
        $current_date = date("Y-m-d");
        if ($due_date < $current_date) {
            $dueDateErr = "Due date cannot be in the past";
        }
    }

    // Insert task into the database if no errors
    if (!$titleErr && !$descriptionErr && !$dueDateErr) {
        $sql = "INSERT INTO tasks (user_id, title, description, due_date) VALUES ('$user_id', '$title', '$description', '$due_date')";
        if (mysqli_query($conn, $sql)) {
            echo "New task created successfully";

            // Add notification logic here
            if ($due_date) {
                $notification_title = mysqli_real_escape_string($conn, "Task Reminder: " . $title);
                $notification_message = mysqli_real_escape_string($conn, "Your task '" . $title . "' is due on " . $due_date);
                $notification_date = mysqli_real_escape_string($conn, $due_date . " 00:00:00");
                $sql = "INSERT INTO notifications (user_id, type, title, message, notification_date) 
                        VALUES ('$user_id', 'Task', '$notification_title', '$notification_message', '$notification_date')";
                mysqli_query($conn, $sql);
            }
        } else {
            echo "Error: " . $sql . "<br>" . mysqli_error($conn);
        }
    }

    mysqli_close($conn);
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
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/task.css">
    <!--Link to fontawesome cdn website to get some icons-->
    <script src="https://kit.fontawesome.com/89c74d5bb8.js" crossorigin="anonymous"></script>
    <script src="../js/nav.js" defer></script>
    <title>Manage Tasks</title>
    <style>
        .error {
            color: #FF0000;
        }
    </style>
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

    <h2>Create New Task</h2>
    <form class="task-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="title">Task Title</label><br>
        <input type="text" id="title" name="title" placeholder="Enter your task title" value="<?php echo $title; ?>" required>
        <span class="error">* <?php echo $titleErr; ?></span><br><br>

        <label for="description">Description</label><br>
        <textarea id="description" name="description" placeholder="Describe your task"><?php echo $description; ?></textarea><br>
        <span class="error"><?php echo $descriptionErr; ?></span><br><br>

        <label for="due_date">Due Date</label><br>
        <input type="date" id="due_date" name="due_date" value="<?php echo $due_date; ?>" required>
        <span class="error">* <?php echo $dueDateErr; ?></span><br><br>

        <button type="submit" name="submit">Add Task</button>
    </form>
</body>

</html>
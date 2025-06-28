<?php
session_start();
include "../includes/conn.php";

// Check if user is logged in
if (!isset($_SESSION['login_user'])) {
    header("Location: SignIn.php");
    exit();
}

$user_id = $_SESSION['login_user'];

// Handle task deletion
if (isset($_GET['delete_task'])) {
    $task_id = $_GET['delete_task'];
    $sql = "DELETE FROM tasks WHERE task_id = $task_id AND user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error deleting task: " . mysqli_error($conn);
    }
}

// Handle goal deletion
if (isset($_GET['delete_goal'])) {
    $goal_id = $_GET['delete_goal'];
    $sql = "DELETE FROM goals WHERE goal_id = $goal_id AND user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: dashboard.php");
        exit();
    } else {
        echo "Error deleting goal: " . mysqli_error($conn);
    }
}

// Fetch username from the database
$sql = "SELECT username FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$username = "User";
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $username = $user['username'];
}

// Fetch tasks from the database
$sql = "SELECT * FROM tasks WHERE user_id = $user_id ORDER BY due_date ASC";
$task_result = mysqli_query($conn, $sql);
$tasks = [];
if (mysqli_num_rows($task_result) > 0) {
    while ($row = mysqli_fetch_assoc($task_result)) {
        $tasks[] = $row;
    }
}

// Fetch goals from the database
$sql = "SELECT * FROM goals WHERE user_id = $user_id ORDER BY target_date ASC";
$goal_result = mysqli_query($conn, $sql);
$goals = [];
if (mysqli_num_rows($goal_result) > 0) {
    while ($row = mysqli_fetch_assoc($goal_result)) {
        $goals[] = $row;
    }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <!--Link to fontawesome cdn website to get some icons-->
    <script src="https://kit.fontawesome.com/89c74d5bb8.js" crossorigin="anonymous"></script>
    <script src="../js/nav.js" defer></script>
    <title>Dashboard</title>
</head>

<body>
    <!-- Header Section -->
    <header>
        <nav class="navbar">
            <div class="logo">
                <img src="../img/logo.ico" alt="logo life planner">
                <h5 style="color: white;">Welcome, <?php echo htmlspecialchars($username); ?>!</h5>
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

    <h3>Your Tasks</h3>
    <div class="task-goal-container">
        <?php foreach ($tasks as $task): ?>
            <div class="task-goal-card">
                <a href="../pages/taskdetailpage.php?task_id=<?php echo $task['task_id']; ?>">
                    <h4>Task: <?php echo htmlspecialchars($task['title']); ?></h4>
                    <p>Progress: <?php echo htmlspecialchars($task['progress']) . "%"; ?></p>
                    <p>Due: <?php echo date("d/m/Y", strtotime($task['due_date'])); ?></p>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: <?php echo htmlspecialchars($task['progress']); ?>%;"></div>
                    </div>
                </a>
                <div class="task-goal-actions">
                    <a href="dashboard.php?delete_task=<?php echo $task['task_id']; ?>" class="delete-btn">Delete</a>
                </div>

            </div>
        <?php endforeach; ?>
    </div>
    <hr>
    <h3>Your Goals</h3>
    <div class="task-goal-container">
        <?php foreach ($goals as $goal): ?>
            <div class="task-goal-card">
                <a href="../pages/goaldetailspage.php?goal_id=<?php echo $goal['goal_id']; ?>">
                    <h4>Goal: <?php echo htmlspecialchars($goal['title']); ?></h4>
                    <p>Due: <?php echo date("d/m/Y", strtotime($goal['target_date'])); ?></p>
                    <div class="progress-bar">
                        <div class="progress-bar-fill" style="width: <?php echo htmlspecialchars($goal['progress']); ?>%;"></div>
                    </div>
                </a>
                <div class="task-goal-actions">
                    <a href="dashboard.php?delete_goal=<?php echo $goal['goal_id']; ?>" class="delete-btn">Delete</a>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</body>

</html>
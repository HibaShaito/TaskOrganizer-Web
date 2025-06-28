<?php
session_start();
include "../includes/conn.php";

// Check if user is logged in
if (!isset($_SESSION['login_user'])) {
    header("Location: SignIn.php");
    exit();
}

$user_id = $_SESSION['login_user'];
$task_id = isset($_GET['task_id']) ? intval($_GET['task_id']) : 0;

// Fetch username from the database
$sql = "SELECT username FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$username = "User";
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $username = $user['username'];
}

// Fetch task details
$sql = "SELECT * FROM tasks WHERE task_id = $task_id AND user_id = $user_id";
$result = mysqli_query($conn, $sql);
$task = mysqli_fetch_assoc($result);

// Fetch subtasks
function fetchSubtasks($conn, $task_id)
{
    $sql = "SELECT * FROM subtasks WHERE task_id = $task_id";
    $subtask_result = mysqli_query($conn, $sql);
    $subtasks = [];
    if (mysqli_num_rows($subtask_result) > 0) {
        while ($row = mysqli_fetch_assoc($subtask_result)) {
            $subtasks[] = $row;
        }
    }
    return $subtasks;
}

$subtasks = fetchSubtasks($conn, $task_id);

// Initialize error variables
$titleErr = $descriptionErr = $dueDateErr = "";

// Handle adding new subtask
if (isset($_POST['new_subtask'])) {
    $new_subtask_title = mysqli_real_escape_string($conn, $_POST['new_subtask']);
    $sql = "INSERT INTO subtasks (task_id, title, is_completed) VALUES ('$task_id', '$new_subtask_title', 0)";
    mysqli_query($conn, $sql);

    header("Location: taskdetailpage.php?task_id=$task_id");
    exit();
}

// Handle updating progress based on subtasks
if (isset($_POST['update_progress'])) {
    foreach ($subtasks as $subtask) {
        $subtask_id = $subtask['subtask_id'];
        $is_completed = isset($_POST["subtask_$subtask_id"]) ? 1 : 0;
        $sql = "UPDATE subtasks SET is_completed=$is_completed WHERE subtask_id=$subtask_id";
        mysqli_query($conn, $sql);
    }

    // Re-fetch subtasks to get updated status
    $subtasks = fetchSubtasks($conn, $task_id);

    // Recalculate progress
    $total_subtasks = count($subtasks);
    $completed_subtasks = 0;
    foreach ($subtasks as $subtask) {
        if ($subtask['is_completed']) {
            $completed_subtasks++;
        }
    }
    $progress = $total_subtasks > 0 ? ($completed_subtasks / $total_subtasks) * 100 : 0;

    // Check previous progress
    $previous_progress = $task['progress'];

    // Update task progress
    $sql = "UPDATE tasks SET progress=$progress WHERE task_id=$task_id AND user_id=$user_id";
    mysqli_query($conn, $sql);

    // Update tasks completed in users table
    if ($progress == 100 && $previous_progress < 100) {
        $sql = "UPDATE users SET tasks_completed = tasks_completed + 1 WHERE user_id = $user_id";
        mysqli_query($conn, $sql);
    } elseif ($progress < 100 && $previous_progress == 100) {
        $sql = "UPDATE users SET tasks_completed = tasks_completed - 1 WHERE user_id = $user_id";
        mysqli_query($conn, $sql);
    }

    // Re-fetch task to get updated progress
    $result = mysqli_query($conn, "SELECT * FROM tasks WHERE task_id = $task_id AND user_id = $user_id");
    $task = mysqli_fetch_assoc($result);

    header("Location: taskdetailpage.php?task_id=$task_id");
    exit();
}

// Update task information
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['new_subtask']) && !isset($_POST['update_progress'])) {
    $task_title = mysqli_real_escape_string($conn, $_POST['title']);
    $task_description = mysqli_real_escape_string($conn, $_POST['description']);
    $task_due = isset($_POST['due_date']) ? mysqli_real_escape_string($conn, $_POST['due_date']) : '';

    // Validate due date
    $current_date = date("Y-m-d");
    if (empty($task_due)) {
        $dueDateErr = "Due date is required";
    } elseif ($task_due < $current_date) {
        $dueDateErr = "Due date cannot be in the past";
    } else {
        // Update task if no errors
        $sql = "UPDATE tasks SET title='$task_title', description='$task_description', due_date='$task_due' WHERE task_id=$task_id AND user_id=$user_id";
        if (mysqli_query($conn, $sql)) {
            // Re-fetch task to get updated details
            $result = mysqli_query($conn, "SELECT * FROM tasks WHERE task_id = $task_id AND user_id = $user_id");
            $task = mysqli_fetch_assoc($result);

            // Add notification logic here
            if ($task_due) {
                $notification_title = mysqli_real_escape_string($conn, "Task Reminder: " . $task_title);
                $notification_message = mysqli_real_escape_string($conn, "Your task '" . $task_title . "' is due on " . $task_due);
                $notification_date = mysqli_real_escape_string($conn, $task_due . " 00:00:00");
                $sql = "INSERT INTO notifications (user_id, type, title, message, notification_date) 
                        VALUES ('$user_id', 'Task', '$notification_title', '$notification_message', '$notification_date')";
                mysqli_query($conn, $sql);
            }

            header("Location: taskdetailpage.php?task_id=$task_id");
            exit();
        } else {
            echo "Error updating task: " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang=" en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/taskdetailpage.css">
    <link rel="stylesheet" href="../css/index.css">
    <!--Link to fontawesome cdn website to get some icons-->
    <script
        src="https://kit.fontawesome.com/89c74d5bb8.js"
        crossorigin="anonymous"></script>
    <script src="../js/nav.js" defer></script>
    <script src="../js/taskdetailpage.js" defer></script>
    <title>Task Details</title>
    <style>
        .error {
            color: #FF0000;
        }
    </style>
</head>

<body>
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
    <h1>Task Details: <?php echo htmlspecialchars($task['title']); ?></h1>
    <section class="task-details">
        <h2>Task Information</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?task_id=$task_id"; ?>">
            <label for="title">Task Title</label><br>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($task['title']); ?>" required><br><br>
            <span class="error">* <?php echo $titleErr; ?></span><br><br>

            <label for="description">Description</label><br>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($task['description']); ?></textarea><br><br>
            <span class="error"><?php echo $descriptionErr; ?></span><br><br>

            <label for="target_date">Target Date</label><br>
            <input type="date" id="due_date" name="due_date" value="<?php echo $task['due_date']; ?>" required><br><br>
            <span class="error">* <?php echo $dueDateErr; ?></span><br><br>

            <button type="submit">Save Changes</button>
        </form>
    </section>
    <section class="subtasks">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?task_id=$task_id"; ?>">
            <label for="new_subtask">New Sub-task:</label>
            <input type="text" id="new_subtask" name="new_subtask" placeholder="Enter new sub-task" required />
            <button type="submit" name="add_subtask">Add Sub-task</button>
        </form>
    </section>

    <section class="progress">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?task_id=$task_id"; ?>">
            <h2>Sub-tasks</h2>
            <ul>
                <?php foreach ($subtasks as $subtask): ?>
                    <li>
                        <input type="checkbox" id="subtask_<?php echo $subtask['subtask_id']; ?>" name="subtask_<?php echo $subtask['subtask_id']; ?>" <?php echo $subtask['is_completed'] ? 'checked' : ''; ?> />
                        <label for="subtask_<?php echo $subtask['subtask_id']; ?>"><?php echo htmlspecialchars($subtask['title']); ?></label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="submit" name="update_progress">Update Progress</button>
        </form>
        <h2>Progress</h2>
        <div class="progress-bar">
            <div class="progress-bar-fill" style="width: <?php echo $task['progress']; ?>%;"></div>
        </div>
        <p><?php echo $task['progress']; ?>% Completed</p>
    </section>
</body>

</html>
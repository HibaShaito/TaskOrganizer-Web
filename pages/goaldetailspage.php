<?php
session_start();
include "../includes/conn.php";

// Check if user is logged in
if (!isset($_SESSION['login_user'])) {
    header("Location: SignIn.php");
    exit();
}

$user_id = $_SESSION['login_user'];
$goal_id = $_GET['goal_id'];

// Fetch username from the database
$sql = "SELECT username FROM users WHERE user_id = $user_id";
$result = mysqli_query($conn, $sql);
$username = "User";
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $username = $user['username'];
}

// Fetch goal details
$sql = "SELECT * FROM goals WHERE goal_id = $goal_id AND user_id = $user_id";
$result = mysqli_query($conn, $sql);
$goal = mysqli_fetch_assoc($result);

// Fetch sub-goals
function fetchSubgoals($conn, $goal_id)
{
    $sql = "SELECT * FROM subgoals WHERE goal_id = $goal_id";
    $subgoal_result = mysqli_query($conn, $sql);
    $subgoals = [];
    if (mysqli_num_rows($subgoal_result) > 0) {
        while ($row = mysqli_fetch_assoc($subgoal_result)) {
            $subgoals[] = $row;
        }
    }
    return $subgoals;
}

$subgoals = fetchSubgoals($conn, $goal_id);

// Initialize error variables
$titleErr = $descriptionErr = $targetDateErr = "";

// Handle adding new sub-goal
if (isset($_POST['new_subgoal'])) {
    $new_subgoal_title = $_POST['new_subgoal'];
    $sql = "INSERT INTO subgoals (goal_id, title, is_completed) VALUES ('$goal_id', '$new_subgoal_title', 0)";
    mysqli_query($conn, $sql);

    header("Location: goaldetailspage.php?goal_id=$goal_id");
    exit();
}

// Handle updating progress based on sub-goals
if (isset($_POST['update_progress'])) {
    foreach ($subgoals as $subgoal) {
        $subgoal_id = $subgoal['subgoal_id'];
        $is_completed = isset($_POST["subgoal_$subgoal_id"]) ? 1 : 0;
        $sql = "UPDATE subgoals SET is_completed=$is_completed WHERE subgoal_id=$subgoal_id";
        mysqli_query($conn, $sql);
    }

    // Re-fetch subgoals to get updated status
    $subgoals = fetchSubgoals($conn, $goal_id);

    // Recalculate progress
    $total_subgoals = count($subgoals);
    $completed_subgoals = 0;
    foreach ($subgoals as $subgoal) {
        if ($subgoal['is_completed']) {
            $completed_subgoals++;
        }
    }
    $progress = $total_subgoals > 0 ? ($completed_subgoals / $total_subgoals) * 100 : 0;

    // Check previous progress
    $previous_progress = $goal['progress'];

    // Update goal progress
    $sql = "UPDATE goals SET progress=$progress WHERE goal_id=$goal_id AND user_id=$user_id";
    mysqli_query($conn, $sql);

    // Update goals achieved in users table
    if ($progress == 100 && $previous_progress < 100) {
        $sql = "UPDATE users SET goals_achieved = goals_achieved + 1 WHERE user_id = $user_id";
        mysqli_query($conn, $sql);
    } elseif ($progress < 100 && $previous_progress == 100) {
        $sql = "UPDATE users SET goals_achieved = goals_achieved - 1 WHERE user_id = $user_id";
        mysqli_query($conn, $sql);
    }

    // Re-fetch goal to get updated progress
    $result = mysqli_query($conn, "SELECT * FROM goals WHERE goal_id = $goal_id AND user_id = $user_id");
    $goal = mysqli_fetch_assoc($result);

    header("Location: goaldetailspage.php?goal_id=$goal_id");
    exit();
}

// Update goal information
if ($_SERVER["REQUEST_METHOD"] == "POST" && !isset($_POST['new_subgoal']) && !isset($_POST['update_progress'])) {
    $goal_title = isset($_POST['title']) ? $_POST['title'] : '';
    $goal_description = isset($_POST['description']) ? $_POST['description'] : '';
    $goal_date = isset($_POST['target_date']) ? $_POST['target_date'] : '';

    // Validate target date
    $current_date = date("Y-m-d");
    if ($goal_date < $current_date) {
        $targetDateErr = "Target date cannot be in the past";
    }

    // Update goal if no errors
    if (!$targetDateErr) {
        $sql = "UPDATE goals SET title='$goal_title', description='$goal_description', target_date='$goal_date' WHERE goal_id=$goal_id AND user_id=$user_id";
        if (mysqli_query($conn, $sql)) {
            // Re-fetch goal to get updated details
            $result = mysqli_query($conn, "SELECT * FROM goals WHERE goal_id = $goal_id AND user_id = $user_id");
            $goal = mysqli_fetch_assoc($result);

            // Add notification logic here
            if ($goal_date) {
                $notification_title = mysqli_real_escape_string($conn, "Goal Reminder: " . $goal_title);
                $notification_message = mysqli_real_escape_string($conn, "Your goal '" . $goal_title . "' is due on " . $goal_date);
                $notification_date = mysqli_real_escape_string($conn, $goal_date . " 00:00:00");
                $sql = "INSERT INTO notifications (user_id, type, title, message, notification_date) 
                        VALUES ('$user_id', 'Goal', '$notification_title', '$notification_message', '$notification_date')";
                mysqli_query($conn, $sql);
            }

            header("Location: goaldetailspage.php?goal_id=$goal_id");
            exit();
        } else {
            echo "Error updating goal: " . mysqli_error($conn);
        }
    }
}

mysqli_close($conn);
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../css/goaldetailspage.css">
    <link rel="stylesheet" href="../css/index.css">
    <!--Link to fontawesome cdn website to get some icons-->
    <script src="https://kit.fontawesome.com/89c74d5bb8.js" crossorigin="anonymous"></script>
    <script src="../js/nav.js" defer></script>
    <script src="../js/taskdetailpage.js" defer></script>
    <title>Goal Details</title>
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
    <h1>Goal Details: <?php echo htmlspecialchars($goal['title']); ?></h1>
    <section class="task-details">
        <h2>Goal Information</h2>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?goal_id=$goal_id"; ?>">
            <label for="title">Goal Title</label><br>
            <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($goal['title']); ?>" required><br><br>
            <span class="error">* <?php echo $titleErr; ?></span><br><br>

            <label for="description">Description</label><br>
            <textarea id="description" name="description" required><?php echo htmlspecialchars($goal['description']); ?></textarea><br><br>
            <span class="error"><?php echo $descriptionErr; ?></span><br><br>

            <label for="target_date">Target Date</label><br>
            <input type="date" id="target_date" name="target_date" value="<?php echo $goal['target_date']; ?>" required><br><br>
            <span class="error">* <?php echo $targetDateErr; ?></span><br><br>

            <button type="submit">Save Changes</button>
        </form>
    </section>

    <section class="subtasks">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?goal_id=$goal_id"; ?>">
            <label for="new_subgoal">New Sub-goal:</label>
            <input type="text" id="new_subgoal" name="new_subgoal" placeholder="Enter new sub-goal" required />
            <button type="submit" name="add_subgoal">Add Sub-goal</button>
        </form>
    </section>

    <section class="progress">
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]) . "?goal_id=$goal_id"; ?>">
            <h2>Sub-goals</h2>
            <ul>
                <?php foreach ($subgoals as $subgoal): ?>
                    <li>
                        <input type="checkbox" id="subgoal_<?php echo $subgoal['subgoal_id']; ?>" name="subgoal_<?php echo $subgoal['subgoal_id']; ?>" <?php echo $subgoal['is_completed'] ? 'checked' : ''; ?> />
                        <label for="subgoal_<?php echo $subgoal['subgoal_id']; ?>"><?php echo htmlspecialchars($subgoal['title']); ?></label>
                    </li>
                <?php endforeach; ?>
            </ul>
            <button type="submit" name="update_progress">Update Progress</button>
        </form>
        <h2>Progress</h2>
        <div class="progress-bar">
            <div class="progress-bar-fill" style="width: <?php echo $goal['progress']; ?>%;"></div>
        </div>
        <p><?php echo $goal['progress']; ?>% Completed</p>
    </section>

</body>

</html>
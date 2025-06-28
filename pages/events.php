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
$titleErr = $descriptionErr = $eventDateErr = "";
$title = $description = $event_date = "";

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["submit"])) {

    // Validate title
    if (empty($_POST["title"])) {
        $titleErr = "Event title is required";
    } else {
        $title = test_input($_POST["title"]);
    }

    // Validate description and set character limit
    if (empty($_POST["description"])) {
        $description = ""; // Description is optional
    } else {
        $description = test_input($_POST["description"]);
        if (strlen($description) > 200) {
            $descriptionErr = "Description must be less than 200 characters";
        }
    }

    // Validate event date
    if (empty($_POST["event_date"])) {
        $eventDateErr = "Event date is required";
    } else {
        $event_date = test_input($_POST["event_date"]);
    }

    // Insert event into the database if no errors
    if (!$titleErr && !$descriptionErr && !$eventDateErr) {
        $sql = "INSERT INTO events (user_id, title, description, event_date) VALUES ('$user_id', '$title', '$description', '$event_date')";
        if (mysqli_query($conn, $sql)) {
            // Add notification logic here
            if ($event_date) {
                $notification_title = mysqli_real_escape_string($conn, "Event Reminder: " . $title);
                $notification_message = mysqli_real_escape_string($conn, "Your event '" . $title . "' is scheduled for " . $event_date);
                $notification_date = mysqli_real_escape_string($conn, $event_date . " 00:00:00");
                $sql = "INSERT INTO notifications (user_id, type, title, message, notification_date) 
                        VALUES ('$user_id', 'Event', '$notification_title', '$notification_message', '$notification_date')";
                mysqli_query($conn, $sql);
            }

            header("Location: " . $_SERVER["PHP_SELF"]);
            exit();
        } else {
            echo "Error: " . mysqli_error($conn);
        }
    }
}

// Handle event deletion
if (isset($_GET['delete'])) {
    $event_id = intval($_GET['delete']);
    $sql = "DELETE FROM events WHERE event_id = $event_id AND user_id = $user_id";
    if (mysqli_query($conn, $sql)) {
        header("Location: " . $_SERVER["PHP_SELF"]);
        exit();
    } else {
        echo "Error deleting event: " . mysqli_error($conn);
    }
}

// Fetch events from the database
$sql = "SELECT * FROM events WHERE user_id = $user_id ORDER BY event_date ASC";
$result = mysqli_query($conn, $sql);
$events = [];
if (mysqli_num_rows($result) > 0) {
    while ($row = mysqli_fetch_assoc($result)) {
        $events[] = $row;
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
    <title>Event Management</title>
    <link rel="stylesheet" href="../css/index.css">
    <link rel="stylesheet" href="../css/event.css">
    <!--Link to fontawesome cdn website to get some icons-->
    <script src="https://kit.fontawesome.com/89c74d5bb8.js" crossorigin="anonymous"></script>
    <script src="../js/nav.js" defer></script>
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

    <!-- Event List Section -->
    <section class="events">
        <h2>Your Events</h2>
        <ul id="event-list">
            <?php foreach ($events as $event): ?>
                <li class="event-item">
                    <div class="event-info">
                        <strong><?php echo htmlspecialchars($event['title']); ?></strong>
                        <span class="tooltip"><?php echo htmlspecialchars($event['description']); ?></span>
                    </div>
                    <span>Date: <?php echo htmlspecialchars($event['event_date']); ?></span>
                    <a href="?delete=<?php echo $event['event_id']; ?>" class="delete-btn">Delete</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </section>

    <!-- Create New Event Section -->
    <h3>Create New Event</h3>
    <form class="event-form" method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
        <label for="title">Event Title</label><br>
        <input type="text" id="title" name="title" placeholder="Enter your event title" required><br>
        <span class="error"><?php echo $titleErr; ?></span><br><br>

        <label for="description">Description</label><br>
        <textarea id="description" name="description" placeholder="Describe your event" maxlength="200"></textarea><br>
        <span class="error"><?php echo $descriptionErr; ?></span><br><br>

        <label for="event_date">Event Date</label><br>
        <input type="date" id="event_date" name="event_date" required><br>
        <span class="error"><?php echo $eventDateErr; ?></span><br><br>

        <button type="submit" name="submit">Add Event</button>
    </form>
</body>

</html>
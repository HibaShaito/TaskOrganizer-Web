<?php
session_start();
include "../includes/conn.php"; // Include database connection

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../pages/SignIn.php");
    exit();
}

$admin_id = intval($_SESSION['login_user']); // Fetch admin ID from session

// Fetch admin details securely
$sql = "SELECT username FROM users WHERE user_id = $admin_id";
$result = mysqli_query($conn, $sql);

if ($result && mysqli_num_rows($result) > 0) {
    $admin = mysqli_fetch_assoc($result);
    $admin_name = $admin['username'];
} else {
    echo "Error: Unable to fetch admin details.";
    exit();
}

// Handle deleting a user
if (isset($_GET['delete_id'])) {
    $delete_id = intval($_GET['delete_id']);

    $delete_sql = "DELETE FROM users WHERE user_id = $delete_id";
    if (mysqli_query($conn, $delete_sql)) {
        echo "<script>alert('User deleted successfully.');</script>";
        header("Location: " . $_SERVER['PHP_SELF']);
        exit();
    } else {
        echo "<script>alert('Error deleting user.');</script>";
    }
}

$nameErr = $emailErr = $websiteErr = "";
$name = $email = $website = $password = "";
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_admin'])) {
    if (empty($_POST["name"])) {
        $nameErr = "Name is required";
    } else {
        $name = test_input($_POST["name"]);
        if (!preg_match("/^[a-zA-Z-' ]*$/", $name)) {
            $nameErr = "Only letters and white space allowed";
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
        $password = password_hash(test_input($_POST["password"]), PASSWORD_BCRYPT);
    }

    // Check if there are no errors before inserting into the database
    if (empty($nameErr) && empty($emailErr) && empty($passwordErr)) {
        // Check if the email already exists
        $email_check_sql = "SELECT * FROM users WHERE email = '$email'";
        $email_check_result = mysqli_query($conn, $email_check_sql);

        if (mysqli_num_rows($email_check_result) > 0) {
            echo "<script>alert('This email address is already registered.');</script>";
        } else {
            $role_id = 1; // Admin role
            $insert_sql = "INSERT INTO users (username, email, password_hash, role_id) VALUES ('$name', '$email', '$password', $role_id)";
            if (mysqli_query($conn, $insert_sql)) {
                echo "<script>alert('New admin added successfully.');</script>";
                header("Location: " . $_SERVER['PHP_SELF']);
                exit();
            } else {
                echo "<script>alert('Error adding new admin.');</script>";
            }
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



// Fetch all admins
$admins_sql = "SELECT * FROM users WHERE role_id = 1";
$admins_result = mysqli_query($conn, $admins_sql);

// Fetch all users (non-admins)
$users_sql = "SELECT * FROM users WHERE role_id = 2";
$users_result = mysqli_query($conn, $users_sql);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Page</title>
    <link rel="stylesheet" href="../css/admin.css">
</head>

<body>
    <div class="container">
        <!-- Logout Button -->
        <form method="POST" action="../pages/logout.php" style="text-align: right;">
            <button type="submit" name="logout">Logout</button>
        </form>

        <!-- Welcome Message -->
        <h2>Welcome Admin, <?php echo $admin_name; ?></h2>

        <!-- Add New Admin Form -->
        <h3>Add New Admin</h3>
        <form method="POST">
            <label for="name">Name:</label>
            <input type="text" name="name" required>
            <span class="error"><?php echo '<p style=color:red;>*' . $nameErr . '</p>'; ?></span>
            <br>
            <label for="email">Email:</label>
            <input type="email" name="email" required>
            <span class="error"> <?php echo '<p style=color:red;>*' . $emailErr . '</p>'; ?></span>
            <br>
            <label for="password">Password:</label>
            <input type="password" name="password" required>
            <button type="submit" name="add_admin">Add Admin</button>
        </form>

        <!-- Admins Table -->
        <h3>Admins</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($admin = mysqli_fetch_assoc($admins_result)) { ?>
                    <tr>
                        <td><?php echo $admin['user_id']; ?></td>
                        <td><?php echo $admin['username']; ?></td>
                        <td><?php echo $admin['email']; ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>

        <!-- Users Table -->
        <h3>Users</h3>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Name</th>
                    <th>Email</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($user = mysqli_fetch_assoc($users_result)) { ?>
                    <tr>
                        <td><?php echo $user['user_id']; ?></td>
                        <td><?php echo $user['username']; ?></td>
                        <td><?php echo $user['email']; ?></td>
                        <td>
                            <a href="?delete_id=<?php echo $user['user_id']; ?>">Delete</a>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>

</html>
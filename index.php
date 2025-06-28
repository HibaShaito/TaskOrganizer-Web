<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Life Planner - Home</title>
  <link rel="stylesheet" href="css/index.css">
  <!--Link to fontawesome cdn website to get some icons-->
  <script
    src="https://kit.fontawesome.com/89c74d5bb8.js"
    crossorigin="anonymous"></script>
  <script src="js/nav.js" defer></script>


</head>

<body>
  <!-- Header Section -->
  <?php include 'includes/header.php'; ?>
  <!-- Hero Section -->
  <section class="hero">
    <div class="hero-content">
      <h1>Welcome to Life Planner</h1>
      <p>Organize your life with ease and achieve your goals today!</p>
    </div>
  </section>

  <!-- Features Section -->
  <main>
    <section class="features">
      <h2>Your Life Planner Tools</h2>
      <div class="feature-grid">
        <a href="pages/dashboard.php" class="feature-box">
          <h3>Your Dashboard</h3>
          <p>Overview of your current tasks, goals, and progress.</p>
        </a>

        <a href="pages/tasks.php" class="feature-box">
          <h3>Manage Tasks</h3>
          <p>Keep track of your daily, weekly, or long-term tasks.</p>
        </a>

        <a href="pages/goals.php" class="feature-box">
          <h3>Track Goals</h3>
          <p>Set goals and monitor your progress towards achieving them.</p>
        </a>

        <a href="pages/events.php" class="feature-box">
          <h3>Upcoming Events</h3>
          <p>Plan and manage your personal and work-related events.</p>
        </a>

        <a href="pages/profile.php" class="feature-box">
          <h3>Your Profile</h3>
          <p>View and update your personal information.</p>
        </a>

        <a href="pages/notifications.php" class="feature-box">
          <h3>Notifications</h3>
          <p>View important reminders and updates.</p>
        </a>
      </div>
    </section>
  </main>

  <!-- Footer -->
  <?php include 'includes/footer.php'; ?>
</body>

</html>
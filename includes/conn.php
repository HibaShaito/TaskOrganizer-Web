<?php
// config.php file
define("DB_SERVER", "localhost"); // Change to your database server address
define("DB_USER", "root"); // Change to your database username
define("DB_PASSWORD", ""); // Change to your database password
define("DB_DBNAME", "life-planner"); // Change to your database name

// Create connection
$conn = mysqli_connect(DB_SERVER, DB_USER, DB_PASSWORD, DB_DBNAME);

// Check connection
if (!$conn) {
  // If connection fails, show an alert with the error message
  echo '<script type="text/javascript"> 
            alert("Error connecting to the database: ' . mysqli_connect_error() . '");
          </script>';
  exit; // Stop script execution if connection fails
}

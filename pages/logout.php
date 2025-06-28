<?php
session_start();
session_unset(); // Clear session variables
// Handle logout
if (session_destroy()) { // Destroy the session) {    
    header("Location: ../pages/SignIn.php"); // Redirect to login page
    exit();
}

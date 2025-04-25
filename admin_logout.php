<?php
// logout.php - Destroy the session and logout the admin

session_start();

// Destroy the session to log out the user
session_unset();  // Removes all session variables
session_destroy();  // Destroys the session

// Redirect to the login page after logging out
header("Location: login.php");
exit();
?>

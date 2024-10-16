<?php
// Start the session to access session variables if needed
session_start();

// Redirect to dashboard.php
header("Location: dashboard.php");

// Exit to ensure no further code is executed
exit();
?>

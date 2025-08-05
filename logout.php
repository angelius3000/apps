<?php # Script 12.11 - logout.php #2
// This page lets the user logout.
// This version uses sessions.

session_start(); // Access the existing session.
session_unset();

// If no session variable exists, redirect the user:
if (!isset($_SESSION['Username'])) {

	// Need the functions:
	require('includes/login_functions.inc.php');
	redirect_user();
} else { // Cancel the session:

	//$_SESSION = array(); // Clear the variables.
	session_unset();
	session_destroy(); // Destroy the session itself.
	//setcookie ('PHPSESSID', '', time()-3600, '/', '', 0, 0); // Destroy the cookie.

}

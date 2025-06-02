<?php
// Initialize the session
session_start();

// Unset all of the session variables
$_SESSION = array();

// Destroy the session.
session_destroy();

// Redirect to login page
echo "使用者已登出，轉入首頁...";
header('Refresh: 2; URL = book_list.php');
?>
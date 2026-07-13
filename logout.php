<?php
require_once 'session_check.php';
session_destroy();

// Clear all cookies
foreach ($_COOKIE as $name => $value) {
    setcookie($name, '', time() - 3600, '/');
}

// Redirect to login
header('Location: dangnhap.php');
exit;
?>

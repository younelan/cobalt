<?php
session_start();

// Clear all session variables and destroy session
$_SESSION = array();
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/');
}
session_destroy();

// Clear localStorage and redirect
echo "<script>localStorage.clear(); window.location.href = 'index.php';</script>";

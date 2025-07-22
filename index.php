<?php
require_once __DIR__ . '/init.php';
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/DBSessionManager.php';

if (!isset($_SESSION['db_credentials'])) {
    header('Location: login.php');
    exit;
}
header('Location: welcome.php');
exit;

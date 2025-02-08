<?php
require_once '../config.php';

if (!isset($_SESSION['db_user']) || !isset($_SESSION['db_pass'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}

header('Content-Type: application/json');

if (!isset($_GET['db'])) {
    echo json_encode([]);
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;dbname={$_GET['db']};charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->query('SHOW TABLES');
    echo json_encode($stmt->fetchAll(PDO::FETCH_COLUMN));
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

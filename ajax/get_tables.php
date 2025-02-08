<?php
require_once '../config.php';

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

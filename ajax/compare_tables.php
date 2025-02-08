<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['db1']) || !isset($_GET['db2'])) {
    echo json_encode(['db1' => [], 'db2' => [], 'differences' => []]);
    exit;
}

try {
    $pdo1 = new PDO("mysql:host=$host;dbname={$_GET['db1']};charset=utf8mb4", $user, $password);
    $pdo2 = new PDO("mysql:host=$host;dbname={$_GET['db2']};charset=utf8mb4", $user, $password);
    
    $tables1 = $pdo1->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    $tables2 = $pdo2->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    
    $differences = [];
    foreach (array_intersect($tables1, $tables2) as $table) {
        // Get columns for both tables
        $cols1 = $pdo1->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $cols2 = $pdo2->query("SHOW COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        $tableDiffs = [];
        
        // Compare columns
        foreach ($cols1 as $col) {
            $col2 = array_filter($cols2, fn($c) => $c['Field'] === $col['Field']);
            if (empty($col2)) {
                $tableDiffs[] = ["Field missing in db2: {$col['Field']}"];
            } else {
                $col2 = current($col2);
                if ($col['Type'] !== $col2['Type']) {
                    $tableDiffs[] = ["Field {$col['Field']} type mismatch: {$col['Type']} vs {$col2['Type']}"];
                }
            }
        }
        
        // Check for columns in db2 that don't exist in db1
        foreach ($cols2 as $col) {
            if (!array_filter($cols1, fn($c) => $c['Field'] === $col['Field'])) {
                $tableDiffs[] = ["Field missing in db1: {$col['Field']}"];
            }
        }
        
        if (!empty($tableDiffs)) {
            $differences[$table] = $tableDiffs;
        }
    }
    
    echo json_encode([
        'db1' => array_values(array_diff($tables1, $tables2)),
        'db2' => array_values(array_diff($tables2, $tables1)),
        'differences' => $differences
    ]);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

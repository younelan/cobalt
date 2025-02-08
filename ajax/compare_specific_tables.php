<?php
require_once '../config.php';

header('Content-Type: application/json');

if (!isset($_GET['db1'], $_GET['db2'], $_GET['table1'], $_GET['table2'])) {
    echo json_encode([
        'error' => 'Missing parameters',
        'missingInDb1' => [],
        'missingInDb2' => [],
        'typeMismatches' => []
    ]);
    exit;
}

try {
    $pdo1 = new PDO("mysql:host=$host;dbname={$_GET['db1']};charset=utf8mb4", $user, $password);
    $pdo2 = new PDO("mysql:host=$host;dbname={$_GET['db2']};charset=utf8mb4", $user, $password);
    
    // Get columns for both tables
    $cols1 = $pdo1->query("SHOW COLUMNS FROM `{$_GET['table1']}`")->fetchAll(PDO::FETCH_ASSOC);
    $cols2 = $pdo2->query("SHOW COLUMNS FROM `{$_GET['table2']}`")->fetchAll(PDO::FETCH_ASSOC);
    
    $missingInDb1 = [];
    $missingInDb2 = [];
    $typeMismatches = [];
    
    // Check fields in table1 against table2
    foreach ($cols1 as $col) {
        $matchingCol = array_filter($cols2, fn($c) => $c['Field'] === $col['Field']);
        if (empty($matchingCol)) {
            $missingInDb2[] = $col['Field'];
        } else {
            $matchingCol = current($matchingCol);
            if ($matchingCol['Type'] !== $col['Type']) {
                $typeMismatches[] = [
                    'field' => $col['Field'],
                    'db1Type' => $col['Type'],
                    'db2Type' => $matchingCol['Type']
                ];
            }
        }
    }
    
    // Check for fields in table2 that don't exist in table1
    foreach ($cols2 as $col) {
        if (!array_filter($cols1, fn($c) => $c['Field'] === $col['Field'])) {
            $missingInDb1[] = $col['Field'];
        }
    }
    
    $identicalStructure = empty($missingInDb1) && empty($missingInDb2) && empty($typeMismatches);
    
    echo json_encode([
        'identicalStructure' => $identicalStructure,
        'missingInDb1' => $missingInDb1,
        'missingInDb2' => $missingInDb2,
        'typeMismatches' => $typeMismatches
    ]);

} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}

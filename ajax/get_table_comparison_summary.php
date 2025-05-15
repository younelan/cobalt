<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/DBSessionManager.php';
require_once __DIR__ . '/../classes/TableComparator.php';

if (!isset($_POST['db1']) || !isset($_POST['db2']) || !isset($_POST['table1']) || !isset($_POST['table2'])) {
    exit(T('Invalid request'));
}

try {
    $session = new DBSessionManager();
    $credentials = $session->getCredentials();
    $db = new Database($credentials['host'], $credentials['username'], $credentials['password']);
    
    // Create a special comparator for the two specific tables
    $comparator = new TableComparator($db, $_POST['db1'], $_POST['db2']);
    $differences = $comparator->getTableDifferences($_POST['table1']);
    
    // Generate the summary section HTML
    $html = '<div class="diff-line mb-2">
        <strong>Comparing:</strong> ' . htmlspecialchars($_POST['table1']) . ' with ' . htmlspecialchars($_POST['table2']) . '
    </div>';

    if (empty($differences)) {
        $html .= '<div class="text-success">' . T('Tables are identical') . '</div>';
    } else {
        // Group differences by type
        $missingDiffs = [];
        $typeDiffs = [];
        $indexDiffs = [];

        foreach ($differences as $diff) {
            switch ($diff['class']) {
                case 'diff-missing':
                    $missingDiffs[] = sprintf('%s: %s', $diff['label'], $diff['content']);
                    break;
                case 'diff-type':
                    $typeDiffs[] = $diff['content'];
                    break;
                case 'diff-index':
                    $indexDiffs[] = sprintf('%s: %s', $diff['label'], $diff['content']);
                    break;
            }
        }

        if (!empty($missingDiffs)) {
            $html .= '<div class="text-danger mb-1">' . implode(' | ', $missingDiffs) . '</div>';
        }
        if (!empty($typeDiffs)) {
            $html .= '<div class="text-warning">' . T('Type mismatches') . ': ' . implode(', ', $typeDiffs) . '</div>';
        }
        if (!empty($indexDiffs)) {
            $html .= '<div class="text-info">' . implode(' | ', $indexDiffs) . '</div>';
        }
    }

    echo $html;
} catch (Exception $e) {
    echo 'Error: ' . htmlspecialchars($e->getMessage());
}

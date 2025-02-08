<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/DBSessionManager.php';
require_once __DIR__ . '/../classes/TableComparator.php';

if (!isset($_POST['db1']) || !isset($_POST['db2']) || !isset($_POST['table1']) || !isset($_POST['table2'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    $session = new DBSessionManager();
    $credentials = $session->getCredentials();
    $db = new Database($credentials['host'], $credentials['username'], $credentials['password']);
    $comparator = new TableComparator($db, $_POST['db1'], $_POST['db2']);

    // Important: Keep the original table name for display
    $original_table = $_POST['table1'];
    $compare_with = $_POST['table2'];

    // Run a fresh comparison between the two tables
    $exists_in_db2 = $comparator->tableExistsInDb2($original_table);
    $comparison = $comparator->compareTableStructure($original_table, $compare_with); // Compare with selected table
    $differences = $comparator->getTableDifferences($original_table, $compare_with); // Pass both tables
    
    // Set status based on new comparison
    $statusClass = empty($differences) ? 'table-identical' : 'table-different';
    $statusBadgeClass = empty($differences) ? 'bg-success' : 'bg-warning';
    $statusText = empty($differences) ? 'Identical' : 'Structure Mismatch';

    // Group differences for display
    $missingDiffs = [];
    $typeDiffs = [];
    $indexDiffs = [];
    
    foreach ($differences as $diff) {
        switch ($diff['class']) {
            case 'diff-missing':
                $missingDiffs[] = sprintf(
                    '<span class="diff-item missing">%s: %s</span>',
                    htmlspecialchars($diff['label']),
                    htmlspecialchars($diff['content'])
                );
                break;
            case 'diff-type':
                $field = substr($diff['content'], 0, strpos($diff['content'], ':'));
                $types = explode(' → ', substr($diff['content'], strpos($diff['content'], ':') + 2));
                $typeDiffs[] = sprintf(
                    '<span class="diff-item mismatch"><strong>%s</strong>: %s<span class="diff-arrow">→</span>%s</span>',
                    htmlspecialchars($field),
                    htmlspecialchars($types[0]),
                    htmlspecialchars($types[1])
                );
                break;
            case 'diff-index':
                $indexDiffs[] = sprintf(
                    '<span class="diff-item index">%s: %s</span>',
                    htmlspecialchars($diff['label']),
                    htmlspecialchars($diff['content'])
                );
                break;
        }
    }

    // Generate the summary section
    $summary = '<div class="summary-section p-3 bg-light border-bottom">';
    if (!empty($missingDiffs)) {
        $summary .= '<div class="diff-line">' . implode(' ', $missingDiffs) . '</div>';
    }
    if (!empty($typeDiffs)) {
        $summary .= '<div class="diff-line">
            <span class="diff-title">Mismatches:</span>
            <div class="diff-items-wrapper">' . implode(' ', $typeDiffs) . '</div>
        </div>';
    }
    if (!empty($indexDiffs)) {
        $summary .= '<div class="diff-line">' . implode(' ', $indexDiffs) . '</div>';
    }
    $summary .= '</div>';

    // Generate dropdown with all available tables
    $table_select = '<select class="form-select form-select-sm compare-with" style="width: auto; display: inline-block; margin-left: 10px;">';
    
    // Always include empty option for consistency
    $table_select .= '<option value="">Select table</option>';
    
    foreach ($comparator->getTables2() as $compare_table) {
        $compare_name = $compare_table['TABLE_NAME'];
        // Use the explicitly passed selected value
        $selected = ($compare_name === $_POST['selected']) ? 'selected' : '';
        $table_select .= sprintf(
            '<option value="%s" %s>%s</option>',
            htmlspecialchars($compare_name),
            $selected,
            htmlspecialchars($compare_name)
        );
    }
    $table_select .= '</select>';

    // If this was a missing table comparison that's now being compared
    $statusClass = 'table-different';
    $statusBadgeClass = 'bg-warning';
    $statusText = 'Structure Mismatch';
    
    if (isset($_POST['was_missing']) && $_POST['was_missing']) {
        // We're comparing to a newly selected table
        $table_name = $_POST['table2'];  // Use the selected table as the main table
    }

    // Generate the row HTML - use original_table for display and data attribute
    $html = sprintf(
        '<div id="table-row-%s" class="table-entry border-bottom table-diff %s" data-table="%s">
            <div class="row p-2 align-items-center">
                <div class="col-auto"><input type="checkbox" class="form-check-input table-select"></div>
                <div class="col">
                    <span class="h6 mb-0">%s</span>
                    <span class="text-muted">compare with</span>
                    %s
                    <span class="badge %s status-badge ms-2">%s</span>
                    <button class="btn btn-sm btn-primary float-end compare-details">Details</button>
                </div>
            </div>
            %s
        </div>',
        htmlspecialchars($original_table),  // Keep original table ID
        empty($differences) ? 'table-identical' : 'table-different',
        htmlspecialchars($original_table),  // Keep original table in data attribute
        htmlspecialchars($original_table),  // Display original table name
        $table_select,
        empty($differences) ? 'bg-success' : 'bg-warning',
        empty($differences) ? 'Identical' : 'Structure Mismatch',
        $summary
    );

    echo json_encode([
        'success' => true,
        'html' => $html,
        'summary' => $summary
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error performing comparison: ' . $e->getMessage()]);
}

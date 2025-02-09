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
    $details = '';
    if ($exists_in_db2 && !empty($differences)) {
        // Group differences by type
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

        $details .= '<div class="diff-summary px-4 pb-2">';
        if (!empty($missingDiffs)) {
            $details .= '<div class="diff-line">' . implode(' ', $missingDiffs) . '</div>';
        }
        if (!empty($typeDiffs)) {
            $details .= '<div class="diff-line">
                <span class="diff-title">Mismatches:</span>
                <div class="diff-items-wrapper">' . implode(' ', $typeDiffs) . '</div>
            </div>';
        }
        if (!empty($indexDiffs)) {
            $details .= '<div class="diff-line">' . implode(' ', $indexDiffs) . '</div>';
        }
        $details .= '</div>';
    }

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

    // Get fresh comparison results
    $comparison = $comparator->compareTableStructure($original_table, $compare_with, true);
    $differences = $comparator->getTableDifferences($original_table, $compare_with, true);

    // Set proper status based on comparison results
    $statusClass = empty($differences) ? 'table-identical' : 'table-different';
    $statusBadgeClass = empty($differences) ? 'bg-success' : 'bg-warning';
    $statusText = empty($differences) ? 'Identical' : 'Structure Mismatch';

    if (!$exists_in_db2) {
        $statusClass = 'table-missing';
        $statusBadgeClass = 'bg-danger';
        $statusText = 'Missing in DB2';
    }

    // Create view switch dropdown
    $viewDropdown = sprintf(
        '<div class="dropdown d-inline-block float-end">
            <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Summary
            </button>
            <ul class="dropdown-menu">
                <li><a class="dropdown-item view-summary active" href="#">Summary</a></li>
                <li><a class="dropdown-item view-details" href="#">Detailed</a></li>
            </ul>
        </div>'
    );

    // Force fresh comparison if requested
    if (isset($_POST['force_fresh']) && $_POST['force_fresh']) {
        $comparison = $comparator->compareTableStructure($original_table, $compare_with, true);
        $differences = $comparator->getTableDifferences($original_table, $compare_with, true);
    }

    // Generate the row HTML
    $html = sprintf(
        '<div id="table-row-%s" class="table-entry border-bottom table-diff %s" data-table="%s">
            <div class="row p-2 align-items-center">
                <div class="col-auto"><input type="checkbox" class="form-check-input table-select"></div>
                <div class="col">
                    <span class="h6 mb-0">%s</span>
                    <span class="text-muted">compare with</span>
                    %s
                    <span class="badge %s status-badge ms-2">%s</span>
                    %s
                </div>
            </div>
            %s
        </div>',
        htmlspecialchars($original_table),
        $statusClass,
        htmlspecialchars($original_table),
        htmlspecialchars($original_table),
        $table_select,
        $statusBadgeClass,
        $statusText,
        $viewDropdown,
        $details
    );

    echo json_encode([
        'success' => true,
        'html' => $html,
        'summary' => $details
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error performing comparison: ' . $e->getMessage()]);
}

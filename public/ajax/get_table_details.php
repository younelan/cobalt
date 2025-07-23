<?php
session_start();
$basedir = dirname(dirname(__DIR__));
require_once $basedir . '/init.php';
require_once $basedir . '/classes/Database.php';
require_once $basedir . '/classes/DBSessionManager.php';
require_once $basedir . '/classes/TableComparator.php';

if (!isset($_POST['db1']) || !isset($_POST['db2']) || !isset($_POST['table1']) || !isset($_POST['table2'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    $session = new DBSessionManager();
    $credentials = $session->getCredentials();
    $db = new Database($credentials['host'], $credentials['username'], $credentials['password']);
    $comparator = new TableComparator($db, $_POST['db1'], $_POST['db2']);

    $table1 = $_POST['table1'];
    $table2 = $_POST['table2'];
    
    $columns1 = $db->getTableColumns($_POST['db1'], $table1);
    $columns2 = $db->getTableColumns($_POST['db2'], $table2);
    $indexes1 = $db->getTableIndexes($_POST['db1'], $table1);
    $indexes2 = $db->getTableIndexes($_POST['db2'], $table2);

    // Get actual comparison status
    $comparison = $comparator->compareTableStructure($table1, $table2, true);
    $differences = $comparator->getTableDifferences($table1, $table2, true);
    $statusBadgeClass = empty($differences) ? 'bg-success' : 'bg-warning';
    $statusText = empty($differences) ? 'Identical' : 'Structure Mismatch';
    
    if (!$comparator->tableExistsInDb2($table1)) {
        $statusBadgeClass = 'bg-danger';
        $statusText = 'Missing in DB2';
    }

    // Build detailed comparison view
    $html = sprintf(
        '<div id="table-row-%s" class="table-entry border-bottom p-3" data-table="%s">
            <div class="row p-2 align-items-center">
                <div class="col-auto"><input type="checkbox" class="form-check-input table-select"></div>
                <div class="col">
                    <span class="h6 mb-0">%s</span>
                    <span class="text-muted">compare with</span>
                    %s
                    <span class="badge %s status-badge ms-2">%s</span>
                    <div class="dropdown d-inline-block float-end">
                        <button class="btn btn-sm btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                            Detailed
                        </button>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item view-summary" href="#" data-table="%s" data-compare="%s">Summary</a></li>
                            <li><a class="dropdown-item view-details active" href="#" data-table="%s" data-compare="%s">Detailed</a></li>
                        </ul>
                    </div>
                </div>
            </div>',
        htmlspecialchars($table1),
        htmlspecialchars($table1),
        htmlspecialchars($table1),
        $comparator->generateTableSelect($table2),
        $statusBadgeClass,
        $statusText,
        htmlspecialchars($table1),
        htmlspecialchars($table2),
        htmlspecialchars($table1),
        htmlspecialchars($table2)
    );

    $html .= '<div class="detailed-comparison mt-3">
        <h6 class="border-bottom pb-2">Column Comparison</h6>
        <div class="table-responsive">
            <table class="table table-sm comparison-table">
                <thead>
                    <tr>
                        <th>Column</th>
                        <th>Database 1</th>
                        <th>Database 2</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>';

    // Process columns
    $all_columns = array_unique(array_merge(
        array_column($columns1, 'COLUMN_NAME'),
        array_column($columns2, 'COLUMN_NAME')
    ));
    sort($all_columns);

    foreach ($all_columns as $column) {
        $col1 = array_values(array_filter($columns1, function($c) use ($column) { 
            return $c['COLUMN_NAME'] === $column; 
        }))[0] ?? null;
        
        $col2 = array_values(array_filter($columns2, function($c) use ($column) { 
            return $c['COLUMN_NAME'] === $column; 
        }))[0] ?? null;

        $status_class = 'text-success';
        $status_icon = '✓ Identical';

        if (!$col1 || !$col2) {
            $status_class = 'text-danger';
            $status_icon = '! Missing';
        } elseif ($col1['COLUMN_TYPE'] !== $col2['COLUMN_TYPE']) {
            $status_class = 'text-warning';
            $status_icon = '≠ Type';
        }

        $html .= sprintf(
            '<tr>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
                <td class="%s">%s</td>
            </tr>',
            htmlspecialchars($column),
            $col1 ? htmlspecialchars($col1['COLUMN_TYPE']) : '<span class="text-danger">[Missing]</span>',
            $col2 ? htmlspecialchars($col2['COLUMN_TYPE']) : '<span class="text-danger">[Missing]</span>',
            $status_class,
            $status_icon
        );
    }

    $html .= '</tbody></table></div>';

    // Add Index Comparison
    $html .= '<h6 class="border-bottom pb-2 mt-4">Index Comparison</h6>
        <div class="table-responsive">
            <table class="table table-sm comparison-table">
                <thead>
                    <tr>
                        <th>Index</th>
                        <th>Database 1</th>
                        <th>Database 2</th>
                    </tr>
                </thead>
                <tbody>';

    // Process indexes
    $all_indexes = array_unique(array_merge(
        array_column($indexes1, 'INDEX_NAME'),
        array_column($indexes2, 'INDEX_NAME')
    ));
    sort($all_indexes);

    foreach ($all_indexes as $index) {
        $idx1_cols = array_column(array_filter($indexes1, function($i) use ($index) { 
            return $i['INDEX_NAME'] === $index; 
        }), 'COLUMN_NAME');
        
        $idx2_cols = array_column(array_filter($indexes2, function($i) use ($index) { 
            return $i['INDEX_NAME'] === $index; 
        }), 'COLUMN_NAME');

        $html .= sprintf(
            '<tr>
                <td>%s</td>
                <td>%s</td>
                <td>%s</td>
            </tr>',
            htmlspecialchars($index),
            !empty($idx1_cols) ? htmlspecialchars(implode(', ', $idx1_cols)) : '<span class="text-danger">[Missing]</span>',
            !empty($idx2_cols) ? htmlspecialchars(implode(', ', $idx2_cols)) : '<span class="text-danger">[Missing]</span>'
        );
    }

    $html .= '</tbody></table></div></div>';

    // Close the main div
    $html .= '</div>';

    echo json_encode([
        'success' => true,
        'html' => $html
    ]);

} catch (Exception $e) {
    echo json_encode(['error' => 'Error loading details: ' . $e->getMessage()]);
}

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

    $table_name = $_POST['table1'];
    $compare_with = $_POST['table2'];
    
    // Create the table dropdown
    $table_select = '<select class="form-select form-select-sm compare-with" style="width: auto; display: inline-block; margin-left: 10px;">';
    foreach ($comparator->getTables2() as $compare_table) {
        $compare_name = $compare_table['TABLE_NAME'];
        $selected = ($compare_name === $compare_with) ? 'selected' : '';
        $table_select .= sprintf(
            '<option value="%s" %s>%s</option>',
            htmlspecialchars($compare_name),
            $selected,
            htmlspecialchars($compare_name)
        );
    }
    $table_select .= '</select>';

    // Get differences between the selected tables
    $differences = $comparator->getTableDifferences($table_name);
    $status = empty($differences) ? 'identical' : 'different';
    $statusClass = empty($differences) ? 'table-identical' : 'table-different';
    $statusBadgeClass = empty($differences) ? 'bg-success' : 'bg-warning';
    $statusText = empty($differences) ? 'Identical' : 'Structure Mismatch';

    // Generate the details section with differences
    $details = '';
    if (!empty($differences)) {
        $diffHtml = '<div class="diff-group">';
        // ...same difference processing as in compare_tables.php...
        $diffHtml .= '</div>';
        $details = '<div class="px-4 pb-2">' . $diffHtml . '</div>';
    }

    // Generate the updated table entry HTML
    $html = sprintf(
        '<div class="table-entry border-bottom table-diff %s" data-table="%s">
            <div class="row p-2 align-items-center">
                <div class="col-auto"><input type="checkbox" class="form-check-input table-select me-2"></div>
                <div class="col">
                    <span class="h6 mb-0">%s</span>
                    <span class="text-muted">compare with</span>%s
                    <span class="badge %s status-badge ms-2">%s</span>
                    <button class="btn btn-sm btn-primary float-end compare-details">Details</button>
                </div>
            </div>
            %s
        </div>',
        $statusClass,
        htmlspecialchars($table_name),
        htmlspecialchars($table_name),
        $table_select,
        $statusBadgeClass,
        htmlspecialchars($statusText),
        $details
    );

    echo json_encode([
        'success' => true,
        'html' => $html
    ]);
} catch (Exception $e) {
    echo json_encode(['error' => 'Error performing comparison: ' . $e->getMessage()]);
}

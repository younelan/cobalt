<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/DBSessionManager.php';

if (!isset($_POST['db1']) || !isset($_POST['db2'])) {
    exit('Invalid request');
}

$session = new DBSessionManager();
$credentials = $session->getCredentials();
$db = new Database($credentials['host'], $credentials['username'], $credentials['password']);

$tables1 = $db->getTables($_POST['db1']);
$tables2 = $db->getTables($_POST['db2']);

$session->setSelectedDatabases($_POST['db1'], $_POST['db2']);

$stats = [
    'total1' => count($tables1),
    'total2' => count($tables2),
    'identical' => 0,
    'missing_db1' => 0,
    'missing_db2' => 0
];

// Create lookup arrays for easier comparison
$tables1_lookup = array_column($tables1, null, 'TABLE_NAME');
$tables2_lookup = array_column($tables2, null, 'TABLE_NAME');

foreach ($tables2_lookup as $table_name => $table_info) {
    if (!isset($tables1_lookup[$table_name])) {
        $stats['missing_db1']++;
    }
}

$output = '<div class="card mb-4">
    <div class="card-header bg-primary text-white">
        <h5 class="mb-0">Comparison Summary</h5>
    </div>
    <div class="card-body">
        <div class="row text-center">
            <div class="col-md-3">
                <h6>Total Tables</h6>
                <p class="h4">DB1: ' . $stats['total1'] . ' | DB2: ' . $stats['total2'] . '</p>
            </div>
            <div class="col-md-3">
                <h6>Identical Tables</h6>
                <p class="h4 text-success">' . $stats['identical'] . '</p>
            </div>
            <div class="col-md-3">
                <h6>Missing in DB1</h6>
                <p class="h4 text-danger">' . $stats['missing_db1'] . '</p>
            </div>
            <div class="col-md-3">
                <h6>Missing in DB2</h6>
                <p class="h4 text-danger">' . $stats['missing_db2'] . '</p>
            </div>
        </div>
    </div>
</div>
<div class="card">
    <div class="card-header">
        <div class="row align-items-center">
            <div class="col-1"><input type="checkbox" id="select-all" class="form-check-input"></div>
            <div class="col-3">Table Name</div>
            <div class="col-2">Engine</div>
            <div class="col-2">Rows</div>
            <div class="col-2">Status</div>
            <div class="col-2">Action</div>
        </div>
    </div>
    <div class="card-body p-0">';

foreach ($tables1 as $table) {
    $table_name = $table['TABLE_NAME'];
    $exists_in_db2 = isset($tables2_lookup[$table_name]);
    $status = $exists_in_db2 ? 'identical' : 'missing';
    $statusClass = $status === 'identical' ? 'table-identical' : 'table-missing';
    $statusBadgeClass = $status === 'identical' ? 'bg-success' : 'bg-danger';
    $statusText = $status === 'identical' ? 'Identical' : 'Missing in DB2';
    
    if ($exists_in_db2) {
        $stats['identical']++;
    } else {
        $stats['missing_db2']++;
    }
    
    $output .= sprintf(
        '<div class="row p-2 border-bottom table-diff %s align-items-center">
            <div class="col-1"><input type="checkbox" class="form-check-input table-select"></div>
            <div class="col-3">%s</div>
            <div class="col-2">%s</div>
            <div class="col-2">%s</div>
            <div class="col-2"><span class="badge %s status-badge">%s</span></div>
            <div class="col-2">
                <button class="btn btn-sm btn-primary compare-details" 
                        data-table="%s" 
                        data-db1="%s" 
                        data-db2="%s">Details</button>
            </div>
        </div>',
        $statusClass,
        htmlspecialchars($table_name),
        htmlspecialchars($table['ENGINE']),
        htmlspecialchars($table['TABLE_ROWS']),
        $statusBadgeClass,
        $statusText,
        htmlspecialchars($table_name),
        htmlspecialchars($_POST['db1']),
        htmlspecialchars($_POST['db2'])
    );
}

// Add missing tables from DB2
foreach ($tables2 as $table) {
    $table_name = $table['TABLE_NAME'];
    if (!isset($tables1_lookup[$table_name])) {
        $output .= sprintf(
            '<div class="row p-2 border-bottom table-diff table-missing align-items-center">
                <div class="col-1"><input type="checkbox" class="form-check-input table-select"></div>
                <div class="col-3">%s</div>
                <div class="col-2">%s</div>
                <div class="col-2">%s</div>
                <div class="col-2"><span class="badge bg-danger status-badge">Missing in DB1</span></div>
                <div class="col-2">
                    <button class="btn btn-sm btn-primary compare-details" 
                            data-table="%s" 
                            data-db1="%s" 
                            data-db2="%s">Details</button>
                </div>
            </div>',
            htmlspecialchars($table_name),
            htmlspecialchars($table['ENGINE']),
            htmlspecialchars($table['TABLE_ROWS']),
            htmlspecialchars($table_name),
            htmlspecialchars($_POST['db1']),
            htmlspecialchars($_POST['db2'])
        );
    }
}

$output .= '</div></div>';
echo $output;

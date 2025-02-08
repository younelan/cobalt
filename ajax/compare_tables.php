<?php
session_start();
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/DBSessionManager.php';
require_once __DIR__ . '/../classes/TableComparator.php';

if (!isset($_POST['db1']) || !isset($_POST['db2'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

try {
    $session = new DBSessionManager();
    $credentials = $session->getCredentials();
    $db = new Database($credentials['host'], $credentials['username'], $credentials['password']);
    $comparator = new TableComparator($db, $_POST['db1'], $_POST['db2']);

    $session->setSelectedDatabases($_POST['db1'], $_POST['db2']);
    $stats = $comparator->getStats();

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
                <div class="col-auto"><input type="checkbox" id="select-all" class="form-check-input me-2"></div>
                <div class="col">Tables Comparison</div>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="summary-section p-3 bg-light border-bottom">
                <div class="text-danger mb-1">Missing Tables in DB1: ' . implode(', ', $comparator->getMissingInDb1()) . '</div>
                <div class="text-danger mb-1">Missing Tables in DB2: ' . implode(', ', $comparator->getMissingInDb2()) . '</div>
                <div class="text-warning">Structure Mismatches: ' . implode(', ', $comparator->getStructureMismatches()) . '</div>
            </div>';

    foreach ($comparator->getTables1() as $table) {
        $table_name = $table['TABLE_NAME'];
        $exists_in_db2 = $comparator->tableExistsInDb2($table_name);
        
        if ($exists_in_db2) {
            $comparison = $comparator->compareTableStructure($table_name);
            $differences = $comparator->getTableDifferences($table_name);
            $status = empty($differences) ? 'identical' : 'different';
            $statusClass = empty($differences) ? 'table-identical' : 'table-different';
            $statusBadgeClass = empty($differences) ? 'bg-success' : 'bg-warning';
            $statusText = empty($differences) ? 'Identical' : 'Structure Mismatch';
            
            if (empty($differences)) {
                $stats['identical']++;
            }
        } else {
            $status = 'missing';
            $statusClass = 'table-missing';
            $statusBadgeClass = 'bg-danger';
            $statusText = 'Missing in DB2';
            $differences = ['Table does not exist in DB2'];
        }
        
        $details = '';
        if ($exists_in_db2 && !empty($differences)) {
            $diffLines = [];
            foreach ($differences as $diff) {
                $diffLines[] = sprintf(
                    '<span class="diff-label %s">%s</span>%s',
                    htmlspecialchars($diff['class']),
                    htmlspecialchars($diff['label']),
                    htmlspecialchars($diff['content'])
                );
            }
            $details = '<div class="px-4 pb-2"><div class="text-muted">' . 
                      implode(' | ', $diffLines) . 
                      '</div></div>';
        }

        $output .= sprintf(
            '<div class="table-entry border-bottom table-diff %s">
                <div class="row p-2 align-items-center">
                    <div class="col-auto"><input type="checkbox" class="form-check-input table-select me-2"></div>
                    <div class="col">
                        <span class="h6 mb-0">%s</span>
                        <span class="badge %s status-badge ms-2">%s</span>
                        <button class="btn btn-sm btn-primary float-end compare-details" 
                                data-table="%s" 
                                data-db1="%s" 
                                data-db2="%s">Details</button>
                    </div>
                </div>
                %s
            </div>',
            $statusClass,
            htmlspecialchars($table_name),
            $statusBadgeClass,
            htmlspecialchars($statusText),
            htmlspecialchars($table_name),
            htmlspecialchars($_POST['db1']),
            htmlspecialchars($_POST['db2']),
            $details
        );
    }

    // Add missing tables from DB2
    foreach ($comparator->getTables2() as $table) {
        $table_name = $table['TABLE_NAME'];
        if (!$comparator->tableExistsInDb1($table_name)) {
            $output .= sprintf(
                '<div class="table-entry border-bottom">
                    <div class="row p-2 align-items-center">
                        <div class="col-auto"><input type="checkbox" class="form-check-input table-select me-2"></div>
                        <div class="col">
                            <span class="h6 mb-0">%s</span>
                            <span class="badge bg-danger status-badge ms-2">Missing in DB1</span>
                            <button class="btn btn-sm btn-primary float-end compare-details" 
                                    data-table="%s" 
                                    data-db1="%s" 
                                    data-db2="%s">Details</button>
                        </div>
                    </div>
                </div>',
                htmlspecialchars($table_name),
                htmlspecialchars($table_name),
                htmlspecialchars($_POST['db1']),
                htmlspecialchars($_POST['db2'])
            );
        }
    }

    $output .= '</div></div>';
    echo $output;

} catch (Exception $e) {
    echo json_encode(['error' => 'Error performing comparison: ' . $e->getMessage()]);
    exit;
}

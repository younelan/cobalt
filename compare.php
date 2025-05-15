<?php
session_start();
require_once __DIR__ . '/init.php';
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/DBSessionManager.php';

$session = new DBSessionManager();
$credentials = $session->getCredentials();

if (!$credentials) {
    header('Location: index.php');
    exit;
}

$db = new Database($credentials['host'], $credentials['username'], $credentials['password']);
$databases = $db->getDatabases();
$selected = $session->getSelectedDatabases();
?>
<!DOCTYPE html>
<html>
<head>
    <title><?= T("Database Comparison Tool") ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .table-diff { border-left: 4px solid; }
        .table-identical { border-left-color: #28a745; }
        .table-different { border-left-color: #ffc107; }
        .table-missing { border-left-color: #dc3545; }
        .status-badge {
            padding: 0.25em 0.6em;
            border-radius: 12px;
            font-size: 0.85em;
        }
        .header-section {
            background: #2c3e50;
            color: white;
            padding: 1rem 0;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .table-entry {
            transition: background-color 0.2s;
        }
        .table-entry:hover {
            background-color: rgba(0,0,0,0.02);
        }
        .text-muted small {
            line-height: 1.6;
        }
        .diff-label {
            display: inline-block;
            padding: 0.15em 0.5em;
            border-radius: 4px;
            font-size: 0.85em;
            font-weight: 500;
            margin-right: 0.5em;
        }
        .diff-type { background: #fff3cd; color: #856404; }
        .diff-missing { background: #f8d7da; color: #721c24; }
        .diff-index { background: #cce5ff; color: #004085; }
        .diff-arrow { color: #6c757d; margin: 0 0.3em; }
        .table-differences {
            padding: 0.5em 1em;
            background: #f8f9fa;
            border-radius: 4px;
            margin-top: 0.5em;
        }
        .diff-group {
            display: inline-flex;
            align-items: center;
            gap: 1rem;
            margin: 0.5rem 0;
        }
        .diff-item {
            display: inline-flex;
            align-items: center;
            padding: 0.25rem 0.75rem;
            border-radius: 1rem;
            font-size: 0.85em;
            background: #f8f9fa;
        }
        .diff-item.missing { background: #f8d7da; color: #721c24; }
        .diff-item.mismatch { background: #fff3cd; color: #856404; }
        .diff-item.index { background: #cce5ff; color: #004085; }
        .diff-item strong { margin-right: 0.35rem; }
        .diff-arrow { color: #6c757d; margin: 0 0.35rem; font-size: 0.9em; }
        .diff-summary {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }
        .diff-line {
            display: flex;
            flex-wrap: wrap;
            gap: 0.75rem;
            align-items: center;
        }
        .diff-title {
            font-weight: 600;
            color: #495057;
            min-width: 100px;
        }
        .diff-item {
            display: inline-flex;
            align-items: center;
            padding: 0.35rem 0.85rem;
            border-radius: 1rem;
            font-size: 0.9em;
            white-space: nowrap;
        }
        .diff-item.missing { background: #f8d7da; color: #721c24; }
        .diff-item.mismatch { background: #fff3cd; color: #856404; }
        .diff-items-wrapper {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            flex: 1;
        }
        .comparison-table {
            border: 1px solid #dee2e6;
            margin-bottom: 1rem;
        }
        .comparison-table th,
        .comparison-table td {
            padding: 0.5rem;
            border: 1px solid #dee2e6;
        }
        .comparison-table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #dee2e6;
        }
        .detailed-comparison {
            background-color: #fff;
            padding: 1rem;
            border-radius: 0.25rem;
        }
    </style>
</head>
<body class="bg-light">
    <div class="header-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col">
                    <h2><?= T("DB Compare") ?></h2>
                </div>
                <div class="col text-end">
                    <small><?= T("Connected as: {username}" , ['username' => htmlspecialchars($credentials['username'])] )  ?></small>
                </div>
            </div>
        </div>
    </div>
    <div class="container">
        <div class="row mb-4">
            <div class="col-12 mb-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?= T("Select Databases to Compare") ?></h5>
                        <div class="row">
                            <div class="col-md-6">
                                <label for="db1" class="form-label"><?= T("Database 1") ?></label>
                                <select id="db1" class="form-select">
                                    <option value=""><?= T("Select Database 1") ?></option>
                                    <?php foreach ($databases as $database): ?>
                                        <option value="<?php echo htmlspecialchars($database); ?>"
                                                <?php echo ($selected && $selected['db1'] === $database) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($database); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="db2" class="form-label"><?= T("Database 2") ?></label>
                                <select id="db2" class="form-select">
                                    <option value=""><?= T("Select Database 2") ?></option>
                                    <?php foreach ($databases as $database): ?>
                                        <option value="<?php echo htmlspecialchars($database); ?>"
                                                <?php echo ($selected && $selected['db2'] === $database) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($database); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="comparison-results" class="mt-4">
            <div class="alert alert-info"><?= T("Please select two databases to compare") ?></div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="js/comparison.js"></script>
    <script>
        const translations = <?php echo json_encode($translations); ?>;
        function T(key, vars = {}) {
            let translation = translations['fr'][key] || key;
            for (const [varName, value] of Object.entries(vars)) {
                translation = translation.replace(`{${varName}}`, value);
            }
            return translation;
        }
    </script>
</body>
</html>

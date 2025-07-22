<?php
session_start();
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/DBSessionManager.php';

if (!isset($_POST['db'])) {
    echo '<div class="alert alert-danger">Invalid request</div>';
    exit;
}

$session = new DBSessionManager();
$credentials = $session->getCredentials();
if (!$credentials) {
    echo '<div class="alert alert-danger">Not authenticated</div>';
    exit;
}

$db = new Database($credentials['host'], $credentials['username'], $credentials['password']);
$database = $_POST['db'];
$tables = $db->getTables($database);
$totalTables = count($tables);
$totalRows = 0;
foreach ($tables as $t) {
    $totalRows += (int)($t['TABLE_ROWS'] ?? 0);
}
?>
<div class="p-3">
  <h5>Database: <span class="text-primary"><?= htmlspecialchars($database) ?></span></h5>
  <ul class="list-group mb-3">
    <li class="list-group-item">Total Tables: <strong><?= $totalTables ?></strong></li>
    <li class="list-group-item">Total Rows: <strong><?= $totalRows ?></strong></li>
  </ul>
  <h6>Tables</h6>
  <ul class="list-group">
    <?php foreach ($tables as $table): ?>
      <li class="list-group-item">
        <a href="#" class="table-link" data-db="<?= htmlspecialchars($database) ?>" data-table="<?= htmlspecialchars($table['TABLE_NAME']) ?>" style="text-decoration:none; color:inherit; display:inline-block;">
          <?= htmlspecialchars($table['TABLE_NAME']) ?>
        </a>
        <span class="text-muted float-end">Rows: <?= htmlspecialchars($table['TABLE_ROWS'] ?? '0') ?></span>
      </li>
    <?php endforeach; ?>
  </ul>
</div>
<script>
if (typeof bindTableLinks === 'function') bindTableLinks(document.currentScript.parentElement);
</script>

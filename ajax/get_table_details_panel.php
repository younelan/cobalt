<?php
session_start();
require_once __DIR__ . '/../init.php';
require_once __DIR__ . '/../classes/Database.php';
require_once __DIR__ . '/../classes/DBSessionManager.php';

if (!isset($_POST['db']) || !isset($_POST['table'])) {
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
$table = $_POST['table'];
$columns = $db->getTableColumns($database, $table);
$indexes = $db->getTableIndexes($database, $table);
?>
<div class="p-3">
  <h5>Table: <span class="text-primary"><?= htmlspecialchars($table) ?></span></h5>
  <h6 class="mt-4">Columns</h6>
  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th>Name</th>
          <th>Type</th>
          <th>Nullable</th>
          <th>Default</th>
          <th>Extra</th>
          <th>Comment</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($columns as $col): ?>
        <tr>
          <td><?= htmlspecialchars($col['COLUMN_NAME'] ?? '') ?></td>
          <td><?= htmlspecialchars($col['COLUMN_TYPE'] ?? '') ?></td>
          <td><?= htmlspecialchars($col['IS_NULLABLE'] ?? '') ?></td>
          <td><?= htmlspecialchars($col['COLUMN_DEFAULT'] ?? '') ?></td>
          <td><?= htmlspecialchars($col['EXTRA'] ?? '') ?></td>
          <td><?= htmlspecialchars($col['COLUMN_COMMENT'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <h6 class="mt-4">Indexes</h6>
  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th>Index Name</th>
          <th>Non Unique</th>
          <th>Column Name</th>
          <th>Seq In Index</th>
          <th>Index Type</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($indexes as $idx): ?>
        <tr>
          <td><?= htmlspecialchars($idx['INDEX_NAME'] ?? '') ?></td>
          <td><?= htmlspecialchars($idx['NON_UNIQUE'] ?? '') ?></td>
          <td><?= htmlspecialchars($idx['COLUMN_NAME'] ?? '') ?></td>
          <td><?= htmlspecialchars($idx['SEQ_IN_INDEX'] ?? '') ?></td>
          <td><?= htmlspecialchars($idx['INDEX_TYPE'] ?? '') ?></td>
        </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>

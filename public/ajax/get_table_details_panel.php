<?php
session_start();

$basedir = dirname(dirname(__DIR__));
require_once $basedir . '/init.php';
require_once $basedir . '/classes/Database.php';
require_once $basedir . '/classes/DBSessionManager.php';

if (!isset($_POST['db']) || !isset($_POST['table'])) {
    echo '<div class="alert alert-danger">' . T('Invalid request') . '</div>';
    exit;
}

$session = new DBSessionManager();
$credentials = $session->getCredentials();
if (!$credentials) {
    echo '<div class="alert alert-danger">' . T('Not authenticated') . '</div>';
    exit;
}

$db = new Database($credentials['host'], $credentials['username'], $credentials['password']);
$database = $_POST['db'];
$table = $_POST['table'];
$columns = $db->getTableColumns($database, $table);
$indexes = $db->getTableIndexes($database, $table);
?>
<div class="p-3">
  <h5><?= T('Table:') ?> <span class="text-primary"><?= htmlspecialchars($table) ?></span></h5>
  <h6 class="mt-4"><?= T('Columns') ?></h6>
  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th><?= T('Name') ?></th>
          <th><?= T('Type') ?></th>
          <th><?= T('Nullable') ?></th>
          <th><?= T('Default') ?></th>
          <th><?= T('Extra') ?></th>
          <th><?= T('Comment') ?></th>
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
  <h6 class="mt-4"><?= T('Indexes') ?></h6>
  <div class="table-responsive">
    <table class="table table-sm table-bordered">
      <thead class="table-light">
        <tr>
          <th><?= T('Index Name') ?></th>
          <th><?= T('Non Unique') ?></th>
          <th><?= T('Column Name') ?></th>
          <th><?= T('Seq In Index') ?></th>
          <th><?= T('Index Type') ?></th>
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

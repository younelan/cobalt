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

include __DIR__ . '/header.php';
?>
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

    // Replace any jQuery AJAX with vanilla fetch for table-to-table comparison
    function updateTableComparison() {
      var db1 = document.getElementById('db1-select')?.value;
      var table1 = document.getElementById('table1-select')?.value;
      var db2 = document.getElementById('db2-select')?.value;
      var table2 = document.getElementById('table2-select')?.value;
      var panel = document.getElementById('table-comparison-panel');
      if (!db1 || !table1 || !db2 || !table2) {
        panel.innerHTML = '<div class="alert alert-info">Please select both databases and tables to compare.</div>';
        return;
      }
      panel.innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div><div>Loading...</div></div>';
      fetch('ajax/compare_tables.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'db1=' + encodeURIComponent(db1) + '&table1=' + encodeURIComponent(table1) + '&db2=' + encodeURIComponent(db2) + '&table2=' + encodeURIComponent(table2)
      })
      .then(r => r.text())
      .then(html => {
        panel.innerHTML = html;
      })
      .catch(e => {
        panel.innerHTML = '<div class="alert alert-danger">Error updating comparison.</div>';
      });
    }

    // Attach change listeners to all relevant dropdowns
    ['db1-select', 'table1-select', 'db2-select', 'table2-select'].forEach(function(id) {
      var el = document.getElementById(id);
      if (el) {
        el.addEventListener('change', updateTableComparison);
      }
    });
</script>
<style>
@media (max-width: 767.98px) {
  .detailed-comparison, .table-differences, .diff-summary, .diff-line, .diff-items-wrapper, .diff-item {
    word-break: break-word;
    white-space: normal !important;
    overflow-wrap: break-word;
  }
}
</style>

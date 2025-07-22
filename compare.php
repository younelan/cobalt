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
</script>

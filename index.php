<?php
require_once './config.php';

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$pageTitle = 'Database Structure Comparison';
require_once 'templates/header.php';
?>

<div class="container-fluid mt-4">
    <form id="dbCompareForm" method="post" action="templates/comparison_result.php">
        <div class="row mb-4">
            <!-- Database selects -->
            <div class="col-12">
                <div class="row g-3">
                    <div class="col-md-5">
                        <select class="form-select" id="db1" name="db1" required>
                            <option value="">Select First Database</option>
                            <?php foreach ($databases as $db): ?>
                                <option value="<?php echo htmlspecialchars($db); ?>"><?php echo htmlspecialchars($db); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <select class="form-select" id="db2" name="db2" required>
                            <option value="">Select Second Database</option>
                            <?php foreach ($databases as $db): ?>
                                <option value="<?php echo htmlspecialchars($db); ?>"><?php echo htmlspecialchars($db); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100">Compare</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Table selection cards -->
        <div class="row">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Database 1 Tables</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-secondary select-all-btn" data-target="1">Select All</button>
                            <button type="button" class="btn btn-sm btn-secondary select-none-btn" data-target="1">Select None</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="tables1" class="table-container"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Database 2 Tables</h5>
                        <div>
                            <button type="button" class="btn btn-sm btn-secondary select-all-btn" data-target="2">Select All</button>
                            <button type="button" class="btn btn-sm btn-secondary select-none-btn" data-target="2">Select None</button>
                        </div>
                    </div>
                    <div class="card-body">
                        <div id="tables2" class="table-container"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
    
    <div id="comparison-results" class="mt-4"></div>
</div>
<?php require_once 'templates/footer.php'; ?>

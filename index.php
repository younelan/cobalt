<?php
require_once './config.php';
define('ABSPATH', dirname(__FILE__));

// Check if user is authenticated
$isAuthenticated = isset($_SESSION['db_user']) && isset($_SESSION['db_pass']);

if (!$isAuthenticated) {
    $pageTitle = 'Database Authentication';
    require_once 'templates/header.php';
    ?>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="mb-0">Database Authentication</h3>
                    </div>
                    <div class="card-body">
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="host" class="form-label">Host</label>
                                <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Connect</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    require_once 'templates/footer.php';
    exit;
}

// Handle comparison form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['db1'], $_POST['db2'])) {
    $pageTitle = "Comparing {$_POST['db1']} vs {$_POST['db2']}";
    require_once ABSPATH . '/templates/header.php';
    require_once ABSPATH . '/templates/comparison_result.php';
    require_once ABSPATH . '/templates/footer.php';
    exit;
}

try {
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $databases = $pdo->query("SHOW DATABASES")->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

$pageTitle = 'Database Structure Comparison';
require_once ABSPATH . '/templates/header.php';
?>

<div class="container-fluid mt-4">
    <form id="dbCompareForm" method="post">
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
                        <div id="tables1"></div>
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
                        <div id="tables2"></div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>
<?php require_once ABSPATH . '/templates/footer.php'; ?>

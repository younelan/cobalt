<?php
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/DBSessionManager.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remember_username'])) {
        setcookie('db_username', $_POST['username'], time() + (86400 * 30), "/");
    }

    $db = new Database($_POST['host'], $_POST['username'], $_POST['password']);
    if ($db->testConnection()) {
        $session = new DBSessionManager();
        $session->setCredentials($_POST);
        header('Location: compare.php');
        exit;
    }
    $error = "Connection failed. Please check your credentials.";
}

$remembered_username = $_COOKIE['db_username'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Database Comparison Tool - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h3 class="text-center">Database Comparison Tool</h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="host" class="form-label">Host</label>
                                <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label">Username</label>
                                <input type="text" class="form-control" id="username" name="username" 
                                    value="<?php echo htmlspecialchars($remembered_username); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_username" name="remember_username">
                                <label class="form-check-label" for="remember_username">Remember username</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Connect</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

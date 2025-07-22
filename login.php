<?php
require_once __DIR__ . '/init.php';
session_start();
require_once __DIR__ . '/classes/Database.php';
require_once __DIR__ . '/classes/DBSessionManager.php';

if (isset($_SESSION['db_credentials'])) {
    header('Location: welcome.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['remember_username'])) {
        setcookie('db_username', $_POST['username'], time() + (86400 * 30), "/");
    }

    $db = new Database($_POST['host'], $_POST['username'], $_POST['password']);
    if ($db->testConnection()) {
        $session = new DBSessionManager();
        $session->setCredentials($_POST);
        header('Location: welcome.php');
        exit;
    }
    $error = T("Connection failed. Please check your credentials.");
}

$remembered_username = $_COOKIE['db_username'] ?? '';
?>
<!DOCTYPE html>
<html>
<head>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo T("Database Comparison Tool - Login"); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <nav class="navbar navbar-light bg-white shadow-sm mb-4">
        <div class="container-fluid justify-content-center">
            <span style="font-size:2.2rem; color:#2563eb; vertical-align:middle;">
                <svg width="36" height="36" viewBox="0 0 24 24" fill="#2563eb" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
                    <ellipse cx="12" cy="6" rx="9" ry="3.5"/>
                    <path d="M3 6v6c0 1.93 4.03 3.5 9 3.5s9-1.57 9-3.5V6" fill="none" stroke="#2563eb" stroke-width="2"/>
                    <path d="M3 12v6c0 1.93 4.03 3.5 9 3.5s9-1.57 9-3.5v-6" fill="none" stroke="#2563eb" stroke-width="2"/>
                </svg>
            </span>
            <span class="fw-bold ms-2" style="font-size:2rem; color:#2563eb; letter-spacing:2px; vertical-align:middle;">COBALT</span>
        </div>
    </nav>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-12" style="max-width: 400px;">
                <div class="card shadow-sm mx-auto w-100">
                    <div class="card-header">
                        <h3 class="text-center mb-0"><?= T("Database Comparison Tool") ?></h3>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
                        <?php endif; ?>
                        <form method="POST">
                            <div class="mb-3">
                                <label for="host" class="form-label"><?= T("Host"); ?></label>
                                <input type="text" class="form-control" id="host" name="host" value="localhost" required>
                            </div>
                            <div class="mb-3">
                                <label for="username" class="form-label"><?= T("Username") ?></label>
                                <input type="text" class="form-control" id="username" name="username" value="<?php echo htmlspecialchars($remembered_username); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label"><?= T("Password"); ?></label>
                                <input type="password" class="form-control" id="password" name="password">
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_username" name="remember_username">
                                <label class="form-check-label" for="remember_username"><?= T("Remember username")?></label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100"><?= T("Connect"); ?></button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
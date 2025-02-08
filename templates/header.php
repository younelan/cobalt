<?php
// Prevent direct access
if (!defined('ABSPATH')) {
    http_response_code(403);
    die('Forbidden');
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Database Comparison'; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .text-danger { font-weight: bold; }
        .table-container { max-height: 600px; overflow-y: auto; }
    </style>
</head>
<body>
<div class="container-fluid py-3">
    <?php if (isset($_SESSION['db_user'])): ?>
        <div class="d-flex justify-content-between mb-3">
            <h1><?php echo $pageTitle ?? 'Database Comparison'; ?></h1>
            <div>
                <span class="text-muted me-3">Connected as: <?php echo htmlspecialchars($_SESSION['db_user']); ?></span>
                <a href="logout.php" class="btn btn-outline-danger btn-sm">Logout</a>
            </div>
        </div>
    <?php endif; ?>

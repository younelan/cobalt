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

include __DIR__ . '/header.php';
?>
<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-body text-center">
                    <h2 class="mb-4"><?= T('Database Comparison Tool') ?></h2>
                    <p class="lead"><?= T('Welcome') ?>, <strong><?= htmlspecialchars($credentials['username']) ?></strong>!</p>
                    <p><?= T('Use the menu above to compare databases or view tables.') ?></p>
                </div>
            </div>
        </div>
    </div>
</div>

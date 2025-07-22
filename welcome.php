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
<div class="container py-5">
  <div class="row justify-content-center">
    <div class="col-md-8 text-center">
      <h1 class="mb-4"><?= T('Database Explorer') ?></h1>
      <p class="lead mb-5"><?= T('Welcome! Choose an action below:') ?></p>
      <div class="row g-4 justify-content-center">
        <div class="col-12 col-md-5">
          <a href="tables.php" class="btn btn-lg btn-outline-primary w-100 py-4 shadow-sm welcome-action" style="font-size:1.5rem;">
            🗂️ <strong><?= T('Explore Data') ?></strong>
            <div class="small mt-2 welcome-desc"><?= T('Browse databases, tables, columns, and indexes.') ?></div>
          </a>
        </div>
        <div class="col-12 col-md-5">
          <a href="compare.php" class="btn btn-lg btn-outline-success w-100 py-4 shadow-sm welcome-action" style="font-size:1.5rem;">
            ⚖️ <strong><?= T('Compare Databases') ?></strong>
            <div class="small mt-2 welcome-desc"><?= T('Find differences between two databases.') ?></div>
          </a>
        </div>
      </div>
    </div>
  </div>
</div>
<style>
.welcome-action {
  color: #212529;
  background: #fff;
  border-width: 2px;
  transition: background 0.2s, color 0.2s;
}
.welcome-action:hover, .welcome-action:focus {
  background: #e9ecef !important;
  color: #212529 !important;
  border-color: #0d6efd !important;
  text-decoration: none;
}
.welcome-action.btn-outline-success:hover, .welcome-action.btn-outline-success:focus {
  border-color: #198754 !important;
}
.welcome-action strong, .welcome-desc {
  color: #212529 !important;
}
</style>

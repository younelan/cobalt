<?php
// header.php - Contains the navigation bar for the Database Comparison Tool
if (!isset($_SESSION['db_credentials'])) {
    exit;
}
?>
<head>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    .table-diff { border-left: 4px solid; }
    .table-identical { border-left-color: #28a745; }
    .table-different { border-left-color: #ffc107; }
    .table-missing { border-left-color: #dc3545; }
    .status-badge {
        padding: 0.25em 0.6em;
        border-radius: 12px;
        font-size: 0.85em;
    }
    .header-section {
        background: #2c3e50;
        color: white;
        padding: 1rem 0;
        margin-bottom: 2rem;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    .table-entry {
        transition: background-color 0.2s;
    }
    .table-entry:hover {
        background-color: rgba(0,0,0,0.02);
    }
    .text-muted small {
        line-height: 1.6;
    }
    .diff-label {
        display: inline-block;
        padding: 0.15em 0.5em;
        border-radius: 4px;
        font-size: 0.85em;
        font-weight: 500;
        margin-right: 0.5em;
    }
    .diff-type { background: #fff3cd; color: #856404; }
    .diff-missing { background: #f8d7da; color: #721c24; }
    .diff-index { background: #cce5ff; color: #004085; }
    .diff-arrow { color: #6c757d; margin: 0 0.3em; }
    .table-differences {
        padding: 0.5em 1em;
        background: #f8f9fa;
        border-radius: 4px;
        margin-top: 0.5em;
    }
    .diff-group {
        display: inline-flex;
        align-items: center;
        gap: 1rem;
        margin: 0.5rem 0;
    }
    .diff-item {
        display: inline-flex;
        align-items: center;
        padding: 0.25rem 0.75rem;
        border-radius: 1rem;
        font-size: 0.85em;
        background: #f8f9fa;
    }
    .diff-item.missing { background: #f8d7da; color: #721c24; }
    .diff-item.mismatch { background: #fff3cd; color: #856404; }
    .diff-item.index { background: #cce5ff; color: #004085; }
    .diff-item strong { margin-right: 0.35rem; }
    .diff-arrow { color: #6c757d; margin: 0 0.35rem; font-size: 0.9em; }
    .diff-summary {
        display: flex;
        flex-direction: column;
        gap: 0.75rem;
    }
    .diff-line {
        display: flex;
        flex-wrap: wrap;
        gap: 0.75rem;
        align-items: center;
    }
    .diff-title {
        font-weight: 600;
        color: #495057;
        min-width: 100px;
    }
    .diff-item {
        display: inline-flex;
        align-items: center;
        padding: 0.35rem 0.85rem;
        border-radius: 1rem;
        font-size: 0.9em;
        white-space: nowrap;
    }
    .diff-item.missing { background: #f8d7da; color: #721c24; }
    .diff-item.mismatch { background: #fff3cd; color: #856404; }
    .diff-items-wrapper {
        display: flex;
        flex-wrap: wrap;
        gap: 0.5rem;
        flex: 1;
    }
    .comparison-table {
        border: 1px solid #dee2e6;
        margin-bottom: 1rem;
    }
    .comparison-table th,
    .comparison-table td {
        padding: 0.5rem;
        border: 1px solid #dee2e6;
    }
    .comparison-table thead th {
        background-color: #f8f9fa;
        border-bottom: 2px solid #dee2e6;
    }
    .detailed-comparison {
        background-color: #fff;
        padding: 1rem;
        border-radius: 0.25rem;
    }
    @media (max-width: 767.98px) {
      .container-fluid, .container, .row, .col-md-3, .col-md-9 {
        padding-left: 0 !important;
        padding-right: 0 !important;
      }
      .navbar .container {
        padding-left: 0.5rem;
        padding-right: 0.5rem;
      }
      .navbar-nav {
        text-align: center;
      }
      .navbar-collapse {
        background: #222;
      }
      .col-md-3, .col-md-9 {
        width: 100% !important;
        max-width: 100% !important;
      }
      .border-end {
        border-right: none !important;
      }
      .p-4, .py-4, .py-5 {
        padding: 1rem !important;
      }
    }
    .form-control, .btn, .input-group {
      min-width: 0;
      width: 100%;
      box-sizing: border-box;
    }
    .login-form {
      max-width: 400px;
      margin: 0 auto;
    }
    .cobalt-menu-bg.collapse.show,
    .cobalt-menu-bg .navbar-nav.ms-auto.d-lg-none {
      background: #2563eb !important;
      word-break: break-word;
      white-space: normal !important;
      overflow-wrap: break-word;
    }
    .cobalt-menu-bg .nav-link, .cobalt-menu-bg .navbar-text {
      word-break: break-word;
      white-space: normal !important;
      overflow-wrap: break-word;
    }
  </style>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm mb-2">
  <div class="container">
    <div class="d-flex flex-column align-items-start">
      <div class="d-flex align-items-center">
        <span style="font-size:2.2rem; color:#2563eb; vertical-align:middle;">
          <svg width="36" height="36" viewBox="0 0 24 24" fill="#2563eb" xmlns="http://www.w3.org/2000/svg" style="vertical-align:middle;">
            <ellipse cx="12" cy="6" rx="9" ry="3.5"/>
            <path d="M3 6v6c0 1.93 4.03 3.5 9 3.5s9-1.57 9-3.5V6" fill="none" stroke="#2563eb" stroke-width="2"/>
            <path d="M3 12v6c0 1.93 4.03 3.5 9 3.5s9-1.57 9-3.5v-6" fill="none" stroke="#2563eb" stroke-width="2"/>
          </svg>
        </span>
        <span class="fw-bold ms-2" style="font-size:2rem; color:#2563eb; letter-spacing:2px; vertical-align:middle;">COBALT</span>
      </div>
      <div class="mt-1 ms-1">
        <span class="text-muted" style="font-size:1.1rem;">Database Explorer</span>
      </div>
    </div>
    <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto d-lg-none pt-3" style="background:#2563eb;">
        <li class="nav-item"><a class="nav-link text-white" href="welcome.php"><?= T('Main Page') ?></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="compare.php"><?= T('DB Comparison') ?></a></li>
        <li class="nav-item"><a class="nav-link text-white" href="tables.php"><?= T('Tables') ?></a></li>
        <?php if (isset($_SESSION['db_credentials'])): ?>
          <li><hr class="dropdown-divider" style="border-color:#fff;"></li>
          <li class="nav-item"><a class="nav-link text-white" href="logout.php"><?= T('Logout') ?></a></li>
        <?php endif; ?>
      </ul>
      <ul class="navbar-nav ms-auto d-none d-lg-flex">
        <li class="nav-item dropdown">
          <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= T('Menu') ?>
          </a>
          <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
            <li><a class="dropdown-item" href="welcome.php"><?= T('Main Page') ?></a></li>
            <li><a class="dropdown-item" href="compare.php"><?= T('DB Comparison') ?></a></li>
            <li><a class="dropdown-item" href="tables.php"><?= T('Tables') ?></a></li>
            <?php if (isset($_SESSION['db_credentials'])): ?>
              <li><hr class="dropdown-divider"></li>
              <li><a class="dropdown-item" href="logout.php"><?= T('Logout') ?></a></li>
            <?php endif; ?>
          </ul>
        </li>
      </ul>
      <?php if (isset($_SESSION['db_credentials'])): ?>
        <span class="navbar-text ms-3 text-white d-lg-none">
          <?= T('Connected as: {username}', ['username' => htmlspecialchars($_SESSION['db_credentials']['username'])]) ?>
        </span>
        <span class="navbar-text ms-3 d-none d-lg-inline">
          <?= T('Connected as: {username}', ['username' => htmlspecialchars($_SESSION['db_credentials']['username'])]) ?>
        </span>
      <?php endif; ?>
    </div>
  </div>
</nav>
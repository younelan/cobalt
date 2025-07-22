<?php
// header.php - Contains the navigation bar for the Database Comparison Tool
?>
<head>
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
  </style>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</head>
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container">
    <a class="navbar-brand" href="welcome.php"><?= T('Database Explorer') ?></a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav ms-auto">
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
        <span class="navbar-text ms-3">
          <?= T('Connected as: {username}', ['username' => htmlspecialchars($_SESSION['db_credentials']['username'])]) ?>
        </span>
      <?php endif; ?>
    </div>
  </div>
</nav>
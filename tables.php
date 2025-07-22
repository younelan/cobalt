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

$databases = (new Database($credentials['host'], $credentials['username'], $credentials['password']))->getDatabases();

// Group databases by __
function buildDbTree($dbs) {
    $tree = [];
    foreach ($dbs as $db) {
        $parts = explode('__', $db);
        $node = &$tree;
        foreach ($parts as $part) {
            if (!isset($node[$part])) $node[$part] = [];
            $node = &$node[$part];
        }
        $node['__db'] = $db;
    }
    return $tree;
}
function renderDbTree($tree, $prefix = '', $fullPath = '') {
    echo '<ul class="list-group">';
    foreach ($tree as $key => $val) {
        if ($key === '__db') continue;
        $hasDb = isset($val['__db']);
        $hasChildren = count(array_diff(array_keys($val), ['__db'])) > 0;
        $id = htmlspecialchars($prefix . $key . uniqid('_cat_'));
        $currentPath = $fullPath === '' ? $key : $fullPath . '__' . $key;
        echo '<li class="list-group-item">';
        if ($hasChildren) {
            echo '<span class="category-toggle" data-cat="' . $id . '" style="cursor:pointer; font-weight:bold; margin-right:8px;"><span class="cat-icon">&#x2795;</span> ' . htmlspecialchars($key) . '</span>';
        }
        if ($hasDb) {
            echo '<span class="db-toggle ms-2" data-db="' . htmlspecialchars($val['__db']) . '" style="font-weight:bold;">';
            echo '<span class="db-expand" data-db="' . htmlspecialchars($val['__db']) . '" style="cursor:pointer; margin-right:6px;"><span class="db-icon">&#x2795;</span></span>';
            echo '<span class="db-name" data-db="' . htmlspecialchars($val['__db']) . '" style="cursor:pointer;">' . htmlspecialchars($hasChildren ? $val['__db'] : $key) . '</span>';
            echo '</span>';
            echo '<div class="tables-list" id="tables-' . htmlspecialchars($val['__db']) . '"></div>';
        }
        if ($hasChildren) {
            echo '<div class="category-list d-none" id="cat-' . $id . '">';
            renderDbTree($val, $prefix . $key . '__', $currentPath);
            echo '</div>';
        }
        echo '</li>';
    }
    echo '</ul>';
}
$tree = buildDbTree($databases);
include __DIR__ . '/header.php';
?>
<div class="container-fluid">
  <div class="row">
    <div class="col-md-3 border-end" style="min-height: 80vh;">
      <h5 class="mt-3 mb-2"><?= T('Databases') ?></h5>
      <div id="db-list">
        <?php renderDbTree($tree); ?>
      </div>
    </div>
    <div class="col-md-9" id="table-details-panel">
      <div class="p-4 text-muted text-center"><?= T('Select a table to view its columns and indexes.') ?></div>
    </div>
  </div>
</div>
<script>
// Category expand/collapse using event delegation
document.getElementById('db-list').addEventListener('click', function(e) {
  // Category expand/collapse
  var toggle = e.target.closest('.category-toggle');
  if (toggle) {
    var cat = toggle.getAttribute('data-cat');
    var icon = toggle.querySelector('.cat-icon');
    var div = document.getElementById('cat-' + cat);
    if (div) {
      var isCollapsed = div.classList.toggle('d-none');
      icon.innerHTML = isCollapsed ? '\u2795' : '\u2796';
    }
    return;
  }
  // Database expand/collapse
  var dbExpand = e.target.closest('.db-expand');
  if (dbExpand) {
    var db = dbExpand.getAttribute('data-db');
    var icon = dbExpand.querySelector('.db-icon');
    var tablesDiv = document.getElementById('tables-' + db);
    if (tablesDiv.classList.contains('loaded')) {
      var isCollapsed = tablesDiv.classList.toggle('d-none');
      icon.innerHTML = isCollapsed ? '\u2795' : '\u2796';
      return;
    }
    tablesDiv.innerHTML = '<div class="text-center py-2"><div class="spinner-border spinner-border-sm"></div><div><?= T('Loading tables...') ?></div></div>';
    fetch('ajax/get_tables_for_db.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'db=' + encodeURIComponent(db)
    })
    .then(r => r.text())
    .then(html => {
      tablesDiv.innerHTML = html;
      tablesDiv.classList.add('loaded');
      tablesDiv.classList.remove('d-none');
      icon.innerHTML = '\u2796';
      // Bind table click events
      tablesDiv.querySelectorAll('.table-item').forEach(function(item) {
        item.replaceWith(item.cloneNode(true));
      });
      bindTableLinks(tablesDiv);
    });
    return;
  }
  // Database details view
  var dbName = e.target.closest('.db-name');
  if (dbName) {
    var db = dbName.getAttribute('data-db');
    var panel = document.getElementById('table-details-panel');
    panel.innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div><div><?= T('Loading...') ?></div></div>';
    fetch('ajax/get_db_details.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: 'db=' + encodeURIComponent(db)
    })
    .then(r => r.text())
    .then(html => {
      panel.innerHTML = html;
      bindTableLinks(panel); // Make table links clickable in db details
    });
    return;
  }
});
// Toggle tables list for each database (AJAX load)
// Removed the static .db-toggle event binding here
// All expand/collapse logic is handled by the event delegation above
function onTableClick(e) {
  e.preventDefault();
  // Remove highlight from all
  document.querySelectorAll('.table-item').forEach(function(item) {
    item.classList.remove('active');
  });
  // Highlight selected
  var li = this.closest('.table-item');
  if (li) li.classList.add('active');
  var db = this.getAttribute('data-db');
  var table = this.getAttribute('data-table');
  var panel = document.getElementById('table-details-panel');
  panel.innerHTML = '<div class="text-center py-4"><div class="spinner-border"></div>';
  fetch('ajax/get_table_details_panel.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: 'db=' + encodeURIComponent(db) + '&table=' + encodeURIComponent(table)
  })
  .then(r => r.text())
  .then(html => { panel.innerHTML = html; });
}
// Update event binding for new .table-link
function bindTableLinks(context) {
  (context || document).querySelectorAll('.table-link').forEach(function(link) {
    link.addEventListener('click', onTableClick);
  });
}
// Initial bind for any preloaded tables
bindTableLinks();
</script>

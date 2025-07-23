<?php
session_start();

$basedir = dirname(dirname(__DIR__));
require_once $basedir . '/init.php';
require_once $basedir . '/classes/Database.php';
require_once $basedir . '/classes/DBSessionManager.php';

if (!isset($_POST['db'])) {
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

// Group tables by __
function buildTableTree($tables) {
    $tree = [];
    foreach ($tables as $table) {
        $name = $table['TABLE_NAME'];
        $parts = explode('__', $name);
        $node = &$tree;
        foreach ($parts as $part) {
            if (!isset($node[$part])) $node[$part] = [];
            $node = &$node[$part];
        }
        $node['__table'] = $table;
        $node['__full_name'] = $name;
    }
    return $tree;
}
function renderTableTree($tree, $db, $prefix = '', $fullPath = '') {
    echo '<ul class="list-group list-group-flush ms-3">';
    foreach ($tree as $key => $val) {
        if ($key === '__table' || $key === '__full_name') continue;
        $hasTable = isset($val['__table']);
        $hasChildren = count(array_diff(array_keys($val), ['__table', '__full_name'])) > 0;
        $id = htmlspecialchars($prefix . $key . uniqid('_cat_'));
        $currentPath = $fullPath === '' ? $key : $fullPath . '__' . $key;
        echo '<li class="list-group-item">';
        if ($hasChildren) {
            echo '<span class="category-toggle" data-cat="' . $id . '" style="cursor:pointer; font-weight:bold; margin-right:8px;"><span class="cat-icon">&#x2795;</span> ' . htmlspecialchars($key) . '</span>';
        }
        if ($hasTable) {
            $table = $val['__table'];
            $fullName = $val['__full_name'];
            echo '<a href="#" class="table-link ms-2" data-db="' . htmlspecialchars($db) . '" data-table="' . htmlspecialchars($fullName) . '" style="text-decoration:none; color:inherit; display:inline-block;">' . htmlspecialchars($fullName) . '</a>';
        }
        if ($hasChildren) {
            echo '<div class="category-list d-none" id="cat-' . $id . '">';
            renderTableTree($val, $db, $prefix . $key . '__', $currentPath);
            echo '</div>';
        }
        echo '</li>';
    }
    echo '</ul>';
}

$session = new DBSessionManager();
$credentials = $session->getCredentials();
if (!$credentials) {
    echo json_encode(['error' => 'Not authenticated']);
    exit;
}
$db = new Database($credentials['host'], $credentials['username'], $credentials['password']);
$database = $_POST['db'];
$tables = $db->getTables($database);
$tree = buildTableTree($tables);
renderTableTree($tree, $database);
?>
<script>
Array.from(document.querySelectorAll('.category-toggle')).forEach(function(toggle) {
  toggle.addEventListener('click', function() {
    var cat = this.getAttribute('data-cat');
    var icon = this.querySelector('.cat-icon');
    var div = this.parentElement.querySelector('#cat-' + cat);
    if (div) {
      div.classList.toggle('d-none');
      icon.innerHTML = div.classList.contains('d-none') ? '\u2795' : '\u2796';
    }
  });
});
</script>

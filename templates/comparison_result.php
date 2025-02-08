<?php
require_once dirname(__DIR__) . '/config.php';
require_once dirname(__DIR__) . '/templates/header.php';

// Prevent direct access
if (!defined('ABSPATH')) {
    http_response_code(403);
    die('Forbidden');
}

if (!isset($_POST['db1']) || !isset($_POST['db2'])) {
    die("<div class='alert alert-danger'>Both databases must be selected</div>");
}

try {
    $db1 = $_POST['db1'];
    $db2 = $_POST['db2'];
    $selected_tables1 = isset($_POST['tables1']) ? $_POST['tables1'] : [];
    $selected_tables2 = isset($_POST['tables2']) ? $_POST['tables2'] : [];
    
    $pdo1 = new PDO("mysql:host=$host;dbname=$db1;charset=utf8mb4", $user, $password);
    $pdo2 = new PDO("mysql:host=$host;dbname=$db2;charset=utf8mb4", $user, $password);
    
    echo "<div class='comparison-nav'>
            <div class='container'>
                <div class='d-flex justify-content-between align-items-center'>
                    <h4 class='mb-0'>
                        Comparing: 
                        <span class='text-primary'>" . htmlspecialchars($db1) . "</span>
                        vs
                        <span class='text-primary'>" . htmlspecialchars($db2) . "</span>
                    </h4>
                    <a href='./' class='btn btn-outline-secondary'>
                        <i class='fas fa-arrow-left'></i> Back to Comparison
                    </a>
                </div>
            </div>
          </div>";

    echo "<div class='container'>";
    
    foreach (array_intersect($selected_tables1, $selected_tables2) as $table) {
        // Get column information for both tables
        $cols1 = $pdo1->query("SHOW FULL COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $cols2 = $pdo2->query("SHOW FULL COLUMNS FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        // Get indexes
        $indexes1 = $pdo1->query("SHOW INDEXES FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $indexes2 = $pdo2->query("SHOW INDEXES FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        
        echo "<div class='card mb-4'>";
        echo "<div class='card-header'><h3 class='mb-0'>Table: {$table}</h3></div>";
        echo "<div class='card-body'>";
        
        // Column Comparison
        echo "<h4>Column Comparison</h4>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-striped'>";
        echo "<thead><tr><th>Column</th><th>{$db1}</th><th>{$db2}</th><th>Status</th></tr></thead>";
        echo "<tbody>";
        
        $allColumns = array_unique(array_merge(
            array_column($cols1, 'Field'),
            array_column($cols2, 'Field')
        ));
        
        foreach ($allColumns as $column) {
            $col1 = array_values(array_filter($cols1, fn($c) => $c['Field'] === $column))[0] ?? null;
            $col2 = array_values(array_filter($cols2, fn($c) => $c['Field'] === $column))[0] ?? null;
            
            echo "<tr>";
            echo "<td>{$column}</td>";
            echo "<td>" . ($col1 ? "{$col1['Type']} {$col1['Null']} {$col1['Default']}" : "<span class='text-danger'>Missing</span>") . "</td>";
            echo "<td>" . ($col2 ? "{$col2['Type']} {$col2['Null']} {$col2['Default']}" : "<span class='text-danger'>Missing</span>") . "</td>";
            echo "<td>";
            if ($col1 && $col2) {
                if ($col1['Type'] !== $col2['Type']) {
                    echo "<span class='text-warning'>Type Mismatch</span>";
                } elseif ($col1['Null'] !== $col2['Null'] || $col1['Default'] !== $col2['Default']) {
                    echo "<span class='text-warning'>Attribute Mismatch</span>";
                } else {
                    echo "<span class='text-success'>Identical</span>";
                }
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
        
        // Index Comparison
        echo "<h4 class='mt-4'>Index Comparison</h4>";
        echo "<div class='table-responsive'>";
        echo "<table class='table table-bordered table-striped'>";
        echo "<thead><tr><th>Index</th><th>{$db1}</th><th>{$db2}</th></tr></thead>";
        echo "<tbody>";
        
        $indexGroups1 = [];
        $indexGroups2 = [];
        
        foreach ($indexes1 as $idx) {
            $indexGroups1[$idx['Key_name']][] = $idx;
        }
        foreach ($indexes2 as $idx) {
            $indexGroups2[$idx['Key_name']][] = $idx;
        }
        
        $allIndexes = array_unique(array_merge(
            array_keys($indexGroups1),
            array_keys($indexGroups2)
        ));
        
        foreach ($allIndexes as $indexName) {
            echo "<tr>";
            echo "<td>{$indexName}</td>";
            
            // Display index details for db1
            echo "<td>";
            if (isset($indexGroups1[$indexName])) {
                foreach ($indexGroups1[$indexName] as $idx) {
                    echo "{$idx['Column_name']} ({$idx['Index_type']})";
                    if ($idx['Non_unique'] == 0) echo " UNIQUE";
                    echo "<br>";
                }
            } else {
                echo "<span class='text-danger'>Missing</span>";
            }
            echo "</td>";
            
            // Display index details for db2
            echo "<td>";
            if (isset($indexGroups2[$indexName])) {
                foreach ($indexGroups2[$indexName] as $idx) {
                    echo "{$idx['Column_name']} ({$idx['Index_type']})";
                    if ($idx['Non_unique'] == 0) echo " UNIQUE";
                    echo "<br>";
                }
            } else {
                echo "<span class='text-danger'>Missing</span>";
            }
            echo "</td>";
            echo "</tr>";
        }
        echo "</tbody></table></div>";
        echo "</div></div>";
    }
    
    echo "</div>";
    echo "<div class='mt-3'><a href='index.php' class='btn btn-primary'>New Comparison</a></div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

require_once dirname(__DIR__) . '/templates/footer.php';

<?php
require_once '../config.php';
require_once 'header.php';

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
    
    echo "<h2>Comparing {$db1} and {$db2}</h2>";
    echo "<div class='table-responsive'>";
    echo "<table class='table table-bordered table-striped'>";
    echo "<thead><tr><th>Table Name</th><th>{$db1} Structure</th><th>{$db2} Structure</th></tr></thead>";
    echo "<tbody>";
    
    foreach (array_unique(array_merge($selected_tables1, $selected_tables2)) as $table) {
        echo "<tr>";
        echo "<td class='fw-bold'>{$table}</td>";
        
        // Get structure for db1
        echo "<td>";
        if (in_array($table, $selected_tables1)) {
            $stmt = $pdo1->prepare("SHOW CREATE TABLE `$table`");
            $stmt->execute();
            $create = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($create) {
                echo "<pre class='mb-0'>" . htmlspecialchars($create['Create Table']) . "</pre>";
            }
        } else {
            echo "<span class='text-danger'>Table not selected</span>";
        }
        echo "</td>";
        
        // Get structure for db2
        echo "<td>";
        if (in_array($table, $selected_tables2)) {
            $stmt = $pdo2->prepare("SHOW CREATE TABLE `$table`");
            $stmt->execute();
            $create = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($create) {
                echo "<pre class='mb-0'>" . htmlspecialchars($create['Create Table']) . "</pre>";
            }
        } else {
            echo "<span class='text-danger'>Table not selected</span>";
        }
        echo "</td>";
        
        echo "</tr>";
    }
    
    echo "</tbody></table></div>";
    
    // Add a back button
    echo "<div class='mt-3'><a href='index.php' class='btn btn-primary'>Back to Comparison</a></div>";
    
} catch (PDOException $e) {
    echo "<div class='alert alert-danger'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
}

require_once 'footer.php';

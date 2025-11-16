<?php
// Simple diagnostic script to check file paths on live server
ini_set('display_errors', 1);
error_reporting(E_ALL);

echo "<h2>Path Diagnostic</h2>";
echo "<p><strong>Current File:</strong> " . __FILE__ . "</p>";
echo "<p><strong>Current Directory:</strong> " . __DIR__ . "</p>";
echo "<p><strong>Parent Directory:</strong> " . dirname(__DIR__) . "</p>";

echo "<h3>Checking Required Files:</h3>";

$files_to_check = [
    '../settings/core.php',
    '../controllers/product_controller.php',
    '../settings/db_cred.php',
    '../classes/product_class.php'
];

foreach ($files_to_check as $file) {
    $full_path = realpath($file);
    $exists = file_exists($file);
    $readable = is_readable($file);

    echo "<p><strong>$file:</strong><br>";
    echo "Full path: " . ($full_path ?: 'NOT FOUND') . "<br>";
    echo "Exists: " . ($exists ? 'YES' : 'NO') . "<br>";
    echo "Readable: " . ($readable ? 'YES' : 'NO') . "</p>";
}

echo "<h3>Attempting to load core.php:</h3>";
try {
    require_once('../settings/core.php');
    echo "<p style='color: green;'>SUCCESS: core.php loaded</p>";

    // Test if functions exist
    $functions = ['isLoggedIn', 'getUserId', 'isRegularCustomer'];
    foreach ($functions as $func) {
        echo "<p>Function '$func': " . (function_exists($func) ? 'EXISTS' : 'NOT FOUND') . "</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR loading core.php: " . $e->getMessage() . "</p>";
}

echo "<h3>Attempting to load product_controller.php:</h3>";
try {
    require_once('../controllers/product_controller.php');
    echo "<p style='color: green;'>SUCCESS: product_controller.php loaded</p>";

    // Test if function exists
    echo "<p>Function 'get_customer_stats_ctr': " . (function_exists('get_customer_stats_ctr') ? 'EXISTS' : 'NOT FOUND') . "</p>";
} catch (Exception $e) {
    echo "<p style='color: red;'>ERROR loading product_controller.php: " . $e->getMessage() . "</p>";
}
?>

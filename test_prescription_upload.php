<?php
// Quick test to check if prescription system is working
require_once('settings/db_class.php');

$db = new db_connection();
$conn = $db->db_conn();

echo "=== Database Table Check ===\n\n";

// Check for prescriptions table
$result = $conn->query("SHOW TABLES LIKE 'prescriptions'");
if ($result && $result->num_rows > 0) {
    echo "✓ prescriptions table EXISTS\n";
} else {
    echo "✗ prescriptions table MISSING - Run db/add_missing_tables.sql\n";
}

// Check for prescription_images table
$result = $conn->query("SHOW TABLES LIKE 'prescription_images'");
if ($result && $result->num_rows > 0) {
    echo "✓ prescription_images table EXISTS\n";
} else {
    echo "✗ prescription_images table MISSING - Run db/add_missing_tables.sql\n";
}

// Check uploads directory
$upload_dir = __DIR__ . '/uploads/prescriptions/';
if (is_dir($upload_dir) && is_writable($upload_dir)) {
    echo "✓ uploads/prescriptions/ directory EXISTS and is WRITABLE\n";
} else {
    echo "✗ uploads/prescriptions/ directory MISSING or NOT WRITABLE\n";
}

echo "\n=== Test Prescription Number Generation ===\n\n";

require_once('controllers/prescription_controller.php');
try {
    $prescription_number = generate_prescription_number_ctr();
    echo "✓ Successfully generated prescription number: $prescription_number\n";
} catch (Exception $e) {
    echo "✗ Error generating prescription number: " . $e->getMessage() . "\n";
}

echo "\nTest complete!\n";

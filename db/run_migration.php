<?php
/**
 * Run Google Maps Database Migration
 * This script adds the necessary columns for Google Maps integration
 */

// Database connection settings
$servername = 'localhost';
$username = 'root';
$password = '';
$dbname = 'pharmavault_db';

echo "<h2>Google Maps Database Migration</h2>";
echo "<pre>";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

echo "✓ Connected to database successfully.\n\n";

// Check if columns already exist
$result = $conn->query("SHOW COLUMNS FROM customer LIKE 'customer_latitude'");
if ($result->num_rows > 0) {
    echo "⚠ Columns already exist! Migration has been run before.\n";
    echo "\nCurrent table structure:\n";
    $columns = $conn->query("SHOW COLUMNS FROM customer");
    while ($row = $columns->fetch_assoc()) {
        echo "  - " . $row['Field'] . " (" . $row['Type'] . ")\n";
    }
    $conn->close();
    exit(0);
}

// Add columns
echo "Step 1: Adding customer_address column...\n";
$sql1 = "ALTER TABLE customer ADD COLUMN customer_address TEXT DEFAULT NULL COMMENT 'Full street address' AFTER customer_city";
if ($conn->query($sql1)) {
    echo "✓ customer_address added successfully.\n\n";
} else {
    echo "✗ Error: " . $conn->error . "\n\n";
}

echo "Step 2: Adding customer_latitude column...\n";
$sql2 = "ALTER TABLE customer ADD COLUMN customer_latitude DECIMAL(10, 8) DEFAULT NULL COMMENT 'Latitude coordinate' AFTER customer_address";
if ($conn->query($sql2)) {
    echo "✓ customer_latitude added successfully.\n\n";
} else {
    echo "✗ Error: " . $conn->error . "\n\n";
}

echo "Step 3: Adding customer_longitude column...\n";
$sql3 = "ALTER TABLE customer ADD COLUMN customer_longitude DECIMAL(11, 8) DEFAULT NULL COMMENT 'Longitude coordinate' AFTER customer_latitude";
if ($conn->query($sql3)) {
    echo "✓ customer_longitude added successfully.\n\n";
} else {
    echo "✗ Error: " . $conn->error . "\n\n";
}

echo "Step 4: Adding index for location queries...\n";
$sql4 = "CREATE INDEX idx_location ON customer(customer_latitude, customer_longitude)";
if ($conn->query($sql4)) {
    echo "✓ Index created successfully.\n\n";
} else {
    echo "✗ Error: " . $conn->error . "\n\n";
}

echo "Step 5: Adding sample default coordinates for existing pharmacies...\n";
$sql5 = "UPDATE customer
         SET customer_latitude = 5.6037,
             customer_longitude = -0.1870,
             customer_address = CONCAT(customer_city, ', ', customer_country)
         WHERE user_role = 1 AND customer_latitude IS NULL";
$result = $conn->query($sql5);
if ($result) {
    $affected = $conn->affected_rows;
    echo "✓ Updated $affected pharmacy records with default Accra coordinates.\n";
    echo "  (You should update these with actual pharmacy locations)\n\n";
} else {
    echo "✗ Error: " . $conn->error . "\n\n";
}

echo "\n" . str_repeat("=", 60) . "\n";
echo "✓ DATABASE MIGRATION COMPLETED SUCCESSFULLY!\n";
echo str_repeat("=", 60) . "\n\n";

echo "Next steps:\n";
echo "1. Add your Google Maps API key to: settings/google_maps_config.php\n";
echo "2. Update pharmacy coordinates with actual locations\n";
echo "3. Visit view/pharmacies.php to see the map in action!\n\n";

echo "Current pharmacy locations:\n";
$pharmacies = $conn->query("SELECT customer_id, customer_name, customer_address, customer_latitude, customer_longitude FROM customer WHERE user_role = 1");
if ($pharmacies->num_rows > 0) {
    while ($pharmacy = $pharmacies->fetch_assoc()) {
        echo sprintf(
            "  ID %d: %s - %s (%.6f, %.6f)\n",
            $pharmacy['customer_id'],
            $pharmacy['customer_name'],
            $pharmacy['customer_address'],
            $pharmacy['customer_latitude'],
            $pharmacy['customer_longitude']
        );
    }
} else {
    echo "  No pharmacies found.\n";
}

$conn->close();

echo "\n</pre>";
echo "<p><a href='../view/pharmacies.php'>→ Go to Pharmacies Page</a></p>";
?>

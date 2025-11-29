<?php
/**
 * Test script to check pharmacies in database
 */

require_once 'settings/db_class.php';

$db = new db_connection();
$conn = $db->db_conn();

echo "<h2>Checking Pharmacies in Database</h2>";

// Check all users and their roles
$all_users = $conn->query("SELECT customer_id, customer_name, customer_email, user_role FROM customer ORDER BY user_role, customer_id");

echo "<h3>All Users by Role:</h3>";
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Role Name</th></tr>";

$role_names = [
    1 => 'Superadmin',
    2 => 'Pharmacy',
    3 => 'Regular Customer'
];

while ($user = $all_users->fetch_assoc()) {
    $role_name = $role_names[$user['user_role']] ?? 'Unknown';
    $highlight = $user['user_role'] == 2 ? " style='background-color: #90EE90;'" : "";
    echo "<tr{$highlight}>";
    echo "<td>{$user['customer_id']}</td>";
    echo "<td>{$user['customer_name']}</td>";
    echo "<td>{$user['customer_email']}</td>";
    echo "<td>{$user['user_role']}</td>";
    echo "<td><strong>{$role_name}</strong></td>";
    echo "</tr>";
}
echo "</table>";

// Check pharmacies specifically
$pharmacies = $conn->query("SELECT customer_id, customer_name, customer_email, customer_contact, customer_country, customer_city FROM customer WHERE user_role = 2");

echo "<h3>Pharmacies Only (user_role = 2):</h3>";

if ($pharmacies && $pharmacies->num_rows > 0) {
    echo "<p><strong>Found {$pharmacies->num_rows} pharmacy/pharmacies</strong></p>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Contact</th><th>Location</th></tr>";

    while ($pharmacy = $pharmacies->fetch_assoc()) {
        echo "<tr>";
        echo "<td>{$pharmacy['customer_id']}</td>";
        echo "<td>{$pharmacy['customer_name']}</td>";
        echo "<td>{$pharmacy['customer_email']}</td>";
        echo "<td>" . ($pharmacy['customer_contact'] ?? 'N/A') . "</td>";
        echo "<td>{$pharmacy['customer_city']}, {$pharmacy['customer_country']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p style='color: red;'><strong>❌ NO PHARMACIES FOUND!</strong></p>";
    echo "<p>There are no users with user_role = 2 in the database.</p>";
    echo "<p><strong>Solution:</strong> You need to:</p>";
    echo "<ol>";
    echo "<li>Register pharmacy accounts, OR</li>";
    echo "<li>Change existing user's role to 2 in database</li>";
    echo "</ol>";
}

// Check table structure
echo "<h3>Customer Table Structure:</h3>";
$structure = $conn->query("DESCRIBE customer");
echo "<table border='1' cellpadding='5'>";
echo "<tr><th>Field</th><th>Type</th><th>Null</th><th>Key</th><th>Default</th></tr>";
while ($field = $structure->fetch_assoc()) {
    echo "<tr>";
    echo "<td>{$field['Field']}</td>";
    echo "<td>{$field['Type']}</td>";
    echo "<td>{$field['Null']}</td>";
    echo "<td>{$field['Key']}</td>";
    echo "<td>" . ($field['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

$conn->close();
?>

<style>
    body { font-family: Arial, sans-serif; padding: 20px; }
    table { border-collapse: collapse; margin: 20px 0; }
    th { background-color: #667eea; color: white; padding: 10px; }
    td { padding: 8px; }
    tr:nth-child(even) { background-color: #f2f2f2; }
</style>

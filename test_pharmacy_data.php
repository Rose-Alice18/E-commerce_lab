<?php
/**
 * Test script to check pharmacy location data in database
 */

require_once('./settings/db_class.php');

class PharmacyChecker extends db_connection {
    public function get_all_pharmacies() {
        $sql = "SELECT customer_id, customer_name, customer_email, customer_city,
                       customer_country, customer_address, customer_latitude,
                       customer_longitude, customer_contact, user_role
                FROM customer
                WHERE user_role = 1
                ORDER BY customer_id";

        $result = $this->db_conn()->query($sql);
        if ($result) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    }
}

$checker = new PharmacyChecker();
$pharmacies = $checker->get_all_pharmacies();

echo "<h2>Pharmacy Location Data Check</h2>";
echo "<p>Total pharmacies found: " . count($pharmacies) . "</p>";

if (count($pharmacies) > 0) {
    echo "<table border='1' cellpadding='10' style='border-collapse: collapse;'>";
    echo "<tr>";
    echo "<th>ID</th>";
    echo "<th>Name</th>";
    echo "<th>City</th>";
    echo "<th>Country</th>";
    echo "<th>Address</th>";
    echo "<th>Latitude</th>";
    echo "<th>Longitude</th>";
    echo "<th>Has Location?</th>";
    echo "</tr>";

    foreach ($pharmacies as $pharmacy) {
        $has_location = (!empty($pharmacy['customer_latitude']) && !empty($pharmacy['customer_longitude'])) ? 'YES' : 'NO';
        $row_color = $has_location == 'YES' ? '#e8f5e9' : '#ffebee';

        echo "<tr style='background: $row_color;'>";
        echo "<td>" . htmlspecialchars($pharmacy['customer_id']) . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['customer_name']) . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['customer_city'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['customer_country'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['customer_address'] ?? 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['customer_latitude'] ?? 'NULL') . "</td>";
        echo "<td>" . htmlspecialchars($pharmacy['customer_longitude'] ?? 'NULL') . "</td>";
        echo "<td><strong>" . $has_location . "</strong></td>";
        echo "</tr>";
    }

    echo "</table>";

    // Summary
    $with_location = array_filter($pharmacies, function($p) {
        return !empty($p['customer_latitude']) && !empty($p['customer_longitude']);
    });

    echo "<h3>Summary:</h3>";
    echo "<p>Pharmacies WITH location data: <strong>" . count($with_location) . "</strong></p>";
    echo "<p>Pharmacies WITHOUT location data: <strong>" . (count($pharmacies) - count($with_location)) . "</strong></p>";

    if (count($with_location) == 0) {
        echo "<div style='background: #fff3cd; padding: 15px; border-left: 4px solid #ffc107; margin: 20px 0;'>";
        echo "<h4>⚠️ Issue Found!</h4>";
        echo "<p>None of the pharmacies have latitude/longitude data.</p>";
        echo "<p><strong>Solution:</strong> You need to add sample coordinates to the pharmacies in the database.</p>";
        echo "<p>You can run the migration: <code>db/sample_pharmacy_coordinates.sql</code></p>";
        echo "</div>";
    }
} else {
    echo "<p>No pharmacies found in database.</p>";
}
?>

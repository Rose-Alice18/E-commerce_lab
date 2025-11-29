<?php
/**
 * Test script to check if search_products returns pharmacy location data
 */

require_once('./classes/product_class.php');

$product_obj = new Product();
$results = $product_obj->search_products('paracetamol');

echo "<h2>Search Results for 'paracetamol'</h2>";
echo "<p>Total results: " . count($results) . "</p>";

if (count($results) > 0) {
    echo "<h3>Sample Product Data (First Result):</h3>";
    echo "<pre>";
    print_r($results[0]);
    echo "</pre>";

    echo "<h3>All Products:</h3>";
    foreach ($results as $index => $product) {
        echo "<div style='border: 1px solid #ccc; padding: 10px; margin: 10px 0;'>";
        echo "<strong>Product " . ($index + 1) . ":</strong> " . htmlspecialchars($product['product_title']) . "<br>";
        echo "<strong>Pharmacy Name:</strong> " . htmlspecialchars($product['pharmacy_name'] ?? 'N/A') . "<br>";
        echo "<strong>Pharmacy City:</strong> " . htmlspecialchars($product['pharmacy_city'] ?? 'N/A') . "<br>";
        echo "<strong>Pharmacy Country:</strong> " . htmlspecialchars($product['pharmacy_country'] ?? 'N/A') . "<br>";
        echo "<strong>Pharmacy Latitude:</strong> " . htmlspecialchars($product['pharmacy_latitude'] ?? 'N/A') . "<br>";
        echo "<strong>Pharmacy Longitude:</strong> " . htmlspecialchars($product['pharmacy_longitude'] ?? 'N/A') . "<br>";
        echo "<strong>Pharmacy Contact:</strong> " . htmlspecialchars($product['pharmacy_contact'] ?? 'N/A') . "<br>";
        echo "</div>";
    }
} else {
    echo "<p>No products found.</p>";
}
?>

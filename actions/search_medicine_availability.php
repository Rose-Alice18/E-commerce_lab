<?php
/**
 * Medicine Availability Search Action
 * Searches for medicine across all pharmacies and returns availability with distances
 */

session_start();
require_once('../settings/db_class.php');

header('Content-Type: application/json');

// Get search query and user location
$search_query = isset($_GET['q']) ? trim($_GET['q']) : '';
$user_lat = isset($_GET['lat']) ? floatval($_GET['lat']) : null;
$user_lng = isset($_GET['lng']) ? floatval($_GET['lng']) : null;

if (empty($search_query)) {
    echo json_encode(['success' => false, 'message' => 'Search query is required']);
    exit();
}

try {
    $db = new db_connection();
    $conn = $db->db_conn();

    // Search for products with pharmacy and location information
    $sql = "SELECT
                p.product_id,
                p.product_title,
                p.product_price,
                p.product_stock,
                p.product_image,
                p.product_desc,
                p.pharmacy_id,
                c.customer_name as pharmacy_name,
                c.customer_email as pharmacy_email,
                c.customer_contact as pharmacy_contact,
                c.customer_city,
                c.customer_country,
                c.latitude,
                c.longitude
            FROM products p
            INNER JOIN customer c ON p.pharmacy_id = c.customer_id
            WHERE c.user_role = 1
            AND p.product_stock > 0
            AND (
                p.product_title LIKE ? OR
                p.product_keywords LIKE ? OR
                p.product_desc LIKE ?
            )
            ORDER BY p.product_stock DESC, c.customer_name ASC";

    $stmt = $conn->prepare($sql);
    $search_param = "%{$search_query}%";
    $stmt->bind_param("sss", $search_param, $search_param, $search_param);
    $stmt->execute();
    $result = $stmt->get_result();

    $results = [];
    $total_pharmacies = 0;
    $total_stock = 0;

    while ($row = $result->fetch_assoc()) {
        // Calculate distance if user location is provided
        $distance = null;
        if ($user_lat !== null && $user_lng !== null &&
            $row['latitude'] !== null && $row['longitude'] !== null) {
            $distance = calculateDistance(
                $user_lat,
                $user_lng,
                floatval($row['latitude']),
                floatval($row['longitude'])
            );
        }

        $results[] = [
            'product_id' => $row['product_id'],
            'product_title' => $row['product_title'],
            'product_price' => floatval($row['product_price']),
            'product_stock' => intval($row['product_stock']),
            'product_image' => $row['product_image'],
            'product_desc' => $row['product_desc'],
            'pharmacy' => [
                'id' => $row['pharmacy_id'],
                'name' => $row['pharmacy_name'],
                'email' => $row['pharmacy_email'],
                'contact' => $row['pharmacy_contact'],
                'location' => $row['customer_city'] . ', ' . $row['customer_country'],
                'latitude' => $row['latitude'],
                'longitude' => $row['longitude']
            ],
            'distance_km' => $distance
        ];

        $total_pharmacies++;
        $total_stock += intval($row['product_stock']);
    }

    // Sort by distance if available
    if ($user_lat !== null && $user_lng !== null) {
        usort($results, function($a, $b) {
            if ($a['distance_km'] === null) return 1;
            if ($b['distance_km'] === null) return -1;
            return $a['distance_km'] <=> $b['distance_km'];
        });
    }

    echo json_encode([
        'success' => true,
        'query' => $search_query,
        'total_pharmacies' => $total_pharmacies,
        'total_stock' => $total_stock,
        'results' => $results,
        'user_location' => [
            'lat' => $user_lat,
            'lng' => $user_lng
        ]
    ]);

} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error searching for medicine: ' . $e->getMessage()
    ]);
}

/**
 * Calculate distance between two coordinates using Haversine formula
 * @param float $lat1 - Latitude of point 1
 * @param float $lng1 - Longitude of point 1
 * @param float $lat2 - Latitude of point 2
 * @param float $lng2 - Longitude of point 2
 * @return float - Distance in kilometers
 */
function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    $earth_radius = 6371; // Earth's radius in kilometers

    $dLat = deg2rad($lat2 - $lat1);
    $dLng = deg2rad($lng2 - $lng1);

    $a = sin($dLat/2) * sin($dLat/2) +
         cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
         sin($dLng/2) * sin($dLng/2);

    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    $distance = $earth_radius * $c;

    return round($distance, 2);
}
?>

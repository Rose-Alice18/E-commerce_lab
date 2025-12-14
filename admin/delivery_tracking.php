<?php
/**
 * Track Deliveries - Super Admin
 * Real-time delivery tracking with Google Maps
 */
session_start();
require_once('../settings/core.php');
require_once('../settings/db_cred.php');
require_once('../settings/google_maps_config.php');

// Check if user is super admin
if (!isSuperAdmin()) {
    header("Location: ../login/login.php");
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

// Get active deliveries (not delivered or cancelled)
// Note: riders table doesn't have vehicle_type, orders table doesn't have order_total
$active_deliveries_query = "SELECT da.*,
                                    r.rider_name, r.rider_phone, r.vehicle_number,
                                    c.customer_name, c.customer_contact, c.customer_address,
                                    c.customer_latitude, c.customer_longitude,
                                    p.customer_name as pharmacy_name,
                                    p.customer_address as pharmacy_address,
                                    p.customer_latitude as pharmacy_latitude,
                                    p.customer_longitude as pharmacy_longitude,
                                    o.invoice_no, o.order_status,
                                    pay.amt as order_total
                             FROM delivery_assignments da
                             INNER JOIN riders r ON da.rider_id = r.rider_id
                             INNER JOIN customer c ON da.customer_id = c.customer_id
                             LEFT JOIN customer p ON da.pharmacy_id = p.customer_id
                             INNER JOIN orders o ON da.order_id = o.order_id
                             LEFT JOIN payment pay ON o.order_id = pay.order_id
                             WHERE da.status IN ('assigned', 'picked_up', 'in_transit')
                             ORDER BY da.assigned_at DESC";

$active_result = $conn->query($active_deliveries_query);
$active_deliveries = [];
if ($active_result) {
    while ($row = $active_result->fetch_assoc()) {
        $active_deliveries[] = $row;
    }
}

// Get delivery statistics
$stats_query = "SELECT
                    COUNT(CASE WHEN status = 'assigned' THEN 1 END) as assigned,
                    COUNT(CASE WHEN status = 'picked_up' THEN 1 END) as picked_up,
                    COUNT(CASE WHEN status = 'in_transit' THEN 1 END) as in_transit,
                    COUNT(CASE WHEN status IN ('assigned', 'picked_up', 'in_transit') THEN 1 END) as total_active
                FROM delivery_assignments";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Track Deliveries - PharmaVault Admin</title>

    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.2">

    <style>
        :root {
            --primary: #667eea;
            --primary-dark: #764ba2;
            --success: #10b981;
            --danger: #ef4444;
            --warning: #f59e0b;
            --info: #3b82f6;
            --sidebar-width: 280px;
        }

        body {
            background: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .main-content {
            margin-left: var(--sidebar-width);
            padding: 2rem;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 2rem;
            border-radius: 15px;
            margin-bottom: 2rem;
        }

        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 800;
            margin-bottom: 0.5rem;
        }

        .stat-label {
            color: #64748b;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .map-container {
            background: white;
            border-radius: 15px;
            padding: 0;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            margin-bottom: 2rem;
            overflow: hidden;
        }

        #deliveryMap {
            width: 100%;
            height: 600px;
            border-radius: 15px;
        }

        .deliveries-list {
            background: white;
            border-radius: 15px;
            padding: 1.5rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }

        .section-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #1e293b;
        }

        .delivery-card {
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 1rem;
            margin-bottom: 1rem;
            transition: all 0.3s;
            cursor: pointer;
        }

        .delivery-card:hover {
            border-color: var(--primary);
            box-shadow: 0 3px 10px rgba(102, 126, 234, 0.1);
        }

        .delivery-card.active {
            border-color: var(--primary);
            background: #f8f9ff;
        }

        .status-badge {
            padding: 0.4rem 0.8rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .status-assigned {
            background: #fef3c7;
            color: #92400e;
        }

        .status-picked_up {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-in_transit {
            background: #e0e7ff;
            color: #4338ca;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }

        .map-controls {
            position: absolute;
            top: 20px;
            right: 20px;
            z-index: 1000;
            background: white;
            border-radius: 10px;
            padding: 1rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .control-btn {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            transition: all 0.3s;
            margin-bottom: 0.5rem;
            width: 100%;
        }

        .refresh-btn {
            background: var(--primary);
            color: white;
        }

        .refresh-btn:hover {
            background: var(--primary-dark);
        }

        @media (max-width: 1024px) {
            .main-content { margin-left: 70px; }
        }

        @media (max-width: 768px) {
            .main-content { margin-left: 0; padding: 1rem; }
            #deliveryMap { height: 400px; }
            .stats-row { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
    <?php include('../view/components/sidebar_super_admin.php'); ?>

    <div class="main-content">
        <div class="page-header">
            <h1><i class="fas fa-map-marked-alt me-3"></i>Track Deliveries</h1>
            <p class="mb-0">Real-time delivery tracking and monitoring</p>
        </div>

        <!-- Statistics -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-number text-warning"><?php echo $stats['assigned']; ?></div>
                <div class="stat-label">Assigned</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-info"><?php echo $stats['picked_up']; ?></div>
                <div class="stat-label">Picked Up</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-primary"><?php echo $stats['in_transit']; ?></div>
                <div class="stat-label">In Transit</div>
            </div>
            <div class="stat-card">
                <div class="stat-number text-success"><?php echo $stats['total_active']; ?></div>
                <div class="stat-label">Total Active</div>
            </div>
        </div>

        <div class="row">
            <!-- Map -->
            <div class="col-lg-8 mb-4">
                <div class="map-container">
                    <div id="deliveryMap"></div>
                </div>
            </div>

            <!-- Active Deliveries List -->
            <div class="col-lg-4">
                <div class="deliveries-list">
                    <h3 class="section-title">Active Deliveries</h3>

                    <?php if (count($active_deliveries) > 0): ?>
                        <div id="deliveriesList">
                            <?php foreach ($active_deliveries as $delivery): ?>
                                <div class="delivery-card"
                                     data-delivery-id="<?php echo $delivery['delivery_id']; ?>"
                                     data-lat="<?php echo $delivery['customer_latitude']; ?>"
                                     data-lng="<?php echo $delivery['customer_longitude']; ?>"
                                     data-pharmacy-lat="<?php echo $delivery['pharmacy_latitude']; ?>"
                                     data-pharmacy-lng="<?php echo $delivery['pharmacy_longitude']; ?>"
                                     onclick="focusDelivery(this)">
                                    <div class="info-row">
                                        <strong><?php echo htmlspecialchars($delivery['invoice_no']); ?></strong>
                                        <span class="status-badge status-<?php echo $delivery['status']; ?>">
                                            <?php echo ucfirst(str_replace('_', ' ', $delivery['status'])); ?>
                                        </span>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-user text-muted me-2"></i>
                                        <small><?php echo htmlspecialchars($delivery['customer_name']); ?></small>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-motorcycle text-muted me-2"></i>
                                        <small><?php echo htmlspecialchars($delivery['rider_name']); ?> - <?php echo htmlspecialchars($delivery['rider_phone']); ?></small>
                                    </div>
                                    <div class="mb-2">
                                        <i class="fas fa-map-marker-alt text-muted me-2"></i>
                                        <small><?php echo htmlspecialchars($delivery['delivery_address']); ?></small>
                                    </div>
                                    <?php if ($delivery['distance_km']): ?>
                                        <div>
                                            <i class="fas fa-route text-muted me-2"></i>
                                            <small><?php echo number_format($delivery['distance_km'], 2); ?> km</small>
                                        </div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php else: ?>
                        <div class="text-center py-5">
                            <i class="fas fa-check-circle fa-4x text-success mb-3"></i>
                            <h4>No Active Deliveries</h4>
                            <p class="text-muted">All deliveries have been completed</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://maps.googleapis.com/maps/api/js?key=<?php echo GOOGLE_MAPS_API_KEY; ?>&callback=initMap" async defer></script>

    <script>
        let map;
        let markers = [];
        let infoWindows = [];
        let currentDeliveryId = null;

        const deliveries = <?php echo json_encode($active_deliveries); ?>;

        function initMap() {
            // Default center (Accra, Ghana)
            let centerLat = <?php echo DEFAULT_MAP_CENTER_LAT; ?>;
            let centerLng = <?php echo DEFAULT_MAP_CENTER_LNG; ?>;

            // If there are deliveries, center on first one
            if (deliveries.length > 0 && deliveries[0].customer_latitude && deliveries[0].customer_longitude) {
                centerLat = parseFloat(deliveries[0].customer_latitude);
                centerLng = parseFloat(deliveries[0].customer_longitude);
            }

            map = new google.maps.Map(document.getElementById('deliveryMap'), {
                center: { lat: centerLat, lng: centerLng },
                zoom: 12,
                styles: [
                    {
                        featureType: 'poi',
                        elementType: 'labels',
                        stylers: [{ visibility: 'off' }]
                    }
                ]
            });

            // Add markers for all active deliveries
            deliveries.forEach(delivery => {
                addDeliveryMarkers(delivery);
            });

            // Auto-refresh every 30 seconds
            setInterval(refreshDeliveries, 30000);
        }

        function addDeliveryMarkers(delivery) {
            if (!delivery.customer_latitude || !delivery.customer_longitude) {
                return;
            }

            const customerLat = parseFloat(delivery.customer_latitude);
            const customerLng = parseFloat(delivery.customer_longitude);

            // Add customer location marker
            const customerMarker = new google.maps.Marker({
                position: { lat: customerLat, lng: customerLng },
                map: map,
                title: delivery.customer_name,
                icon: {
                    url: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
                },
                deliveryId: delivery.delivery_id
            });

            const customerInfo = new google.maps.InfoWindow({
                content: `
                    <div style="padding: 10px;">
                        <h6 style="margin-bottom: 10px; font-weight: bold;">Delivery Location</h6>
                        <p style="margin: 5px 0;"><strong>Invoice:</strong> ${delivery.invoice_no}</p>
                        <p style="margin: 5px 0;"><strong>Customer:</strong> ${delivery.customer_name}</p>
                        <p style="margin: 5px 0;"><strong>Phone:</strong> ${delivery.customer_contact}</p>
                        <p style="margin: 5px 0;"><strong>Address:</strong> ${delivery.customer_address || delivery.delivery_address}</p>
                        <p style="margin: 5px 0;"><strong>Rider:</strong> ${delivery.rider_name}</p>
                        <p style="margin: 5px 0;"><strong>Status:</strong> <span style="background: #fef3c7; padding: 3px 8px; border-radius: 10px;">${delivery.status.replace('_', ' ')}</span></p>
                    </div>
                `
            });

            customerMarker.addListener('click', () => {
                closeAllInfoWindows();
                customerInfo.open(map, customerMarker);
                highlightDeliveryCard(delivery.delivery_id);
            });

            markers.push(customerMarker);
            infoWindows.push(customerInfo);

            // Add pharmacy location marker if available
            if (delivery.pharmacy_latitude && delivery.pharmacy_longitude) {
                const pharmacyLat = parseFloat(delivery.pharmacy_latitude);
                const pharmacyLng = parseFloat(delivery.pharmacy_longitude);

                const pharmacyMarker = new google.maps.Marker({
                    position: { lat: pharmacyLat, lng: pharmacyLng },
                    map: map,
                    title: delivery.pharmacy_name,
                    icon: {
                        url: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
                    }
                });

                const pharmacyInfo = new google.maps.InfoWindow({
                    content: `
                        <div style="padding: 10px;">
                            <h6 style="margin-bottom: 10px; font-weight: bold;">Pickup Location</h6>
                            <p style="margin: 5px 0;"><strong>Pharmacy:</strong> ${delivery.pharmacy_name}</p>
                            <p style="margin: 5px 0;"><strong>Address:</strong> ${delivery.pharmacy_address}</p>
                        </div>
                    `
                });

                pharmacyMarker.addListener('click', () => {
                    closeAllInfoWindows();
                    pharmacyInfo.open(map, pharmacyMarker);
                });

                markers.push(pharmacyMarker);
                infoWindows.push(pharmacyInfo);

                // Draw route line between pharmacy and customer
                const routePath = new google.maps.Polyline({
                    path: [
                        { lat: pharmacyLat, lng: pharmacyLng },
                        { lat: customerLat, lng: customerLng }
                    ],
                    geodesic: true,
                    strokeColor: '#667eea',
                    strokeOpacity: 0.6,
                    strokeWeight: 3
                });

                routePath.setMap(map);
            }
        }

        function closeAllInfoWindows() {
            infoWindows.forEach(info => info.close());
        }

        function focusDelivery(element) {
            const lat = parseFloat(element.dataset.lat);
            const lng = parseFloat(element.dataset.lng);
            const deliveryId = element.dataset.deliveryId;

            if (lat && lng) {
                map.setCenter({ lat, lng });
                map.setZoom(14);

                // Find and open the marker's info window
                const marker = markers.find(m => m.deliveryId == deliveryId);
                if (marker) {
                    const infoIndex = markers.indexOf(marker);
                    closeAllInfoWindows();
                    infoWindows[infoIndex].open(map, marker);
                }
            }

            highlightDeliveryCard(deliveryId);
        }

        function highlightDeliveryCard(deliveryId) {
            document.querySelectorAll('.delivery-card').forEach(card => {
                card.classList.remove('active');
            });
            document.querySelector(`[data-delivery-id="${deliveryId}"]`)?.classList.add('active');
        }

        function refreshDeliveries() {
            location.reload();
        }

        // Auto-refresh notification
        setTimeout(() => {
            const notification = document.createElement('div');
            notification.style.cssText = 'position: fixed; top: 20px; right: 20px; background: #10b981; color: white; padding: 1rem; border-radius: 10px; z-index: 9999; box-shadow: 0 4px 10px rgba(0,0,0,0.2);';
            notification.innerHTML = '<i class="fas fa-sync-alt me-2"></i>Auto-refresh enabled (30s)';
            document.body.appendChild(notification);
            setTimeout(() => notification.remove(), 3000);
        }, 2000);
    </script>
</body>
</html>

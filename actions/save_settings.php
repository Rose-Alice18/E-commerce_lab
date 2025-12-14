<?php
/**
 * Save System Settings
 * Update system-wide configuration settings
 */
session_start();
require_once('../settings/core.php');

header('Content-Type: application/json');

// Check if user is super admin
if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$updated_by = $_SESSION['user_id'];
$success_count = 0;
$error_count = 0;

// List of expected settings
$settings_to_save = [
    'site_name', 'site_email', 'site_phone', 'maintenance_mode', 'allow_registration',
    'require_email_verification', 'max_upload_size', 'delivery_fee_per_km',
    'tax_percentage', 'currency_symbol', 'prescription_verification_required',
    'auto_approve_products', 'smtp_enabled', 'smtp_host', 'smtp_port',
    'smtp_username', 'items_per_page', 'session_timeout'
];

foreach ($settings_to_save as $key) {
    if (isset($_POST[$key])) {
        $value = $_POST[$key];

        // Validate numeric values
        if (in_array($key, ['max_upload_size', 'delivery_fee_per_km', 'tax_percentage', 'smtp_port', 'items_per_page', 'session_timeout'])) {
            if (!is_numeric($value)) {
                $error_count++;
                continue;
            }
        }

        // Update or insert setting
        $query = "INSERT INTO system_settings (setting_key, setting_value, updated_by)
                  VALUES (?, ?, ?)
                  ON DUPLICATE KEY UPDATE setting_value = ?, updated_by = ?, updated_at = NOW()";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssiis", $key, $value, $updated_by, $value, $updated_by);

        if ($stmt->execute()) {
            $success_count++;
        } else {
            $error_count++;
        }

        $stmt->close();
    }
}

$conn->close();

if ($error_count == 0) {
    echo json_encode([
        'success' => true,
        'message' => "$success_count settings saved successfully"
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => "$success_count settings saved, $error_count failed"
    ]);
}
?>

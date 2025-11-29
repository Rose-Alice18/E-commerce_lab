<?php
/**
 * Upload Prescription Action
 * Handles multiple prescription image uploads
 */

session_start();
require_once('../settings/core.php');
require_once('../controllers/prescription_controller.php');

header('Content-Type: application/json');

// Check if user is logged in
if (!isLoggedIn() || !isRegularCustomer()) {
    echo json_encode(['success' => false, 'message' => 'Please login as a customer']);
    exit();
}

$customer_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Debug logging
        error_log("=== Prescription Upload Started ===");
        error_log("Customer ID: " . $customer_id);
        error_log("FILES data: " . print_r($_FILES, true));
        error_log("POST data: " . print_r($_POST, true));

        // Validate required fields - only images are required
        if (empty($_FILES['prescription_images']['name'][0])) {
            error_log("Error: No files uploaded");
            throw new Exception('At least one prescription image is required');
        }

        // Generate prescription number
        $prescription_number = generate_prescription_number_ctr();
        error_log("Generated prescription number: " . $prescription_number);

        // Prepare prescription data - all fields optional except customer_id and prescription_number
        // Pharmacist will read details from the prescription images
        $prescription_data = [
            'customer_id' => $customer_id,
            'prescription_number' => $prescription_number,
            'doctor_name' => null, // Will be read from prescription image
            'doctor_license' => null, // Will be read from prescription image
            'issue_date' => null, // Will be read from prescription image
            'expiry_date' => null, // Will be read from prescription image
            'prescription_image' => '', // Will be updated with first image
            'prescription_notes' => !empty($_POST['prescription_notes']) ? trim($_POST['prescription_notes']) : null,
            'allow_pharmacy_access' => isset($_POST['allow_pharmacy_access']) ? intval($_POST['allow_pharmacy_access']) : 1
        ];

        // Upload prescription images
        $uploaded_images = [];
        $files = $_FILES['prescription_images'];
        $file_count = count($files['name']);

        for ($i = 0; $i < $file_count; $i++) {
            // Skip if no file
            if (empty($files['name'][$i])) {
                continue;
            }

            // Create file array for upload function
            $file = [
                'name' => $files['name'][$i],
                'type' => $files['type'][$i],
                'tmp_name' => $files['tmp_name'][$i],
                'error' => $files['error'][$i],
                'size' => $files['size'][$i]
            ];

            // Upload file
            error_log("Uploading file: " . $file['name']);
            $upload_result = upload_prescription_image($file, $customer_id);
            error_log("Upload result: " . print_r($upload_result, true));

            if (!$upload_result['success']) {
                error_log("Upload failed for file: " . $file['name'] . " - " . $upload_result['message']);
                throw new Exception($upload_result['message']);
            }

            $uploaded_images[] = [
                'path' => $upload_result['file_path'],
                'is_primary' => ($i === 0) // First image is primary
            ];
        }

        if (empty($uploaded_images)) {
            throw new Exception('No images were uploaded');
        }

        // Set primary image
        $prescription_data['prescription_image'] = $uploaded_images[0]['path'];

        // Create prescription record
        error_log("Creating prescription record: " . print_r($prescription_data, true));
        $prescription_id = upload_prescription_ctr($prescription_data);
        error_log("Created prescription ID: " . $prescription_id);

        if (!$prescription_id) {
            error_log("Failed to create prescription record");
            throw new Exception('Failed to create prescription record');
        }

        // Save all prescription images to prescription_images table
        require_once('../settings/db_class.php');
        $db = new db_connection();
        $conn = $db->db_conn();

        foreach ($uploaded_images as $index => $image) {
            $image_type = ($index === 0) ? 'front' : (($index === 1) ? 'back' : 'other');
            $is_primary = $image['is_primary'] ? 1 : 0;

            $sql = "INSERT INTO prescription_images (prescription_id, image_path, image_type, is_primary)
                    VALUES (?, ?, ?, ?)";

            $stmt = $conn->prepare($sql);
            $stmt->bind_param("issi", $prescription_id, $image['path'], $image_type, $is_primary);
            $stmt->execute();
        }

        // Log activity
        error_log("Prescription uploaded - ID: $prescription_id, Customer: $customer_id, Images: " . count($uploaded_images));

        echo json_encode([
            'success' => true,
            'message' => 'Prescription uploaded successfully',
            'prescription_id' => $prescription_id,
            'prescription_number' => $prescription_number,
            'image_count' => count($uploaded_images)
        ]);

    } catch (Exception $e) {
        error_log("Error uploading prescription: " . $e->getMessage());

        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
}
?>

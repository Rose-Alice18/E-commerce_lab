<?php
/**
 * Get Announcement Details
 * Fetch details of a specific announcement
 */
session_start();
require_once('../settings/core.php');

header('Content-Type: application/json');

// Check if user is super admin
if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$announcement_id = isset($_GET['announcement_id']) ? intval($_GET['announcement_id']) : 0;

if ($announcement_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$query = "SELECT * FROM announcements WHERE announcement_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $announcement_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $announcement = $result->fetch_assoc();
    echo json_encode([
        'success' => true,
        'announcement' => $announcement
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Announcement not found'
    ]);
}

$stmt->close();
$conn->close();
?>

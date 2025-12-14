<?php
/**
 * Delete Announcement
 * Remove an announcement from the system
 */
session_start();
require_once('../settings/core.php');

header('Content-Type: application/json');

// Check if user is super admin
if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

$announcement_id = isset($_POST['announcement_id']) ? intval($_POST['announcement_id']) : 0;

if ($announcement_id <= 0) {
    echo json_encode(['success' => false, 'message' => 'Invalid announcement ID']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$query = "DELETE FROM announcements WHERE announcement_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $announcement_id);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode([
            'success' => true,
            'message' => 'Announcement deleted successfully'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Announcement not found'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete announcement: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>

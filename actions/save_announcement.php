<?php
/**
 * Save Announcement
 * Create or update an announcement
 */
session_start();
require_once('../settings/core.php');

header('Content-Type: application/json');

// Check if user is super admin
if (!isSuperAdmin()) {
    echo json_encode(['success' => false, 'message' => 'Unauthorized access']);
    exit();
}

// Get form data
$announcement_id = isset($_POST['announcement_id']) ? intval($_POST['announcement_id']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$priority = isset($_POST['priority']) ? trim($_POST['priority']) : 'medium';
$status = isset($_POST['status']) ? trim($_POST['status']) : 'draft';
$target_audience = isset($_POST['target_audience']) ? trim($_POST['target_audience']) : 'all';
$expires_at = isset($_POST['expires_at']) && !empty($_POST['expires_at']) ? $_POST['expires_at'] : null;

// Validate inputs
if (empty($title)) {
    echo json_encode(['success' => false, 'message' => 'Title is required']);
    exit();
}

if (empty($content)) {
    echo json_encode(['success' => false, 'message' => 'Content is required']);
    exit();
}

if (!in_array($priority, ['low', 'medium', 'high'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid priority']);
    exit();
}

if (!in_array($status, ['draft', 'active', 'archived'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status']);
    exit();
}

if (!in_array($target_audience, ['all', 'customers', 'pharmacies', 'admins'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid target audience']);
    exit();
}

$conn = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed']);
    exit();
}

$created_by = $_SESSION['user_id'];

if ($announcement_id > 0) {
    // Update existing announcement
    if ($expires_at) {
        $query = "UPDATE announcements
                  SET title = ?, content = ?, priority = ?, status = ?,
                      target_audience = ?, expires_at = ?, updated_at = NOW()
                  WHERE announcement_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $title, $content, $priority, $status,
                         $target_audience, $expires_at, $announcement_id);
    } else {
        $query = "UPDATE announcements
                  SET title = ?, content = ?, priority = ?, status = ?,
                      target_audience = ?, expires_at = NULL, updated_at = NOW()
                  WHERE announcement_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $title, $content, $priority, $status,
                         $target_audience, $announcement_id);
    }
} else {
    // Create new announcement
    if ($expires_at) {
        $query = "INSERT INTO announcements
                  (title, content, priority, status, target_audience, expires_at, created_by, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("ssssssi", $title, $content, $priority, $status,
                         $target_audience, $expires_at, $created_by);
    } else {
        $query = "INSERT INTO announcements
                  (title, content, priority, status, target_audience, created_by, created_at)
                  VALUES (?, ?, ?, ?, ?, ?, NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("sssssi", $title, $content, $priority, $status,
                         $target_audience, $created_by);
    }
}

if ($stmt->execute()) {
    echo json_encode([
        'success' => true,
        'message' => 'Announcement saved successfully',
        'announcement_id' => $announcement_id > 0 ? $announcement_id : $stmt->insert_id
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to save announcement: ' . $stmt->error
    ]);
}

$stmt->close();
$conn->close();
?>

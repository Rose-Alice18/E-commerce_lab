<?php
session_start();
header('Content-Type: application/json');

require_once('../settings/core.php');
requireAdmin();

$sender_id = $_SESSION['user_id'];
$receiver_id = isset($_POST['receiver_id']) ? intval($_POST['receiver_id']) : 0;
$subject = isset($_POST['subject']) ? trim($_POST['subject']) : '';
$message = isset($_POST['message']) ? trim($_POST['message']) : '';

if (!$receiver_id || !$subject || !$message) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit();
}

try {
    require_once('../settings/db_class.php');
    $db = new db_connection();
    $conn = $db->db_conn();

    $sql = "INSERT INTO messages (sender_id, receiver_id, subject, message_text) VALUES (?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiss", $sender_id, $receiver_id, $subject, $message);

    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Message sent successfully']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to send message']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>

<?php
session_start();
require_once('../settings/core.php');
requireAdmin();

require_once('../settings/db_class.php');
$db = new db_connection();
$conn = $db->db_conn();

$user_id = $_SESSION['user_id'];
$user_role = $_SESSION['user_role'];

// Get all messages
$sql = "SELECT m.*,
        sender.customer_name as sender_name, sender.user_role as sender_role,
        receiver.customer_name as receiver_name
        FROM messages m
        JOIN customer sender ON m.sender_id = sender.customer_id
        JOIN customer receiver ON m.receiver_id = receiver.customer_id
        WHERE m.sender_id = ? OR m.receiver_id = ?
        ORDER BY m.created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get super admins for messaging
$super_admins = [];
if ($user_role == 1) {
    $sql = "SELECT customer_id, customer_name, customer_email FROM customer WHERE user_role IN (0, 7)";
    $result = $conn->query($sql);
    $super_admins = $result->fetch_all(MYSQLI_ASSOC);
}

// Get pharmacies for super admin
$pharmacies = [];
if ($user_role == 0 || $user_role == 7) {
    $sql = "SELECT customer_id, customer_name, customer_email FROM customer WHERE user_role = 1";
    $result = $conn->query($sql);
    $pharmacies = $result->fetch_all(MYSQLI_ASSOC);
}

// Mark messages as read
$sql = "UPDATE messages SET is_read = 1 WHERE receiver_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Messages - PharmaVault</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../css/sidebar.css?v=2.5">
    <style>
        .message-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
            transition: all 0.3s;
        }
        .message-card.unread {
            border-left: 4px solid #667eea;
            background: #f8f9ff;
        }
        .message-card:hover {
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .sender-info {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .sender-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
        }
    </style>
</head>
<body>
    <?php
    if ($user_role == 0 || $user_role == 7) {
        include '../view/components/sidebar_super_admin.php';
    } else {
        include '../view/components/sidebar_pharmacy_admin.php';
    }
    ?>

    <div class="main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-comments"></i> Messages</h1>
                    <button class="btn btn-primary" onclick="composeMessage()">
                        <i class="fas fa-plus"></i> New Message
                    </button>
                </div>

                <!-- Messages List -->
                <?php if (empty($messages)): ?>
                    <div class="text-center py-5">
                        <i class="fas fa-inbox" style="font-size: 64px; color: #ddd;"></i>
                        <h3 class="mt-3 text-muted">No Messages</h3>
                        <p>Start a conversation by sending a message</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($messages as $message): ?>
                        <div class="message-card <?php echo !$message['is_read'] && $message['receiver_id'] == $user_id ? 'unread' : ''; ?>">
                            <div class="message-header">
                                <div class="sender-info">
                                    <div class="sender-avatar">
                                        <i class="fas fa-user"></i>
                                    </div>
                                    <div>
                                        <strong>
                                            <?php if ($message['sender_id'] == $user_id): ?>
                                                To: <?php echo htmlspecialchars($message['receiver_name']); ?>
                                            <?php else: ?>
                                                From: <?php echo htmlspecialchars($message['sender_name']); ?>
                                            <?php endif; ?>
                                        </strong>
                                        <div class="text-muted small">
                                            <?php echo date('M d, Y h:i A', strtotime($message['created_at'])); ?>
                                        </div>
                                    </div>
                                </div>
                                <?php if (!$message['is_read'] && $message['receiver_id'] == $user_id): ?>
                                    <span class="badge bg-primary">New</span>
                                <?php endif; ?>
                            </div>
                            <h5><?php echo htmlspecialchars($message['subject']); ?></h5>
                            <p class="mb-0"><?php echo nl2br(htmlspecialchars($message['message_text'])); ?></p>
                            <?php if ($message['sender_id'] != $user_id): ?>
                                <div class="mt-3">
                                    <button class="btn btn-sm btn-outline-primary" onclick="replyToMessage('<?php echo htmlspecialchars($message['sender_name']); ?>', <?php echo $message['sender_id']; ?>, '<?php echo htmlspecialchars($message['subject']); ?>')">
                                        <i class="fas fa-reply"></i> Reply
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
    </div>

    <script src="../js/sidebar.js?v=2.5"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const userRole = <?php echo $user_role; ?>;
        const superAdmins = <?php echo json_encode($super_admins); ?>;
        const pharmacies = <?php echo json_encode($pharmacies); ?>;

        function composeMessage() {
            let recipientOptions = '';

            if (userRole == 1) {
                recipientOptions = superAdmins.map(admin =>
                    `<option value="${admin.customer_id}">${admin.customer_name} (${admin.customer_email})</option>`
                ).join('');
            } else {
                recipientOptions = pharmacies.map(pharmacy =>
                    `<option value="${pharmacy.customer_id}">${pharmacy.customer_name}</option>`
                ).join('');
            }

            Swal.fire({
                title: 'New Message',
                html: `
                    <select id="recipient" class="swal2-select">
                        <option value="">Select Recipient</option>
                        ${recipientOptions}
                    </select>
                    <input id="subject" class="swal2-input" placeholder="Subject">
                    <textarea id="message" class="swal2-textarea" placeholder="Message" rows="5"></textarea>
                `,
                confirmButtonText: 'Send',
                showCancelButton: true,
                preConfirm: () => {
                    const recipient = document.getElementById('recipient').value;
                    const subject = document.getElementById('subject').value;
                    const message = document.getElementById('message').value;

                    if (!recipient || !subject || !message) {
                        Swal.showValidationMessage('Please fill all fields');
                        return false;
                    }

                    return { recipient, subject, message };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    sendMessage(result.value.recipient, result.value.subject, result.value.message);
                }
            });
        }

        function replyToMessage(senderName, senderId, originalSubject) {
            Swal.fire({
                title: `Reply to ${senderName}`,
                html: `
                    <input id="subject" class="swal2-input" value="Re: ${originalSubject}" readonly>
                    <textarea id="message" class="swal2-textarea" placeholder="Your reply..." rows="5"></textarea>
                `,
                confirmButtonText: 'Send Reply',
                showCancelButton: true,
                preConfirm: () => {
                    const message = document.getElementById('message').value;
                    if (!message) {
                        Swal.showValidationMessage('Please enter a message');
                        return false;
                    }
                    return { message };
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    sendMessage(senderId, `Re: ${originalSubject}`, result.value.message);
                }
            });
        }

        function sendMessage(recipient, subject, message) {
            fetch('../actions/send_message.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `receiver_id=${recipient}&subject=${encodeURIComponent(subject)}&message=${encodeURIComponent(message)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire('Sent!', data.message, 'success')
                        .then(() => location.reload());
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    </script>
</body>
</html>

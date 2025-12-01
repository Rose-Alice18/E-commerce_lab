<?php
/**
 * PharmaVault Chatbot Component
 * Include this file on any page where you want the chatbot to appear
 *
 * Usage:
 * <?php include '../view/components/chatbot.php'; ?>
 */

// Only show chatbot for customers (not admin pages)
$show_chatbot = !isset($_SESSION['user_role']) || $_SESSION['user_role'] == 2;

if ($show_chatbot):
?>

<!-- Chatbot CSS -->
<link rel="stylesheet" href="../css/chatbot.css">

<!-- Chatbot will be injected here by JavaScript -->

<!-- Chatbot JavaScript -->
<script src="../js/chatbot.js"></script>

<?php endif; ?>

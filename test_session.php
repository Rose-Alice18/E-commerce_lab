<?php
/**
 * TEST SESSION FILE
 * Use this to debug your session and login issues
 * Access at: http://yoursite/test_session.php
 */

// Start session
session_start();

// Include core functions
require_once(dirname(__FILE__) . '/settings/core.php');

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Session Debug Test</title>
    <style>
        body {
            font-family: monospace;
            padding: 20px;
            background: #f5f5f5;
        }
        .debug-box {
            background: white;
            padding: 20px;
            border-radius: 5px;
            margin: 10px 0;
            border-left: 4px solid #667eea;
        }
        .success { border-left-color: #10b981; }
        .error { border-left-color: #ef4444; }
        h2 { margin-top: 0; color: #667eea; }
        pre { background: #f9fafb; padding: 10px; border-radius: 3px; overflow-x: auto; }
        .status { 
            display: inline-block; 
            padding: 5px 10px; 
            border-radius: 3px; 
            font-weight: bold;
        }
        .status.yes { background: #10b981; color: white; }
        .status.no { background: #ef4444; color: white; }
    </style>
</head>
<body>
    <h1>🔍 Session Debug Test - PharmacyHub</h1>
    
    <div class="debug-box <?php echo isLoggedIn() ? 'success' : 'error'; ?>">
        <h2>1. Login Status</h2>
        <p>
            Logged In: <span class="status <?php echo isLoggedIn() ? 'yes' : 'no'; ?>">
                <?php echo isLoggedIn() ? 'YES ✓' : 'NO ✗'; ?>
            </span>
        </p>
        <p>
            Is Admin: <span class="status <?php echo hasAdminPrivileges() ? 'yes' : 'no'; ?>">
                <?php echo hasAdminPrivileges() ? 'YES ✓' : 'NO ✗'; ?>
            </span>
        </p>
    </div>

    <div class="debug-box">
        <h2>2. Session Data</h2>
        <?php if (!empty($_SESSION)): ?>
            <pre><?php print_r($_SESSION); ?></pre>
        <?php else: ?>
            <p style="color: #ef4444;">⚠️ Session is EMPTY - No user logged in</p>
        <?php endif; ?>
    </div>

    <div class="debug-box">
        <h2>3. Expected Session Variables</h2>
        <table style="width: 100%; border-collapse: collapse;">
            <tr style="background: #f9fafb;">
                <th style="text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb;">Variable</th>
                <th style="text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb;">Status</th>
                <th style="text-align: left; padding: 8px; border-bottom: 2px solid #e5e7eb;">Value</th>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">customer_id</td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                    <?php echo isset($_SESSION['customer_id']) ? '✓' : '✗'; ?>
                </td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                    <?php echo isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 'Not set'; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">customer_name</td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                    <?php echo isset($_SESSION['customer_name']) ? '✓' : '✗'; ?>
                </td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                    <?php echo isset($_SESSION['customer_name']) ? $_SESSION['customer_name'] : 'Not set'; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">customer_email</td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                    <?php echo isset($_SESSION['customer_email']) ? '✓' : '✗'; ?>
                </td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                    <?php echo isset($_SESSION['customer_email']) ? $_SESSION['customer_email'] : 'Not set'; ?>
                </td>
            </tr>
            <tr>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">user_role</td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                    <?php echo isset($_SESSION['user_role']) ? '✓' : '✗'; ?>
                </td>
                <td style="padding: 8px; border-bottom: 1px solid #e5e7eb;">
                    <?php 
                    if (isset($_SESSION['user_role'])) {
                        echo $_SESSION['user_role'] . ' (' . ($_SESSION['user_role'] == 1 ? 'Admin' : 'Customer') . ')';
                    } else {
                        echo 'Not set';
                    }
                    ?>
                </td>
            </tr>
        </table>
    </div>

    <div class="debug-box">
        <h2>4. Database Check</h2>
        <?php
        // Check if we can connect to database
        try {
            require_once 'db/db_class.php';
            $db = new db_connection();
            
            // Get admin users from database
            $sql = "SELECT customer_id, customer_name, customer_email, user_role FROM customer WHERE user_role = 1";
            $result = $db->db_conn()->query($sql);
            
            if ($result && $result->num_rows > 0) {
                echo "<p style='color: #10b981;'>✓ Found " . $result->num_rows . " admin user(s) in database:</p>";
                echo "<pre>";
                while ($row = $result->fetch_assoc()) {
                    print_r($row);
                }
                echo "</pre>";
            } else {
                echo "<p style='color: #ef4444;'>✗ No admin users found in database!</p>";
                echo "<p>You need to create an admin user. Run this SQL:</p>";
                echo "<pre>INSERT INTO customer (customer_name, customer_email, customer_pass, customer_contact, user_role) 
VALUES ('Admin User', 'admin@pharmahub.com', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0501234567', 1);</pre>";
                echo "<p><small>Password for this admin: <strong>password</strong></small></p>";
            }
        } catch (Exception $e) {
            echo "<p style='color: #ef4444;'>✗ Database connection error: " . $e->getMessage() . "</p>";
        }
        ?>
    </div>

    <div class="debug-box">
        <h2>5. Quick Actions</h2>
        <p>
            <a href="index.php" style="display: inline-block; padding: 10px 20px; background: #667eea; color: white; text-decoration: none; border-radius: 5px;">
                Go to Index
            </a>
            <a href="login/login.php" style="display: inline-block; padding: 10px 20px; background: #10b981; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">
                Go to Login
            </a>
            <?php if (isLoggedIn()): ?>
            <a href="login/logout.php" style="display: inline-block; padding: 10px 20px; background: #ef4444; color: white; text-decoration: none; border-radius: 5px; margin-left: 10px;">
                Logout
            </a>
            <?php endif; ?>
        </p>
    </div>

    <div class="debug-box">
        <h2>6. Core Functions Test</h2>
        <p>Testing if core.php functions are working:</p>
        <pre><?php
        echo "isLoggedIn() exists: " . (function_exists('isLoggedIn') ? 'YES' : 'NO') . "\n";
        echo "hasAdminPrivileges() exists: " . (function_exists('hasAdminPrivileges') ? 'YES' : 'NO') . "\n";
        echo "getUserId() exists: " . (function_exists('getUserId') ? 'YES' : 'NO') . "\n";
        
        if (function_exists('getUserId')) {
            echo "getUserId() returns: " . (getUserId() ?? 'NULL') . "\n";
        }
        ?></pre>
    </div>

    <p style="text-align: center; color: #6b7280; margin-top: 40px;">
        <small>⚠️ Remember to delete this file after debugging!</small>
    </p>
</body>
</html>
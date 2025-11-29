<?php
/**
 * Database Table Checker and Installer
 * Run this file once to ensure all required tables exist
 */

require_once('../settings/db_class.php');

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - PharmaVault</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            max-width: 900px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f7fa;
        }
        .container {
            background: white;
            border-radius: 10px;
            padding: 30px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        h1 {
            color: #667eea;
            margin-bottom: 30px;
        }
        .table-status {
            margin: 15px 0;
            padding: 12px;
            border-radius: 8px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .exists {
            background: #d1fae5;
            border-left: 4px solid #10b981;
        }
        .missing {
            background: #fee2e2;
            border-left: 4px solid #ef4444;
        }
        .created {
            background: #dbeafe;
            border-left: 4px solid #3b82f6;
        }
        .btn {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }
        .error {
            background: #fee2e2;
            border: 1px solid #ef4444;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .success {
            background: #d1fae5;
            border: 1px solid #10b981;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>📊 Database Table Checker</h1>

        <?php
        $db = new db_connection();
        $conn = $db->db_conn();

        if (!$conn) {
            echo '<div class="error">❌ Database connection failed!</div>';
            exit();
        }

        // Tables to check
        $required_tables = [
            'wishlist' => 'Stores customer product wishlists',
            'prescriptions' => 'Main prescription records',
            'prescription_items' => 'Individual medications in prescriptions',
            'prescription_images' => 'Multiple images for each prescription',
            'order_prescriptions' => 'Links prescriptions to orders',
            'prescription_verification_log' => 'Tracks prescription status changes',
            'suggestions' => 'Customer suggestions for categories/brands',
            'product_reviews' => 'Product ratings and reviews'
        ];

        $missing_tables = [];
        $existing_tables = [];

        // Check each table
        foreach ($required_tables as $table => $description) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                $existing_tables[] = $table;
                echo "<div class='table-status exists'>";
                echo "<span><strong>$table</strong> - $description</span>";
                echo "<span style='color: #10b981;'>✓ EXISTS</span>";
                echo "</div>";
            } else {
                $missing_tables[] = $table;
                echo "<div class='table-status missing'>";
                echo "<span><strong>$table</strong> - $description</span>";
                echo "<span style='color: #ef4444;'>✗ MISSING</span>";
                echo "</div>";
            }
        }

        echo "<hr style='margin: 30px 0; border: none; border-top: 2px solid #e5e7eb;'>";

        if (empty($missing_tables)) {
            echo "<div class='success'>";
            echo "<h3 style='margin-top: 0;'>✅ All Required Tables Exist!</h3>";
            echo "<p>Your database is properly set up. You can start using the prescription upload feature.</p>";
            echo "</div>";
        } else {
            echo "<div class='error'>";
            echo "<h3 style='margin-top: 0;'>⚠️ Missing " . count($missing_tables) . " Table(s)</h3>";
            echo "<p>The following tables need to be created:</p>";
            echo "<ul>";
            foreach ($missing_tables as $table) {
                echo "<li><strong>$table</strong></li>";
            }
            echo "</ul>";
            echo "<p><strong>Action Required:</strong> Run the SQL file to create missing tables:</p>";
            echo "<ol>";
            echo "<li>Open <strong>phpMyAdmin</strong> in your browser</li>";
            echo "<li>Select the <strong>pharmavault_db</strong> database</li>";
            echo "<li>Click on the <strong>SQL</strong> tab</li>";
            echo "<li>Copy the contents of <code>db/add_missing_tables.sql</code></li>";
            echo "<li>Paste and click <strong>Go</strong></li>";
            echo "</ol>";
            echo "</div>";
        }

        // Check uploads directory
        echo "<hr style='margin: 30px 0; border: none; border-top: 2px solid #e5e7eb;'>";
        echo "<h2>📁 Upload Directory Check</h2>";

        $upload_dir = __DIR__ . '/../uploads/prescriptions/';
        if (is_dir($upload_dir) && is_writable($upload_dir)) {
            echo "<div class='table-status exists'>";
            echo "<span><strong>uploads/prescriptions/</strong> - Prescription image storage</span>";
            echo "<span style='color: #10b981;'>✓ EXISTS & WRITABLE</span>";
            echo "</div>";
        } else {
            echo "<div class='table-status missing'>";
            echo "<span><strong>uploads/prescriptions/</strong> - Prescription image storage</span>";
            echo "<span style='color: #ef4444;'>✗ MISSING OR NOT WRITABLE</span>";
            echo "</div>";

            // Try to create it
            if (!is_dir($upload_dir)) {
                if (@mkdir($upload_dir, 0755, true)) {
                    echo "<div class='success'>✅ Created directory successfully!</div>";
                } else {
                    echo "<div class='error'>❌ Failed to create directory. Please create it manually with write permissions.</div>";
                }
            } else {
                echo "<div class='error'>❌ Directory exists but is not writable. Please set permissions to 755.</div>";
            }
        }

        $conn->close();
        ?>

        <hr style='margin: 30px 0; border: none; border-top: 2px solid #e5e7eb;'>
        <a href="../view/upload_prescription.php" class="btn">→ Go to Prescription Upload</a>
        <button onclick="location.reload()" class="btn" style="background: linear-gradient(135deg, #10b981, #059669); margin-left: 10px;">🔄 Refresh Check</button>
    </div>
</body>
</html>

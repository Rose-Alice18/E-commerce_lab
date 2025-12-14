-- Add System Settings and Announcements Tables
-- Run this SQL file to add the required tables for system settings and announcements

-- System Settings Table
CREATE TABLE IF NOT EXISTS `system_settings` (
  `setting_id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(100) NOT NULL UNIQUE,
  `setting_value` text DEFAULT NULL,
  `setting_type` enum('text','number','boolean','json') DEFAULT 'text',
  `description` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `updated_by` int(11) DEFAULT NULL,
  PRIMARY KEY (`setting_id`),
  KEY `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='System-wide configuration settings';

-- Announcements Table
CREATE TABLE IF NOT EXISTS `announcements` (
  `announcement_id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `content` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('draft','active','archived') DEFAULT 'draft',
  `target_audience` enum('all','customers','pharmacies','admins') DEFAULT 'all',
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`announcement_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `idx_target_audience` (`target_audience`),
  FOREIGN KEY (`created_by`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='System announcements and notifications';

-- Insert Default System Settings
INSERT INTO `system_settings` (`setting_key`, `setting_value`, `setting_type`, `description`) VALUES
('site_name', 'PharmaVault', 'text', 'The name of your pharmacy platform'),
('site_email', 'admin@pharmavault.com', 'text', 'Primary email for customer communications'),
('site_phone', '+233 123 456 789', 'text', 'Primary phone number for support'),
('maintenance_mode', '0', 'boolean', 'Temporarily disable the site for maintenance'),
('allow_registration', '1', 'boolean', 'Allow new users to register accounts'),
('require_email_verification', '1', 'boolean', 'Users must verify email before accessing account'),
('max_upload_size', '5', 'number', 'Maximum file size for uploads (MB)'),
('delivery_fee_per_km', '2.50', 'number', 'Base delivery charge per kilometer'),
('tax_percentage', '12.5', 'number', 'Default tax rate applied to orders (%)'),
('currency_symbol', 'GH₵', 'text', 'Symbol used for displaying prices'),
('prescription_verification_required', '1', 'boolean', 'Admin approval required for prescriptions'),
('auto_approve_products', '0', 'boolean', 'Automatically approve pharmacy product listings'),
('smtp_enabled', '0', 'boolean', 'Use SMTP server for sending emails'),
('smtp_host', '', 'text', 'SMTP server hostname'),
('smtp_port', '587', 'number', 'SMTP server port'),
('smtp_username', '', 'text', 'Email account for sending'),
('items_per_page', '20', 'number', 'Number of items shown in lists'),
('session_timeout', '30', 'number', 'User session expiration time (minutes)')
ON DUPLICATE KEY UPDATE `setting_key` = `setting_key`;

-- Sample Announcement (Optional - for testing)
-- INSERT INTO `announcements` (`title`, `content`, `priority`, `status`, `target_audience`, `created_by`) VALUES
-- ('Welcome to PharmaVault', 'We are excited to announce the launch of our new pharmacy platform!', 'high', 'active', 'all', 1);

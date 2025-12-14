-- Additional Tables for Super Admin Features
-- Run this SQL file to create tables for reviews, feedback, tickets, and logs

-- Support Tickets Table
CREATE TABLE IF NOT EXISTS `support_tickets` (
  `ticket_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `subject` varchar(255) NOT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high','urgent') DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `assigned_to` int(11) DEFAULT NULL COMMENT 'Admin user_id',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `resolved_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`ticket_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_status` (`status`),
  KEY `idx_priority` (`priority`),
  FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Customer support tickets';

-- Product Reviews Table (if not already created by add_reviews_table.sql)
CREATE TABLE IF NOT EXISTS `product_reviews` (
  `review_id` int(11) NOT NULL AUTO_INCREMENT,
  `product_id` int(11) NOT NULL,
  `customer_id` int(11) NOT NULL,
  `rating` tinyint(1) NOT NULL COMMENT '1-5 stars',
  `review_title` varchar(200) DEFAULT NULL,
  `review_text` text DEFAULT NULL,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `helpful_count` int DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_customer_product_review` (`product_id`, `customer_id`),
  KEY `idx_product` (`product_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_rating` (`rating`),
  FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE,
  CONSTRAINT `chk_rating` CHECK (`rating` >= 1 AND `rating` <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Product reviews and ratings';

-- Customer Feedback Table
CREATE TABLE IF NOT EXISTS `customer_feedback` (
  `feedback_id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) NOT NULL,
  `type` enum('complaint','suggestion','praise','other') DEFAULT 'other',
  `subject` varchar(255) NOT NULL,
  `message` text NOT NULL,
  `status` enum('new','reviewed','actioned') DEFAULT 'new',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` timestamp NULL DEFAULT NULL,
  `reviewed_by` int(11) DEFAULT NULL COMMENT 'Admin user_id',
  PRIMARY KEY (`feedback_id`),
  KEY `idx_customer` (`customer_id`),
  KEY `idx_type` (`type`),
  KEY `idx_status` (`status`),
  FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Customer feedback and suggestions';

-- System Logs Table
CREATE TABLE IF NOT EXISTS `system_logs` (
  `log_id` int(11) NOT NULL AUTO_INCREMENT,
  `log_level` enum('info','warning','error') DEFAULT 'info',
  `message` text NOT NULL,
  `action` varchar(100) DEFAULT NULL COMMENT 'Action performed',
  `user_id` int(11) DEFAULT NULL COMMENT 'User who performed action',
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`log_id`),
  KEY `idx_level` (`log_level`),
  KEY `idx_user` (`user_id`),
  KEY `idx_created` (`created_at`),
  FOREIGN KEY (`user_id`) REFERENCES `customer` (`customer_id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='System activity and error logs';

-- Order Details Index (if not exists)
-- This improves performance for order item lookups
ALTER TABLE `orderdetails`
ADD INDEX IF NOT EXISTS `idx_order_id` (`order_id`),
ADD INDEX IF NOT EXISTS `idx_product_id` (`product_id`);

-- Sample Data (Optional - for testing)

-- Sample Support Ticket
INSERT INTO `support_tickets` (`customer_id`, `subject`, `description`, `priority`, `status`) VALUES
(2, 'Order Delivery Issue', 'My order has not arrived yet. It was supposed to be delivered yesterday.', 'high', 'open');

-- Sample Product Review
INSERT INTO `product_reviews` (`product_id`, `customer_id`, `rating`, `review_title`, `review_text`, `is_verified_purchase`) VALUES
(1, 2, 5, 'Great Product!', 'Excellent product! Fast delivery and great quality.', 1)
ON DUPLICATE KEY UPDATE rating=rating;

-- Sample Customer Feedback
INSERT INTO `customer_feedback` (`customer_id`, `type`, `subject`, `message`, `status`) VALUES
(2, 'suggestion', 'Mobile App Needed', 'It would be great to have a mobile app for easier ordering.', 'new');

-- Sample System Log
INSERT INTO `system_logs` (`log_level`, `message`, `action`, `user_id`, `ip_address`) VALUES
('info', 'System backup completed successfully', 'backup', 1, '127.0.0.1');

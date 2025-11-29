-- Product Reviews Table
-- Allows customers to rate and review products they've purchased

USE pharmavault_db;

CREATE TABLE IF NOT EXISTS `product_reviews` (
  `review_id` int NOT NULL AUTO_INCREMENT,
  `product_id` int NOT NULL,
  `customer_id` int NOT NULL,
  `rating` tinyint NOT NULL CHECK (rating >= 1 AND rating <= 5),
  `review_title` varchar(200) DEFAULT NULL,
  `review_text` text,
  `is_verified_purchase` tinyint(1) DEFAULT 0,
  `helpful_count` int DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`review_id`),
  UNIQUE KEY `unique_customer_product_review` (`product_id`, `customer_id`),
  KEY `idx_product_id` (`product_id`),
  KEY `idx_customer_id` (`customer_id`),
  KEY `idx_rating` (`rating`),
  CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`product_id`) ON DELETE CASCADE,
  CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`customer_id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert some sample reviews for testing (optional)
-- You can delete this section if you don't want sample data

-- Note: Make sure these product_id and customer_id values exist in your database
-- INSERT INTO `product_reviews` (`product_id`, `customer_id`, `rating`, `review_title`, `review_text`, `is_verified_purchase`) VALUES
-- (1, 1, 5, 'Excellent product!', 'This medication worked perfectly for my condition. Highly recommended!', 1),
-- (1, 2, 4, 'Good quality', 'Good product, fast delivery. Would buy again.', 1),
-- (2, 1, 5, 'Very effective', 'Exactly what I needed. Great price too.', 1);

-- Main database used by the Precious Nkomose reservation system.
CREATE DATABASE IF NOT EXISTS `preciousnkomose`
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE `preciousnkomose`;

-- Drop tables in dependency order so the import can be rerun during development.
DROP TABLE IF EXISTS `emails`;
DROP TABLE IF EXISTS `catering_requests`;
DROP TABLE IF EXISTS `reviews`;
DROP TABLE IF EXISTS `reservations`;
DROP TABLE IF EXISTS `restaurant_tables`;
DROP TABLE IF EXISTS `menu`;
DROP TABLE IF EXISTS `customers`;

-- Stores both customer and admin accounts. Admins are identified by is_admin = 1.
CREATE TABLE `customers` (
  `id` VARCHAR(32) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `email` VARCHAR(200) NOT NULL,
  `phone` VARCHAR(60) DEFAULT NULL,
  `password` VARCHAR(255) NOT NULL,
  `is_admin` TINYINT(1) NOT NULL DEFAULT 0,
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `verification_token` VARCHAR(255) DEFAULT NULL,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_customers_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- menu items displayed on the public menu page and admin menu page.
CREATE TABLE `menu` (
  `id` VARCHAR(32) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `description` TEXT,
  `category` VARCHAR(120) DEFAULT NULL,
  `price` DECIMAL(10,2) NOT NULL DEFAULT 0.00,
  `available` TINYINT(1) NOT NULL DEFAULT 1,
  `image` VARCHAR(255) DEFAULT NULL,
  `popular` TINYINT(1) NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- available seating sections/tables for reservations.
CREATE TABLE `restaurant_tables` (
  `id` VARCHAR(32) NOT NULL,
  `label` VARCHAR(200) NOT NULL,
  `capacity` INT NOT NULL,
  `description` TEXT,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- customer table reservations and supports booking history/cancellation.
CREATE TABLE `reservations` (
  `id` VARCHAR(32) NOT NULL,
  `customer_id` VARCHAR(32) NOT NULL,
  `customer_name` VARCHAR(200) NOT NULL,
  `customer_email` VARCHAR(200) NOT NULL,
  `preferred_section` VARCHAR(120) NOT NULL,
  `section_label` VARCHAR(200) NOT NULL,
  `capacity` INT NOT NULL,
  `date` DATE NOT NULL,
  `time` TIME NOT NULL,
  `guests` INT NOT NULL,
  `notes` TEXT,
  `status` VARCHAR(50) NOT NULL DEFAULT 'Confirmed',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_reservations_customer` (`customer_id`),
  KEY `idx_reservations_slot` (`preferred_section`,`date`,`time`),
  CONSTRAINT `fk_reservations_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- customer reviews submitted from the website.
CREATE TABLE `reviews` (
  `id` VARCHAR(32) NOT NULL,
  `customer_id` VARCHAR(32) NOT NULL,
  `name` VARCHAR(200) NOT NULL,
  `rating` INT NOT NULL,
  `comment` TEXT NOT NULL,
  `date` DATE NOT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_reviews_customer` (`customer_id`),
  CONSTRAINT `fk_reviews_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- catering quote requests submitted by logged-in customers.
CREATE TABLE `catering_requests` (
  `id` VARCHAR(32) NOT NULL,
  `customer_id` VARCHAR(32) NOT NULL,
  `customer_name` VARCHAR(200) NOT NULL,
  `customer_email` VARCHAR(200) NOT NULL,
  `event_type` VARCHAR(150) NOT NULL,
  `event_date` DATE NOT NULL,
  `guest_count` INT NOT NULL,
  `location` VARCHAR(255) NOT NULL,
  `notes` TEXT NOT NULL,
  `quote` TEXT NULL DEFAULT NULL,
  `quote_sent_at` DATETIME NULL DEFAULT NULL,
  `status` VARCHAR(60) NOT NULL DEFAULT 'Pending',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_catering_customer` (`customer_id`),
  CONSTRAINT `fk_catering_customer`
    FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Logs system email messages; the current project records them instead of sending real email.
CREATE TABLE `emails` (
  `id` VARCHAR(32) NOT NULL,
  `recipient` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `body` TEXT NOT NULL,
  `sent_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Default admin login: admin@localhost / admin123
INSERT INTO `customers` (`id`, `name`, `email`, `phone`, `password`, `is_admin`, `created_at`) VALUES
('U0000', 'Administrator', 'admin@localhost', '', '$2y$10$BjLvfiOsCPzvFGCxeGrfd.nav7g/fbVQS8RuwNR0RFKgRnJC.4Bb2', 1, NOW());

-- Initial restaurant seating sections used by the reservation form.
INSERT INTO `restaurant_tables` (`id`, `label`, `capacity`, `description`, `is_active`) VALUES
('corner-nw', 'Corner Bench North West', 6, 'Cozy padded bench seating with a private finish.', 1),
('corner-ne', 'Corner Bench North East', 6, 'Bright corner seating with a warm restaurant view.', 1),
('corner-sw', 'Corner Bench South West', 6, 'Curtained semi-private seating for groups of six.', 1),
('corner-se', 'Corner Bench South East', 6, 'Soft bench seating with a luxurious feel.', 1),
('center-left', 'Center Round Table A', 4, 'Intimate round table in the heart of the room.', 1),
('center-right', 'Center Round Table B', 4, 'Shared center table ideal for seafood feasts.', 1);

-- Complete menu with all dishes including popular items marked with popular=1
INSERT INTO `menu` (`id`, `name`, `description`, `category`, `price`, `available`, `image`, `popular`) VALUES
('M001', 'Kingklip Grilled Fillet', 'Fresh line-caught kingklip grilled to perfection with lemon butter', 'Seafood Mains', 285.00, 1, 'Fillet.jpg', 0),
('M002', 'West Coast Rock Lobster Tail', 'Succulent lobster tail served with garlic aioli and seasonal vegetables', 'Seafood Mains', 395.00, 1, 'lobster.jpg', 1),
('M003', 'Snoek Pan-Fried', 'Crispy snoek fillet served with SA lemon and herbs', 'Seafood Mains', 245.00, 1, 'snoek.jpg', 0),
('M004', 'Seafood Boil - King''s Feast', 'Lobster, crab, mussels, prawns, squid with crispy fries and coleslaw', 'Seafood Mains', 520.00, 1, 'seaFB-kingF.jpg', 1),
('M005', 'Bobotie Supreme', 'Traditional Cape Malay spiced mince with eggs and turmeric rice', 'SA Mains', 195.00, 1, 'bobotie.jpg', 0),
('M006', 'Sosaties with Mango Chutney', 'Grilled Cape kebabs with traditional mango chutney and rice', 'SA Mains', 225.00, 1, 'sosaties.jpg', 0),
('M007', 'Boerewors with Pap', 'Traditional SA sausage served with creamy pap and chakalaka', 'SA Mains', 185.00, 1, 'wors&pap.jpg', 0),
('M008', 'Potjiekos - Lamb Stew', 'Slow-cooked lamb with potatoes, carrots and traditional spices', 'SA Mains', 265.00, 1, 'stew.jpg', 0),
('M009', 'Biltong Salad', 'Mixed greens with sliced biltong, roasted vegetables and vinaigrette', 'Salads', 145.00, 1, 'biltong_salad.jpg', 0),
('M010', 'Shrimp & Mango Salad', 'Chilled prawns, fresh mango, avocado with citrus dressing', 'Salads', 165.00, 1, 'prawns.jpg', 0),
('M011', 'Grilled Prawns', 'Jumbo prawns grilled with chilli and garlic butter', 'Appetizers', 185.00, 1, 'prawns.jpg', 0),
('M012', 'Calamari Rings', 'Crispy fried squid rings with lemon and spicy dipping sauce', 'Appetizers', 135.00, 1, 'calamari.jpg', 0),
('M013', 'Milk Tart', 'Traditional South African milk tart with cinnamon', 'Desserts', 85.00, 1, 'milkTart.jpg', 0),
('M014', 'Koeksister', 'Traditional spiced dough fried and coated with golden syrup', 'Desserts', 65.00, 1, 'koeksister.jpg', 0),
('M015', 'Malva Pudding', 'Spiced sponge pudding served with hot caramel sauce and vanilla ice cream', 'Desserts', 95.00, 1, 'malvaPudding.jpg', 0),
('M016', 'House Wine Selection', 'Premium South African wines', 'Beverages', 45.00, 1, 'homemadeWine.jpg', 0),
('M017', 'Craft Beer Flight', 'Tasting selection of local craft beers', 'Beverages', 75.00, 1, 'craftBeer.jpg', 0),
('M018', 'Chocolate flavoured smoothie', 'Rich chocolate smoothie with a hint of espresso', 'Beverages', 47.00, 1, 'chocoSmoothie.jpg', 0),
('M019', 'Strawberry flavoured smoothie', 'Fresh strawberry smoothie with a hint of vanilla', 'Beverages', 65.00, 1, 'strawberrySmoothie.jpg', 0),
('M020', 'Vanilla ice cream flavoured smoothie', 'Creamy vanilla ice cream smoothie with a hint of cinnamon or sprinkles', 'Beverages', 55.00, 1, 'vanillaSmoothie.jpg', 0),
('M021', 'Bunny Chow', 'Hollowed-out bread loaf filled with spiced curry - iconic SA street food', 'SA Classics', 125.00, 1, 'BunnyChow.jpg', 0),
('M022', 'Curry & Rice', 'Aromatic South African curry with fragrant rice and sambals', 'SA Classics', 155.00, 1, 'Curry and rice.jpg', 0),
('M023', 'Gatsby Sandwich', 'Oversized bread stuffed with meat, chips, and cheese sauce', 'SA Classics', 95.00, 1, 'gatsby.jpg', 0),
('M024', 'Seven Colours', 'Rainbow-layered South African sweet treat', 'Desserts', 75.00, 1, 'seven_colours.jpg', 0),
('M025', 'Grilled Steak', 'Premium cut grilled to perfection with seasonal vegetables and jus', 'SA Mains', 345.00, 1, 'steak.jpg', 1),
('M026', 'Steamed Bread & Beans', 'Traditional humble SA comfort meal', 'SA Classics', 85.00, 1, 'bread_beans.jpg', 0),
('M027', 'Seafood Boil Share', 'Large format seafood feast designed for sharing', 'Seafood Mains', 650.00, 1, 'seafood_share.jpg', 0);

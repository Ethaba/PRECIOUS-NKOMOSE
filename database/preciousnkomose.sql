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

-- Starter menu data moved from the old JSON file into SQL.
INSERT INTO `menu` (`id`, `name`, `description`, `category`, `price`, `available`, `image`) VALUES
('M001', 'Kingklip Grilled Fillet', 'Fresh line-caught kingklip grilled to perfection with lemon butter', 'Seafood Mains', 285.00, 1, 'kingklip.jpg'),
('M002', 'West Coast Rock Lobster Tail', 'Succulent lobster tail served with garlic aioli and seasonal vegetables', 'Seafood Mains', 395.00, 1, 'lobster.jpg'),
('M003', 'Snoek Pan-Fried', 'Crispy snoek fillet served with SA lemon and herbs', 'Seafood Mains', 245.00, 1, 'snoek.jpg'),
('M004', 'Seafood Boil - King''s Feast', 'Lobster, crab, mussels, prawns, squid with crispy fries and coleslaw', 'Seafood Mains', 520.00, 1, 'seafood_boil.jpg'),
('M005', 'Bobotie Supreme', 'Traditional Cape Malay spiced mince with eggs and turmeric rice', 'SA Mains', 195.00, 1, 'bobotie.jpg'),
('M006', 'Sosaties with Mango Chutney', 'Grilled Cape kebabs with traditional mango chutney and rice', 'SA Mains', 225.00, 1, 'sosaties.jpg'),
('M007', 'Boerewors with Pap', 'Traditional SA sausage served with creamy pap and chakalaka', 'SA Mains', 185.00, 1, 'boerewors.jpg'),
('M008', 'Potjiekos - Lamb Stew', 'Slow-cooked lamb with potatoes, carrots and traditional spices', 'SA Mains', 265.00, 1, 'potjiekos.jpg'),
('M009', 'Biltong Salad', 'Mixed greens with sliced biltong, roasted vegetables and vinaigrette', 'Salads', 145.00, 1, 'biltong_salad.jpg'),
('M010', 'Shrimp & Mango Salad', 'Chilled prawns, fresh mango, avocado with citrus dressing', 'Salads', 165.00, 1, 'shrimp_salad.jpg'),
('M011', 'Garlic Butter Mussels', 'Fresh mussels steamed in white wine, garlic and herbs', 'Appetizers', 155.00, 1, 'mussels.jpg'),
('M012', 'Grilled Prawns', 'Jumbo prawns grilled with chilli and garlic butter', 'Appetizers', 185.00, 1, 'prawns.jpg'),
('M013', 'Calamari Rings', 'Crispy fried squid rings with lemon and spicy dipping sauce', 'Appetizers', 135.00, 1, 'calamari.jpg'),
('M014', 'Milk Tart', 'Traditional South African milk tart with cinnamon', 'Desserts', 85.00, 1, 'melktert.jpg'),
('M015', 'Koeksister', 'Traditional spiced dough fried and coated with golden syrup', 'Desserts', 65.00, 1, 'koeksister.jpg'),
('M016', 'Malva Pudding', 'Spiced sponge pudding served with hot caramel sauce and vanilla ice cream', 'Desserts', 95.00, 1, 'malva.jpg'),
('M017', 'House Wine Selection', 'Premium South African wines', 'Beverages', 45.00, 1, 'wine.jpg'),
('M018', 'Craft Beer', 'Selection of local craft beers', 'Beverages', 55.00, 1, 'beer.jpg');

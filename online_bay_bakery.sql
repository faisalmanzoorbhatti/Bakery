/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.20-13.0.2-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: online_bay_bakery
-- ------------------------------------------------------
-- Server version	13.0.2-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*M!100616 SET @OLD_NOTE_VERBOSITY=@@NOTE_VERBOSITY, NOTE_VERBOSITY=0 */;

--
-- Table structure for table `cart`
--

DROP TABLE IF EXISTS `cart`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `cart` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`),
  CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cart`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `cart` WRITE;
/*!40000 ALTER TABLE `cart` DISABLE KEYS */;
/*!40000 ALTER TABLE `cart` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `categories`
--

DROP TABLE IF EXISTS `categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `categories`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `categories` WRITE;
/*!40000 ALTER TABLE `categories` DISABLE KEYS */;
INSERT INTO `categories` VALUES
(1,'Artisanal Cakes & Cheesecakes','https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80'),
(2,'Fresh Sourdough & Crusty Breads','https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80'),
(3,'Viennoiserie & French Pastries','https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=800&q=80'),
(4,'Gourmet Cookies & Brownies','https://images.unsplash.com/photo-1499636136210-6f4ee915583e?auto=format&fit=crop&w=800&q=80'),
(5,'Savory Bites & Artisan Quiches','https://images.unsplash.com/photo-1621996346565-e3d5d6281691?auto=format&fit=crop&w=800&q=80'),
(6,'Handcrafted Beverages & Roastery','https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80');
/*!40000 ALTER TABLE `categories` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `customers`
--

DROP TABLE IF EXISTS `customers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `customers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `reset_token` varchar(255) DEFAULT NULL,
  `role` varchar(20) NOT NULL DEFAULT 'customer',
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `customers`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `customers` WRITE;
/*!40000 ALTER TABLE `customers` DISABLE KEYS */;
INSERT INTO `customers` VALUES
(1,'Demo Customer','customer@example.com','$2y$10$PBnB5UaKG9hWOUpuPikPl.1gC.ttAvubwuuYLgDHJ9pyVcNE9dPqi','03001234567',NULL,'customer'),
(2,'Admin User','admin@example.com','$2y$10$PBnB5UaKG9hWOUpuPikPl.1gC.ttAvubwuuYLgDHJ9pyVcNE9dPqi','03007654321',NULL,'admin');
/*!40000 ALTER TABLE `customers` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `order_items`
--

DROP TABLE IF EXISTS `order_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `order_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `order_id` int(11) DEFAULT NULL,
  `product_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `order_id` (`order_id`),
  KEY `product_id` (`product_id`),
  CONSTRAINT `order_items_ibfk_1` FOREIGN KEY (`order_id`) REFERENCES `orders` (`id`),
  CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `order_items`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `order_items` WRITE;
/*!40000 ALTER TABLE `order_items` DISABLE KEYS */;
INSERT INTO `order_items` VALUES
(1,1,1,1,28.50),
(2,1,30,1,5.00),
(3,1,13,1,3.80),
(4,2,2,1,32.00),
(5,2,22,1,14.00),
(6,2,31,2,5.25),
(7,3,7,1,6.50),
(8,3,30,2,5.00);
/*!40000 ALTER TABLE `order_items` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `orders`
--

DROP TABLE IF EXISTS `orders`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `orders` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `customer_id` int(11) DEFAULT NULL,
  `total_amount` decimal(10,2) DEFAULT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` varchar(50) DEFAULT NULL,
  `order_date` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `customer_id` (`customer_id`),
  CONSTRAINT `orders_ibfk_1` FOREIGN KEY (`customer_id`) REFERENCES `customers` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `orders`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `orders` WRITE;
/*!40000 ALTER TABLE `orders` DISABLE KEYS */;
INSERT INTO `orders` VALUES
(1,1,38.00,'JazzCash','Preparing','2024-12-30 06:15:00'),
(2,1,56.50,'EasyPaisa','Paid','2024-12-31 04:30:00'),
(3,1,16.50,'Cash on Delivery','Out for Delivery','2024-12-31 11:45:00');
/*!40000 ALTER TABLE `orders` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;

--
-- Table structure for table `products`
--

DROP TABLE IF EXISTS `products`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) DEFAULT NULL,
  `name` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `image_path` varchar(500) DEFAULT NULL,
  `badge` varchar(50) DEFAULT NULL,
  `is_featured` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `products_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=35 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `products`
--

SET @OLD_AUTOCOMMIT=@@AUTOCOMMIT, @@AUTOCOMMIT=0;
LOCK TABLES `products` WRITE;
/*!40000 ALTER TABLE `products` DISABLE KEYS */;
INSERT INTO `products` VALUES
(1,1,'Belgian Dark Chocolate Ganache Cake','Triple-layer rich Dutch cocoa sponge smothered in 70% dark Belgian couverture ganache with chocolate curls.',28.50,'','https://images.unsplash.com/photo-1578985545062-69928b1d9587?auto=format&fit=crop&w=800&q=80','⭐ Best Seller',1),
(2,1,'Signature Lotus Biscoff Baked Cheesecake','Velvety slow-baked cream cheese on a crunchy caramelized Biscoff crust, crowned with melted cookie butter drip.',32.00,'','https://images.unsplash.com/photo-1533134242443-d4fd215305ad?auto=format&fit=crop&w=800&q=80','🔥 Trending',1),
(3,1,'Red Velvet Royale with Vanilla Frosting','Velvety crimson buttermilk sponge balanced with tangy Madagascar vanilla bean cream cheese frosting.',26.00,'','https://images.unsplash.com/photo-1586788680434-30d324b2d46f?auto=format&fit=crop&w=800&q=80','❤️ Chef Special',1),
(4,1,'New York Style Baked Berry Cheesecake','Classic dense, silky New York cheesecake infused with real citrus zest and drizzled with wild berry compote.',29.50,'','https://images.unsplash.com/photo-1524351199678-941a58a3df50?auto=format&fit=crop&w=800&q=80','Classic',0),
(5,1,'Salted Caramel & Roasted Pecan Crunch Cake','Soft brown butter sponge layered with homemade fleur-de-sel salted caramel and toasted Georgia pecans.',30.00,'','https://images.unsplash.com/photo-1565958011703-44f9829ba187?auto=format&fit=crop&w=800&q=80','Gourmet',0),
(6,1,'Classic Italian Tiramisu Gateau','Espresso and Marsala soaked Savoiardi biscuits layered with whipped mascarpone and dark Valrhona dusting.',27.00,'','https://images.unsplash.com/photo-1571115177098-24ec42ed204d?auto=format&fit=crop&w=800&q=80','Italian Recipe',0),
(7,2,'San Francisco Artisan Sourdough Boule','Naturally fermented 48-hour slow proof with a blistered crunchy crust and open, chewy interior crumb.',6.50,'','https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80','🌾 48-Hr Ferment',1),
(8,2,'Traditional French Parisian Baguette','Crisp golden exterior, airy honeycomb crumb, baked stone-deck fresh three times each morning.',4.00,'','https://images.unsplash.com/photo-1589367920969-ab8e050bbb04?auto=format&fit=crop&w=800&q=80','🥖 Oven Fresh',1),
(9,2,'Rosemary & Sea Salt Olive Oil Focaccia','Generously dimpled Italian dough infused with extra virgin olive oil, fresh aromatic rosemary, and Maldon salt flakes.',5.50,'','https://images.unsplash.com/photo-1608198093002-ad4e005484ec?auto=format&fit=crop&w=800&q=80','Extra Virgin Olive',0),
(10,2,'Golden Normandy Brioche Toasting Loaf','Feather-light enriched golden bread crafted with pure French cultured butter and free-range farm eggs.',7.00,'','https://images.unsplash.com/photo-1549931319-a545dcf3bc73?auto=format&fit=crop&w=800&q=80','French Butter',0),
(11,2,'Artisan Ancient Grains & Seeded Loaf','Wholesome loaf packed with toasted pumpkin seeds, organic flax, sesame, and whole rolled heritage oats.',6.80,'','https://images.unsplash.com/photo-1586444248902-2f64eddc13df?auto=format&fit=crop&w=800&q=80','🌱 Healthy Choice',0),
(12,2,'Garlic Herb & Aged Cheddar Pull-Apart','Warm pull-apart loaf drenched in slow-roasted garlic butter, fresh parsley, and melted sharp cheddar cheese.',6.20,'','https://images.unsplash.com/photo-1573140247632-f8fd74997d5c?auto=format&fit=crop&w=800&q=80','Warm & Cheesy',0),
(13,3,'Pure Butter Croissant (Pain au Beurre)','27 golden flaky layers made exclusively with imported French Normandy cultured butter. Crisp and airy.',3.80,'','https://images.unsplash.com/photo-1555507036-ab1f4038808a?auto=format&fit=crop&w=800&q=80','⭐ Must Try',1),
(14,3,'Pain au Chocolat (Chocolate Croissant)','Laminated buttery viennoiserie pastry folded around two batons of 64% semi-sweet dark chocolate.',4.50,'','https://images.unsplash.com/photo-1623334044303-2510867551c9?auto=format&fit=crop&w=800&q=80','French Classic',1),
(15,3,'Roasted Almond Frangipane Croissant','Twice-baked croissant filled with rich almond cream frangipane and capped with toasted sliced almonds.',5.20,'','https://images.unsplash.com/photo-1608198093002-ad4e005484ec?auto=format&fit=crop&w=800&q=80','Nutty & Crisp',0),
(16,3,'Bronte Pistachio Custard Danish','Crispy puff pastry ring filled with smooth Sicilian pistachio cream and sprinkled with crushed roasted pistachios.',5.50,'','https://images.unsplash.com/photo-1509365465985-25d11c17e812?auto=format&fit=crop&w=800&q=80','Premium',1),
(17,3,'Cinnamon Swirl Brioche Roll with Glaze','Tender brioche rolled with Ceylon cinnamon and brown sugar, topped with rich cream cheese drizzle.',4.20,'','https://images.unsplash.com/photo-1509440159596-0249088772ff?auto=format&fit=crop&w=800&q=80','Warm Cinnamon',0),
(18,3,'Fresh Berry Vanilla Custard Tartlet','Crisp butter shortcrust filled with real Tahitian vanilla bean pastry cream, fresh strawberries, and berries.',5.80,'','https://images.unsplash.com/photo-1519915028121-7d3463d20b13?auto=format&fit=crop&w=800&q=80','Seasonal Fruit',0),
(19,4,'Valrhona Double Dark Chocolate Sea Salt Cookie','Thick, chewy center packed with melted Valrhona dark chocolate puddles and sprinkled with sea salt.',3.50,'','https://images.unsplash.com/photo-1499636136210-6f4ee915583e?auto=format&fit=crop&w=800&q=80','⭐ Top Rated',1),
(20,4,'Red Velvet White Choc Chunk Cookie','Rich ruby cocoa cookie studded with jumbo chunks of Belgian white chocolate, soft-baked to perfection.',3.80,'','https://images.unsplash.com/photo-1558961363-fa8fdf82db35?auto=format&fit=crop&w=800&q=80','Soft Baked',0),
(21,4,'Fudge Walnut Brownie Slab','Ultra-fudgy, dense Belgian chocolate brownie loaded with toasted California walnut halves and crackly top crust.',4.50,'','https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=800&q=80','Pure Fudge',1),
(22,4,'Parisian Macaron Luxe Gift Box (6 Pcs)','Assortment of handmade French macarons: Pistachio, Madagascar Vanilla, Salted Toffee, Dark Truffle, Raspberry, Lemon.',14.00,'','https://images.unsplash.com/photo-1569864358642-9d1684040f43?auto=format&fit=crop&w=800&q=80','🎁 Gift Box',1),
(23,4,'Salted Caramel Pecan Butter Blondie','Browned butter batter baked with rich butterscotch morsels, toasted pecans, and sea salt drizzle.',4.20,'','https://images.unsplash.com/photo-1514849302-984523450ce4?auto=format&fit=crop&w=800&q=80','Butterscotch',0),
(24,4,'Nutella Lava Stuffed Giant Cookie','Warm jumbo golden cookie dough packed with a decadent molten hazelnut cocoa cream center.',4.00,'','https://images.unsplash.com/photo-1590080875515-8a3a8dc5735e?auto=format&fit=crop&w=800&q=80','Molten Lava',1),
(25,5,'Smoked Chicken & Wild Mushroom Quiche','Golden butter shortcrust holding a creamy egg custard filled with oak-smoked chicken and sautéed wild mushrooms.',7.50,'','https://images.unsplash.com/photo-1621996346565-e3d5d6281691?auto=format&fit=crop&w=800&q=80','Savory Special',1),
(26,5,'Spinach, Sun-Dried Tomato & Feta Danish','Multi-layered puff pastry filled with tender baby spinach, tangy Mediterranean feta cheese, and dried tomatoes.',5.20,'','https://images.unsplash.com/photo-1621236378699-8597faf6a173?auto=format&fit=crop&w=800&q=80','Vegetarian',0),
(27,5,'Woodfired Smoked Beef Pastrami Calzone','Crisp sourdough calzone stuffed with sliced beef pastrami, aged mozzarella, oregano, and marinara glaze.',6.50,'','https://images.unsplash.com/photo-1513104890138-7c749659a591?auto=format&fit=crop&w=800&q=80','Woodfired',0),
(28,5,'Crispy Golden Chicken Patties (2 Pcs)','Traditional bakery puff pastry triangles baked to deep golden flakes with a mildly spiced chicken filling.',4.80,'','https://images.unsplash.com/photo-1541832676-9b763b0239ab?auto=format&fit=crop&w=800&q=80','Bakery Classic',1),
(29,5,'Tandoori Paneer & Roasted Pepper Puff','Crispy laminated pastry turnover stuffed with marinated spiced cottage cheese and charred bell peppers.',5.00,'','https://images.unsplash.com/photo-1565557623262-b51c2513a641?auto=format&fit=crop&w=800&q=80','Spicy & Crispy',0),
(30,6,'Madagascar Vanilla Bean Velvet Latte','Double shot of single-origin espresso blended with micro-foamed milk and infused with organic vanilla bean.',5.00,'','https://images.unsplash.com/photo-1514432324607-a09d9b4aefdd?auto=format&fit=crop&w=800&q=80','Specialty Brew',1),
(31,6,'Iced Salted Toffee Caramel Macchiato','Cold layered milk, rich caramel syrup, freshly pulled espresso shots, and crystalline toffee crunch.',5.50,'','https://images.unsplash.com/photo-1517701550927-30cf4ba1dba5?auto=format&fit=crop&w=800&q=80','Cold Coffee',1),
(32,6,'Belgian Dark Hot Cocoa with Marshmallows','Real melted 60% Belgian dark chocolate simmered with fresh whole milk and crowned with fluffy marshmallows.',5.20,'','https://images.unsplash.com/photo-1542990253-0d0f5be5f0ed?auto=format&fit=crop&w=800&q=80','Decadent Warm',0),
(33,6,'Ceremonial Japanese Uji Matcha Latte','First-harvest stone ground Japanese matcha green tea whisked smooth with creamy oat milk.',5.80,'','https://images.unsplash.com/photo-1536256263959-770b48d82b0a?auto=format&fit=crop&w=800&q=80','Antioxidant',0),
(34,6,'Cold Pressed Citrus & Garden Mint Sparkler','Refreshing blend of freshly pressed Sicilian oranges, lemons, sparkling mineral water, and bruised garden mint.',4.50,'','https://images.unsplash.com/photo-1513558161293-cdaf765ed2fd?auto=format&fit=crop&w=800&q=80','Refreshing',0);
/*!40000 ALTER TABLE `products` ENABLE KEYS */;
UNLOCK TABLES;
COMMIT;
SET AUTOCOMMIT=@OLD_AUTOCOMMIT;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*M!100616 SET NOTE_VERBOSITY=@OLD_NOTE_VERBOSITY */;

-- Dump completed on 2026-10-28  9:08:19

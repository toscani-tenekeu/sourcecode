-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Hôte : 127.0.0.1:3306
-- Généré le : jeu. 24 avr. 2025 à 16:30
-- Version du serveur : 5.7.40
-- Version de PHP : 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données : `sourcecode_db`
--

-- --------------------------------------------------------

--
-- Structure de la table `categories`
--

DROP TABLE IF EXISTS `categories`;
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `categories`
--

INSERT INTO `categories` (`id`, `name`, `slug`, `description`, `created_at`) VALUES
(1, 'Complete Projects', 'complete-projects', 'Full-stack projects with frontend and backend', '2025-04-24 15:29:03'),
(2, 'Templates', 'templates', 'Frontend templates for various technologies', '2025-04-24 15:29:03');

-- --------------------------------------------------------

--
-- Structure de la table `pending_transactions`
--

DROP TABLE IF EXISTS `pending_transactions`;
CREATE TABLE IF NOT EXISTS `pending_transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `external_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_link` varchar(255) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `pending_transactions`
--

INSERT INTO `pending_transactions` (`id`, `user_id`, `product_id`, `transaction_id`, `external_id`, `amount`, `payment_link`, `created_at`) VALUES
(1, 1, 7, 'durUaXkj', '0424162838281', '8000.00', 'https://checkout.fapshi.com/payment/680a66bcba118fd1cb4294db', '2025-04-24 16:28:43');

-- --------------------------------------------------------

--
-- Structure de la table `products`
--

DROP TABLE IF EXISTS `products`;
CREATE TABLE IF NOT EXISTS `products` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `description` text,
  `price` decimal(10,2) NOT NULL,
  `preview_url` varchar(255) DEFAULT NULL,
  `image` varchar(255) DEFAULT NULL,
  `tech_stack` varchar(255) DEFAULT NULL,
  `requirements` text,
  `file_path` varchar(255) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `slug` (`slug`),
  KEY `category_id` (`category_id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `products`
--

INSERT INTO `products` (`id`, `category_id`, `name`, `slug`, `description`, `price`, `preview_url`, `image`, `tech_stack`, `requirements`, `file_path`, `created_at`) VALUES
(1, 1, 'E-commerce Platform', 'ecommerce-platform', 'Complete e-commerce platform with admin dashboard, product management, and payment integration', '25000.00', 'https://preview.toscanisoft.site/ecommerce', '/assets/images/products/ecommerce.jpg', 'PHP, MySQL, Tailwind CSS, JavaScript', 'PHP 8.0+, MySQL 5.7+, Apache/Nginx server', '/downloads/ecommerce-platform.zip', '2025-04-24 15:29:03'),
(2, 1, 'Blog CMS', 'blog-cms', 'Content Management System for blogs with user management, content editing, and SEO features', '15000.00', 'https://preview.toscanisoft.site/blog-cms', '/assets/images/products/blog-cms.jpg', 'PHP, MySQL, Tailwind CSS, CKEditor', 'PHP 8.0+, MySQL 5.7+, Apache/Nginx server', '/downloads/blog-cms.zip', '2025-04-24 15:29:03'),
(3, 1, 'School Management System', 'school-management', 'Complete school management system with student, teacher, and course management', '35000.00', 'https://preview.toscanisoft.site/school', '/assets/images/products/school.jpg', 'PHP, MySQL, Tailwind CSS, Chart.js', 'PHP 8.0+, MySQL 5.7+, Apache/Nginx server', '/downloads/school-management.zip', '2025-04-24 15:29:03'),
(4, 1, 'Real Estate Platform', 'real-estate', 'Property listing and management platform with agent profiles and property search', '28000.00', 'https://preview.toscanisoft.site/real-estate', '/assets/images/products/real-estate.jpg', 'PHP, MySQL, Tailwind CSS, Leaflet.js', 'PHP 8.0+, MySQL 5.7+, Apache/Nginx server', '/downloads/real-estate.zip', '2025-04-24 15:29:03'),
(5, 1, 'Inventory Management', 'inventory-management', 'Stock and inventory management system for small to medium businesses', '22000.00', 'https://preview.toscanisoft.site/inventory', '/assets/images/products/inventory.jpg', 'PHP, MySQL, Tailwind CSS, Alpine.js', 'PHP 8.0+, MySQL 5.7+, Apache/Nginx server', '/downloads/inventory.zip', '2025-04-24 15:29:03'),
(6, 2, 'Admin Dashboard Template', 'admin-dashboard', 'Modern admin dashboard template with dark mode and responsive design', '12000.00', 'https://preview.toscanisoft.site/admin-template', '/assets/images/products/admin-dashboard.jpg', 'HTML, Tailwind CSS, JavaScript', 'Any web server', '/downloads/admin-dashboard.zip', '2025-04-24 15:29:03'),
(7, 2, 'Portfolio Template', 'portfolio-template', 'Clean and professional portfolio template for developers and creatives', '8000.00', 'https://preview.toscanisoft.site/portfolio', '/assets/images/products/portfolio.jpg', 'HTML, Tailwind CSS, JavaScript', 'Any web server', '/downloads/portfolio.zip', '2025-04-24 15:29:03'),
(8, 2, 'Landing Page Template', 'landing-page', 'High-converting landing page template for products and services', '10000.00', 'https://preview.toscanisoft.site/landing', '/assets/images/products/landing.jpg', 'HTML, Tailwind CSS, JavaScript', 'Any web server', '/downloads/landing-page.zip', '2025-04-24 15:29:03'),
(9, 2, 'E-commerce Frontend', 'ecommerce-frontend', 'E-commerce frontend template with product listing, cart, and checkout pages', '15000.00', 'https://preview.toscanisoft.site/ecommerce-frontend', '/assets/images/products/ecommerce-frontend.jpg', 'HTML, Tailwind CSS, Alpine.js', 'Any web server', '/downloads/ecommerce-frontend.zip', '2025-04-24 15:29:03'),
(10, 2, 'Blog Theme', 'blog-theme', 'Elegant blog theme with multiple post layouts and category pages', '9000.00', 'https://preview.toscanisoft.site/blog-theme', '/assets/images/products/blog-theme.jpg', 'HTML, Tailwind CSS, JavaScript', 'Any web server', '/downloads/blog-theme.zip', '2025-04-24 15:29:03');

-- --------------------------------------------------------

--
-- Structure de la table `transactions`
--

DROP TABLE IF EXISTS `transactions`;
CREATE TABLE IF NOT EXISTS `transactions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `transaction_id` varchar(100) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `status` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `external_id` varchar(100) DEFAULT '',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `product_id` (`product_id`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=MyISAM AUTO_INCREMENT=2 DEFAULT CHARSET=latin1;

--
-- Déchargement des données de la table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `created_at`) VALUES
(1, 'root', 'root@gmail.com', '$2y$10$4.usceO/kx5atDnX8ja1cOwldR5hEc0M/o.qob08jLlo378OJJNTW', 'root', '+237650500018', '2025-04-24 16:10:03');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

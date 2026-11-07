-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Apr 26, 2026 at 12:54 PM
-- Server version: 9.1.0
-- PHP Version: 8.3.14

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `concert_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `concerts`
--

DROP TABLE IF EXISTS `concerts`;
CREATE TABLE IF NOT EXISTS `concerts` (
  `id` bigint UNSIGNED NOT NULL AUTO_INCREMENT,
  `title` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `artist` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `venue` varchar(191) COLLATE utf8mb4_unicode_ci NOT NULL,
  `date` date NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `image_path` varchar(191) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `seating_areas` json DEFAULT NULL,
  `ticket_price` decimal(10,2) NOT NULL,
  `total_ticket` int NOT NULL,
  `created_by` bigint UNSIGNED NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `concerts_created_by_foreign` (`created_by`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `concerts`
--

INSERT INTO `concerts` (`id`, `title`, `artist`, `venue`, `date`, `description`, `image_path`, `seating_areas`, `ticket_price`, `total_ticket`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Taylor Swift: The Eras Tour', 'Taylor Swift', 'Madison Square Garden', '2026-05-09', 'Experience the ultimate concert event of the year! Taylor Swift\'s The Eras Tour brings together all the most iconic moments from her legendary career. Featuring hits from all her albums, stunning visuals, and unforgettable performances. This is a must-see event for all Swifties!', 'concerts/taylor-swift-eras-tour.png', NULL, 150.00, 20000, 1, '2026-04-24 06:15:41', '2026-04-24 06:15:41'),
(2, 'The Weeknd: After Hours Tour', 'The Weeknd', 'Crypto.com Arena', '2026-05-19', 'Join The Weeknd on an extraordinary journey through his darkest dreams and wildest fantasies. The After Hours Tour features incredible visual effects, stunning choreography, and all your favorite hits. Limited availability - book your tickets now!', 'concerts/the-weeknd-after-hours-tour.png', NULL, 120.00, 18000, 1, '2026-04-24 06:15:41', '2026-04-24 06:15:41'),
(3, 'Beyoncé: Renaissance Tour', 'Beyoncé', 'MetLife Stadium', '2026-05-24', 'Experience a visual and musical masterpiece as Beyoncé brings the Renaissance to life. This groundbreaking tour celebrates the album that dominated the world. Prepare to be amazed by the production, the sound, and the presence of the most incredible performer alive.', 'concerts/beyonce-renaissance-tour.png', NULL, 200.00, 82500, 1, '2026-04-24 06:15:41', '2026-04-24 06:15:41'),
(4, 'Coldplay: Music of the Spheres Tour', 'Coldplay', 'SoFi Stadium', '2026-05-14', 'Coldplay returns with their most ambitious tour ever! The Music of the Spheres Tour is a celebration of their incredible catalog, combined with cutting-edge production and breathtaking visuals. Join thousands of fans for an unforgettable night of music.', 'concerts/coldplay-music-of-the-spheres-tour.png', NULL, 110.00, 70000, 1, '2026-04-24 06:15:41', '2026-04-24 06:15:41'),
(5, 'Drake: OVO Sound Fest', 'Drake', 'Scotiabank Arena', '2026-05-29', 'The largest hip-hop festival in Canada! Drake brings together the hottest artists in the industry for an incredible day of music, entertainment, and pure energy. From Toronto, to the world!', 'concerts/drake-ovo-sound-fest.png', NULL, 95.00, 20000, 1, '2026-04-24 06:15:41', '2026-04-24 06:15:41'),
(6, 'Ed Sheeran: + − × ÷ Tour', 'Ed Sheeran', 'Wembley Stadium', '2026-06-03', 'Ed Sheeran brings his massive mathematical tour to legendary Wembley Stadium. Experience all the biggest hits from his career, featuring elaborate stage design and intimate storytelling moments. One of the most anticipated tours of the year!', 'concerts/ed-sheeran-tour.png', NULL, 130.00, 65000, 1, '2026-04-24 06:15:41', '2026-04-24 06:15:41'),
(7, 'Billie Eilish: Happier Than Ever Tour', 'Billie Eilish', 'Staples Center', '2026-05-16', 'The dark pop sensation takes over! Billie Eilish performs in an immersive experience unlike anything you\'ve ever seen. Watch as she commands the stage with raw energy and authenticity. A generational talent at her finest!', 'concerts/billie-eilish-happier-than-ever-tour.png', NULL, 85.00, 20000, 1, '2026-04-24 06:15:41', '2026-04-24 06:15:41'),
(8, 'Ariana Grande: Eternal Sunshine Tour', 'Ariana Grande', 'T-Mobile Arena', '2026-05-22', 'Ariana Grande returns with her most spectacular tour yet! The Eternal Sunshine Tour features amazing production, incredible vocal performances, and all the greatest hits. Don\'t miss this magical experience!', 'concerts/ariana-grande-eternal-sunshine-tour.png', NULL, 140.00, 20000, 1, '2026-04-24 06:15:41', '2026-04-24 06:15:41'),
(11, 'ddddddd', 'ddd', 'ddd', '2026-04-27', '555', 'concerts/euFgh8bbLXp9sS2qrc1RaChw1X1injbtzAPBiHdY.jpg', '[\"55\"]', 55.00, 55, 25, '2026-04-25 01:00:58', '2026-04-25 11:47:58'),
(12, 'junhao', 'junhao', '1111', '2026-04-27', 'dadadadas', 'concerts/kLWcG46ApUTG4MROOIRQXZgOWXQb9MSkJaWpNoT6.jpg', '[\"sadawdaw\"]', 1000.00, 100, 25, '2026-04-25 07:25:35', '2026-04-25 07:25:35'),
(13, 'gdgd', 'sffsfs', 'fsfsfs', '2026-04-28', '111111111', 'concerts/yddbfO48mt6swJuyvinhvWeoXQUf3wCrgMAEduHF.jpg', '[\"1111\"]', 11111.00, 111111, 25, '2026-04-25 11:47:42', '2026-04-25 11:47:42'),
(14, 'junhao', 'dadasad', 'sadwa', '2026-04-29', 'dadsadw', 'concerts/T2cS0IFEqp7o066ybn1fv1I50gRUw0NwfM9MpUol.jpg', '[\"sadwasa\"]', 11111111.00, 9996, 25, '2026-04-25 22:54:42', '2026-04-25 22:55:48');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

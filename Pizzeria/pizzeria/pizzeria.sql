-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Creato il: Ott 24, 2026 alle 15:48
-- Versione del server: 10.4.32-MariaDB
-- Versione PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `pizzeria`
--

-- --------------------------------------------------------

--
-- Struttura della tabella `ingredients`
--

CREATE TABLE `ingredients` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `ingredients`
--

INSERT INTO `ingredients` (`id`, `user_id`, `name`, `price`) VALUES
(1, 1, 'Pomodoro', 0.00),
(2, 1, 'Mozzarella', 0.00),
(3, 1, 'Wurstel', 1.00),
(4, 1, 'Funghi Champignon', 0.50),
(5, 1, 'Prosciutto Cotto', 1.00),
(6, 1, 'Salame Piccante', 1.00),
(7, 1, 'Olive Nere', 0.50),
(8, 1, 'Acciughe', 1.00),
(9, 1, 'Capperi', 0.50),
(10, 1, 'Carciofi', 1.00),
(11, 1, 'Gorgonzola (Zola)', 1.00),
(12, 1, 'Salsiccia', 1.00),
(13, 1, 'Pancetta Affumicata', 1.00),
(14, 1, 'Uovo', 0.50),
(15, 1, 'Pecorino Romano', 1.00),
(16, 1, 'Patatine Fritte', 0.50),
(17, 1, 'Cipolla', 0.50),
(18, 1, 'Melanzane Grigliate', 0.50),
(19, 1, 'Zucchine Grigliate', 0.50),
(20, 1, 'Rucola', 0.50),
(21, 1, 'Grana Padano', 0.50),
(22, 1, 'Bufala (dopo cottura)', 1.50),
(23, 1, 'Basilico Fresco', 0.00),
(24, 1, 'Olio EVO', 0.00),
(25, 1, 'Cipolle', 0.50),
(26, 1, 'Zola', 1.00),
(27, 1, 'Zucchine', 0.50),
(28, 1, 'Grana', 0.50),
(29, 1, 'Pancetta', 1.00),
(30, 1, 'Patatine', 0.50),
(31, 1, 'Bresaola', 1.50),
(32, 1, 'Porcini', 1.50),
(33, 1, 'Mozzarella di bufala', 1.50),
(34, 1, 'Prosciutto', 1.00),
(35, 1, 'Funghi', 0.50),
(36, 1, 'Basilico', 0.00),
(37, 1, 'Olive', 0.50),
(38, 1, 'Pomodorini freschi', 0.50),
(39, 1, 'Panna', 0.50),
(40, 1, 'Brie', 0.50),
(41, 1, 'Speck', 1.50),
(42, 1, 'Crudo', 1.50),
(43, 1, 'Salamino piccante', 1.00),
(44, 1, 'Frutti di mare', 2.00),
(45, 1, 'Gamberetti', 1.50),
(46, 1, 'Formaggio di monte', 0.50),
(47, 1, 'Lardo', 1.00),
(48, 1, 'Kebab', 1.50),
(49, 1, 'Salsa', 0.50),
(50, 1, 'Pomodorini', 0.50),
(51, 1, 'Salsa piccante', 0.50),
(52, 1, 'Olio all\'aglio', 0.00),
(53, 1, 'Origano', 0.00),
(54, 1, 'Funghi porcini', 1.50),
(55, 1, 'Formaggi vari', 1.50),
(56, 1, 'Friarielli', 1.00),
(57, 1, 'Ricotta', 0.50),
(58, 1, 'Tonno', 1.50),
(59, 1, 'Trevisana', 0.50),
(60, 1, 'Verdure grigliate', 1.50),
(61, 1, 'Fior di latte stracciatella', 2.00);

-- --------------------------------------------------------

--
-- Struttura della tabella `pizzas`
--

CREATE TABLE `pizzas` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `base_price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `type` enum('classica','famiglia','gourmet','scrocchiarella') NOT NULL DEFAULT 'classica',
  `image_path` varchar(255) DEFAULT NULL,
  `position` int(11) DEFAULT 0,
  `is_visible` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `pizzas`
--

INSERT INTO `pizzas` (`id`, `user_id`, `name`, `description`, `base_price`, `type`, `image_path`, `position`, `is_visible`) VALUES
(1, 1, 'MARGHERITA', 'Pomodoro, Mozzarella', 6.50, 'classica', NULL, 25, 1),
(2, 1, 'NAPOLETANA', 'Pomodoro, Mozzarella, Acciughe, Origano', 6.50, 'classica', NULL, 28, 1),
(3, 1, 'MARINARA', 'Pomodoro, Olio all\'aglio, Origano', 5.00, 'classica', NULL, 27, 1),
(4, 1, 'PROSCIUTTO', 'Pomodoro, Mozzarella, Prosciutto', 6.50, 'classica', NULL, 33, 1),
(6, 1, 'DIAVOLA', 'Pomodoro, Mozzarella, Acciughe, Salamino piccante', 6.50, 'classica', NULL, 17, 1),
(7, 1, 'CAPRICCIOSA', 'Pomodoro, Mozzarella, Carciofi, Prosciutto, Funghi', 6.00, 'classica', NULL, 11, 1),
(8, 1, 'QUATTRO STAGIONI', 'Pomodoro, Mozzarella, Carciofi, Prosciutto, Funghi, Olive', 6.00, 'classica', NULL, 37, 1),
(9, 1, 'QUATTRO FORMAGGI', 'Pomodoro, Mozzarella, Formaggi vari', 6.50, 'classica', NULL, 36, 1),
(11, 1, 'ADEL', 'Pomodoro, Mozzarella, Cipolle, Zola, Zucchine', 6.50, 'classica', NULL, 1, 1),
(100, 1, 'GOURMET LA REGINA', 'Pomodoro, Burrata, Prosciutto Crudo DOP', 14.00, 'gourmet', NULL, 1, 1),
(103, 1, 'Amatriciana', 'Pomodoro, Mozzarella, Cipolle, Grana, Pancetta', 6.50, 'classica', NULL, 4, 1),
(104, 1, 'Americana', 'Pomodoro, Mozzarella, Wurstel, Patatine', 6.50, 'classica', NULL, 5, 1),
(105, 1, 'Boscaiola', 'Pomodoro, Mozzarella, Bresaola, Porcini', 6.50, 'classica', NULL, 6, 1),
(106, 1, 'Bufala', 'Pomodoro, Mozzarella di bufala', 6.50, 'classica', NULL, 7, 1),
(107, 1, 'Calzone', 'Pomodoro, Mozzarella, Prosciutto', 7.00, 'classica', NULL, 8, 1),
(108, 1, 'Calzone Farcito', 'Pomodoro, Mozzarella, Carciofi, Prosciutto, Funghi', 6.50, 'classica', NULL, 9, 1),
(109, 1, 'Caprese', 'Pomodoro, Mozzarella, Basilico, Olive, Pomodorini freschi', 6.50, 'classica', NULL, 10, 1),
(110, 1, 'Carbonara', 'Pomodoro, Mozzarella, Uovo, Grana, Pancetta, Panna', 6.50, 'classica', NULL, 12, 1),
(111, 1, 'Casa', 'Pomodoro, Mozzarella, Porcini, Brie, Speck', 6.50, 'classica', NULL, 13, 1),
(112, 1, 'Crudo', 'Pomodoro, Mozzarella, Crudo', 6.50, 'classica', NULL, 14, 1),
(113, 1, 'Frutti di Mare', 'Pomodoro, Mozzarella, Frutti di mare', 6.50, 'classica', NULL, 19, 1),
(114, 1, 'Gamberetti', 'Pomodoro, Mozzarella, Gamberetti', 6.50, 'classica', NULL, 21, 1),
(115, 1, 'Il Nostro Calzone', 'Pomodoro, Mozzarella, Crudo, Formaggio di monte, Lardo', 5.50, 'classica', NULL, 22, 1),
(116, 1, 'Kebab', 'Pomodoro, Mozzarella, Kebab, Salsa', 6.50, 'classica', NULL, 23, 1),
(117, 1, 'Kebab Farcita', 'Pomodoro, Mozzarella, Rucola, Cipolle, Kebab, Pomodorini, Salsa piccante', 6.50, 'classica', NULL, 24, 1),
(118, 1, 'Panino Kebab', 'Rucola, Cipolle, Kebab, Pomodorini, Salsa piccante', 6.50, 'classica', NULL, 29, 1),
(119, 1, 'Pantera', 'Pomodoro, Mozzarella, Rucola, Panna, Gamberetti', 6.00, 'classica', NULL, 30, 1),
(120, 1, 'Patatine', 'Pomodoro, Mozzarella, Patatine', 6.50, 'classica', NULL, 31, 1),
(121, 1, 'Porcini', 'Pomodoro, Mozzarella, Funghi porcini', 6.50, 'classica', NULL, 32, 1),
(122, 1, 'Prosciutto e Funghi', 'Pomodoro, Mozzarella, Prosciutto, Funghi', 6.50, 'classica', NULL, 34, 1),
(123, 1, 'Pugliese', 'Pomodoro, Mozzarella, Cipolle, Grana', 6.50, 'classica', NULL, 35, 1),
(124, 1, 'Romana', 'Pomodoro, Mozzarella, Acciughe, Capperi, Olive', 6.50, 'classica', NULL, 38, 1),
(125, 1, 'Salamino Piccante', 'Pomodoro, Mozzarella, Salamino piccante', 6.00, 'classica', NULL, 39, 1),
(126, 1, 'Salsiccia', 'Pomodoro, Mozzarella, Salsiccia', 6.50, 'classica', NULL, 40, 1),
(127, 1, 'Salsiccia e Friarielli', 'Pomodoro, Mozzarella, Salsiccia, Friarielli', 6.50, 'classica', NULL, 41, 1),
(128, 1, 'Sfiziosa', 'Pomodoro, Mozzarella, Acciughe, Rucola, Pomodorini, Ricotta', 6.50, 'classica', NULL, 42, 1),
(129, 1, 'Tirolese', 'Pomodoro, Mozzarella, Grana, Speck', 6.00, 'classica', NULL, 44, 1),
(130, 1, 'Tonno', 'Pomodoro, Mozzarella, Tonno', 6.50, 'classica', NULL, 45, 1),
(131, 1, 'Tonno e Cipolle', 'Pomodoro, Mozzarella, Cipolle, Tonno', 6.50, 'classica', NULL, 47, 1),
(132, 1, 'Tonno e Prosciutto', 'Pomodoro, Mozzarella, Prosciutto, Tonno', 6.50, 'classica', NULL, 48, 1),
(133, 1, 'Tonno e Zola', 'Pomodoro, Mozzarella, Zola, Tonno', 6.00, 'classica', NULL, 49, 1),
(134, 1, 'Verdure Grigliate', 'Pomodoro, Mozzarella, Trevisana, Verdure grigliate', 6.50, 'classica', NULL, 50, 1),
(135, 1, 'Vulcano', 'Pomodoro, Mozzarella, Uovo, Prosciutto', 6.50, 'classica', NULL, 51, 1),
(136, 1, 'Wally', 'Pomodoro, Acciughe, Olive, Pomodorini, Fior di latte stracciatella', 6.00, 'classica', NULL, 52, 1),
(137, 1, 'Wurstel', 'Pomodoro, Mozzarella, Wurstel', 6.50, 'classica', NULL, 53, 1),
(138, 1, 'Zola', 'Pomodoro, Mozzarella, Zola', 6.50, 'classica', NULL, 54, 1),
(139, 1, 'Ai Due Gusti', 'Famiglia 2 gusti', 22.00, 'famiglia', NULL, 2, 1),
(140, 1, 'Ai Quattro Gusti', 'Famiglia 4 gusti', 24.00, 'famiglia', NULL, 3, 1),
(141, 1, 'Farcita', 'Famiglia farcita classica', 20.00, 'famiglia', NULL, 18, 1),
(142, 1, 'Margherita', 'Famiglia margherita', 17.00, 'famiglia', NULL, 26, 1),
(148, 1, 'GOURMET 1', 'Pomodoro, Mozzarella, Basilico, Origano, Olio d\'oliva del Garda', 11.00, 'gourmet', NULL, 2, 1),
(149, 1, 'GOURMET 2', 'Pomodoro, Mozzarella, Prosciutto crudo DOP', 13.00, 'gourmet', NULL, 3, 1),
(150, 1, 'GOURMET 3', 'Pomodoro, Mozzarella, acciughe in olio d\'oliva, origano, capperi', 13.00, 'gourmet', NULL, 4, 1),
(151, 1, 'GOURMET 4', 'Pomodoro, Burrata, Gamberi Flambè ', 14.00, 'gourmet', NULL, 5, 1),
(152, 1, 'GOURMET 5', 'Pomodoro, Mozzarella, Prosciutto cotto DOP, Carciofi, Crema di radicchio', 13.00, 'gourmet', NULL, 6, 1),
(153, 1, 'GOURMET 6', 'Pomodoro, Mortadella, Pesto, Burrata', 14.00, 'gourmet', NULL, 7, 1),
(154, 1, 'GOURMET 7', 'Pomodoro, Salmone Affumicato, Rucola, Fette d\'Arancia', 13.00, 'gourmet', NULL, 8, 1),
(155, 1, 'GOURMET 8', 'Pomodoro, Mozzarella, Gorgonzola, Mele, Noci', 13.00, 'gourmet', NULL, 9, 1),
(156, 1, 'GOURMET 9', 'Pomodoro, Mozzarella, Verdure di stagione, Crema di Radicchio', 13.00, 'gourmet', NULL, 10, 1),
(157, 1, 'GOURMET 10', 'Pomodoro, Mozzarella, Crema di radicchio, Scamorza, Speck', 13.00, 'gourmet', NULL, 11, 1),
(158, 1, 'GOURMET 11', 'Pomodoro, Pomodorini Ciliegino, Burrata, Pesto', 13.00, 'gourmet', NULL, 12, 1),
(159, 1, 'SCROCCHIARELLA LA REGINA', 'Pomodoro, Burrata, Prosciutto Crudo DOP', 25.00, 'scrocchiarella', NULL, 1, 1),
(160, 1, 'SCROCCHIARELLA 1', 'Pomodoro, Mozzarella, Basilico, Origano, Olio d\'oliva del garda', 20.00, 'scrocchiarella', NULL, 2, 1),
(161, 1, 'SCROCCHIARELLA 2', 'Pomodoro, Mozzarella, Prosciutto Crudo DOP', 23.00, 'scrocchiarella', NULL, 3, 1),
(162, 1, 'SCROCCHIARELLA 3', 'Pomodoro, Mozzarella, Acciughe in olio d\'oliva, capperi, origano', 23.00, 'scrocchiarella', NULL, 4, 1),
(163, 1, 'SCROCCHIARELLA 4', 'Pomodoro, Burrata, Gamberi Flambè', 25.00, 'scrocchiarella', NULL, 5, 1),
(164, 1, 'SCROCCHIARELLA 5', 'Pomodoro, Mozzarella, Prosciutto cotto DOP, Carciofi, Crema di radicchio', 24.00, 'scrocchiarella', NULL, 6, 1),
(165, 1, 'SCROCCHIARELLA 6', 'Pomodoro, Mortadella, Burrata, Pesto', 25.00, 'scrocchiarella', NULL, 7, 1),
(166, 1, 'SCROCCHIARELLA 7', 'Pomodoro, Salmone Affumicato, Rucola, Fette d\'Arancia', 24.00, 'scrocchiarella', NULL, 8, 1),
(167, 1, 'SCROCCHIARELLA 8', 'Pomodoro, Mozzarella, Gorgonzola, Mele, Noci', 24.00, 'scrocchiarella', NULL, 9, 1),
(168, 1, 'SCROCCHIARELLA 9', 'Pomodoro, Mozzarella, Verdure di Stagione, Crema, di Radicchio', 24.00, 'scrocchiarella', NULL, 10, 1),
(169, 1, 'SCROCCHIARELLA 10', 'Pomodoro, Mozzarella, Crema di Radicchio, Scamorza, Speck', 24.00, 'scrocchiarella', NULL, 11, 1),
(170, 1, 'SCROCCHIARELLA 11', 'Pomodoro, Pomodorini Ciliegino, Pesto', 23.00, 'scrocchiarella', NULL, 12, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `pizza_categories`
--

CREATE TABLE `pizza_categories` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `slug` varchar(100) NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `sort_order` int(11) DEFAULT 0,
  `col_pos` int(11) DEFAULT 1,
  `row_pos` int(11) DEFAULT 1,
  `col_span` int(11) DEFAULT 1,
  `inner_cols` int(11) DEFAULT 1,
  `image_path` varchar(255) DEFAULT NULL,
  `screen_number` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `pizza_categories`
--

INSERT INTO `pizza_categories` (`id`, `user_id`, `name`, `slug`, `is_visible`, `sort_order`, `col_pos`, `row_pos`, `col_span`, `inner_cols`, `image_path`, `screen_number`) VALUES
(1, 1, 'Classiche', 'classica', 1, 1, 1, 1, 10, 2, NULL, 1),
(2, 5, 'Classiche', 'classica', 1, 1, 1, 1, 1, 1, NULL, 1),
(3, 6, 'Classiche', 'classica', 1, 1, 1, 1, 1, 1, NULL, 1),
(4, 1, 'Famiglia', 'famiglia', 1, 2, 1, 2, 10, 1, NULL, 1),
(5, 5, 'Famiglia', 'famiglia', 1, 2, 1, 1, 1, 1, NULL, 1),
(6, 6, 'Famiglia', 'famiglia', 1, 2, 1, 1, 1, 1, NULL, 1),
(7, 1, 'Gourmet', 'gourmet', 1, 3, 4, 1, 1, 1, NULL, 2),
(8, 5, 'Gourmet', 'gourmet', 1, 3, 1, 1, 1, 1, NULL, 1),
(9, 6, 'Gourmet', 'gourmet', 1, 3, 1, 1, 1, 1, NULL, 1),
(10, 1, 'Scrocchiarelle', 'scrocchiarella', 1, 4, 5, 1, 1, 1, NULL, 2),
(11, 5, 'Scrocchiarelle', 'scrocchiarella', 1, 4, 1, 1, 1, 1, NULL, 1),
(12, 6, 'Scrocchiarelle', 'scrocchiarella', 1, 4, 1, 1, 1, 1, NULL, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `pizza_ingredients`
--

CREATE TABLE `pizza_ingredients` (
  `pizza_id` int(11) NOT NULL,
  `ingredient_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `pizza_ingredients`
--

INSERT INTO `pizza_ingredients` (`pizza_id`, `ingredient_id`) VALUES
(1, 1),
(1, 2),
(2, 1),
(2, 2),
(2, 8),
(2, 53),
(3, 1),
(3, 52),
(3, 53),
(4, 1),
(4, 2),
(4, 34),
(6, 1),
(6, 2),
(6, 8),
(6, 43),
(7, 1),
(7, 2),
(7, 10),
(7, 34),
(7, 35),
(8, 1),
(8, 2),
(8, 10),
(8, 34),
(8, 35),
(8, 37),
(9, 1),
(9, 2),
(9, 55),
(11, 1),
(11, 2),
(11, 25),
(11, 26),
(11, 27),
(103, 1),
(103, 2),
(103, 25),
(103, 28),
(103, 29),
(104, 1),
(104, 2),
(104, 3),
(104, 30),
(105, 1),
(105, 2),
(105, 31),
(105, 32),
(106, 1),
(106, 33),
(107, 1),
(107, 2),
(107, 34),
(108, 1),
(108, 2),
(108, 10),
(108, 34),
(108, 35),
(109, 1),
(109, 2),
(109, 36),
(109, 37),
(109, 38),
(110, 1),
(110, 2),
(110, 14),
(110, 28),
(110, 29),
(110, 39),
(111, 1),
(111, 2),
(111, 32),
(111, 40),
(111, 41),
(112, 1),
(112, 2),
(112, 42),
(113, 1),
(113, 2),
(113, 44),
(114, 1),
(114, 2),
(114, 45),
(115, 1),
(115, 2),
(115, 42),
(115, 46),
(115, 47),
(116, 1),
(116, 2),
(116, 48),
(116, 49),
(117, 1),
(117, 2),
(117, 20),
(117, 25),
(117, 48),
(117, 50),
(117, 51),
(118, 20),
(118, 25),
(118, 48),
(118, 50),
(118, 51),
(119, 1),
(119, 2),
(119, 20),
(119, 39),
(119, 45),
(120, 1),
(120, 2),
(120, 30),
(121, 1),
(121, 2),
(121, 54),
(122, 1),
(122, 2),
(122, 34),
(122, 35),
(123, 1),
(123, 2),
(123, 25),
(123, 28),
(124, 1),
(124, 2),
(124, 8),
(124, 9),
(124, 37),
(125, 1),
(125, 2),
(125, 43),
(126, 1),
(126, 2),
(126, 12),
(127, 1),
(127, 2),
(127, 12),
(127, 56),
(128, 1),
(128, 2),
(128, 8),
(128, 20),
(128, 50),
(128, 57),
(129, 1),
(129, 2),
(129, 28),
(129, 41),
(130, 1),
(130, 2),
(130, 58),
(131, 1),
(131, 2),
(131, 25),
(131, 58),
(132, 1),
(132, 2),
(132, 34),
(132, 58),
(133, 1),
(133, 2),
(133, 26),
(133, 58),
(134, 1),
(134, 2),
(134, 59),
(134, 60),
(135, 1),
(135, 2),
(135, 14),
(135, 34),
(136, 1),
(136, 8),
(136, 37),
(136, 50),
(136, 61),
(137, 1),
(137, 2),
(137, 3),
(138, 1),
(138, 2),
(138, 26);

-- --------------------------------------------------------

--
-- Struttura della tabella `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `category` enum('stuzzicheria','bibita','focaccia') NOT NULL,
  `is_visible` tinyint(1) DEFAULT 1,
  `image_path` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `products`
--

INSERT INTO `products` (`id`, `user_id`, `name`, `price`, `category`, `is_visible`, `image_path`) VALUES
(1, 1, 'Patatine Fritte', 4.50, 'stuzzicheria', 1, NULL),
(4, 1, 'Coca Cola Lattina', 2.50, 'bibita', 1, NULL),
(5, 1, 'Alette messicane', 5.00, 'stuzzicheria', 1, NULL),
(6, 1, 'Crocchette', 4.50, 'stuzzicheria', 1, NULL),
(7, 1, 'Mozzarelline al tartufo', 5.00, 'stuzzicheria', 1, NULL),
(9, 1, 'Speedy-pollo', 5.00, 'stuzzicheria', 1, NULL),
(10, 1, 'Classica', 5.50, 'focaccia', 1, NULL),
(11, 1, 'Grana', 6.00, 'focaccia', 1, NULL),
(12, 1, 'Pomodoro, capperi, origano', 6.00, 'focaccia', 1, NULL);

-- --------------------------------------------------------

--
-- Struttura della tabella `settings`
--

CREATE TABLE `settings` (
  `user_id` int(11) NOT NULL,
  `pizzeria_name` varchar(255) DEFAULT 'La Piazzetta',
  `refresh_time` int(11) DEFAULT 300,
  `qr_path` varchar(255) DEFAULT NULL,
  `qr_url` varchar(255) DEFAULT 'http://localhost/pizzeria/views/menu_online.php',
  `visibility_specials` tinyint(1) DEFAULT 1,
  `screen_columns` int(11) DEFAULT 2,
  `col_classiche` int(11) NOT NULL DEFAULT 1,
  `col_stuzzicherie` int(11) NOT NULL DEFAULT 1,
  `col_speciali` int(11) NOT NULL DEFAULT 2,
  `row_classiche` int(11) NOT NULL DEFAULT 1,
  `span_classiche` int(11) NOT NULL DEFAULT 1,
  `row_stuzzicherie` int(11) NOT NULL DEFAULT 2,
  `span_stuzzicherie` int(11) NOT NULL DEFAULT 1,
  `row_speciali` int(11) NOT NULL DEFAULT 1,
  `span_speciali` int(11) NOT NULL DEFAULT 1,
  `img_classiche` varchar(255) DEFAULT NULL,
  `img_stuzzicherie` varchar(255) DEFAULT NULL,
  `img_speciali` varchar(255) DEFAULT NULL,
  `inner_cols_classiche` int(11) DEFAULT 1,
  `inner_cols_stuzzicherie` int(11) DEFAULT 1,
  `inner_cols_speciali` int(11) DEFAULT 1,
  `screen_rows` int(11) DEFAULT 1,
  `last_updated` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `show_classica` tinyint(1) DEFAULT 1,
  `show_famiglia` tinyint(1) DEFAULT 1,
  `show_gourmet` tinyint(1) DEFAULT 1,
  `show_scrocchiarella` tinyint(1) DEFAULT 1,
  `enable_second_screen` tinyint(1) DEFAULT 0,
  `screen_stuzzicherie` int(11) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `settings`
--

INSERT INTO `settings` (`user_id`, `pizzeria_name`, `refresh_time`, `qr_path`, `qr_url`, `visibility_specials`, `screen_columns`, `col_classiche`, `col_stuzzicherie`, `col_speciali`, `row_classiche`, `span_classiche`, `row_stuzzicherie`, `span_stuzzicherie`, `row_speciali`, `span_speciali`, `img_classiche`, `img_stuzzicherie`, `img_speciali`, `inner_cols_classiche`, `inner_cols_stuzzicherie`, `inner_cols_speciali`, `screen_rows`, `last_updated`, `show_classica`, `show_famiglia`, `show_gourmet`, `show_scrocchiarella`, `enable_second_screen`, `screen_stuzzicherie`) VALUES
(1, 'PIZZERIA LA PIAZZETTA', 86400, 'uploads/qrcodes/menu_qr_1773323583.png', 'http://192.168.1.167/pizzeria/admin.php', 1, 10, 1, 1, 2, 1, 1, 3, 10, 1, 1, NULL, NULL, NULL, 1, 3, 1, 3, '2026-10-13 06:42:24', 1, 1, 1, 1, 1, 1),
(5, 'La Pizzeria di FilippoAtestadmin', 300, NULL, 'http://localhost/pizzeria/views/menu_online.php', 1, 2, 1, 1, 2, 1, 1, 2, 1, 1, 1, NULL, NULL, NULL, 1, 1, 1, 1, '2026-02-23 11:17:32', 1, 1, 1, 1, 0, 1),
(6, 'La Pizzeria di FilippoAadmin123', 300, NULL, 'http://localhost/pizzeria/views/menu_online.php', 1, 2, 1, 1, 2, 1, 1, 2, 1, 1, 1, NULL, NULL, NULL, 1, 1, 1, 1, '2026-02-23 11:18:52', 1, 1, 1, 1, 0, 1);

-- --------------------------------------------------------

--
-- Struttura della tabella `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dump dei dati per la tabella `users`
--

INSERT INTO `users` (`id`, `username`, `password`) VALUES
(1, 'FilippoA', '$2y$10$29.JHgAZgnlmGRhdPmo3SO/zf.cLWq1.R7b3E3XyBcDUVBAxhRb6S'),
(5, 'FilippoAtestadmin', '$2y$10$8wczsAj9HCK9LiQ7iPmAV.azNKgLsCnHpXyk5ivsXwI0EnfVrwJxW'),
(6, 'FilippoAadmin123', '$2y$10$04oiZO/maWYs/p17v5uiYuUb263tGXawIcOJcf4SE.0WnCVG0rrt2');

--
-- Indici per le tabelle scaricate
--

--
-- Indici per le tabelle `ingredients`
--
ALTER TABLE `ingredients`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `pizzas`
--
ALTER TABLE `pizzas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `pizza_categories`
--
ALTER TABLE `pizza_categories`
  ADD PRIMARY KEY (`id`);

--
-- Indici per le tabelle `pizza_ingredients`
--
ALTER TABLE `pizza_ingredients`
  ADD PRIMARY KEY (`pizza_id`,`ingredient_id`),
  ADD KEY `ingredient_id` (`ingredient_id`);

--
-- Indici per le tabelle `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indici per le tabelle `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`user_id`);

--
-- Indici per le tabelle `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT per le tabelle scaricate
--

--
-- AUTO_INCREMENT per la tabella `ingredients`
--
ALTER TABLE `ingredients`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=62;

--
-- AUTO_INCREMENT per la tabella `pizzas`
--
ALTER TABLE `pizzas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=171;

--
-- AUTO_INCREMENT per la tabella `pizza_categories`
--
ALTER TABLE `pizza_categories`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT per la tabella `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT per la tabella `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Limiti per le tabelle scaricate
--

--
-- Limiti per la tabella `ingredients`
--
ALTER TABLE `ingredients`
  ADD CONSTRAINT `ingredients_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `pizzas`
--
ALTER TABLE `pizzas`
  ADD CONSTRAINT `pizzas_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `pizza_ingredients`
--
ALTER TABLE `pizza_ingredients`
  ADD CONSTRAINT `pizza_ingredients_ibfk_1` FOREIGN KEY (`pizza_id`) REFERENCES `pizzas` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pizza_ingredients_ibfk_2` FOREIGN KEY (`ingredient_id`) REFERENCES `ingredients` (`id`) ON DELETE CASCADE;

--
-- Limiti per la tabella `products`
--
ALTER TABLE `products`
  ADD CONSTRAINT `products_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);

--
-- Limiti per la tabella `settings`
--
ALTER TABLE `settings`
  ADD CONSTRAINT `settings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 4.6.4deb1
-- https://www.phpmyadmin.net/
--
-- Client :  localhost:3306
-- Généré le :  Jeu 23 Mars 2017 à 16:24
-- Version du serveur :  5.7.17-0ubuntu0.16.10.1
-- Version de PHP :  7.0.15-0ubuntu0.16.10.4

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de données :  `dolibarr_50`
--

--
-- Contenu de la table `llx_minigolf_parcours`
--

INSERT INTO `llx_minigolf_parcours` (`rowid`, `date_cre`, `date_maj`, `name`, `difficulty`) VALUES
(1, '1970-01-01 01:00:00', '2017-03-23 10:46:03', 'Parcours du Bois 2', 1),
(2, NULL, NULL, 'Parcours des vernes', 1),
(3, '2017-03-23 09:46:51', '2017-03-23 10:10:43', 'Parcours du Volcan 1', 1),
(4, '2017-03-23 09:47:48', '2017-03-23 10:46:42', 'Parcours du Volcan 7777', 1111),
(5, '2017-03-23 09:57:19', '2017-03-23 10:10:48', 'Parcours du Volcan 3', 1),
(6, '2017-03-23 09:58:15', '2017-03-23 09:58:15', 'Parcours du Volcan', 1),
(7, '2017-03-23 10:02:24', '2017-03-23 10:02:24', 'Parcours du Volcan 72', 1),
(8, '2017-03-23 10:09:58', '2017-03-23 10:09:58', 'Parcours des vernes', 1),
(9, '2017-03-23 10:10:04', '2017-03-23 10:10:04', 'Parcours du Volcan 1', 1),
(10, '2017-03-23 10:17:45', '2017-03-23 10:17:45', 'Choissiez un 12313112nom', 0);

--
-- Contenu de la table `llx_minigolf_parcours_trou`
--

INSERT INTO `llx_minigolf_parcours_trou` (`rowid`, `date_cre`, `date_maj`, `ordre`, `fk_trou`, `fk_parcours`) VALUES
(1, NULL, NULL, 5, 1, 2),
(5, '2017-03-23 14:32:22', '2017-03-23 14:32:22', 2, 2, 1),
(6, '2017-03-23 14:32:49', '2017-03-23 14:32:49', 1, 1, 1),
(7, '2017-03-23 14:33:27', '2017-03-23 14:33:27', 3, 3, 1);

--
-- Contenu de la table `llx_minigolf_partie`
--

INSERT INTO `llx_minigolf_partie` (`rowid`, `date_cre`, `date_maj`, `parcoursId`, `userId`) VALUES
(1, NULL, NULL, 1, 1),
(2, NULL, NULL, 1, 2);

--
-- Contenu de la table `llx_minigolf_score`
--

INSERT INTO `llx_minigolf_score` (`rowid`, `date_cre`, `date_maj`, `fk_partie`, `fk_trou`, `score`) VALUES
(1, NULL, NULL, 1, 1, 10),
(2, NULL, NULL, 1, 2, 2),
(3, NULL, NULL, 1, 3, 5);

--
-- Contenu de la table `llx_minigolf_trou`
--

INSERT INTO `llx_minigolf_trou` (`rowid`, `date_cre`, `date_maj`, `name`, `difficulty`) VALUES
(1, '1970-01-01 01:00:00', '2017-03-23 10:14:16', 'Trou n°1', 1),
(2, NULL, NULL, 'Trou n°2', 2),
(3, '2017-03-23 14:07:43', '2017-03-23 14:07:43', 'Trou du lac', 3);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

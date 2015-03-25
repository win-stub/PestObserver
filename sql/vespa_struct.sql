-- phpMyAdmin SQL Dump
-- version 4.2.12deb2
-- http://www.phpmyadmin.net
--
-- Client :  localhost
-- Généré le :  Mer 25 Mars 2015 à 12:26
-- Version du serveur :  5.5.42-1-log
-- Version de PHP :  5.6.6-2

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données :  `vespa`
--
CREATE DATABASE IF NOT EXISTS `vespa` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE `vespa`;

-- --------------------------------------------------------

--
-- Structure de la table `area`
--

DROP TABLE IF EXISTS `area`;
CREATE TABLE IF NOT EXISTS `area` (
  `Id` int(11) NOT NULL,
  `Name` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `bioagressor`
--

DROP TABLE IF EXISTS `bioagressor`;
CREATE TABLE IF NOT EXISTS `bioagressor` (
  `Id` int(11) NOT NULL,
  `Name` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `disease`
--

DROP TABLE IF EXISTS `disease`;
CREATE TABLE IF NOT EXISTS `disease` (
  `Id` int(11) NOT NULL,
  `Name` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `plant`
--

DROP TABLE IF EXISTS `plant`;
CREATE TABLE IF NOT EXISTS `plant` (
  `Id` int(11) NOT NULL,
  `Name` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `plant_bioagressor`
--

DROP TABLE IF EXISTS `plant_bioagressor`;
CREATE TABLE IF NOT EXISTS `plant_bioagressor` (
  `Id` int(11) NOT NULL,
  `Comment` longtext,
  `Id_Plant` int(11) NOT NULL,
  `Id_Bioagressor` int(11) NOT NULL,
  `Id_Report` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `plant_disease`
--

DROP TABLE IF EXISTS `plant_disease`;
CREATE TABLE IF NOT EXISTS `plant_disease` (
  `Id` int(11) NOT NULL,
  `Comment` longtext,
  `Id_Report` int(11) NOT NULL,
  `Id_Plant` int(11) NOT NULL,
  `Id_Disease` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `report`
--

DROP TABLE IF EXISTS `report`;
CREATE TABLE IF NOT EXISTS `report` (
  `Id` int(11) NOT NULL,
  `Name` longtext NOT NULL,
  `Content` longtext NOT NULL,
  `Id_Area` int(11) NOT NULL,
  `Date` datetime DEFAULT NULL,
  `Datestr` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `sysdiagrams`
--

DROP TABLE IF EXISTS `sysdiagrams`;
CREATE TABLE IF NOT EXISTS `sysdiagrams` (
  `name` varchar(160) NOT NULL,
  `principal_id` int(11) NOT NULL,
`diagram_id` int(11) NOT NULL,
  `version` int(11) DEFAULT NULL,
  `definition` longblob
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `users`
--

DROP TABLE IF EXISTS `users`;
CREATE TABLE IF NOT EXISTS `users` (
`id` int(11) unsigned NOT NULL,
  `email` varchar(100) NOT NULL DEFAULT '',
  `password` varchar(255) DEFAULT NULL,
  `salt` varchar(255) NOT NULL DEFAULT '',
  `roles` varchar(255) NOT NULL DEFAULT '',
  `name` varchar(100) NOT NULL DEFAULT '',
  `time_created` int(11) unsigned NOT NULL DEFAULT '0',
  `username` varchar(100) DEFAULT NULL,
  `isEnabled` tinyint(1) NOT NULL DEFAULT '1',
  `confirmationToken` varchar(100) DEFAULT NULL,
  `timePasswordResetRequested` int(11) unsigned DEFAULT NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Structure de la table `user_custom_fields`
--

DROP TABLE IF EXISTS `user_custom_fields`;
CREATE TABLE IF NOT EXISTS `user_custom_fields` (
  `user_id` int(11) unsigned NOT NULL,
  `attribute` varchar(50) NOT NULL DEFAULT '',
  `value` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Index pour les tables exportées
--

--
-- Index pour la table `area`
--
ALTER TABLE `area`
 ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `bioagressor`
--
ALTER TABLE `bioagressor`
 ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `disease`
--
ALTER TABLE `disease`
 ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `plant`
--
ALTER TABLE `plant`
 ADD PRIMARY KEY (`Id`);

--
-- Index pour la table `plant_bioagressor`
--
ALTER TABLE `plant_bioagressor`
 ADD PRIMARY KEY (`Id`), ADD KEY `IX_FK_Plant_Bioagressor2Bioagressor` (`Id_Bioagressor`), ADD KEY `IX_FK_Plant_Bioagressor2Plant` (`Id_Plant`), ADD KEY `IX_FK_Plant_Bioagressor2Report` (`Id_Report`);

--
-- Index pour la table `plant_disease`
--
ALTER TABLE `plant_disease`
 ADD PRIMARY KEY (`Id`), ADD KEY `IX_FK_MaladieRelation` (`Id_Disease`), ADD KEY `IX_FK_PlanteRelation` (`Id_Plant`), ADD KEY `IX_FK_RelationBulletin` (`Id_Report`);

--
-- Index pour la table `report`
--
ALTER TABLE `report`
 ADD PRIMARY KEY (`Id`), ADD KEY `IX_FK_ReportArea` (`Id_Area`);

--
-- Index pour la table `sysdiagrams`
--
ALTER TABLE `sysdiagrams`
 ADD PRIMARY KEY (`diagram_id`), ADD UNIQUE KEY `UK_principal_name` (`principal_id`,`name`);

--
-- Index pour la table `users`
--
ALTER TABLE `users`
 ADD PRIMARY KEY (`id`), ADD UNIQUE KEY `unique_email` (`email`), ADD UNIQUE KEY `username` (`username`);

--
-- Index pour la table `user_custom_fields`
--
ALTER TABLE `user_custom_fields`
 ADD PRIMARY KEY (`user_id`,`attribute`);

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: 21.01.2026 klo 08:29
-- Palvelimen versio: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `laiterekisteri`
--

-- --------------------------------------------------------

--
-- Rakenne taululle `kayttajat`
--

CREATE TABLE `kayttajat` (
  `KayttajaID` int(11) NOT NULL,
  `Kayttajatunnus` varchar(255) DEFAULT NULL,
  `Salasana` varchar(255) DEFAULT NULL,
  `Rooli` enum('Opettaja','Admin') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `laitteet`
--

CREATE TABLE `laitteet` (
  `LaiteID` int(11) NOT NULL,
  `Nimi` varchar(255) DEFAULT NULL,
  `Laiteryma` enum('Pöytätietokoneet','Kannettavat_tietokoneet','Tabletit','Näytöt','Näppäimistöt','Hiiret','Webkamerat','Kuulokkeet','Kaiuttimet') DEFAULT NULL,
  `Varastohuone` enum('A2TS16','A2TS20','A2TS24') DEFAULT NULL,
  `Kaappi` enum('16.1','16.2','16.3','16.4','20.1','20.2','20.3','20.4','24.1','24.2','24.3','24.4') DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Rakenne taululle `varaukset`
--

CREATE TABLE `varaukset` (
  `VarausID` int(11) NOT NULL,
  `KayttajaID` int(6) DEFAULT NULL,
  `LaiteID` int(6) DEFAULT NULL,
  `Varaus_alku` datetime DEFAULT NULL,
  `Varaus_loppu` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `kayttajat`
--
ALTER TABLE `kayttajat`
  ADD PRIMARY KEY (`KayttajaID`);

--
-- Indexes for table `laitteet`
--
ALTER TABLE `laitteet`
  ADD PRIMARY KEY (`LaiteID`);

--
-- Indexes for table `varaukset`
--
ALTER TABLE `varaukset`
  ADD PRIMARY KEY (`VarausID`),
  ADD KEY `KayttajaID` (`KayttajaID`),
  ADD KEY `LaiteID` (`LaiteID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `kayttajat`
--
ALTER TABLE `kayttajat`
  MODIFY `KayttajaID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `laitteet`
--
ALTER TABLE `laitteet`
  MODIFY `LaiteID` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `varaukset`
--
ALTER TABLE `varaukset`
  MODIFY `VarausID` int(11) NOT NULL AUTO_INCREMENT;

--
-- Rajoitteet vedostauluille
--

--
-- Rajoitteet taululle `varaukset`
--
ALTER TABLE `varaukset`
  ADD CONSTRAINT `varaukset_ibfk_1` FOREIGN KEY (`KayttajaID`) REFERENCES `kayttajat` (`KayttajaID`),
  ADD CONSTRAINT `varaukset_ibfk_2` FOREIGN KEY (`LaiteID`) REFERENCES `laitteet` (`LaiteID`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

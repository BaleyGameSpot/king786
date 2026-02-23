-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Tempo de geração: 25-Maio-2025 às 23:04
-- Versão do servidor: 10.11.13-MariaDB
-- versão do PHP: 8.3.20

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de dados: `ridey_dudeapps`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `driver_request`
--

CREATE TABLE `driver_request` (
  `iDriverRequestId` int(11) NOT NULL,
  `iOrderId` int(11) NOT NULL,
  `iRequestId` int(11) NOT NULL,
  `iDriverId` int(11) NOT NULL,
  `iUserId` int(11) NOT NULL,
  `iTripId` int(11) NOT NULL,
  `eStatus` enum('Decline','Accept','Timeout','Received','Sent','Pending') NOT NULL DEFAULT 'Timeout',
  `eAcceptAttempted` enum('Yes','No') NOT NULL DEFAULT 'No',
  `tDate` timestamp NOT NULL DEFAULT current_timestamp(),
  `vMsgCode` mediumtext NOT NULL,
  `vStartLatlong` mediumtext NOT NULL,
  `vEndLatlong` mediumtext NOT NULL,
  `tStartAddress` mediumtext NOT NULL,
  `tEndAddress` mediumtext NOT NULL,
  `dAddedDate` datetime NOT NULL,
  `eReceivedByPubSub` enum('Yes','No') NOT NULL DEFAULT 'No',
  `dPunSubDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `eReceivedByPush` enum('Yes','No') NOT NULL DEFAULT 'No',
  `dPushDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `eReceivedByScript` enum('Yes','No') NOT NULL DEFAULT 'No',
  `dScriptDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
  `eFromDevice` enum('Android','Ios') NOT NULL COMMENT 'From device type. (Generally, passenger''s device type)',
  `eToDevice` enum('Android','Ios') NOT NULL COMMENT 'To device type. (Generally, driver''s device type)',
  `eSent` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'This will be yes when passenger sent a request.',
  `dSentDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date when passenger sent a request',
  `eTimeOut` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'This will be yes if driver received request is timeout.',
  `dTimeOutDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date when driver received request is timeout. This field must be in server''s time zone not user''s device time zone.',
  `eReceived` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'This field must be ignored in every case. Should not use for report. This is for internal use only',
  `eOpened` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'This will be yes if driver opens request.',
  `dOpenedDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date when driver opens request. This field must be in server''s timezone not in user''s device timezone.',
  `eAccept` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'This will be yes if driver accepts request.',
  `dAcceptDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date when driver declines request. This must be in server''s timezone not user''s device time zone.',
  `eDecline` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'This will be yes if driver accepts request. This field must be in server''s time zone not user''s device time zone',
  `dDeclineDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date when driver declines request. This must be in server''s timezone not user''s device time zone.',
  `eDiscardByApp` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'This will be yes if app discards request.',
  `dDiscardByApp` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date when server discards request. This field must be in server time zone not user''s device time zone. ',
  `eDiscard` enum('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'This will be yes if server discards request.',
  `dDiscardDate` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00' COMMENT 'Date when server discards request.',
  `fTaxiBidAmount` decimal(14,7) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;

--
-- Extraindo dados da tabela `driver_request`
--

INSERT INTO `driver_request` (`iDriverRequestId`, `iOrderId`, `iRequestId`, `iDriverId`, `iUserId`, `iTripId`, `eStatus`, `eAcceptAttempted`, `tDate`, `vMsgCode`, `vStartLatlong`, `vEndLatlong`, `tStartAddress`, `tEndAddress`, `dAddedDate`, `eReceivedByPubSub`, `dPunSubDate`, `eReceivedByPush`, `dPushDate`, `eReceivedByScript`, `dScriptDate`, `eFromDevice`, `eToDevice`, `eSent`, `dSentDate`, `eTimeOut`, `dTimeOutDate`, `eReceived`, `eOpened`, `dOpenedDate`, `eAccept`, `dAcceptDate`, `eDecline`, `dDeclineDate`, `eDiscardByApp`, `dDiscardByApp`, `eDiscard`, `dDiscardDate`, `fTaxiBidAmount`) VALUES
(1, 0, 1, 1, 2, 0, 'Pending', 'No', '2025-05-24 02:55:56', '17480517548265', '38.75660674453515,-9.238182270513326', '38.7106557,-9.1376722', 'Praça da Igreja 14, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 02:55:56', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(2, 0, 2, 1, 2, 0, 'Pending', 'No', '2025-05-24 10:15:18', '17480781179976', '38.75664733233974,-9.238188629216289', '38.7600049,-9.3286083', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rio de Mouro, Portugal', '2025-05-24 10:15:18', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(3, 0, 3, 1, 2, 0, 'Pending', 'No', '2025-05-24 10:18:16', '17480782955882', '38.75664284062532,-9.238189374248073', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 10:18:16', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(4, 0, 4, 1, 2, 0, 'Pending', 'No', '2025-05-24 11:14:08', '17480816471881', '38.75671566001955,-9.23824651167657', '33.223191,43.679291', 'Praça da Igreja 13, 2700-794 Amadora, Portugal', 'Iraq', '2025-05-24 11:14:08', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(5, 0, 5, 1, 2, 0, 'Pending', 'No', '2025-05-24 11:15:22', '17480817204923', '38.756714922107385,-9.2381982015585', '38.7106557,-9.1376722', 'Praça da Igreja 13B, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 11:15:22', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(6, 0, 6, 1, 2, 0, 'Pending', 'No', '2025-05-24 13:41:11', '17480904704828', '38.75667767200459,-9.238148825040346', '38.7106557,-9.1376722', 'Praça da Igreja 13, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 13:41:11', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(7, 0, 7, 1, 2, 0, 'Pending', 'No', '2025-05-24 15:09:21', '17480957608918', '38.75658312479444,-9.238164868379263', '38.7106557,-9.1376722', 'Praça da Igreja 14, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 15:09:21', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(8, 0, 8, 1, 2, 0, 'Pending', 'No', '2025-05-24 15:11:24', '17480958842519', '38.756627482858896,-9.238149393304504', '38.7106557,-9.1376722', 'Praça da Igreja 13, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 15:11:24', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(9, 0, 9, 1, 2, 0, 'Pending', 'No', '2025-05-24 17:09:05', '17481029436953', '38.75663122182843,-9.238216423161075', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 17:09:05', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(10, 0, 10, 1, 2, 0, 'Pending', 'No', '2025-05-24 17:25:11', '17481039104986', '38.756390328686386,-9.238176513146556', '38.7106557,-9.1376722', 'R. Pio XII 16A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 17:25:11', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(11, 0, 11, 1, 2, 0, 'Pending', 'No', '2025-05-24 17:28:34', '17481041139857', '38.75665585514058,-9.238229747870228', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 17:28:34', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(12, 0, 12, 1, 2, 0, 'Pending', 'No', '2025-05-24 17:31:09', '17481042685882', '38.75665335876775,-9.238202672116461', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-24 17:31:09', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(13, 0, 13, 1, 2, 0, 'Pending', 'No', '2025-05-25 06:34:15', '17481512553984', '38.75665729296941,-9.238174256602711', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-25 06:34:15', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(14, 0, 14, 1, 2, 0, 'Pending', 'No', '2025-05-25 06:35:20', '17481513194719', '38.75665729296941,-9.238174256602711', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-25 06:35:20', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(15, 0, 15, 1, 2, 0, 'Pending', 'No', '2025-05-25 07:25:11', '17481543111209', '38.756390328686386,-9.238176513146556', '38.7106557,-9.1376722', 'R. Pio XII 16A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-25 07:25:11', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(16, 0, 16, 1, 2, 0, 'Pending', 'No', '2025-05-25 07:29:29', '17481545689032', '38.75663024531778,-9.238188622531299', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-25 07:29:29', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(17, 0, 17, 1, 1, 0, 'Pending', 'No', '2025-05-25 15:38:01', '17481838817170', '38.75665729296941,-9.238174256602711', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-25 15:38:01', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(18, 0, 18, 1, 1, 0, 'Pending', 'No', '2025-05-25 17:07:43', '17481892637344', '38.75665729296941,-9.238174256602711', ',', 'ok,ok\nPraça da Igreja 14A, 2700-794 Amadora, Portugal', '', '2025-05-25 17:07:43', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(19, 0, 19, 1, 1, 0, 'Pending', 'No', '2025-05-25 17:33:01', '17481907807170', '38.75665729296941,-9.238174256602711', ',', 'ok,ok\nPraça da Igreja 14A, 2700-794 Amadora, Portugal', '', '2025-05-25 17:33:01', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(20, 0, 20, 1, 1, 0, 'Pending', 'No', '2025-05-25 18:19:19', '17481935586540', '38.75665729296941,-9.238174256602711', ',', 'ok,ok\nPraça da Igreja 14A, 2700-794 Amadora, Portugal', '', '2025-05-25 18:19:19', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(21, 0, 21, 1, 1, 0, 'Pending', 'No', '2025-05-25 18:19:39', '17481935781050', '38.75665729296941,-9.238174256602711', ',', 'ok,ok\nPraça da Igreja 14A, 2700-794 Amadora, Portugal', '', '2025-05-25 18:19:39', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(22, 0, 22, 1, 1, 0, 'Pending', 'No', '2025-05-25 18:22:29', '17481937495067', '38.75665729296941,-9.238174256602711', ',', 'ok,ok\nPraça da Igreja 14A, 2700-794 Amadora, Portugal', '', '2025-05-25 18:22:29', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000),
(23, 0, 23, 1, 1, 0, 'Pending', 'No', '2025-05-25 23:53:52', '17482136326972', '38.756631933417225,-9.238181517816024', '38.7106557,-9.1376722', 'Praça da Igreja 14A, 2700-794 Amadora, Portugal', 'Rua Augusta, Lisbon, Portugal', '2025-05-25 23:53:52', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'Android', 'Android', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 'No', '0000-00-00 00:00:00', 0.0000000);

--
-- Índices para tabelas despejadas
--

--
-- Índices para tabela `driver_request`
--
ALTER TABLE `driver_request`
  ADD PRIMARY KEY (`iDriverRequestId`);

--
-- AUTO_INCREMENT de tabelas despejadas
--

--
-- AUTO_INCREMENT de tabela `driver_request`
--
ALTER TABLE `driver_request`
  MODIFY `iDriverRequestId` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

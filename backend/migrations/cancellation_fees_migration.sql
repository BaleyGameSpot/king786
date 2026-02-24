-- ============================================================
-- Cancellation Fees Migration SQL
-- Generated: 2026-02-24
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- ============================================================
-- 1. PROPORTIONAL CANCELLATION FEE
-- Adds distance-based fee to existing cancel_reason table
-- ============================================================
ALTER TABLE `cancel_reason`
  ADD COLUMN IF NOT EXISTS `eProportionalFee` ENUM('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'Whether to charge proportional fee based on distance',
  ADD COLUMN IF NOT EXISTS `fProportionalFeeRate` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Fee rate per KM traveled (in local currency)',
  ADD COLUMN IF NOT EXISTS `fMinProportionalFee` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Minimum proportional cancellation fee',
  ADD COLUMN IF NOT EXISTS `fMaxProportionalFee` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Maximum proportional cancellation fee (0 = no max)';

ALTER TABLE `trips`
  ADD COLUMN IF NOT EXISTS `fDriverDistanceToPickup` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Distance driver traveled toward pickup point (KM)',
  ADD COLUMN IF NOT EXISTS `fProportionalCancelFee` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Calculated proportional cancellation fee',
  ADD COLUMN IF NOT EXISTS `eProportionalFeeTransferred` ENUM('Yes','No') NOT NULL DEFAULT 'No' COMMENT 'Whether fee was auto-transferred to driver wallet';

-- ============================================================
-- 2. NO-SHOW FEE WITH GPS VERIFICATION
-- ============================================================
CREATE TABLE IF NOT EXISTS `no_show_fee_requests` (
  `iNoShowId` int(11) NOT NULL AUTO_INCREMENT,
  `iTripId` int(11) NOT NULL,
  `iDriverId` int(11) NOT NULL,
  `iUserId` int(11) NOT NULL,
  `fFeeAmount` decimal(10,4) NOT NULL DEFAULT 0.0000,
  `vDriverGpsLat` varchar(50) NOT NULL DEFAULT '',
  `vDriverGpsLng` varchar(50) NOT NULL DEFAULT '',
  `vPickupLat` varchar(50) NOT NULL DEFAULT '',
  `vPickupLng` varchar(50) NOT NULL DEFAULT '',
  `fGpsDistanceFromPickup` decimal(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Distance between driver GPS and pickup (meters)',
  `vDriverPhoto` varchar(255) NOT NULL DEFAULT '' COMMENT 'Driver photo evidence at pickup',
  `eStatus` enum('Pending','Approved','Rejected') NOT NULL DEFAULT 'Pending',
  `eFeePaid` enum('Yes','No') NOT NULL DEFAULT 'No',
  `vAdminNote` text NOT NULL,
  `iApprovedBy` int(11) NOT NULL DEFAULT 0,
  `dRequestDate` datetime NOT NULL DEFAULT current_timestamp(),
  `dApprovalDate` datetime DEFAULT NULL,
  PRIMARY KEY (`iNoShowId`),
  KEY `iTripId` (`iTripId`),
  KEY `iDriverId` (`iDriverId`),
  KEY `eStatus` (`eStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

ALTER TABLE `setup_info`
  ADD COLUMN IF NOT EXISTS `fNoShowFeeAmount` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Default no-show fee amount',
  ADD COLUMN IF NOT EXISTS `fNoShowMaxGpsDistanceMeters` DECIMAL(10,4) NOT NULL DEFAULT 100.0000 COMMENT 'Max meters from pickup for GPS confirmation',
  ADD COLUMN IF NOT EXISTS `eNoShowFeeEnabled` ENUM('Yes','No') NOT NULL DEFAULT 'No';

COMMIT;

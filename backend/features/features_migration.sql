-- ============================================================
-- FEATURES MIGRATION FILE
-- Ridey App - Full Feature Set Implementation
-- Generated: 2026-02-25
-- ============================================================
-- Run this file on MariaDB 10.11+ (database: ridey_dudeapps)
-- ============================================================

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";
SET NAMES utf8mb4;

-- ============================================================
-- FEATURE 1: RIDE BIDDING AUCTION (InDrive-style)
-- Price negotiation between driver and passenger
-- ============================================================

CREATE TABLE IF NOT EXISTS `ride_bid_negotiations` (
  `iNegotiationId`   INT(11)          NOT NULL AUTO_INCREMENT,
  `iRequestId`       INT(11)          NOT NULL COMMENT 'FK to driver_request.iRequestId',
  `iDriverId`        INT(11)          NOT NULL,
  `iUserId`          INT(11)          NOT NULL,
  `fPassengerOffer`  DECIMAL(14,2)    NOT NULL DEFAULT 0.00 COMMENT 'Initial passenger offer',
  `fDriverCounter`   DECIMAL(14,2)    NOT NULL DEFAULT 0.00 COMMENT 'Driver counter-offer',
  `fFinalPrice`      DECIMAL(14,2)    NOT NULL DEFAULT 0.00 COMMENT 'Agreed price after negotiation',
  `eStatus`          ENUM('Pending','DriverCountered','PassengerCountered','Accepted','Rejected','Expired') NOT NULL DEFAULT 'Pending',
  `iRoundCount`      TINYINT(3)       NOT NULL DEFAULT 0 COMMENT 'Number of negotiation rounds',
  `iMaxRounds`       TINYINT(3)       NOT NULL DEFAULT 3 COMMENT 'Max allowed rounds before auto-reject',
  `dExpiresAt`       DATETIME         NOT NULL COMMENT 'Offer expiry timestamp',
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  `dUpdatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`iNegotiationId`),
  KEY `idx_request` (`iRequestId`),
  KEY `idx_driver` (`iDriverId`),
  KEY `idx_user` (`iUserId`),
  KEY `idx_status` (`eStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='InDrive-style bid negotiation history';

CREATE TABLE IF NOT EXISTS `ride_bid_rounds` (
  `iRoundId`         INT(11)          NOT NULL AUTO_INCREMENT,
  `iNegotiationId`   INT(11)          NOT NULL,
  `eOfferedBy`       ENUM('Passenger','Driver') NOT NULL,
  `fAmount`          DECIMAL(14,2)    NOT NULL,
  `vNote`            VARCHAR(255)     DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iRoundId`),
  KEY `idx_negotiation` (`iNegotiationId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Individual negotiation rounds for bid auction';

-- Add bid_mode flag to driver_request if not already present
ALTER TABLE `driver_request`
  ADD COLUMN IF NOT EXISTS `eBidMode`          ENUM('Standard','Auction') NOT NULL DEFAULT 'Standard' COMMENT 'Standard=fixed price; Auction=InDrive negotiation',
  ADD COLUMN IF NOT EXISTS `fPassengerBidOffer` DECIMAL(14,7) NOT NULL DEFAULT 0.0000000 COMMENT 'Passenger initial bid offer';

-- ============================================================
-- FEATURE 2: BOOK FOR OTHERS (Request on behalf of 3rd party)
-- ============================================================

CREATE TABLE IF NOT EXISTS `ride_book_for_others` (
  `iBookForOthersId` INT(11)          NOT NULL AUTO_INCREMENT,
  `iRequestId`       INT(11)          NOT NULL COMMENT 'FK to driver_request.iRequestId',
  `iBookedByUserId`  INT(11)          NOT NULL COMMENT 'User who booked the ride',
  `vBeneficiaryName` VARCHAR(100)     NOT NULL COMMENT 'Passenger who will take the ride',
  `vBeneficiaryPhone` VARCHAR(20)     NOT NULL,
  `vBeneficiaryCountryCode` VARCHAR(10) NOT NULL DEFAULT '+55',
  `eRelationship`    ENUM('Family','Friend','Colleague','Other') NOT NULL DEFAULT 'Other',
  `eStatus`          ENUM('Active','Cancelled','Completed') NOT NULL DEFAULT 'Active',
  `eNotifyBeneficiary` ENUM('Yes','No') NOT NULL DEFAULT 'Yes' COMMENT 'Send SMS/push to beneficiary',
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iBookForOthersId`),
  KEY `idx_request` (`iRequestId`),
  KEY `idx_booked_by` (`iBookedByUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Bookings made on behalf of other people';

-- ============================================================
-- FEATURE 3: PROPORTIONAL CANCELLATION FEE
-- Charge based on actual distance driver traveled toward pickup
-- ============================================================

CREATE TABLE IF NOT EXISTS `proportional_cancellation_fee` (
  `iCancelFeeId`     INT(11)          NOT NULL AUTO_INCREMENT,
  `iRequestId`       INT(11)          NOT NULL,
  `iDriverId`        INT(11)          NOT NULL,
  `iUserId`          INT(11)          NOT NULL,
  `vDriverStartLatLng` VARCHAR(50)    NOT NULL COMMENT 'Driver GPS when accepted',
  `vDriverCancelLatLng` VARCHAR(50)   NOT NULL COMMENT 'Driver GPS when cancelled',
  `vPickupLatLng`    VARCHAR(50)      NOT NULL COMMENT 'Passenger pickup point',
  `fTotalDistanceToPickup` DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Full distance from driver to pickup (km)',
  `fDistanceTraveled`      DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'Distance driver traveled toward pickup (km)',
  `fProportionTraveled`    DECIMAL(5,4) NOT NULL DEFAULT 0.0000 COMMENT '0.0-1.0 ratio (traveled/total)',
  `fBaseCancellationFee`   DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Full cancellation fee configured',
  `fChargedFee`            DECIMAL(14,2) NOT NULL DEFAULT 0.00 COMMENT 'Actual charged = base * proportion',
  `eWho`             ENUM('Passenger','Driver') NOT NULL DEFAULT 'Passenger' COMMENT 'Who cancelled',
  `ePaymentStatus`   ENUM('Pending','Charged','Waived','Failed') NOT NULL DEFAULT 'Pending',
  `vPaymentRef`      VARCHAR(100)     DEFAULT NULL,
  `dCancelledAt`     DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iCancelFeeId`),
  KEY `idx_request` (`iRequestId`),
  KEY `idx_driver` (`iDriverId`),
  KEY `idx_user` (`iUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Proportional cancellation fees based on driver distance traveled';

-- ============================================================
-- FEATURE 4: VERIFIED NO-SHOW FEE
-- GPS + admin approval to validate passenger absence
-- ============================================================

CREATE TABLE IF NOT EXISTS `no_show_incidents` (
  `iNoShowId`        INT(11)          NOT NULL AUTO_INCREMENT,
  `iRequestId`       INT(11)          NOT NULL,
  `iTripId`          INT(11)          NOT NULL DEFAULT 0,
  `iDriverId`        INT(11)          NOT NULL,
  `iUserId`          INT(11)          NOT NULL,
  `vDriverAtPickupLatLng` VARCHAR(50) NOT NULL COMMENT 'Driver GPS at pickup time',
  `vPickupLatLng`    VARCHAR(50)      NOT NULL COMMENT 'Agreed pickup location',
  `fDistanceFromPickup`   DECIMAL(10,4) NOT NULL DEFAULT 0.0000 COMMENT 'How far driver was from pickup (m)',
  `fNoShowFee`       DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `dDriverArrivedAt` DATETIME         DEFAULT NULL COMMENT 'When driver marked arrived',
  `dWaitExpiredAt`   DATETIME         DEFAULT NULL COMMENT 'When wait time expired',
  `iWaitMinutes`     TINYINT(3)       NOT NULL DEFAULT 5 COMMENT 'Wait time before no-show allowed',
  `eStatus`          ENUM('PendingReview','AdminApproved','AdminRejected','FeeCharged','Waived') NOT NULL DEFAULT 'PendingReview',
  `iAdminId`         INT(11)          DEFAULT NULL COMMENT 'Admin who approved/rejected',
  `vAdminNote`       TEXT             DEFAULT NULL,
  `dAdminActionAt`   DATETIME         DEFAULT NULL,
  `vGpsProofJson`    LONGTEXT         DEFAULT NULL COMMENT 'JSON array of GPS breadcrumbs as proof',
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iNoShowId`),
  KEY `idx_request` (`iRequestId`),
  KEY `idx_driver` (`iDriverId`),
  KEY `idx_status` (`eStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Verified no-show incidents with GPS proof and admin approval';

-- ============================================================
-- FEATURE 5: LOST & FOUND CHAT + PAID RETURN TRIP
-- ============================================================

CREATE TABLE IF NOT EXISTS `lost_found_tickets` (
  `iTicketId`        INT(11)          NOT NULL AUTO_INCREMENT,
  `iUserId`          INT(11)          NOT NULL COMMENT 'Passenger who lost item',
  `iDriverId`        INT(11)          NOT NULL,
  `iTripId`          INT(11)          NOT NULL,
  `iRequestId`       INT(11)          NOT NULL DEFAULT 0,
  `vItemDescription` TEXT             NOT NULL,
  `vItemCategory`    VARCHAR(100)     DEFAULT NULL COMMENT 'e.g. Phone, Bag, Keys, Document',
  `vItemImagePath`   VARCHAR(255)     DEFAULT NULL,
  `eStatus`          ENUM('Open','InProgress','ItemFound','ItemReturned','Closed','ReturnTripCreated') NOT NULL DEFAULT 'Open',
  `eReturnTripCreated` ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `iReturnTripRequestId` INT(11)      DEFAULT NULL COMMENT 'FK to driver_request for return trip',
  `fReturnTripFare`  DECIMAL(14,2)    NOT NULL DEFAULT 0.00 COMMENT 'Agreed fare for return trip',
  `vAdminNote`       TEXT             DEFAULT NULL,
  `iHandledByAdminId` INT(11)         DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  `dUpdatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`iTicketId`),
  KEY `idx_user` (`iUserId`),
  KEY `idx_driver` (`iDriverId`),
  KEY `idx_trip` (`iTripId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Lost and found support tickets';

CREATE TABLE IF NOT EXISTS `lost_found_messages` (
  `iMessageId`       INT(11)          NOT NULL AUTO_INCREMENT,
  `iTicketId`        INT(11)          NOT NULL,
  `eSenderType`      ENUM('Passenger','Driver','Admin','System') NOT NULL,
  `iSenderId`        INT(11)          NOT NULL,
  `vMessage`         TEXT             NOT NULL,
  `vAttachmentPath`  VARCHAR(255)     DEFAULT NULL,
  `eIsRead`          ENUM('Yes','No') NOT NULL DEFAULT 'No',
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iMessageId`),
  KEY `idx_ticket` (`iTicketId`),
  KEY `idx_sender` (`eSenderType`, `iSenderId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Chat messages for lost and found tickets';

-- ============================================================
-- FEATURE 6: FACIAL RECOGNITION (AI identity verification)
-- ============================================================

CREATE TABLE IF NOT EXISTS `facial_verification_logs` (
  `iVerificationId`  INT(11)          NOT NULL AUTO_INCREMENT,
  `iUserId`          INT(11)          NOT NULL,
  `eUserType`        ENUM('Passenger','Driver') NOT NULL,
  `eEventType`       ENUM('Onboarding','Login','TripStart','Random') NOT NULL DEFAULT 'Login',
  `eProvider`        ENUM('AWSRekognition','FacePlusPlus','Azure','Custom') NOT NULL DEFAULT 'AWSRekognition',
  `vReferenceImagePath` VARCHAR(255)  NOT NULL COMMENT 'Stored reference face image',
  `vLiveImagePath`   VARCHAR(255)     DEFAULT NULL COMMENT 'Live captured image',
  `fSimilarityScore` DECIMAL(5,2)     NOT NULL DEFAULT 0.00 COMMENT '0-100 face match score',
  `fThresholdUsed`   DECIMAL(5,2)     NOT NULL DEFAULT 90.00 COMMENT 'Minimum score to pass',
  `eResult`          ENUM('Passed','Failed','Error','Pending') NOT NULL DEFAULT 'Pending',
  `vProviderResponse` LONGTEXT        DEFAULT NULL COMMENT 'Raw JSON response from face API',
  `vIpAddress`       VARCHAR(45)      DEFAULT NULL,
  `vDeviceInfo`      VARCHAR(255)     DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iVerificationId`),
  KEY `idx_user` (`iUserId`, `eUserType`),
  KEY `idx_event` (`eEventType`),
  KEY `idx_result` (`eResult`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='AI facial recognition verification log';

-- Add facial verification fields to user tables
ALTER TABLE `register`
  ADD COLUMN IF NOT EXISTS `vFaceReferenceImage` VARCHAR(255) DEFAULT NULL COMMENT 'Reference face image path',
  ADD COLUMN IF NOT EXISTS `eFaceVerified`        ENUM('Yes','No','Pending') NOT NULL DEFAULT 'Pending',
  ADD COLUMN IF NOT EXISTS `dFaceVerifiedAt`      DATETIME DEFAULT NULL;

ALTER TABLE `register_driver`
  ADD COLUMN IF NOT EXISTS `vFaceReferenceImage` VARCHAR(255) DEFAULT NULL,
  ADD COLUMN IF NOT EXISTS `eFaceVerified`        ENUM('Yes','No','Pending') NOT NULL DEFAULT 'Pending',
  ADD COLUMN IF NOT EXISTS `dFaceVerifiedAt`      DATETIME DEFAULT NULL;

-- ============================================================
-- FEATURE 7: SMART PUSH NOTIFICATIONS (Scheduled + Repeatable)
-- ============================================================

CREATE TABLE IF NOT EXISTS `smart_notifications` (
  `iNotificationId`  INT(11)          NOT NULL AUTO_INCREMENT,
  `vTitle`           VARCHAR(200)     NOT NULL,
  `vBody`            TEXT             NOT NULL,
  `vDataPayload`     LONGTEXT         DEFAULT NULL COMMENT 'JSON extra data payload',
  `eTargetType`      ENUM('AllUsers','AllDrivers','SpecificUser','SpecificDriver','Segment','Franchise') NOT NULL DEFAULT 'AllUsers',
  `vTargetIds`       TEXT             DEFAULT NULL COMMENT 'CSV of user/driver IDs or franchise ID',
  `iFranchiseId`     INT(11)          DEFAULT NULL COMMENT 'Target specific franchise city',
  `eScheduleType`    ENUM('Immediate','Scheduled','Recurring') NOT NULL DEFAULT 'Immediate',
  `dScheduledAt`     DATETIME         DEFAULT NULL COMMENT 'When to send (for Scheduled)',
  `eRepeatInterval`  ENUM('None','Hourly','Daily','Weekly','Monthly') NOT NULL DEFAULT 'None',
  `iRepeatCount`     SMALLINT(5)      NOT NULL DEFAULT 1 COMMENT '0=unlimited; N=send N times',
  `iSentCount`       SMALLINT(5)      NOT NULL DEFAULT 0,
  `eStatus`          ENUM('Draft','Queued','Sending','Sent','Paused','Cancelled','Failed') NOT NULL DEFAULT 'Draft',
  `dNextRunAt`       DATETIME         DEFAULT NULL,
  `dLastSentAt`      DATETIME         DEFAULT NULL,
  `iCreatedByAdmin`  INT(11)          DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  `dUpdatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`iNotificationId`),
  KEY `idx_schedule` (`eStatus`, `dNextRunAt`),
  KEY `idx_franchise` (`iFranchiseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Smart push notification scheduler';

CREATE TABLE IF NOT EXISTS `smart_notification_logs` (
  `iLogId`           INT(11)          NOT NULL AUTO_INCREMENT,
  `iNotificationId`  INT(11)          NOT NULL,
  `iUserId`          INT(11)          NOT NULL,
  `eUserType`        ENUM('Passenger','Driver') NOT NULL DEFAULT 'Passenger',
  `eDeliveryStatus`  ENUM('Sent','Delivered','Failed','Clicked') NOT NULL DEFAULT 'Sent',
  `vFcmToken`        VARCHAR(300)     DEFAULT NULL,
  `dSentAt`          DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iLogId`),
  KEY `idx_notification` (`iNotificationId`),
  KEY `idx_user` (`iUserId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Push notification delivery log';

-- ============================================================
-- FEATURE 8: IN-APP RECEIPTS (Auto-generated trip & order summaries)
-- ============================================================

CREATE TABLE IF NOT EXISTS `in_app_receipts` (
  `iReceiptId`       INT(11)          NOT NULL AUTO_INCREMENT,
  `vReceiptNumber`   VARCHAR(50)      NOT NULL UNIQUE COMMENT 'Human-readable e.g. RCP-20260225-001',
  `eReceiptType`     ENUM('Trip','Order','Bidding','LostFoundReturn','Cancellation') NOT NULL DEFAULT 'Trip',
  `iReferenceId`     INT(11)          NOT NULL COMMENT 'Trip/Order/Request ID depending on type',
  `iUserId`          INT(11)          NOT NULL,
  `iDriverId`        INT(11)          DEFAULT NULL,
  `fSubtotal`        DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `fDiscount`        DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `fTax`             DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `fTip`             DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `fTotal`           DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `vCurrency`        VARCHAR(10)      NOT NULL DEFAULT 'BRL',
  `ePaymentMethod`   VARCHAR(50)      DEFAULT NULL COMMENT 'Cash, Card, Wallet, Pix',
  `vReceiptJson`     LONGTEXT         NOT NULL COMMENT 'Full receipt data as JSON',
  `vPdfPath`         VARCHAR(255)     DEFAULT NULL COMMENT 'Generated PDF file path',
  `eSentToEmail`     ENUM('Yes','No','NA') NOT NULL DEFAULT 'No',
  `dSentAt`          DATETIME         DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iReceiptId`),
  KEY `idx_user` (`iUserId`),
  KEY `idx_reference` (`eReceiptType`, `iReferenceId`),
  KEY `idx_number` (`vReceiptNumber`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Auto-generated in-app receipts for trips and orders';

-- ============================================================
-- FEATURE 9: FRANCHISE MANAGEMENT SYSTEM
-- Independent city-based management with Master vs Franchisee levels
-- ============================================================

CREATE TABLE IF NOT EXISTS `franchises` (
  `iFranchiseId`     INT(11)          NOT NULL AUTO_INCREMENT,
  `vFranchiseName`   VARCHAR(150)     NOT NULL,
  `vCity`            VARCHAR(100)     NOT NULL,
  `vState`           VARCHAR(100)     NOT NULL DEFAULT '',
  `vCountry`         VARCHAR(100)     NOT NULL DEFAULT 'Brazil',
  `vCoverageAreaJson` LONGTEXT        DEFAULT NULL COMMENT 'GeoJSON polygon of franchise coverage area',
  `fRevenueSharePercent` DECIMAL(5,2) NOT NULL DEFAULT 10.00 COMMENT 'Franchisee cut from each trip',
  `fMasterSharePercent`  DECIMAL(5,2) NOT NULL DEFAULT 15.00 COMMENT 'Master platform cut',
  `fDriverSharePercent`  DECIMAL(5,2) NOT NULL DEFAULT 75.00 COMMENT 'Driver cut',
  `eStatus`          ENUM('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  `vPagarmeRecipientId` VARCHAR(100)  DEFAULT NULL COMMENT 'Pagar.me recipient ID for this franchise',
  `vEfiClientId`     VARCHAR(200)     DEFAULT NULL COMMENT 'EfiPay client credentials for this franchise',
  `vEfiClientSecret` VARCHAR(200)     DEFAULT NULL,
  `iMasterAdminId`   INT(11)          DEFAULT NULL COMMENT 'Master admin who owns this franchise',
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  `dUpdatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`iFranchiseId`),
  KEY `idx_city` (`vCity`),
  KEY `idx_status` (`eStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Franchise city territories';

CREATE TABLE IF NOT EXISTS `franchise_users` (
  `iFranchiseUserId` INT(11)          NOT NULL AUTO_INCREMENT,
  `iFranchiseId`     INT(11)          NOT NULL,
  `vName`            VARCHAR(100)     NOT NULL,
  `vEmail`           VARCHAR(150)     NOT NULL UNIQUE,
  `vPassword`        VARCHAR(255)     NOT NULL,
  `vPhone`           VARCHAR(20)      DEFAULT NULL,
  `eRole`            ENUM('Master','Franchisee','Operator') NOT NULL DEFAULT 'Franchisee',
  `eStatus`          ENUM('Active','Inactive','Suspended') NOT NULL DEFAULT 'Active',
  `vPermissionsJson` LONGTEXT         DEFAULT NULL COMMENT 'JSON array of allowed admin actions',
  `dLastLogin`       DATETIME         DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iFranchiseUserId`),
  KEY `idx_franchise` (`iFranchiseId`),
  KEY `idx_role` (`eRole`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Franchise admins with role-based access';

CREATE TABLE IF NOT EXISTS `franchise_driver_map` (
  `iMapId`           INT(11)          NOT NULL AUTO_INCREMENT,
  `iFranchiseId`     INT(11)          NOT NULL,
  `iDriverId`        INT(11)          NOT NULL,
  `eStatus`          ENUM('Active','Inactive') NOT NULL DEFAULT 'Active',
  `dAssignedAt`      DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iMapId`),
  UNIQUE KEY `uq_franchise_driver` (`iFranchiseId`, `iDriverId`),
  KEY `idx_driver` (`iDriverId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Driver-to-franchise assignment';

-- ============================================================
-- FEATURE 10: PAGAR.ME SPLIT PAYMENT
-- Automated real-time payout division
-- ============================================================

CREATE TABLE IF NOT EXISTS `pagarme_split_logs` (
  `iSplitLogId`      INT(11)          NOT NULL AUTO_INCREMENT,
  `iRequestId`       INT(11)          NOT NULL,
  `iTripId`          INT(11)          NOT NULL DEFAULT 0,
  `iFranchiseId`     INT(11)          DEFAULT NULL,
  `iDriverId`        INT(11)          NOT NULL,
  `iUserId`          INT(11)          NOT NULL,
  `fTotalAmount`     DECIMAL(14,2)    NOT NULL,
  `fMasterAmount`    DECIMAL(14,2)    NOT NULL DEFAULT 0.00 COMMENT 'Platform Master cut',
  `fFranchiseeAmount` DECIMAL(14,2)   NOT NULL DEFAULT 0.00 COMMENT 'Franchisee cut',
  `fDriverAmount`    DECIMAL(14,2)    NOT NULL DEFAULT 0.00 COMMENT 'Driver payout',
  `vPagarmeOrderId`  VARCHAR(100)     DEFAULT NULL COMMENT 'Pagar.me order ID',
  `vPagarmeChargeId` VARCHAR(100)     DEFAULT NULL,
  `vPagarmeTransactionId` VARCHAR(100) DEFAULT NULL,
  `eStatus`          ENUM('Pending','Processing','Completed','Failed','Refunded') NOT NULL DEFAULT 'Pending',
  `vRawResponseJson` LONGTEXT         DEFAULT NULL COMMENT 'Full Pagar.me API response',
  `vErrorMessage`    TEXT             DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  `dProcessedAt`     DATETIME         DEFAULT NULL,
  PRIMARY KEY (`iSplitLogId`),
  KEY `idx_request` (`iRequestId`),
  KEY `idx_franchise` (`iFranchiseId`),
  KEY `idx_status` (`eStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Pagar.me real-time split payment logs';

-- ============================================================
-- FEATURE 11: B2B BILLING MODELS (EfiPay)
-- Fixed monthly fee + Tiered overage billing for franchisees
-- ============================================================

CREATE TABLE IF NOT EXISTS `franchise_billing_plans` (
  `iPlanId`          INT(11)          NOT NULL AUTO_INCREMENT,
  `iFranchiseId`     INT(11)          NOT NULL,
  `ePlanType`        ENUM('FixedMonthly','TieredOverage') NOT NULL DEFAULT 'FixedMonthly',
  `fMonthlyFee`      DECIMAL(14,2)    NOT NULL DEFAULT 0.00 COMMENT 'For FixedMonthly plan',
  `iMonthlyTripQuota` INT(11)         NOT NULL DEFAULT 0 COMMENT 'For TieredOverage: trips included',
  `fOverageFeePerTrip` DECIMAL(14,2)  NOT NULL DEFAULT 0.00 COMMENT 'Fee per trip above quota',
  `vEfiSubscriptionId` VARCHAR(200)   DEFAULT NULL COMMENT 'EfiPay subscription ID',
  `vEfiPlanId`       VARCHAR(200)     DEFAULT NULL COMMENT 'EfiPay plan ID',
  `eStatus`          ENUM('Active','Inactive','Cancelled') NOT NULL DEFAULT 'Active',
  `dNextBillingDate` DATE             DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iPlanId`),
  KEY `idx_franchise` (`iFranchiseId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='B2B billing plan configurations per franchise';

CREATE TABLE IF NOT EXISTS `franchise_billing_invoices` (
  `iInvoiceId`       INT(11)          NOT NULL AUTO_INCREMENT,
  `iFranchiseId`     INT(11)          NOT NULL,
  `iPlanId`          INT(11)          NOT NULL,
  `vInvoiceNumber`   VARCHAR(50)      NOT NULL UNIQUE,
  `iBillingPeriodMonth` TINYINT(2)    NOT NULL COMMENT '1-12',
  `iBillingPeriodYear`  SMALLINT(4)   NOT NULL,
  `iTripsCompleted`  INT(11)          NOT NULL DEFAULT 0,
  `iTripsIncluded`   INT(11)          NOT NULL DEFAULT 0,
  `iTripsOverage`    INT(11)          NOT NULL DEFAULT 0,
  `fBaseFee`         DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `fOverageFee`      DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `fTotalAmount`     DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `eStatus`          ENUM('Draft','Sent','Paid','Overdue','Cancelled') NOT NULL DEFAULT 'Draft',
  `vEfiChargeId`     VARCHAR(200)     DEFAULT NULL COMMENT 'EfiPay charge ID',
  `vEfiPaymentLink`  VARCHAR(500)     DEFAULT NULL COMMENT 'Payment link from EfiPay',
  `dDueDate`         DATE             DEFAULT NULL,
  `dPaidAt`          DATETIME         DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iInvoiceId`),
  KEY `idx_franchise` (`iFranchiseId`),
  KEY `idx_period` (`iBillingPeriodYear`, `iBillingPeriodMonth`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Monthly B2B billing invoices for franchisees';

-- ============================================================
-- FEATURE 12: AUTOMATED PENALTY TRANSFERS
-- Cancellation fees credited directly to penalized driver's wallet
-- ============================================================

CREATE TABLE IF NOT EXISTS `penalty_transfer_logs` (
  `iPenaltyLogId`    INT(11)          NOT NULL AUTO_INCREMENT,
  `iRequestId`       INT(11)          NOT NULL,
  `iDriverId`        INT(11)          NOT NULL COMMENT 'Driver who incurred penalty',
  `iUserId`          INT(11)          NOT NULL,
  `ePenaltyType`     ENUM('NoShow','Cancellation','LateArrival','Custom') NOT NULL DEFAULT 'Cancellation',
  `fPenaltyAmount`   DECIMAL(14,2)    NOT NULL DEFAULT 0.00,
  `vCurrency`        VARCHAR(10)      NOT NULL DEFAULT 'BRL',
  `eTransferTo`      ENUM('DriverWallet','Passenger','Platform') NOT NULL DEFAULT 'DriverWallet' COMMENT 'Where penalty proceeds go',
  `iWalletTransactionId` INT(11)      DEFAULT NULL COMMENT 'FK to wallet transaction if applicable',
  `eStatus`          ENUM('Pending','Transferred','Failed','Reversed') NOT NULL DEFAULT 'Pending',
  `vNote`            TEXT             DEFAULT NULL,
  `dTransferredAt`   DATETIME         DEFAULT NULL,
  `dCreatedAt`       DATETIME         NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`iPenaltyLogId`),
  KEY `idx_request` (`iRequestId`),
  KEY `idx_driver` (`iDriverId`),
  KEY `idx_status` (`eStatus`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COMMENT='Automated penalty fee transfers to driver wallet';

-- ============================================================
-- Indexes and Foreign Key Hints (FK not enforced for performance)
-- ============================================================

-- ride_bid_rounds -> ride_bid_negotiations
ALTER TABLE `ride_bid_rounds`
  ADD CONSTRAINT `fk_rbn_negotiation` FOREIGN KEY (`iNegotiationId`)
    REFERENCES `ride_bid_negotiations` (`iNegotiationId`) ON DELETE CASCADE;

-- lost_found_messages -> lost_found_tickets
ALTER TABLE `lost_found_messages`
  ADD CONSTRAINT `fk_lfm_ticket` FOREIGN KEY (`iTicketId`)
    REFERENCES `lost_found_tickets` (`iTicketId`) ON DELETE CASCADE;

-- franchise_billing_invoices -> franchise_billing_plans
ALTER TABLE `franchise_billing_invoices`
  ADD CONSTRAINT `fk_fbi_plan` FOREIGN KEY (`iPlanId`)
    REFERENCES `franchise_billing_plans` (`iPlanId`) ON DELETE RESTRICT;

COMMIT;

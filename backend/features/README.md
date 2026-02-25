# Ridey App – New Features Module

## Overview

This directory contains all 12 new features implemented for the Ridey platform.
Each feature is a self-contained PHP class with a corresponding webservice API.

---

## Features Implemented

| # | Feature | Class File | API type |
|---|---------|------------|----------|
| 1 | Ride Bidding (InDrive-style auction) | `RideBidding.php` | `rideBidding` |
| 2 | Book for Others | `BookForOthers.php` | `bookForOthers` |
| 3 | Proportional Cancellation Fee | `ProportionalCancellation.php` | `cancelFee` |
| 4 | Verified No-Show Fee | `NoShowVerification.php` | `noShow` |
| 5 | Lost & Found Chat | `LostAndFound.php` | `lostFound` |
| 6 | Facial Recognition | `FacialRecognition.php` | `facialRecognition` |
| 7 | Smart Push Notifications | `SmartNotifications.php` | `notification` |
| 8 | In-App Receipts | `InAppReceipts.php` | `receipt` |
| 9 | Franchise Management System | `FranchiseManagement.php` | `franchise` |
| 10 | Pagar.me Split Payment | `PagarmePayment.php` | `pagarme` |
| 11 | B2B Billing (EfiPay) | `EfiBilling.php` | `efiBilling` |
| 12 | Automated Penalty Transfers | `PenaltyTransfer.php` | `penalty` |

---

## Setup

### 1. Run Database Migration

```sql
-- Import into ridey_dudeapps database:
mysql -u root -p ridey_dudeapps < backend/features/features_migration.sql
```

### 2. Configure Constants

Add to `backend/app_configuration_file.php`:

```php
// Facial Recognition
define('FACE_PROVIDER', 'AWSRekognition'); // or 'FacePlusPlus' or 'Azure'
define('FACE_SIMILARITY_THRESHOLD', 90);
define('FACE_AWS_KEY', 'YOUR_AWS_ACCESS_KEY');
define('FACE_AWS_SECRET', 'YOUR_AWS_SECRET');
define('FACE_AWS_REGION', 'us-east-1');

// Pagar.me
define('PAGARME_SECRET_KEY', 'sk_YOUR_KEY');
define('PAGARME_PUBLIC_KEY', 'pk_YOUR_KEY');
define('PAGARME_MASTER_RECIPIENT_ID', 'rp_YOUR_MASTER_RECIPIENT');

// EfiPay (B2B Billing)
define('EFI_CLIENT_ID', 'Client_Id_...');
define('EFI_CLIENT_SECRET', 'Client_Secret_...');
define('EFI_SANDBOX', 'Yes'); // Change to 'No' for production
```

### 3. Cron Jobs

Add to server crontab:

```bash
# Smart Push Notifications (every minute)
* * * * * php /var/www/backend/features/cron_smart_notifications.php >> /var/log/ridey/smart_notif.log 2>&1

# Monthly B2B Billing Invoices (1st of each month at 9am)
0 9 1 * * php /var/www/backend/features/cron_billing_invoices.php >> /var/log/ridey/billing.log 2>&1
```

---

## API Usage

All features are accessible via `webservice_shark.php` with the `type` parameter.

### Feature 1: Ride Bidding

```
POST webservice_shark.php
type=rideBidding
bid_action=passengerInitiate
iRequestId=123
iUserId=456
iDriverId=789
fPassengerOffer=25.00
```

**Actions:** `passengerInitiate` | `driverCounter` | `passengerCounter` | `acceptBid` | `rejectBid` | `getBidStatus` | `getActiveBids`

---

### Feature 2: Book for Others

```
POST webservice_shark.php
type=bookForOthers
book_others_action=create
iRequestId=123
iUserId=456
vBeneficiaryName=João Silva
vBeneficiaryPhone=11999999999
vBeneficiaryCountryCode=+55
eRelationship=Family
eNotifyBeneficiary=Yes
```

**Actions:** `create` | `getMyBookings` | `getBookingDetail` | `cancel` | `updateBeneficiary`

---

### Feature 3: Proportional Cancellation Fee

```
POST webservice_shark.php
type=cancelFee
cancel_action=recordCancel
iRequestId=123
iDriverId=789
iUserId=456
vDriverStartLatLng=-23.5505,-46.6333
vDriverCancelLatLng=-23.5550,-46.6350
vPickupLatLng=-23.5600,-46.6400
fBaseCancellationFee=10.00
eWho=Passenger
```

**Actions:** `calculateFee` | `recordCancel` | `waiveFee` | `getCancelDetail`

---

### Feature 4: Verified No-Show Fee

```
POST webservice_shark.php
type=noShow
noshow_action=driverArrived
iRequestId=123
iDriverId=789
iUserId=456
vDriverLatLng=-23.5505,-46.6333
vPickupLatLng=-23.5506,-46.6334
```

**Actions:** `driverArrived` | `reportNoShow` | `addGpsProof` | `adminApprove` | `adminReject` | `getIncident` | `getPendingList`

---

### Feature 5: Lost & Found Chat

```
POST webservice_shark.php
type=lostFound
laf_action=openTicket
iUserId=456
iDriverId=789
iTripId=100
vItemDescription=iPhone 15 Pro Max, black case
vItemCategory=Phone
```

**Actions:** `openTicket` | `sendMessage` | `getMessages` | `getTickets` | `getTicketDetail` | `updateStatus` | `createReturnTrip` | `uploadImage` | `closeTicket`

---

### Feature 6: Facial Recognition

```
POST webservice_shark.php (multipart/form-data)
type=facialRecognition
face_action=verify
iDriverId=789
eEventType=Login
[file] liveImage = <photo.jpg>
```

**Actions:** `uploadReference` | `verify` | `getHistory` | `getSettings`

---

### Feature 7: Smart Push Notifications

```
POST webservice_shark.php
type=notification
notif_action=create
vTitle=Special Offer
vBody=Get 20% off your next ride!
eTargetType=AllUsers
eScheduleType=Recurring
eRepeatInterval=Daily
iRepeatCount=7
```

**Actions:** `create` | `send` | `pause` | `cancel` | `resume` | `getList` | `getDetail` | `getStats` | `processQueue`

---

### Feature 8: In-App Receipts

```
POST webservice_shark.php
type=receipt
receipt_action=generate
eReceiptType=Trip
iReferenceId=123
iUserId=456
iDriverId=789
```

**Actions:** `generate` | `get` | `getMyReceipts` | `emailReceipt` | `generatePdf`

---

### Feature 9: Franchise Management

```
POST webservice_shark.php
type=franchise
franchise_action=create
vFranchiseName=Ridey São Paulo
vCity=São Paulo
vState=SP
fMasterSharePercent=15
fRevenueSharePercent=10
fDriverSharePercent=75
```

**Actions:** `create` | `update` | `getList` | `getDetail` | `suspend` | `activate` | `addUser` | `updateUser` | `listUsers` | `assignDriver` | `unassignDriver` | `listDrivers` | `getDashboard` | `updateRevenueShares` | `loginFranchiseUser`

---

### Feature 10: Pagar.me Split Payment

```
POST webservice_shark.php
type=pagarme
pagarme_action=processRidePayment
iRequestId=123
iTripId=100
iDriverId=789
iUserId=456
fTotalAmount=35.50
iFranchiseId=1
vPaymentToken=tok_...
```

**Actions:** `processRidePayment` | `createRecipient` | `getSplitDetail` | `refundPayment` | `getRecipientBalance`

---

### Feature 11: B2B Billing (EfiPay)

```
POST webservice_shark.php
type=efiBilling
billing_action=createPlan
iFranchiseId=1
ePlanType=TieredOverage
iMonthlyTripQuota=500
fOverageFeePerTrip=0.50
```

**Actions:** `createPlan` | `updatePlan` | `getPlan` | `generateMonthlyInvoice` | `getInvoice` | `getInvoiceList` | `createEfiSubscription` | `cancelSubscription` | `processOverage` | `webhookEfi`

---

### Feature 12: Automated Penalty Transfers

```
POST webservice_shark.php
type=penalty
penalty_action=transfer
iRequestId=123
iDriverId=789
iUserId=456
fPenaltyAmount=8.50
ePenaltyType=Cancellation
eTransferTo=DriverWallet
```

**Actions:** `transfer` | `reverse` | `getLog` | `getDriverLog` | `getStats`

---

## Admin Panel Pages

| Page | URL | Description |
|------|-----|-------------|
| Franchise List | `admin703/franchise_list.php` | View all franchise territories |
| Add/Edit Franchise | `admin703/franchise_add.php` | Create or edit franchise |
| Franchise Billing | `admin703/franchise_billing.php?id=X` | Manage billing plans and invoices |
| No-Show Review | `admin703/no_show_review.php` | GPS-verified no-show approvals |
| Lost & Found | `admin703/lost_found_admin.php` | Manage lost item tickets |
| Notifications | `admin703/smart_notifications_admin.php` | Schedule and send push notifications |

---

## Architecture Notes

- All feature classes accept `$obj` (MySQLi wrapper) and `$tconfig` as constructor arguments.
- Each class has a `handleRequest(array $req)` dispatcher that reads `{feature}_action` from the request.
- Push notifications use FCM via the existing `sendPushNotification()` function if available.
- Wallet operations use existing `chargeUserWallet()` / `addDriverWalletCredit()` if available, with DB fallbacks.
- The `ProportionalCancellation` and `NoShowVerification` classes automatically call `PenaltyTransfer` to credit drivers.

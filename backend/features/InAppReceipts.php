<?php
/**
 * FEATURE 8: IN-APP RECEIPTS
 * ---------------------------
 * Automated generation of trip and order summaries within the user app.
 * Generates structured JSON receipts with human-readable receipt numbers.
 * Optional PDF generation (uses TCPDF already in project).
 * Optional email delivery.
 *
 * Integration: Call after trip completion / order delivery.
 */

class InAppReceipts
{
    private $db;
    private $config;

    public function __construct($db, array $config)
    {
        $this->db     = $db;
        $this->config = $config;
    }

    public function handleRequest(array $req): string
    {
        $action = trim($req['receipt_action'] ?? '');

        switch ($action) {
            case 'generate':      return $this->generateReceipt($req);
            case 'get':           return $this->getReceipt($req);
            case 'getMyReceipts': return $this->getMyReceipts($req);
            case 'emailReceipt':  return $this->emailReceipt($req);
            case 'generatePdf':   return $this->generatePdf($req);
            default:
                return $this->error('Unknown receipt_action.');
        }
    }

    // ----------------------------------------------------------------
    // 1. Generate a receipt for a completed trip or order
    // ----------------------------------------------------------------
    public function generateReceipt(array $req): string
    {
        $receiptType = trim($req['eReceiptType'] ?? 'Trip');
        $referenceId = (int)($req['iReferenceId'] ?? 0);
        $userId      = (int)($req['iUserId']      ?? 0);
        $driverId    = (int)($req['iDriverId']    ?? 0);

        if (!$receiptType || !$referenceId || !$userId) {
            return $this->error('eReceiptType, iReferenceId and iUserId required.');
        }

        // Check if receipt already exists
        $existing = $this->db->MySQLSelect(
            "SELECT iReceiptId, vReceiptNumber FROM in_app_receipts
             WHERE eReceiptType='$receiptType' AND iReferenceId=$referenceId AND iUserId=$userId
             LIMIT 1"
        );
        if (!empty($existing)) {
            return $this->success(['iReceiptId' => $existing[0]['iReceiptId'],
                                   'vReceiptNumber' => $existing[0]['vReceiptNumber'],
                                   'message' => 'Receipt already exists.']);
        }

        // Build receipt data based on type
        $receiptData = $this->buildReceiptData($receiptType, $referenceId, $userId, $driverId);
        if (empty($receiptData)) {
            return $this->error('Could not retrieve trip/order data to generate receipt.');
        }

        $receiptNumber = $this->generateReceiptNumber($receiptType);
        $receiptJson   = json_encode($receiptData, JSON_UNESCAPED_UNICODE);
        $currency      = $receiptData['currency']      ?? 'BRL';
        $paymentMethod = $receiptData['paymentMethod'] ?? 'Cash';

        $subtotal = (float)($receiptData['subtotal']       ?? 0);
        $discount = (float)($receiptData['discount']       ?? 0);
        $tax      = (float)($receiptData['tax']            ?? 0);
        $tip      = (float)($receiptData['tip']            ?? 0);
        $total    = (float)($receiptData['total']          ?? ($subtotal - $discount + $tax + $tip));

        $rNum    = addslashes($receiptNumber);
        $rJson   = addslashes($receiptJson);
        $pmeth   = addslashes($paymentMethod);
        $curr    = addslashes($currency);
        $now     = date('Y-m-d H:i:s');
        $drv     = $driverId ?: 'NULL';

        $this->db->sql_query(
            "INSERT INTO in_app_receipts
                (vReceiptNumber, eReceiptType, iReferenceId, iUserId, iDriverId,
                 fSubtotal, fDiscount, fTax, fTip, fTotal, vCurrency, ePaymentMethod,
                 vReceiptJson, eSentToEmail, dCreatedAt)
             VALUES
                ('$rNum', '$receiptType', $referenceId, $userId, $drv,
                 $subtotal, $discount, $tax, $tip, $total, '$curr', '$pmeth',
                 '$rJson', 'No', '$now')"
        );
        $receiptId = $this->db->MySQLLastInsertID();

        return $this->success([
            'iReceiptId'     => $receiptId,
            'vReceiptNumber' => $receiptNumber,
            'receipt'        => $receiptData,
            'fTotal'         => $total,
            'vCurrency'      => $currency,
        ]);
    }

    // ----------------------------------------------------------------
    // 2. Fetch a receipt by ID or receipt number
    // ----------------------------------------------------------------
    public function getReceipt(array $req): string
    {
        $receiptId  = (int)($req['iReceiptId']      ?? 0);
        $receiptNum = trim($req['vReceiptNumber']   ?? '');

        if (!$receiptId && !$receiptNum) return $this->error('iReceiptId or vReceiptNumber required.');

        $where = $receiptId ? "iReceiptId=$receiptId" : "vReceiptNumber='" . addslashes($receiptNum) . "'";
        $rows  = $this->db->MySQLSelect("SELECT * FROM in_app_receipts WHERE $where LIMIT 1");

        if (empty($rows)) return $this->error('Receipt not found.');

        $receipt = $rows[0];
        $receipt['receiptData'] = json_decode($receipt['vReceiptJson'], true);

        return $this->success(['receipt' => $receipt]);
    }

    // ----------------------------------------------------------------
    // 3. Get all receipts for a user
    // ----------------------------------------------------------------
    public function getMyReceipts(array $req): string
    {
        $userId      = (int)($req['iUserId']      ?? 0);
        $receiptType = trim($req['eReceiptType']  ?? '');
        $page        = max(1, (int)($req['iPage'] ?? 1));
        $limit       = 20;
        $offset      = ($page - 1) * $limit;

        if (!$userId) return $this->error('iUserId required.');

        $typeWhere = $receiptType ? "AND eReceiptType='" . addslashes($receiptType) . "'" : '';

        $receipts = $this->db->MySQLSelect(
            "SELECT iReceiptId, vReceiptNumber, eReceiptType, iReferenceId,
                    fTotal, vCurrency, ePaymentMethod, eSentToEmail, dCreatedAt
             FROM in_app_receipts
             WHERE iUserId=$userId $typeWhere
             ORDER BY dCreatedAt DESC
             LIMIT $limit OFFSET $offset"
        );

        return $this->success(['receipts' => $receipts ?: [], 'page' => $page]);
    }

    // ----------------------------------------------------------------
    // 4. Send receipt to passenger's email
    // ----------------------------------------------------------------
    public function emailReceipt(array $req): string
    {
        $receiptId = (int)($req['iReceiptId'] ?? 0);
        if (!$receiptId) return $this->error('iReceiptId required.');

        $rows = $this->db->MySQLSelect(
            "SELECT iar.*, r.vEmail, r.vName
             FROM in_app_receipts iar
             LEFT JOIN register r ON r.iUserId = iar.iUserId
             WHERE iar.iReceiptId=$receiptId LIMIT 1"
        );
        if (empty($rows)) return $this->error('Receipt not found.');

        $rec   = $rows[0];
        $email = $rec['vEmail'];
        $name  = $rec['vName'] ?? 'Passenger';

        if (!$email) return $this->error('No email address on record for this user.');

        $subject = "Your Ridey Receipt – {$rec['vReceiptNumber']}";
        $body    = $this->buildEmailBody($rec, $name);

        $sent = false;
        if (function_exists('sendMail')) {
            $sent = sendMail($email, $subject, $body);
        } elseif (function_exists('mail')) {
            $headers  = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=UTF-8\r\n";
            $headers .= "From: noreply@ridey.app\r\n";
            $sent = mail($email, $subject, $body, $headers);
        }

        if ($sent) {
            $now = date('Y-m-d H:i:s');
            $this->db->sql_query(
                "UPDATE in_app_receipts SET eSentToEmail='Yes', dSentAt='$now' WHERE iReceiptId=$receiptId"
            );
        }

        return $this->success(['sent' => $sent, 'email' => $email, 'message' => $sent ? 'Email sent.' : 'Email failed.']);
    }

    // ----------------------------------------------------------------
    // 5. Generate PDF (uses TCPDF already in the project)
    // ----------------------------------------------------------------
    public function generatePdf(array $req): string
    {
        $receiptId = (int)($req['iReceiptId'] ?? 0);
        if (!$receiptId) return $this->error('iReceiptId required.');

        $rows = $this->db->MySQLSelect("SELECT * FROM in_app_receipts WHERE iReceiptId=$receiptId LIMIT 1");
        if (empty($rows)) return $this->error('Receipt not found.');

        $rec         = $rows[0];
        $receiptData = json_decode($rec['vReceiptJson'], true);

        $tcpdfPath = dirname(__DIR__) . '/admin703/TCPDF-master/tcpdf.php';
        if (!file_exists($tcpdfPath)) {
            return $this->error('PDF library not available. Check TCPDF installation.');
        }

        require_once $tcpdfPath;

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Ridey App');
        $pdf->SetAuthor('Ridey');
        $pdf->SetTitle('Receipt ' . $rec['vReceiptNumber']);
        $pdf->SetSubject('Trip Receipt');
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetMargins(15, 15, 15);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 16);

        $html = $this->buildPdfHtml($rec, $receiptData);
        $pdf->writeHTML($html, true, false, true, false, '');

        $pdfDir = dirname(__DIR__) . '/webimages/receipts/';
        if (!is_dir($pdfDir)) mkdir($pdfDir, 0755, true);

        $filename = 'receipt_' . $rec['vReceiptNumber'] . '.pdf';
        $fullPath = $pdfDir . $filename;
        $pdf->Output($fullPath, 'F');

        $relativePath = 'webimages/receipts/' . $filename;
        $this->db->sql_query(
            "UPDATE in_app_receipts SET vPdfPath='" . addslashes($relativePath) . "'
             WHERE iReceiptId=$receiptId"
        );

        return $this->success(['vPdfPath' => $relativePath, 'message' => 'PDF generated.']);
    }

    // ----------------------------------------------------------------
    // Build receipt data from DB (trip or order)
    // ----------------------------------------------------------------
    private function buildReceiptData(string $type, int $refId, int $userId, int $driverId): array
    {
        $data = [];

        if ($type === 'Trip') {
            // Fetch from driver_request / trip tables
            $trip = $this->db->MySQLSelect(
                "SELECT dr.*, r.vName AS passengerName, r.vEmail,
                        CONCAT(rd.vName,' ',rd.vLastName) AS driverName
                 FROM driver_request dr
                 LEFT JOIN register r ON r.iUserId=dr.iUserId
                 LEFT JOIN register_driver rd ON rd.iDriverId=dr.iDriverId
                 WHERE dr.iRequestId=$refId LIMIT 1"
            );
            if (empty($trip)) return [];
            $t = $trip[0];

            $data = [
                'type'          => 'Trip',
                'receiptFor'    => 'Ride',
                'tripId'        => $refId,
                'passengerName' => $t['passengerName']  ?? 'Passenger',
                'driverName'    => $t['driverName']     ?? 'Driver',
                'pickup'        => $t['tStartAddress']  ?? '',
                'dropoff'       => $t['tEndAddress']    ?? '',
                'date'          => $t['dAddedDate']     ?? date('Y-m-d H:i:s'),
                'subtotal'      => (float)$t['fTaxiBidAmount'],
                'discount'      => 0.00,
                'tax'           => 0.00,
                'tip'           => 0.00,
                'total'         => (float)$t['fTaxiBidAmount'],
                'currency'      => 'BRL',
                'paymentMethod' => 'Cash',
            ];
        }

        if ($type === 'Cancellation') {
            $fee = $this->db->MySQLSelect(
                "SELECT * FROM proportional_cancellation_fee WHERE iRequestId=$refId LIMIT 1"
            );
            if (empty($fee)) return [];
            $f = $fee[0];
            $data = [
                'type'          => 'Cancellation',
                'receiptFor'    => 'Cancellation Fee',
                'requestId'     => $refId,
                'distanceTraveled' => $f['fDistanceTraveled'],
                'proportion'    => $f['fProportionTraveled'],
                'subtotal'      => (float)$f['fChargedFee'],
                'discount'      => 0.00,
                'tax'           => 0.00,
                'tip'           => 0.00,
                'total'         => (float)$f['fChargedFee'],
                'currency'      => 'BRL',
                'paymentMethod' => 'Wallet',
                'date'          => $f['dCancelledAt'],
            ];
        }

        return $data;
    }

    private function generateReceiptNumber(string $type): string
    {
        $prefix = match ($type) {
            'Trip'         => 'TR',
            'Order'        => 'OR',
            'Bidding'      => 'BD',
            'Cancellation' => 'CN',
            default        => 'RC',
        };
        return $prefix . '-' . date('Ymd') . '-' . str_pad((string)rand(1, 99999), 5, '0', STR_PAD_LEFT);
    }

    private function buildEmailBody(array $rec, string $name): string
    {
        $data     = json_decode($rec['vReceiptJson'], true);
        $total    = number_format((float)$rec['fTotal'], 2, ',', '.');
        $currency = $rec['vCurrency'];
        $rNum     = $rec['vReceiptNumber'];

        $rows = '';
        foreach ($data as $k => $v) {
            if (in_array($k, ['type', 'currency', 'paymentMethod'])) continue;
            $label = ucwords(str_replace('_', ' ', $k));
            $val   = is_array($v) ? json_encode($v) : htmlspecialchars((string)$v);
            $rows .= "<tr><td style='padding:4px 8px;border-bottom:1px solid #eee'><b>$label</b></td>"
                   . "<td style='padding:4px 8px;border-bottom:1px solid #eee'>$val</td></tr>";
        }

        return <<<HTML
<html><body style='font-family:Arial,sans-serif;color:#333;max-width:600px;margin:auto'>
<h2 style='background:#2563eb;color:#fff;padding:16px;border-radius:8px 8px 0 0;margin:0'>
  Ridey – Receipt #{$rNum}</h2>
<div style='padding:16px'>
  <p>Dear {$name},</p>
  <p>Thank you for using Ridey! Here's your receipt summary:</p>
  <table width='100%' cellspacing='0' cellpadding='0' style='border-collapse:collapse'>
    $rows
    <tr style='background:#f3f4f6'><td colspan='2' style='padding:8px;font-size:18px'>
      <b>Total: {$currency} {$total}</b></td></tr>
  </table>
  <p style='margin-top:16px;color:#6b7280;font-size:12px'>
    Payment method: {$rec['ePaymentMethod']}<br>
    Date: {$rec['dCreatedAt']}
  </p>
</div></body></html>
HTML;
    }

    private function buildPdfHtml(array $rec, array $receiptData): string
    {
        $total  = number_format((float)$rec['fTotal'], 2, ',', '.');
        $rows   = '';
        foreach ($receiptData as $k => $v) {
            if (in_array($k, ['type', 'currency', 'paymentMethod'])) continue;
            $label = ucwords(str_replace('_', ' ', $k));
            $val   = is_array($v) ? json_encode($v) : htmlspecialchars((string)$v);
            $rows .= "<tr><td><b>$label</b></td><td>$val</td></tr>";
        }
        return <<<HTML
<h1>Ridey Receipt</h1>
<p><b>Receipt #:</b> {$rec['vReceiptNumber']}</p>
<p><b>Date:</b> {$rec['dCreatedAt']}</p>
<table border='1' cellpadding='4' cellspacing='0' width='100%'>$rows</table>
<p><b>Total: {$rec['vCurrency']} {$total}</b></p>
<p>Payment: {$rec['ePaymentMethod']}</p>
HTML;
    }

    private function success(array $data): string
    {
        return json_encode(array_merge(['status' => 'success'], $data));
    }

    private function error(string $message): string
    {
        return json_encode(['status' => 'error', 'message' => $message]);
    }
}
